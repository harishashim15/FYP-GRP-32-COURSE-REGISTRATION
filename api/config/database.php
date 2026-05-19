<?php
/**
 * Database configuration file for fypdb3
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Database configuration for fypdb3
define('DB_HOST', 'localhost');
define('DB_NAME', 'fypdb3');  // Changed to fypdb3
define('DB_USER', 'root');
define('DB_PASS', '');

session_start();

function getDBConnection(): PDO {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
        return $pdo;
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
        exit();
    }
}

function getCurrentUser(): ?array {
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    try {
        $pdo = getDBConnection();
        // Map fypdb3 columns to expected names
        $stmt = $pdo->prepare("
            SELECT 
                user_id as id, 
                user_name as name, 
                utm_email as email, 
                role, 
                matrix_number,
                phone,
                programme,
                year,
                advisor_id,
                login_cred
            FROM users 
            WHERE user_id = ?
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        return $user ?: null;
    } catch (PDOException $e) {
        error_log($e->getMessage());
        return null;
    }
}

function requireAuth(): array {
    $user = getCurrentUser();
    if (!$user) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized. Please login.']);
        exit();
    }
    return $user;
}

function requireRole(array|string $allowedRoles): array {
    $user = requireAuth();
    $allowed = (array)$allowedRoles;
    if (!in_array($user['role'], $allowed)) {
        http_response_code(403);
        echo json_encode(['error' => 'Forbidden. Insufficient permissions.']);
        exit();
    }
    return $user;
}
?>