<?php
session_start();
include '../db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.html");
    exit();
}

$message = '';
$msg_type = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $code = trim($_POST['code']);
    $name = trim($_POST['name']);
    $credits = intval($_POST['credits']);

    $error = false;

    if (empty($code) || empty($name) || empty($credits)) {
        $message = "All fields are required.";
        $msg_type = 'danger';
        $error = true;
    }

    if (!$error && $credits < 1 || $credits > 5) {
        $message = "Credits must be between 1 and 5.";
        $msg_type = 'danger';
        $error = true;
    }

    if (!$error) {
        $insert = $conn->prepare("INSERT INTO subjects (code, name, credits) VALUES (?, ?, ?)");
        $insert->bind_param("ssi", $code, $name, $credits);
        if ($insert->execute()) {
            header("Location: manage_subjects.php?msg=Subject added successfully.");
            exit();
        } else {
            $message = "Error: " . $conn->error;
            $msg_type = 'danger';
        }
        $insert->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Subject - Admin Portal</title>
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
        .btn-cancel { background-color: #6b7280; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; transition: 0.2s; }
        .btn-cancel:hover { background-color: #4b5563; color: white; }
        .form-card { background: white; border-radius: 16px; padding: 30px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); max-width: 600px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 6px; color: #4b5563; font-weight: 500; }
        .form-group input { width: 100%; padding: 10px 14px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 15px; transition: 0.2s; }
        .form-group input:focus { outline: none; border-color: #7A0D2A; box-shadow: 0 0 0 3px rgba(122, 13, 42, 0.1); }
        .btn-submit { background-color: #7A0D2A; color: white; padding: 12px 30px; border: none; border-radius: 8px; font-size: 16px; cursor: pointer; transition: 0.2s; font-weight: 500; }
        .btn-submit:hover { background-color: #5c0920; }
        .alert { padding: 12px 20px; border-radius: 8px; margin-bottom: 20px; }
        .alert-danger { background-color: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
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
                <li><a href="manage_advisors.php"><i class="fas fa-users"></i> Manage Advisors</a></li>
                <li><a href="manage_subjects.php" class="active"><i class="fas fa-book"></i> Manage Subjects</a></li>
                <li><a href="../forgot_password.php"><i class="fas fa-key"></i> Forgot Password</a></li>
                <li><a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </nav>
    </aside>
    <div class="main-content">
        <div class="page-header">
            <h2>Add New Subject</h2>
            <a href="manage_subjects.php" class="btn-cancel"><i class="fas fa-arrow-left"></i> Back</a>
        </div>
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $msg_type; ?>"><?php echo $message; ?></div>
        <?php endif; ?>
        <div class="form-card">
            <form method="POST" action="">
                <div class="form-group">
                    <label for="code">Subject Code</label>
                    <input type="text" id="code" name="code" placeholder="e.g., CSC301" required>
                </div>
                <div class="form-group">
                    <label for="name">Subject Name</label>
                    <input type="text" id="name" name="name" placeholder="e.g., Database Systems" required>
                </div>
                <div class="form-group">
                    <label for="credits">Credits</label>
                    <input type="number" id="credits" name="credits" min="1" max="5" required>
                </div>
                <button type="submit" class="btn-submit"><i class="fas fa-book"></i> Add Subject</button>
            </form>
        </div>
    </div>
</body>
</html>