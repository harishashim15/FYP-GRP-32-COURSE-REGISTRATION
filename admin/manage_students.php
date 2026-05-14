<?php
session_start();
include '../db_connect.php';

// 1. Security: Only Admin allowed
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.html");
    exit();
}

// 2. Delete student if delete_id is set
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    
    // Delete from users table
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role = 'student'");
    $stmt->bind_param("i", $delete_id);
    if ($stmt->execute()) {
        $message = "Student deleted successfully.";
        $msg_type = 'success';
    } else {
        $message = "Error deleting student.";
        $msg_type = 'danger';
    }
    $stmt->close();
}

// 3. Fetch all students
$students = [];
$query = "SELECT id, name, email FROM users WHERE role = 'student' ORDER BY id DESC";
$result = $conn->query($query);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students - Admin Portal</title>
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Reuse same styles as dashboard -->
    <style>
        /* Reset & Base */
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { display: flex; background-color: #f4f6f9; min-height: 100vh; }
        
        /* Sidebar (Same as dashboard) */
        .sidebar { width: 250px; background-color: #7A0D2A; color: white; display: flex; flex-direction: column; padding: 20px 0; position: fixed; height: 100%; left: 0; top: 0; z-index: 1000; }
        .sidebar h1 { font-size: 24px; font-weight: 600; padding: 0 25px 30px 25px; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar nav ul { list-style: none; padding: 20px 15px; }
        .sidebar nav ul li { margin-bottom: 12px; }
        .sidebar nav ul li a { display: flex; align-items: center; text-decoration: none; color: white; padding: 12px 20px; border-radius: 8px; transition: 0.3s ease; font-size: 16px; }
        .sidebar nav ul li a i { margin-right: 15px; width: 20px; text-align: center; }
        .sidebar nav ul li a:hover { background-color: rgba(255,255,255,0.1); }
        .sidebar nav ul li a.active { background-color: #DE9E1F; color: #fff; font-weight: 500; }
        .sidebar nav ul li a.logout { margin-top: 60px; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 20px; border-radius: 0; }

        /* Main Content */
        .main-content { margin-left: 250px; width: calc(100% - 250px); padding: 30px; background-color: #f4f6f9; }
        
        /* Page Header */
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .page-header h2 { color: #1f2937; }
        .btn-add { background-color: #7A0D2A; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; transition: 0.2s; border: none; font-weight: 500; }
        .btn-add:hover { background-color: #5c0920; color: white; }

        /* Table */
        .table-card { background: white; border-radius: 16px; padding: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 15px; }
        th { text-align: left; background-color: #f8f9fa; padding: 12px 15px; color: #4b5563; font-weight: 600; border-bottom: 2px solid #e5e7eb; }
        td { padding: 12px 15px; border-bottom: 1px solid #e5e7eb; color: #1f2937; }
        tr:hover { background-color: #f9fafb; }

        /* Action Buttons */
        .action-btn { padding: 6px 12px; border: none; border-radius: 6px; font-size: 13px; cursor: pointer; text-decoration: none; display: inline-block; margin-right: 5px; }
        .btn-edit { background-color: #DE9E1F; color: white; }
        .btn-edit:hover { background-color: #c48a1a; color: white; }
        .btn-delete { background-color: #dc2626; color: white; }
        .btn-delete:hover { background-color: #b91c1c; color: white; }

        /* Alert */
        .alert { padding: 12px 20px; border-radius: 8px; margin-bottom: 20px; }
        .alert-success { background-color: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .alert-danger { background-color: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

        /* Responsive */
        @media (max-width: 768px) { .sidebar { width: 200px; } .main-content { margin-left: 200px; padding: 20px; } }
        @media (max-width: 576px) { body { flex-direction: column; } .sidebar { width: 100%; height: auto; position: relative; } .main-content { margin-left: 0; width: 100%; } }
    </style>
</head>
<body>

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <h1>Admin Portal</h1>
        <nav>
            <ul>
                <li><a href="admin_dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="profile.php"><i class="fas fa-user"></i> Profile</a></li>
                <li><a href="manage_students.php" class="active"><i class="fas fa-user-graduate"></i> Manage Students</a></li>
                <li><a href="manage_advisors.php"><i class="fas fa-users"></i> Manage Advisors</a></li>
                <li><a href="manage_subjects.php"><i class="fas fa-book"></i> Manage Subjects</a></li>
                <li><a href="../forgot_password.php"><i class="fas fa-key"></i> Forgot Password</a></li>
                <li><a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </nav>
    </aside>

    <!-- MAIN CONTENT -->
    <div class="main-content">

        <!-- PAGE HEADER -->
        <div class="page-header">
            <h2>Manage Students</h2>
            <a href="add_student.php" class="btn-add"><i class="fas fa-plus-circle"></i> Add New Student</a>
        </div>

        <!-- ALERT MESSAGE -->
        <?php if (isset($message)): ?>
            <div class="alert alert-<?php echo $msg_type; ?>"><?php echo $message; ?></div>
        <?php endif; ?>

        <!-- STUDENT TABLE -->
        <div class="table-card">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th style="text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($students) > 0): ?>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($student['id']); ?></td>
                                <td><?php echo htmlspecialchars($student['name']); ?></td>
                                <td><?php echo htmlspecialchars($student['email']); ?></td>
                                <td style="text-align: center;">
                                    <a href="edit_student.php?id=<?php echo $student['id']; ?>" class="action-btn btn-edit">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="manage_students.php?delete_id=<?php echo $student['id']; ?>" 
                                       class="action-btn btn-delete" 
                                       onclick="return confirm('Are you sure you want to delete this student? This cannot be undone.');">
                                        <i class="fas fa-trash-alt"></i> Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 30px; color: #6b7280;">
                                No students found. <a href="add_student.php" style="color: #7A0D2A; font-weight: 500;">Add a student</a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

</body>
</html>