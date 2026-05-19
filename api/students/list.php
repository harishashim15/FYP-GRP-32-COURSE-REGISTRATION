<?php
require_once __DIR__ . '/../config/database.php';

$advisor = requireRole('advisor');
$pdo = getDBConnection();

// Fetch all students assigned to this advisor
$stmt = $pdo->prepare("
    SELECT 
        s.user_id AS id,
        s.matrix_number AS matrix,
        s.user_name AS name,
        s.programme,
        s.year,
        s.utm_email,
        s.second_email,
        s.phone
    FROM students s
    WHERE s.advisor_id = ?
    ORDER BY s.user_name ASC
");
$stmt->execute([$advisor['id']]);
$students = $stmt->fetchAll();

// Get advisor name for display
$advisorName = $advisor['name'] ?? 'Academic Advisor';

// Determine registration status for each student
foreach ($students as &$student) {
    $stmt = $pdo->prepare("
        SELECT status 
        FROM course_registrations 
        WHERE student_id = ? 
        ORDER BY id DESC 
        LIMIT 1
    ");
    $stmt->execute([$student['id']]);
    $reg = $stmt->fetch();
    $status = $reg ? $reg['status'] : 'pending';

    switch ($status) {
        case 'approved':
            $student['status_text'] = 'Completed';
            $student['status_class'] = 'active';
            break;
        case 'rejected':
            $student['status_text'] = 'Rejected';
            $student['status_class'] = 'rejected';
            break;
        default:
            $student['status_text'] = 'Pending Registration';
            $student['status_class'] = 'pending';
    }
    $student['advisor_name'] = $advisorName;
}

echo json_encode($students);
?>