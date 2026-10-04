<?php
// api/residents.php - RESTful endpoints for BRIS Resident Management & Analytics

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/db.php';
$pdo = getDbConnection();

$method = $_SERVER['REQUEST_METHOD'];

// Parse URL path info for RESTful resource routing
// e.g. /api/residents.php/123 or /api/residents.php/analytics
$requestUri = $_SERVER['REQUEST_URI'];
$scriptName = $_SERVER['SCRIPT_NAME'];

$pathInfo = '';
if (strpos($requestUri, $scriptName) === 0) {
    $pathInfo = substr($requestUri, strlen($scriptName));
} else {
    $pathInfo = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
}

$pathInfo = trim(parse_url($pathInfo, PHP_URL_PATH), '/');
$pathSegments = $pathInfo !== '' ? explode('/', $pathInfo) : [];

$resourceId = null;
$action = isset($_GET['action']) ? $_GET['action'] : '';

if (count($pathSegments) > 0) {
    if (is_numeric($pathSegments[0])) {
        $resourceId = (int)$pathSegments[0];
        if (isset($pathSegments[1])) {
            $action = $pathSegments[1];
        }
    } else {
        $action = $pathSegments[0];
        if (isset($pathSegments[1]) && is_numeric($pathSegments[1])) {
            $resourceId = (int)$pathSegments[1];
        }
    }
}

if (!$resourceId && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $resourceId = (int)$_GET['id'];
}

switch ($method) {
    case 'GET':
        if ($action === 'analytics') {
            getAnalytics($pdo);
        } else if ($resourceId) {
            getResidentById($pdo, $resourceId);
        } else {
            getResidentsList($pdo);
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            $data = $_POST;
        }

        if ($action === 'archive') {
            archiveResident($pdo, $data, $resourceId);
        } else if ($action === 'restore') {
            restoreResident($pdo, $data, $resourceId);
        } else if ($action === 'remove') {
            softRemoveResident($pdo, $data, $resourceId);
        } else {
            createResident($pdo, $data);
        }
        break;

    case 'PUT':
    case 'PATCH':
        $data = json_decode(file_get_contents('php://input'), true);
        if ($resourceId && !isset($data['id'])) {
            $data['id'] = $resourceId;
        }

        if ($action === 'archive') {
            archiveResident($pdo, $data, $resourceId);
        } else if ($action === 'restore') {
            restoreResident($pdo, $data, $resourceId);
        } else if ($action === 'remove') {
            softRemoveResident($pdo, $data, $resourceId);
        } else {
            updateResident($pdo, $data);
        }
        break;

    case 'DELETE':
        softRemoveResident($pdo, [], $resourceId);
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method Not Allowed']);
        break;
}

function getResidentsList($pdo) {
    // archived: 0 = Active, 1 = Archived (Moved Out / Deceased), 2 = Removed
    $archived = isset($_GET['archived']) ? (int)$_GET['archived'] : 0;
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $purok = isset($_GET['purok']) ? trim($_GET['purok']) : '';
    $sex = isset($_GET['sex']) ? trim($_GET['sex']) : '';
    $civil_status = isset($_GET['civil_status']) ? trim($_GET['civil_status']) : '';
    $voter_status = isset($_GET['voter_status']) ? trim($_GET['voter_status']) : '';
    $age_group = isset($_GET['age_group']) ? trim($_GET['age_group']) : '';

    $where = ["archived = :archived"];
    $params = [':archived' => $archived];

    if ($search !== '') {
        $where[] = "(first_name LIKE :search OR last_name LIKE :search OR middle_name LIKE :search OR contact_number LIKE :search OR address LIKE :search)";
        $params[':search'] = "%$search%";
    }

    if ($purok !== '' && $purok !== 'All') {
        $where[] = "purok = :purok";
        $params[':purok'] = $purok;
    }

    if ($sex !== '' && $sex !== 'All') {
        $where[] = "sex = :sex";
        $params[':sex'] = $sex;
    }

    if ($civil_status !== '' && $civil_status !== 'All') {
        $where[] = "civil_status = :civil_status";
        $params[':civil_status'] = $civil_status;
    }

    if ($voter_status !== '' && $voter_status !== 'All') {
        $where[] = "voter_status = :voter_status";
        $params[':voter_status'] = $voter_status;
    }

    if ($age_group !== '' && $age_group !== 'All') {
        if ($age_group === 'Minor') {
            $where[] = "age < 18";
        } else if ($age_group === 'Adult') {
            $where[] = "age >= 18 AND age < 60";
        } else if ($age_group === 'Senior') {
            $where[] = "age >= 60";
        }
    }

    $sql = "SELECT * FROM residents WHERE " . implode(' AND ', $where) . " ORDER BY last_name ASC, first_name ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $residents = $stmt->fetchAll();

    http_response_code(200);
    echo json_encode(['success' => true, 'data' => $residents]);
}

function getResidentById($pdo, $id) {
    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid Resident ID']);
        return;
    }

    $stmt = $pdo->prepare("SELECT * FROM residents WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $resident = $stmt->fetch();

    if ($resident) {
        http_response_code(200);
        echo json_encode(['success' => true, 'data' => $resident]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Resident not found']);
    }
}

function createResident($pdo, $data) {
    $fields = ['first_name', 'last_name', 'birthdate', 'purok', 'address', 'civil_status', 'sex'];
    foreach ($fields as $field) {
        if (empty($data[$field])) {
            http_response_code(400);
            echo json_encode(['error' => "Missing required field: $field"]);
            return;
        }
    }

    $birthdate = new DateTime($data['birthdate']);
    $today = new DateTime();
    $age = $today->diff($birthdate)->y;

    $sql = "INSERT INTO residents (
        first_name, middle_name, last_name, suffix, birthdate, age, sex,
        civil_status, purok, address, occupation, contact_number,
        voter_status, status, archived, archive_reason
    ) VALUES (
        :first_name, :middle_name, :last_name, :suffix, :birthdate, :age, :sex,
        :civil_status, :purok, :address, :occupation, :contact_number,
        :voter_status, 'Active', 0, ''
    )";

    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([
        ':first_name' => trim($data['first_name']),
        ':middle_name' => isset($data['middle_name']) ? trim($data['middle_name']) : '',
        ':last_name' => trim($data['last_name']),
        ':suffix' => isset($data['suffix']) ? trim($data['suffix']) : '',
        ':birthdate' => $data['birthdate'],
        ':age' => $age,
        ':sex' => $data['sex'],
        ':civil_status' => $data['civil_status'],
        ':purok' => $data['purok'],
        ':address' => trim($data['address']),
        ':occupation' => !empty($data['occupation']) ? trim($data['occupation']) : 'N/A',
        ':contact_number' => isset($data['contact_number']) ? trim($data['contact_number']) : '',
        ':voter_status' => isset($data['voter_status']) ? $data['voter_status'] : 'Not Registered'
    ]);

    if ($result) {
        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Resident registered successfully',
            'id' => (int)$pdo->lastInsertId()
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to register resident']);
    }
}

function updateResident($pdo, $data) {
    if (empty($data['id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Resident ID is required for update']);
        return;
    }

    $birthdate = new DateTime($data['birthdate']);
    $today = new DateTime();
    $age = $today->diff($birthdate)->y;

    $sql = "UPDATE residents SET
        first_name = :first_name,
        middle_name = :middle_name,
        last_name = :last_name,
        suffix = :suffix,
        birthdate = :birthdate,
        age = :age,
        sex = :sex,
        civil_status = :civil_status,
        purok = :purok,
        address = :address,
        occupation = :occupation,
        contact_number = :contact_number,
        voter_status = :voter_status,
        updated_at = CURRENT_TIMESTAMP
    WHERE id = :id";

    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([
        ':id' => (int)$data['id'],
        ':first_name' => trim($data['first_name']),
        ':middle_name' => isset($data['middle_name']) ? trim($data['middle_name']) : '',
        ':last_name' => trim($data['last_name']),
        ':suffix' => isset($data['suffix']) ? trim($data['suffix']) : '',
        ':birthdate' => $data['birthdate'],
        ':age' => $age,
        ':sex' => $data['sex'],
        ':civil_status' => $data['civil_status'],
        ':purok' => $data['purok'],
        ':address' => trim($data['address']),
        ':occupation' => !empty($data['occupation']) ? trim($data['occupation']) : 'N/A',
        ':contact_number' => isset($data['contact_number']) ? trim($data['contact_number']) : '',
        ':voter_status' => isset($data['voter_status']) ? $data['voter_status'] : 'Not Registered'
    ]);

    if ($result) {
        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'Resident updated successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update resident']);
    }
}

function archiveResident($pdo, $data, $resourceId = null) {
    $id = $resourceId ? $resourceId : (isset($data['id']) ? (int)$data['id'] : 0);
    if (!$id || empty($data['status'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Resident ID and Archive Status (Moved Out/Deceased) are required']);
        return;
    }

    $reason = isset($data['archive_reason']) ? trim($data['archive_reason']) : '';

    $sql = "UPDATE residents SET
        archived = 1,
        status = :status,
        archive_reason = :reason,
        updated_at = CURRENT_TIMESTAMP
    WHERE id = :id";

    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([
        ':id' => $id,
        ':status' => $data['status'],
        ':reason' => $reason
    ]);

    if ($result) {
        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'Resident archived successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to archive resident']);
    }
}

function restoreResident($pdo, $data, $resourceId = null) {
    $id = $resourceId ? $resourceId : (isset($data['id']) ? (int)$data['id'] : 0);
    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'Resident ID is required to restore']);
        return;
    }

    $sql = "UPDATE residents SET
        archived = 0,
        status = 'Active',
        archive_reason = '',
        updated_at = CURRENT_TIMESTAMP
    WHERE id = :id";

    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([':id' => $id]);

    if ($result) {
        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'Resident restored to active records']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to restore resident']);
    }
}

function softRemoveResident($pdo, $data, $resourceId = null) {
    $id = $resourceId ? $resourceId : (isset($data['id']) ? (int)$data['id'] : 0);
    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'Resident ID is required for removal']);
        return;
    }

    $sql = "UPDATE residents SET
        archived = 2,
        status = 'Removed',
        archive_reason = 'Removed by Admin',
        updated_at = CURRENT_TIMESTAMP
    WHERE id = :id";

    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([':id' => $id]);

    if ($result) {
        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'Resident moved to removed records']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to remove resident record']);
    }
}

function getAnalytics($pdo) {
    // Total Population Active vs Archived vs Removed
    $totalActive = $pdo->query("SELECT COUNT(*) FROM residents WHERE archived = 0")->fetchColumn();
    $totalArchived = $pdo->query("SELECT COUNT(*) FROM residents WHERE archived = 1")->fetchColumn();
    $totalRemoved = $pdo->query("SELECT COUNT(*) FROM residents WHERE archived = 2")->fetchColumn();

    // Demographics
    $minors = $pdo->query("SELECT COUNT(*) FROM residents WHERE archived = 0 AND age < 18")->fetchColumn();
    $adults = $pdo->query("SELECT COUNT(*) FROM residents WHERE archived = 0 AND age >= 18 AND age < 60")->fetchColumn();
    $seniors = $pdo->query("SELECT COUNT(*) FROM residents WHERE archived = 0 AND age >= 60")->fetchColumn();

    // Sex Breakdown
    $males = $pdo->query("SELECT COUNT(*) FROM residents WHERE archived = 0 AND sex = 'Male'")->fetchColumn();
    $females = $pdo->query("SELECT COUNT(*) FROM residents WHERE archived = 0 AND sex = 'Female'")->fetchColumn();

    // Voters Status
    $voters = $pdo->query("SELECT COUNT(*) FROM residents WHERE archived = 0 AND voter_status = 'Registered'")->fetchColumn();
    $nonVoters = $pdo->query("SELECT COUNT(*) FROM residents WHERE archived = 0 AND voter_status = 'Not Registered'")->fetchColumn();

    // Population per Purok
    $purokStmt = $pdo->query("SELECT purok, COUNT(*) as count FROM residents WHERE archived = 0 GROUP BY purok ORDER BY purok ASC");
    $purokData = $purokStmt->fetchAll();

    // Civil Status Breakdown
    $civilStmt = $pdo->query("SELECT civil_status, COUNT(*) as count FROM residents WHERE archived = 0 GROUP BY civil_status");
    $civilData = $civilStmt->fetchAll();

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'data' => [
            'total_active' => (int)$totalActive,
            'total_archived' => (int)$totalArchived,
            'total_removed' => (int)$totalRemoved,
            'minors' => (int)$minors,
            'adults' => (int)$adults,
            'seniors' => (int)$seniors,
            'males' => (int)$males,
            'females' => (int)$females,
            'voters' => (int)$voters,
            'non_voters' => (int)$nonVoters,
            'purok_distribution' => $purokData,
            'civil_status_distribution' => $civilData
        ]
    ]);
}
