<?php
require_once __DIR__ . '/config/database.php';

$user = getCurrentUser();
if ($user) {
    echo json_encode(['id' => $user['id'], 'name' => $user['name'], 'email' => $user['email'], 'role' => $user['role'], 'matrix_number' => $user['matrix_number'] ?? null]);
} else {
    http_response_code(401);
    echo json_encode(['error' => 'Not logged in']);
}
?>