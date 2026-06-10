<?php
require_once __DIR__ . '/../config/database.php';

$admin = requireRole('admin');
$pdo = getDBConnection();

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || empty($input['subject_code']) || empty($input['action'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

$code = $input['subject_code'];
$action = $input['action'];

if (!in_array($action, ['hide', 'show'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid action. Use "hide" or "show"']);
    exit;
}

$new_hidden = ($action === 'hide') ? 1 : 0;

$stmt = $pdo->prepare("UPDATE subjects SET is_hidden = ? WHERE subject_code = ?");
$stmt->execute([$new_hidden, $code]);

echo json_encode(['success' => true, 'is_hidden' => $new_hidden]);
?>