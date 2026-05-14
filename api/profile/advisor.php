<?php
/**
 * API: Get advisor profile data
 * Method: GET
 * Role: advisor
 * Response: JSON with advisor profile fields
 */

require_once __DIR__ . '/../config/database.php';

// Only advisors can access
$advisor = requireRole('advisor');

$pdo = getDBConnection();

// Get advisor details
$stmt = $pdo->prepare("
    SELECT 
        name as full_name,
        email,
        phone,
        Matrix as staff_id,
        role,
        profile_pic
    FROM users
    WHERE id = ?
");
$stmt->execute([$advisor['id']]);
$profile = $stmt->fetch();

if (!$profile) {
    http_response_code(404);
    echo json_encode(['error' => 'Profile not found']);
    exit();
}

// Add department (may come from a separate field; using default or join if exists)
// For now, we can either set a default or query from an advisor_details table if exists.
// Assuming department is not in users table; we can add a placeholder.
$profile['department'] = 'School of Professional & Continuing Education';

// Ensure profile_pic has a default if null
if (empty($profile['profile_pic'])) {
    $profile['profile_pic'] = 'https://cdn-icons-png.flaticon.com/512/3135/3135715.png';
}

echo json_encode($profile);
?>