<?php
/**
 * API: Verify (approve/reject) a registration request
 * Method: POST
 * Role: advisor
 * Request body: { "id": registration_id, "action": "approve"|"reject", "remarks": "optional text" }
 * Response: JSON success or error
 */

require_once __DIR__ . '/../config/database.php';

// Only advisors can access
$advisor = requireRole('advisor');

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['id']) || !isset($input['action'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields: id and action']);
    exit();
}

$regId = (int)$input['id'];
$action = $input['action'];
$remarks = isset($input['remarks']) ? trim($input['remarks']) : null;

// Validate action
if (!in_array($action, ['approve', 'reject'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid action. Must be "approve" or "reject"']);
    exit();
}

$pdo = getDBConnection();

// Verify that this registration belongs to a student under this advisor
$stmt = $pdo->prepare("
    SELECT cr.id, cr.status, u.name as student_name
    FROM course_registrations cr
    JOIN users u ON cr.student_id = u.id
    WHERE cr.id = ? AND u.advisor_id = ?
");
$stmt->execute([$regId, $advisor['id']]);
$reg = $stmt->fetch();

if (!$reg) {
    http_response_code(404);
    echo json_encode(['error' => 'Registration not found or not under your supervision']);
    exit();
}

if ($reg['status'] !== 'pending') {
    http_response_code(400);
    echo json_encode(['error' => 'This registration has already been ' . $reg['status']]);
    exit();
}

// Update registration status
$newStatus = $action === 'approve' ? 'approved' : 'rejected';
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