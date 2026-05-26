<?php
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
        echo json_encode(['error' => 'Please select section for all courses']);
        exit;
    }
}

$submissionDate = date('Y-m-d H:i:s');
$session = '2025/2026 - Semester 2';

// Insert each course as a separate registration
$success = true;
foreach ($items as $item) {
    $stmt = $pdo->prepare("
        INSERT INTO course_registrations (student_id, submission_date, status, section, session)
        VALUES (?, ?, 'pending', ?, ?)
    ");
    if (!$stmt->execute([$student['id'], $submissionDate, $item['section'], $session])) {
        $success = false;
        break;
    }
}

if ($success) {
    // Clear cart
    $stmt = $pdo->prepare("DELETE FROM registration_cart WHERE student_id = ?");
    $stmt->execute([$student['id']]);
    echo json_encode(['success' => true, 'message' => 'Registration submitted for approval']);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to submit registration']);
}
?>