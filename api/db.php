<?php
// api/db.php - SQL Database Connection with MySQL primary & SQLite fallback

function getDbConnection() {
    $mysqlHost = '127.0.0.1';
    $mysqlDb   = 'bris_db';
    $mysqlUser = 'root';
    $mysqlPass = '';

    // Attempt MySQL connection first
    try {
        $dsn = "mysql:host=$mysqlHost;dbname=$mysqlDb;charset=utf8mb4";
        $pdo = new PDO($dsn, $mysqlUser, $mysqlPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        return $pdo;
    } catch (PDOException $e) {
        // Fallback to SQLite SQL engine if MySQL service is offline or unavailable in sandbox
        $dbFile = __DIR__ . '/bris_database.sqlite';
        $dbExists = file_exists($dbFile);

        try {
            $pdo = new PDO('sqlite:' . $dbFile);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            if (!$dbExists || filesize($dbFile) === 0) {
                initializeSqliteDatabase($pdo);
            }

            return $pdo;
        } catch (PDOException $sqliteEx) {
            header('Content-Type: application/json', true, 500);
            echo json_encode(['error' => 'Database Connection Failed: ' . $sqliteEx->getMessage()]);
            exit;
        }
    }
}

function initializeSqliteDatabase($pdo) {
    $schema = "
    CREATE TABLE IF NOT EXISTS residents (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        first_name TEXT NOT NULL,
        middle_name TEXT DEFAULT '',
        last_name TEXT NOT NULL,
        suffix TEXT DEFAULT '',
        birthdate DATE NOT NULL,
        age INTEGER NOT NULL,
        sex TEXT NOT NULL,
        civil_status TEXT NOT NULL,
        purok TEXT NOT NULL,
        address TEXT NOT NULL,
        occupation TEXT DEFAULT 'N/A',
        contact_number TEXT DEFAULT '',
        voter_status TEXT NOT NULL DEFAULT 'Not Registered',
        status TEXT NOT NULL DEFAULT 'Active',
        archived INTEGER NOT NULL DEFAULT 0,
        archive_reason TEXT DEFAULT '',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );
    ";

    $pdo->exec($schema);
    seedInitialData($pdo);
}

function seedInitialData($pdo) {
    $stmtCheck = $pdo->query("SELECT COUNT(*) FROM residents");
    if ($stmtCheck->fetchColumn() > 0) {
        return;
    }

    $sampleResidents = [
        [
            'first_name' => 'Juan',
            'middle_name' => 'Santos',
            'last_name' => 'Dela Cruz',
            'suffix' => '',
            'birthdate' => '1988-05-12',
            'age' => 36,
            'sex' => 'Male',
            'civil_status' => 'Married',
            'purok' => 'Purok 1',
            'address' => '123 Mahogany St., Purok 1',
            'occupation' => 'Barangay Health Worker',
            'contact_number' => '09171234567',
            'voter_status' => 'Registered',
            'status' => 'Active',
            'archived' => 0,
            'archive_reason' => ''
        ],
        [
            'first_name' => 'Maria Clara',
            'middle_name' => 'Reyes',
            'last_name' => 'Santos',
            'suffix' => '',
            'birthdate' => '1995-10-24',
            'age' => 29,
            'sex' => 'Female',
            'civil_status' => 'Single',
            'purok' => 'Purok 2',
            'address' => '45 Sampaguita St., Purok 2',
            'occupation' => 'Public School Teacher',
            'contact_number' => '09189876543',
            'voter_status' => 'Registered',
            'status' => 'Active',
            'archived' => 0,
            'archive_reason' => ''
        ],
        [
            'first_name' => 'Jose',
            'middle_name' => 'Protacio',
            'last_name' => 'Rizal',
            'suffix' => 'Jr.',
            'birthdate' => '1961-06-19',
            'age' => 63,
            'sex' => 'Male',
            'civil_status' => 'Married',
            'purok' => 'Purok 3',
            'address' => '78 Ilang-Ilang Lane, Purok 3',
            'occupation' => 'Physician',
            'contact_number' => '09201112233',
            'voter_status' => 'Registered',
            'status' => 'Active',
            'archived' => 0,
            'archive_reason' => ''
        ],
        [
            'first_name' => 'Andres',
            'middle_name' => 'Castro',
            'last_name' => 'Bonifacio',
            'suffix' => '',
            'birthdate' => '1975-11-30',
            'age' => 49,
            'sex' => 'Male',
            'civil_status' => 'Married',
            'purok' => 'Purok 4',
            'address' => '12 Balagtas St., Purok 4',
            'occupation' => 'Business Owner',
            'contact_number' => '09175554433',
            'voter_status' => 'Registered',
            'status' => 'Active',
            'archived' => 0,
            'archive_reason' => ''
        ],
        [
            'first_name' => 'Melchora',
            'middle_name' => 'Aquino',
            'last_name' => 'Ramos',
            'suffix' => '',
            'birthdate' => '1952-01-06',
            'age' => 72,
            'sex' => 'Female',
            'civil_status' => 'Widowed',
            'purok' => 'Purok 5',
            'address' => '88 Banaba Drive, Purok 5',
            'occupation' => 'Retired',
            'contact_number' => '09223334455',
            'voter_status' => 'Registered',
            'status' => 'Active',
            'archived' => 0,
            'archive_reason' => ''
        ],
        [
            'first_name' => 'Emilio',
            'middle_name' => 'Famy',
            'last_name' => 'Aguinaldo',
            'suffix' => '',
            'birthdate' => '1999-03-22',
            'age' => 25,
            'sex' => 'Male',
            'civil_status' => 'Single',
            'purok' => 'Purok 6',
            'address' => '101 Narra Ave., Purok 6',
            'occupation' => 'IT Specialist',
            'contact_number' => '09198887766',
            'voter_status' => 'Not Registered',
            'status' => 'Active',
            'archived' => 0,
            'archive_reason' => ''
        ],
        [
            'first_name' => 'Gabriela',
            'middle_name' => 'Silang',
            'last_name' => 'Cariño',
            'suffix' => '',
            'birthdate' => '2008-08-15',
            'age' => 16,
            'sex' => 'Female',
            'civil_status' => 'Single',
            'purok' => 'Purok 7',
            'address' => '55 Acacia St., Purok 7',
            'occupation' => 'Student',
            'contact_number' => '09170001122',
            'voter_status' => 'Not Registered',
            'status' => 'Active',
            'archived' => 0,
            'archive_reason' => ''
        ],
        [
            'first_name' => 'Apolinario',
            'middle_name' => 'Maranan',
            'last_name' => 'Mabini',
            'suffix' => '',
            'birthdate' => '1970-07-23',
            'age' => 54,
            'sex' => 'Male',
            'civil_status' => 'Single',
            'purok' => 'Purok 1',
            'address' => '33 Mabini St., Purok 1',
            'occupation' => 'Legal Consultant',
            'contact_number' => '09183332211',
            'voter_status' => 'Registered',
            'status' => 'Moved Out',
            'archived' => 1,
            'archive_reason' => 'Relocated to Manila'
        ]
    ];

    $sql = "INSERT INTO residents (
        first_name, middle_name, last_name, suffix, birthdate, age, sex,
        civil_status, purok, address, occupation, contact_number,
        voter_status, status, archived, archive_reason
    ) VALUES (
        :first_name, :middle_name, :last_name, :suffix, :birthdate, :age, :sex,
        :civil_status, :purok, :address, :occupation, :contact_number,
        :voter_status, :status, :archived, :archive_reason
    )";

    $stmt = $pdo->prepare($sql);
    foreach ($sampleResidents as $res) {
        $stmt->execute($res);
    }
}
