<?php
// api/upload.php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $tutorId = $_POST['tutor_id'] ?? 'tut-1';
    $docType = $_POST['doc_type'] ?? 'diploma'; // diploma, tor, id

    $uploadDir = __DIR__ . '/../uploads/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $fileName = time() . '_' . basename($_FILES['file']['name']);
        $targetFile = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['file']['tmp_name'], $targetFile)) {
            $col = ($docType === 'tor') ? 'tor_status' : (($docType === 'id') ? 'id_status' : 'diploma_status');
            $stmt = $pdo->prepare("UPDATE tutors SET $col = 'Uploaded ($fileName)' WHERE id = ?");
            $stmt->execute([$tutorId]);

            echo json_encode(['status' => 'success', 'message' => "Document $docType uploaded successfully"]);
            exit();
        }
    }

    echo json_encode(['status' => 'error', 'message' => 'Upload failed']);
    exit();
}
?>