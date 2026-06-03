<?php
// Set Malaysia timezone
date_default_timezone_set('Asia/Kuala_Lumpur');

require_once __DIR__ . '/../config/database.php';

$student = requireRole('student');
$pdo = getDBConnection();

// Get cart items
$stmt = $pdo->prepare("SELECT subject_code, section FROM registration_cart WHERE student_id = ?");
$stmt->execute([$student['id']]);
$items = $stmt->fetchAll();

if (empty($items)) {
    http_response_code(400);
    echo json_encode(['error' => 'Cart is empty']);
    exit;
}

foreach ($items as $item) {
    if (empty($item['section'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Please select a section for all courses']);
        exit;
    }
}

// This will now use Malaysia time (UTC+8)
$submissionDate = date('Y-m-d H:i:s');
$session = '2025/2026 - Semester 2';

// Create a single registration batch
$stmt = $pdo->prepare("
    INSERT INTO course_registrations (student_id, submission_date, status, session)
    VALUES (?, ?, 'pending', ?)
");
$stmt->execute([$student['id'], $submissionDate, $session]);
$registrationId = $pdo->lastInsertId();

// Insert each course into registration_courses linked to this registration
foreach ($items as $item) {
    $stmt = $pdo->prepare("
        INSERT INTO registration_courses (registration_id, subject_code, section)
        VALUES (?, ?, ?)
    ");
    $stmt->execute([$registrationId, $item['subject_code'], $item['section']]);
}

// Clear the cart
$stmt = $pdo->prepare("DELETE FROM registration_cart WHERE student_id = ?");
$stmt->execute([$student['id']]);

echo json_encode(['success' => true, 'message' => 'Registration submitted for approval']);
?>