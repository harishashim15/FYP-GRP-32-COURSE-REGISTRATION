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

// Get subject code from URL
if (!isset($_GET['code']) || empty($_GET['code'])) {
    header("Location: manage_subjects.php?msg=Invalid subject code.");
    exit();
}
$subject_code = trim($_GET['code']);

// Fetch subject details
$stmt = $conn->prepare("SELECT subject_code, subject_name, credits FROM subjects WHERE subject_code = ?");
$stmt->bind_param("s", $subject_code);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    header("Location: manage_subjects.php?msg=Subject not found.");
    exit();
}
$subject = $result->fetch_assoc();
$stmt->close();

$message = '';
$msg_type = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $code = trim($_POST['code']);
    $name = trim($_POST['name']);
    $credits = intval($_POST['credits']);

    $errors = [];

    if (empty($code)) $errors[] = "Subject code is required.";
    if (empty($name)) $errors[] = "Subject name is required.";
    if ($credits < 1 || $credits > 5) $errors[] = "Credits must be between 1 and 5.";

    if (empty($errors)) {
        // Check if new code already exists (when code is being changed)
        if ($code !== $subject_code) {
            $stmt = $conn->prepare("SELECT subject_code FROM subjects WHERE subject_code = ?");
            $stmt->bind_param("s", $code);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                $errors[] = "Subject code already exists. Cannot change to a duplicate code.";
            }
            $stmt->close();
        }
    }

    if (!empty($errors)) {
        $message = implode("<br>", $errors);
        $msg_type = 'danger';
    } else {
        $stmt = $conn->prepare("UPDATE subjects SET subject_code = ?, subject_name = ?, credits = ? WHERE subject_code = ?");
        $stmt->bind_param("ssis", $code, $name, $credits, $subject_code);
        if ($stmt->execute()) {
            header("Location: manage_subjects.php?msg=Subject updated successfully.");
            exit();
        } else {
            $message = "Database error: " . $conn->error;
            $msg_type = 'danger';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Subject - Admin Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background: #f8f6f4; display: flex; overflow-x: hidden; }
        .sidebar {
            width: 280px; height: 100vh;
            background: linear-gradient(to bottom, #670019, #8b0022);
            position: fixed; padding: 30px 20px; color: white;
            transition: transform 0.3s ease;
        }
        .sidebar.collapsed { transform: translateX(-280px); }
        .logo { text-align: center; margin-bottom: 50px; }
        .logo img { width: 130px; }
        .system-title { color: white; font-size: 16px; font-weight: 600; margin-top: 12px; }
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
        .main-content { margin-left: 280px; padding: 30px; transition: margin-left 0.3s ease; width: calc(100% - 280px); }
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
        .btn-cancel { background: #6c757d; color: white; padding: 8px 20px; border-radius: 25px; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; }
        .btn-cancel:hover { background: #5a6268; color: white; }
        .form-card { background: white; border-radius: 25px; padding: 35px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); max-width: 800px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 6px; font-weight: 500; color: #333; }
        .form-group input, .form-group select { width: 100%; padding: 10px 15px; border: 1px solid #ddd; border-radius: 12px; font-size: 14px; }
        .form-group input:focus, .form-group select:focus { outline: none; border-color: #670019; box-shadow: 0 0 0 3px rgba(103,0,25,0.08); }
        .row-custom { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .btn-submit { background: linear-gradient(to right, #670019, #8b0022); color: white; border: none; padding: 12px 30px; border-radius: 25px; font-weight: 600; cursor: pointer; transition: 0.3s; }
        .btn-submit:hover { background: linear-gradient(to right, #8b0022, #a80028); transform: translateY(-2px); }
        .alert { padding: 12px 20px; border-radius: 20px; margin-bottom: 20px; }
        .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        @media (max-width: 992px) {
            .sidebar { transform: translateX(-280px); }
            .main-content { margin-left: 0; width: 100%; }
            .row-custom { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div class="sidebar">
    <div class="logo"><img src="../images/utmlogo.png" alt="UTM Logo"><div class="system-title">COURSE REGISTRATION SYSTEM</div></div>
    <div class="menu">
        <a href="admin_dashboard.php" ><i class="bi bi-house-fill"></i> Dashboard</a>
        <a href="manage_students.php"><i class="bi bi-people-fill"></i> Manage Students</a>
        <a href="manage_advisors.php" ><i class="bi bi-person-badge-fill"></i> Manage Advisors</a>
        <a href="manage_subjects.php" class="active"><i class="bi bi-book-fill"></i> Manage Subjects</a>
        <a href="manage_registration_period.php"><i class="bi bi-calendar-event"></i> Registration Period</a>
        <a href="admin_changepassword.php"><i class="bi bi-key-fill"></i> Change Password</a>
    </div>
    <div class="logout"><a href="../index.html"><i class="bi bi-box-arrow-right"></i> Logout</a></div>
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
        <h2>Edit Subject</h2>
        <a href="manage_subjects.php" class="btn-cancel"><i class="bi bi-arrow-left"></i> Back</a>
    </div>
    <?php if ($message): ?>
        <div class="alert alert-danger"><?php echo nl2br(htmlspecialchars($message)); ?></div>
    <?php endif; ?>
    <div class="form-card">
        <form method="POST">
            <div class="row-custom">
                <div class="form-group">
                    <label>Subject Code</label>
                    <input type="text" name="code" value="<?php echo htmlspecialchars($subject['subject_code']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Subject Name</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($subject['subject_name']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Credits</label>
                    <input type="number" name="credits" min="1" max="5" value="<?php echo $subject['credits']; ?>" required>
                </div>
            </div>
            <button type="submit" class="btn-submit"><i class="bi bi-save"></i> Update Subject</button>
        </form>
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