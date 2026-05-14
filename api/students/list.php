<?php
/**
 * API: Get students under current advisor
 * Method: GET
 * Role: advisor
 * Response: JSON array of students
 */

require_once __DIR__ . '/../config/database.php';

// Only advisors can access
$advisor = requireRole('advisor');

$pdo = getDBConnection();

// Get all students with advisor_id = current advisor
$stmt = $pdo->prepare("
    SELECT 
        u.id,
        u.name,
        u.Matrix as matrix,
        u.programme,
        u.year,
        u.email,
        u.phone
    FROM users u
    WHERE u.advisor_id = ? AND u.role = 'student'
    ORDER BY u.name ASC
");
$stmt->execute([$advisor['id']]);
$students = $stmt->fetchAll();

// For each student, determine registration status based on their latest course_registration
$result = [];
foreach ($students as $student) {
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
    
    // Map status to display text and CSS class
    $statusText = '';
    $statusClass = '';
    switch ($status) {
        case 'approved':
            $statusText = 'Completed';
            $statusClass = 'active';
            break;
        case 'pending':
            $statusText = 'Pending Registration';
            $statusClass = 'pending';
            break;
        case 'rejected':
            $statusText = 'Rejected';
            $statusClass = 'rejected';
            break;
        default:
            $statusText = 'Pending Registration';
            $statusClass = 'pending';
    }
    
    $result[] = [
        'id' => $student['id'],
        'name' => $student['name'],
        'matrix' => $student['matrix'],
        'programme' => $student['programme'],
        'year' => $student['year'],
        'email' => $student['email'],
        'phone' => $student['phone'],
        'status_text' => $statusText,
        'status_class' => $statusClass,
        'advisor_name' => $advisor['name']
    ];
}

echo json_encode($result);
?>