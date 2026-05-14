<?php
/**
 * Database configuration file
 * Include this in all API endpoints
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'dummyfyp');
define('DB_USER', 'root');
define('DB_PASS', ''); // Set your MySQL password

// Enable session for user authentication
session_start();

/**
 * Get database connection
 * @return PDO
 */
function getDBConnection() {
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

/**
 * Get current logged-in user from session
 * @return array|null
 */
function getCurrentUser() {
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("SELECT id, name, email, role, Matrix as student_id, advisor_id, phone, programme, year FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        return $user ?: null;
    } catch (PDOException $e) {
        return null;
    }
}

/**
 * Require authentication – returns 401 if not logged in
 */
function requireAuth() {
    $user = getCurrentUser();
    if (!$user) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized. Please login.']);
        exit();
    }
    return $user;
}

/**
 * Require specific role – returns 403 if role doesn't match
 */
function requireRole($allowedRoles) {
    $user = requireAuth();
    if (!in_array($user['role'], (array)$allowedRoles)) {
        http_response_code(403);
        echo json_encode(['error' => 'Forbidden. Insufficient permissions.']);
        exit();
    }
    return $user;
}
?>