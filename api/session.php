<?php
/**
 * API: Get current logged-in user session info
 * Method: GET
 * Response: JSON with user data or null if not logged in
 */

require_once __DIR__ . '/config/database.php';

$user = getCurrentUser();

if ($user) {
    // Return only necessary fields (exclude password)
    echo json_encode([
        'id' => $user['id'],
        'name' => $user['name'],
        'email' => $user['email'],
        'role' => $user['role'],
        'student_id' => $user['student_id'] ?? null,
        'advisor_id' => $user['advisor_id'] ?? null,
        'phone' => $user['phone'] ?? null,
        'programme' => $user['programme'] ?? null,
        'year' => $user['year'] ?? null
    ]);
} else {
    http_response_code(401);
    echo json_encode(['error' => 'Not logged in', 'user' => null]);
}
?>