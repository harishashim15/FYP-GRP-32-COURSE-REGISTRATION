<?php
session_start();
include '../db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.html");
    exit();
}

if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role = 'advisor'");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();
    header("Location: manage_advisors.php?msg=Advisor deleted successfully.");
    exit();
}

$advisors = [];
$query = "SELECT id, name, email FROM users WHERE role = 'advisor' ORDER BY id DESC";
$result = $conn->query($query);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $advisors[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Advisors - Admin Portal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { display: flex; background-color: #f4f6f9; min-height: 100vh; }
        .sidebar { width: 250px; background-color: #7A0D2A; color: white; display: flex; flex-direction: column; padding: 20px 0; position: fixed; height: 100%; left: 0; top: 0; z-index: 1000; }
        .sidebar h1 { font-size: 24px; font-weight: 600; padding: 0 25px 30px 25px; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar nav ul { list-style: none; padding: 20px 15px; }
        .sidebar nav ul li { margin-bottom: 12px; }
        .sidebar nav ul li a { display: flex; align-items: center; text-decoration: none; color: white; padding: 12px 20px; border-radius: 8px; transition: 0.3s ease; font-size: 16px; }
        .sidebar nav ul li a i { margin-right: 15px; width: 20px; text-align: center; }
        .sidebar nav ul li a:hover { background-color: rgba(255,255,255,0.1); }
        .sidebar nav ul li a.active { background-color: #DE9E1F; color: #fff; font-weight: 500; }
        .sidebar nav ul li a.logout { margin-top: 60px; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 20px; border-radius: 0; }
        .main-content { margin-left: 250px; width: calc(100% - 250px); padding: 30px; background-color: #f4f6f9; }
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .page-header h2 { color: #1f2937; }
        .btn-add { background-color: #7A0D2A; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; transition: 0.2s; }
        .btn-add:hover { background-color: #5c0920; color: white; }
        .table-card { background: white; border-radius: 16px; padding: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 15px; }
        th { text-align: left; background-color: #f8f9fa; padding: 12px 15px; color: #4b5563; font-weight: 600; border-bottom: 2px solid #e5e7eb; }
        td { padding: 12px 15px; border-bottom: 1px solid #e5e7eb; color: #1f2937; }
        tr:hover { background-color: #f9fafb; }
        .action-btn { padding: 6px 12px; border: none; border-radius: 6px; font-size: 13px; cursor: pointer; text-decoration: none; display: inline-block; margin-right: 5px; }
        .btn-edit { background-color: #DE9E1F; color: white; }
        .btn-edit:hover { background-color: #c48a1a; color: white; }
        .btn-delete { background-color: #dc2626; color: white; }
        .btn-delete:hover { background-color: #b91c1c; color: white; }
        .alert { padding: 12px 20px; border-radius: 8px; margin-bottom: 20px; }
        .alert-success { background-color: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        @media (max-width: 768px) { .sidebar { width: 200px; } .main-content { margin-left: 200px; } }
        @media (max-width: 576px) { body { flex-direction: column; } .sidebar { width: 100%; height: auto; position: relative; } .main-content { margin-left: 0; width: 100%; } }
    </style>
</head>
<body>
    <aside class="sidebar">
        <h1>Admin Portal</h1>
        <nav>
            <ul>
                <li><a href="admin_dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="profile.php"><i class="fas fa-user"></i> Profile</a></li>
                <li><a href="manage_students.php"><i class="fas fa-user-graduate"></i> Manage Students</a></li>
                <li><a href="manage_advisors.php" class="active"><i class="fas fa-users"></i> Manage Advisors</a></li>
                <li><a href="manage_subjects.php"><i class="fas fa-book"></i> Manage Subjects</a></li>
                <li><a href="../forgot_password.php"><i class="fas fa-key"></i> Forgot Password</a></li>
                <li><a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </nav>
    </aside>
    <div class="main-content">
        <div class="page-header">
            <h2>Manage Advisors</h2>
            <a href="add_advisor.php" class="btn-add"><i class="fas fa-plus-circle"></i> Add New Advisor</a>
        </div>
        <?php if (isset($_GET['msg'])): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($_GET['msg']); ?></div>
        <?php endif; ?>
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
                    <?php if (count($advisors) > 0): ?>
                        <?php foreach ($advisors as $advisor): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($advisor['id']); ?></td>
                                <td><?php echo htmlspecialchars($advisor['name']); ?></td>
                                <td><?php echo htmlspecialchars($advisor['email']); ?></td>
                                <td style="text-align: center;">
                                    <a href="edit_advisor.php?id=<?php echo $advisor['id']; ?>" class="action-btn btn-edit">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="manage_advisors.php?delete_id=<?php echo $advisor['id']; ?>" 
                                       class="action-btn btn-delete" 
                                       onclick="return confirm('Delete this advisor?');">
                                        <i class="fas fa-trash-alt"></i> Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 30px; color: #6b7280;">
                                No advisors found. <a href="add_advisor.php" style="color: #7A0D2A;">Add an advisor</a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>