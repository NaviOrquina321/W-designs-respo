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

    $stmt = $pdo->prepare("DELETE FROM subjects WHERE id = ?");
    $stmt->execute([$subId]);

    echo json_encode(['status' => 'success', 'message' => 'Subject deleted.']);
} else {
    jsonError(405, 'Method not allowed');
}
?>