<?php
/**
 * API: Get student profile data
 * Method: GET
 * Role: student
 * Response: JSON with student profile fields
 */

require_once __DIR__ . '/../config/database.php';

// Only students can access
$student = requireRole('student');

$pdo = getDBConnection();

// Get student details
$stmt = $pdo->prepare("
    SELECT 
        name as full_name,
        email,
        phone,
        Matrix as student_id,
        programme,
        year,
        role,
        profile_pic
    FROM users
    WHERE id = ?
");
$stmt->execute([$student['id']]);
$profile = $stmt->fetch();

if (!$profile) {
    http_response_code(404);
    echo json_encode(['error' => 'Profile not found']);
    exit();
}

// Ensure profile_pic has a default if null
if (empty($profile['profile_pic'])) {
    $profile['profile_pic'] = 'https://cdn-icons-png.flaticon.com/512/3135/3135715.png';
}

echo json_encode($profile);
?>