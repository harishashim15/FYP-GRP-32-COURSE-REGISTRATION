<?php
require_once __DIR__ . '/../config/database.php';

$user = requireAuth();
$pdo = getDBConnection();

$default_password = 'pass1234';
$stmt = $pdo->prepare("SELECT password FROM users WHERE user_id = ?");
$stmt->execute([$user['id']]);
$stored_hash = $stmt->fetchColumn();

$is_default = password_verify($default_password, $stored_hash);

echo json_encode(['is_default' => $is_default]);
?>