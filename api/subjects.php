<?php
// api/subjects.php - Subjects Catalog REST API
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $pdo->query("SELECT id, name, category, created_at FROM subjects ORDER BY name ASC");
    $subjects = $stmt->fetchAll();
    echo json_encode(['status' => 'success', 'data' => $subjects]);
} elseif ($method === 'POST') {
    $user = requireRole(['admin']);
    $input = getInput();

    $subId = trim($input['id'] ?? '');
    $name = trim($input['name'] ?? '');
    $category = trim($input['category'] ?? 'STEM');

    if (empty($name)) {
        jsonError(400, 'Subject name required.');
    }

    if (!empty($subId)) {
        // Edit subject
        $stmt = $pdo->prepare("UPDATE subjects SET name = ?, category = ? WHERE id = ?");
        $stmt->execute([$name, $category, $subId]);
        echo json_encode(['status' => 'success', 'message' => 'Subject updated.']);
    } else {
        // Add subject
        $newSubId = newId('SUB-');
        $stmt = $pdo->prepare("INSERT INTO subjects (id, name, category) VALUES (?, ?, ?)");
        $stmt->execute([$newSubId, $name, $category]);
        echo json_encode([
            'status' => 'success',
            'message' => 'Subject created.',
            'data' => ['id' => $newSubId, 'name' => $name, 'category' => $category]
        ]);
    }
} elseif ($method === 'DELETE') {
    $user = requireRole(['admin']);
    $input = getInput();
    $subId = trim($input['id'] ?? ($_GET['id'] ?? ''));

    if (empty($subId)) {
        jsonError(400, 'Subject ID required.');
    }

    $stmtFetch = $pdo->prepare("SELECT name FROM subjects WHERE id = ?");
    $stmtFetch->execute([$subId]);
    $subRow = $stmtFetch->fetch();

    if (!$subRow) {
        jsonError(404, 'Subject not found.');
    }

    $subName = $subRow['name'];

    // Check usage in tutors
    $stT = $pdo->prepare("SELECT COUNT(*) as cnt FROM tutors WHERE LOWER(subjects) LIKE ?");
    $stT->execute(['%' . strtolower($subName) . '%']);
    if ($stT->fetch()['cnt'] > 0) {
        jsonError(400, "Cannot delete subject '$subName' because it is referenced by existing tutors.");
    }

    // Check usage in matches
    $stM = $pdo->prepare("SELECT COUNT(*) as cnt FROM matches WHERE LOWER(subject) = ?");
    $stM->execute([strtolower($subName)]);
    if ($stM->fetch()['cnt'] > 0) {
        jsonError(400, "Cannot delete subject '$subName' because it is referenced in match requests.");
    }

    // Check usage in sessions
    $stS = $pdo->prepare("SELECT COUNT(*) as cnt FROM sessions WHERE LOWER(subject) = ?");
    $stS->execute([strtolower($subName)]);
    if ($stS->fetch()['cnt'] > 0) {
        jsonError(400, "Cannot delete subject '$subName' because it is referenced in tutoring sessions.");
    }

    $stmt = $pdo->prepare("DELETE FROM subjects WHERE id = ?");
    $stmt->execute([$subId]);

    echo json_encode(['status' => 'success', 'message' => 'Subject deleted.']);
} else {
    jsonError(405, 'Method not allowed');
}
?>