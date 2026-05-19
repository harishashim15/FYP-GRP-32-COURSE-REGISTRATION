<?php
require_once __DIR__ . '/../config/database.php';

$advisor = requireRole('advisor');
$pdo = getDBConnection();

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !isset($input['id']) || !isset($input['action'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields: id and action']);
    exit;
}

$regId = $input['id'];
$action = $input['action'];
$remarks = trim($input['remarks'] ?? '');

if (!in_array($action, ['approve', 'reject'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid action. Must be "approve" or "reject"']);
    exit;
}

// Verify registration exists and belongs to advisor's student
$stmt = $pdo->prepare("
    SELECT cr.id, cr.status, s.user_name AS student_name
    FROM course_registrations cr
    JOIN students s ON cr.student_id = s.user_id
    WHERE cr.id = ? AND s.advisor_id = ?
");
$stmt->execute([$regId, $advisor['id']]);
$reg = $stmt->fetch();

if (!$reg) {
    http_response_code(404);
    echo json_encode(['error' => 'Registration not found or not under your supervision']);
    exit;
}

if ($reg['status'] !== 'pending') {
    http_response_code(400);
    echo json_encode(['error' => 'This registration has already been ' . $reg['status']]);
    exit;
}

$newStatus = ($action === 'approve') ? 'approved' : 'rejected';

$stmt = $pdo->prepare("
    UPDATE course_registrations 
    SET status = ?, advisor_remarks = ?, reviewed_by = ?, reviewed_at = NOW()
    WHERE id = ?
");
$stmt->execute([$newStatus, $remarks, $advisor['id'], $regId]);

echo json_encode([
    'success' => true,
    'message' => "Registration {$action}d successfully",
    'registration_id' => $regId,
    'status' => $newStatus
]);
?>