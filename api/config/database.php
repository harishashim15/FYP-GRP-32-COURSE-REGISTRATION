<?php
header('Content-Type: application/json');
session_set_cookie_params([
    'path' => '/',
    'httponly' => true,
    'samesite' => 'Lax'
]);
session_start();

define('DB_HOST', 'localhost');
define('DB_NAME', 'fypdb3');
define('DB_USER', 'root');
define('DB_PASS', '');

function getDBConnection(): PDO {
    return new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4", DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
}

function getCurrentUser(): ?array {
    if (!isset($_SESSION['user_id'])) return null;
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT user_id as id, user_name as name, utm_email as email, role, matrix_number FROM users WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch() ?: null;
}

function requireAuth(): array {
    $user = getCurrentUser();
    if (!$user) { http_response_code(401); echo json_encode(['error'=>'Unauthorized']); exit; }
    return $user;
}

function requireRole(array|string $roles): array {
    $user = requireAuth();
    if (!in_array($user['role'], (array)$roles)) { http_response_code(403); echo json_encode(['error'=>'Forbidden']); exit; }
    return $user;
}
?>