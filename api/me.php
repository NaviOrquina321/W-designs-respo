<?php
// api/me.php - Return Current User Session State
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';

$user = requireLogin();

echo json_encode([
    'status' => 'success',
    'user' => [
        'id' => $user['id'],
        'email' => $user['email'],
        'name' => $user['name'],
        'role' => $user['role']
    ]
]);
?>