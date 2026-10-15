<?php
// api/subjects.php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $pdo->query("SELECT * FROM subjects ORDER BY name ASC");
    $subjects = $stmt->fetchAll();
    echo json_encode(['status' => 'success', 'data' => $subjects]);
    exit();
}

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $action = $data['action'] ?? 'add';

    if ($action === 'add') {
        $id = 'SUB-' . rand(100, 999);
        $name = trim($data['name'] ?? '');
        $category = trim($data['category'] ?? 'General');

        if (empty($name)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Subject name is required']);
            exit();
        }

        $stmt = $pdo->prepare("INSERT INTO subjects (id, name, category) VALUES (?, ?, ?)");
        $stmt->execute([$id, $name, $category]);

        echo json_encode(['status' => 'success', 'message' => 'Subject added successfully', 'id' => $id]);
        exit();
    }

    if ($action === 'edit') {
        $id = $data['id'] ?? '';
        $name = trim($data['name'] ?? '');
        $category = trim($data['category'] ?? 'General');

        if (empty($id) || empty($name)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'ID and Subject name are required']);
            exit();
        }

        $stmt = $pdo->prepare("UPDATE subjects SET name = ?, category = ? WHERE id = ?");
        $stmt->execute([$name, $category, $id]);

        echo json_encode(['status' => 'success', 'message' => 'Subject updated successfully']);
        exit();
    }

    if ($action === 'delete') {
        $id = $data['id'] ?? '';
        if (empty($id)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'ID is required']);
            exit();
        }

        $stmt = $pdo->prepare("DELETE FROM subjects WHERE id = ?");
        $stmt->execute([$id]);

        echo json_encode(['status' => 'success', 'message' => 'Subject deleted successfully']);
        exit();
    }
}
?>