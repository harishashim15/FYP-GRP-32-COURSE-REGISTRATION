<?php
require_once __DIR__ . '/../config/database.php';

$advisor = requireRole('advisor');
$pdo = getDBConnection();

// Fetch from users table
$stmt = $pdo->prepare("
    SELECT 
        user_name AS full_name,
        matrix_number AS staff_id,
        utm_email AS email,
        second_email,
        phone,
        role
    FROM users
    WHERE user_id = ?
");
$stmt->execute([$advisor['id']]);
$profile = $stmt->fetch();

if (!$profile) {
    http_response_code(404);
    echo json_encode(['error' => 'Profile not found']);
    exit;
}

// Fetch additional advisor details from 'advisor' table (faculty, department)
$stmt = $pdo->prepare("
    SELECT faculty, department 
    FROM advisor 
    WHERE user_id = ?
");
$stmt->execute([$advisor['id']]);
$advisorDetails = $stmt->fetch();

$profile['department'] = $advisorDetails ? $advisorDetails['department'] : 'Computer Science';
$profile['faculty'] = $advisorDetails ? $advisorDetails['faculty'] : 'Faculty Of SPACE';

// Add default profile picture if none set
$profile['profile_pic'] = 'https://cdn-icons-png.flaticon.com/512/3135/3135715.png';

echo json_encode($profile);
?>