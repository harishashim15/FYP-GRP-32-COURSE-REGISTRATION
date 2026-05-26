<?php
session_start();
require_once '../db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.html");
    exit();
}

// Fetch admin name
$admin_name = 'Admin';
$stmt = $conn->prepare("SELECT user_name FROM users WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $admin_name = $row['user_name'];
}
$stmt->close();

// Delete subject
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM subjects WHERE subject_code = ?");
    $stmt->bind_param("s", $delete_id);
    $stmt->execute();
    $stmt->close();
    header("Location: manage_subjects.php?msg=Subject deleted successfully.");
    exit();
}

// Fetch all subjects
$subjects = [];
$query = "SELECT subject_code, subject_name, credits FROM subjects ORDER BY subject_code";
$result = $conn->query($query);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $subjects[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Subjects - Admin Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background: #f8f6f4; overflow-x: hidden; }
        .sidebar {
            width: 280px; height: 100vh;
            background: linear-gradient(to bottom, #670019, #8b0022);
            position: fixed; padding: 30px 20px; color: white;
            transition: transform 0.3s ease;
        }
        .sidebar.collapsed { transform: translateX(-280px); }
        .logo { text-align: center; margin-bottom: 50px; }
        .logo img { width: 130px; }
        .system-title { color: #ffc107; font-size: 16px; font-weight: 600; margin-top: 12px; }
        .menu a {
            display: flex; align-items: center; gap: 15px;
            text-decoration: none; color: white; padding: 12px 20px;
            border-radius: 14px; margin-bottom: 12px; transition: 0.3s; font-size: 16px;
        }
        .menu a:hover, .menu .active { background: linear-gradient(to right, #f4a000, #e08700); }
        .menu i { font-size: 20px; }
        .logout {
            position: absolute; bottom: 30px;
            width: calc(100% - 40px); left: 20px;
        }
        .logout a {
            display: flex; align-items: center; gap: 15px;
            text-decoration: none; color: white; padding: 12px 20px;
            border-radius: 14px; background: rgba(255,255,255,0.1);
        }
        .logout a:hover { background: linear-gradient(to right, #f4a000, #e08700); }
        .main-content { margin-left: 280px; padding: 30px; transition: margin-left 0.3s ease; }
        .main-content.expanded { margin-left: 0; }
        .topbar {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 30px; background: white; padding: 15px 25px;
            border-radius: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .toggle-btn { background: none; border: none; font-size: 22px; cursor: pointer; }
        .profile-box { display: flex; align-items: center; gap: 15px; cursor: pointer; }
        .profile-box img { width: 50px; height: 50px; border-radius: 50%; }
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .page-header h2 { color: #670019; font-weight: 700; }
        .btn-add {
            background: linear-gradient(to right, #670019, #8b0022);
            color: white; padding: 10px 20px; border-radius: 25px;
            text-decoration: none; display: inline-flex; align-items: center; gap: 8px;
            transition: 0.3s;
        }
        .btn-add:hover { background: linear-gradient(to right, #8b0022, #a80028); color: white; }
        .table-card { background: white; border-radius: 25px; padding: 25px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; background: #f8f6f4; padding: 12px 15px; color: #670019; font-weight: 600; }
        td { padding: 12px 15px; border-bottom: 1px solid #eee; color: #333; }
        tr:hover { background: #fdf9f7; }
        .action-btn { padding: 5px 12px; border-radius: 20px; text-decoration: none; font-size: 13px; display: inline-block; margin-right: 5px; }
        .btn-edit { background: #f4a000; color: white; }
        .btn-edit:hover { background: #e08700; color: white; }
        .btn-delete { background: #dc2626; color: white; }
        .btn-delete:hover { background: #b91c1c; color: white; }
        .alert { padding: 12px 20px; border-radius: 20px; margin-bottom: 20px; background: #d4edda; color: #155724; }
        @media (max-width: 992px) {
            .sidebar { transform: translateX(-280px); }
            .main-content { margin-left: 0; }
        }
    </style>
</head>
<body>
<div class="sidebar">
    <div class="logo"><img src="../images/utmlogo.png" alt="UTM Logo"><div class="system-title">COURSE REGISTRATION SYSTEM</div></div>
    <div class="menu">
        <a href="admin_dashboard.php"><i class="bi bi-house-fill"></i> Dashboard</a>
        <a href="manage_students.php"><i class="bi bi-people-fill"></i> Manage Students</a>
        <a href="manage_advisors.php"><i class="bi bi-person-badge-fill"></i> Manage Advisors</a>
        <a href="manage_subjects.php" class="active"><i class="bi bi-book-fill"></i> Manage Subjects</a>
        <a href="profile.php"><i class="bi bi-person-fill"></i> Profile</a>
        <a href="../forgot_password.php"><i class="bi bi-key-fill"></i> Forgot Password</a>
    </div>
    <div class="logout"><a href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></div>
</div>
<div class="main-content">
    <div class="topbar">
        <button class="toggle-btn" onclick="toggleSidebar()"><i class="bi bi-list"></i></button>
        <div class="profile-box" onclick="location.href='profile.php'">
            <i class="bi bi-bell fs-5"></i>
            <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" alt="Profile">
            <div><h6 class="mb-0"><?php echo htmlspecialchars($admin_name); ?></h6><small class="text-muted">Admin</small></div>
        </div>
    </div>
    <div class="page-header">
        <h2>Manage Subjects</h2>
        <a href="add_subject.php" class="btn-add"><i class="bi bi-plus-circle"></i> Add Subject</a>
    </div>
    <?php if (isset($_GET['msg'])): ?>
        <div class="alert"><?php echo htmlspecialchars($_GET['msg']); ?></div>
    <?php endif; ?>
    <div class="table-card">
        <table>
            <thead><tr><th>Subject Code</th><th>Subject Name</th><th>Credits</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($subjects as $s): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($s['subject_code']); ?></td>
                        <td><?php echo htmlspecialchars($s['subject_name']); ?></td>
                        <td><?php echo $s['credits']; ?></td>
                        <td>
                            <a href="edit_subject.php?code=<?php echo urlencode($s['subject_code']); ?>" class="action-btn btn-edit"><i class="bi bi-pencil"></i> Edit</a>
                            <a href="manage_subjects.php?delete_id=<?php echo urlencode($s['subject_code']); ?>" class="action-btn btn-delete" onclick="return confirm('Delete this subject?')"><i class="bi bi-trash"></i> Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($subjects)): ?>
                    <tr><td colspan="4" class="text-center">No subjects found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<script>
    function toggleSidebar() {
        const sidebar = document.querySelector('.sidebar');
        const main = document.querySelector('.main-content');
        sidebar.classList.toggle('collapsed');
        main.classList.toggle('expanded');
        localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
    }
    (function() {
        if (localStorage.getItem('sidebarCollapsed') === 'true') {
            document.querySelector('.sidebar').classList.add('collapsed');
            document.querySelector('.main-content').classList.add('expanded');
        }
    })();
</script>
</body>
</html>