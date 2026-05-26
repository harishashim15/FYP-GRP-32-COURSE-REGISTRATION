<?php
session_start();
require_once '../db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.html");
    exit();
}

$message = '';
$msg_type = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $matrix = trim($_POST['matrix']);
    $name   = trim($_POST['name']);
    $email  = trim($_POST['email']);
    $second_email = trim($_POST['second_email']);
    $phone  = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];
    $faculty = $_POST['faculty'] ?? 'Faculty Of SPACE';
    $department = $_POST['department'] ?? 'Computer Science';

    $error = false;

    if (empty($matrix) || empty($name) || empty($email) || empty($phone) || empty($password)) {
        $message = "All required fields must be filled.";
        $msg_type = 'danger';
        $error = true;
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format.";
        $msg_type = 'danger';
        $error = true;
    } elseif (strlen($password) < 6) {
        $message = "Password must be at least 6 characters.";
        $msg_type = 'danger';
        $error = true;
    } elseif ($password !== $confirm) {
        $message = "Passwords do not match.";
        $msg_type = 'danger';
        $error = true;
    } else {
        // Check duplicate matrix
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE matrix_number = ?");
        $stmt->bind_param("s", $matrix);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $message = "Matrix number already exists.";
            $msg_type = 'danger';
            $error = true;
        }
        $stmt->close();

        // Check duplicate email
        if (!$error) {
            $stmt = $conn->prepare("SELECT user_id FROM users WHERE utm_email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                $message = "UTM email already registered.";
                $msg_type = 'danger';
                $error = true;
            }
            $stmt->close();
        }
    }

    if (!$error) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $login_cred = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $name)); // simple login_cred

        // Insert into users table
        $stmt = $conn->prepare("INSERT INTO users (matrix_number, user_name, utm_email, second_email, phone, password, role, login_cred) VALUES (?, ?, ?, ?, ?, ?, 'advisor', ?)");
        $stmt->bind_param("sssssss", $matrix, $name, $email, $second_email, $phone, $hashed, $login_cred);
        if ($stmt->execute()) {
            $user_id = $stmt->insert_id;

            // Insert into advisor table
            $stmt2 = $conn->prepare("INSERT INTO advisor (user_id, advisor_name, matrix_number, utm_email, second_email, faculty, department) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt2->bind_param("issssss", $user_id, $name, $matrix, $email, $second_email, $faculty, $department);
            $stmt2->execute();
            $stmt2->close();

            header("Location: manage_advisors.php?msg=Advisor added successfully.");
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
    <title>Add Advisor - Admin Portal</title>
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
        .form-card { background: white; border-radius: 25px; padding: 35px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); max-width: 700px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 6px; font-weight: 500; color: #333; }
        .form-group input, .form-group select { width: 100%; padding: 10px 15px; border: 1px solid #ddd; border-radius: 12px; font-size: 14px; transition: 0.2s; }
        .form-group input:focus, .form-group select:focus { outline: none; border-color: #670019; box-shadow: 0 0 0 3px rgba(103,0,25,0.08); }
        .btn-submit { background: linear-gradient(to right, #670019, #8b0022); color: white; border: none; padding: 12px 30px; border-radius: 25px; font-weight: 600; cursor: pointer; transition: 0.3s; }
        .btn-submit:hover { background: linear-gradient(to right, #8b0022, #a80028); transform: translateY(-2px); }
        .alert { padding: 12px 20px; border-radius: 20px; margin-bottom: 20px; }
        .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        @media (max-width: 992px) {
            .sidebar { transform: translateX(-280px); }
            .main-content { margin-left: 0; width: 100%; }
        }
    </style>
</head>
<body>
<div class="sidebar">
    <div class="logo"><img src="../images/utmlogo.png" alt="UTM Logo"><div class="system-title">COURSE REGISTRATION SYSTEM</div></div>
    <div class="menu">
        <a href="admin_dashboard.php"><i class="bi bi-house-fill"></i> Dashboard</a>
        <a href="manage_students.php"><i class="bi bi-people-fill"></i> Manage Students</a>
        <a href="manage_advisors.php" class="active"><i class="bi bi-person-badge-fill"></i> Manage Advisors</a>
        <a href="manage_subjects.php"><i class="bi bi-book-fill"></i> Manage Subjects</a>
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
            <div><h6 class="mb-0">Admin</h6><small class="text-muted">Admin</small></div>
        </div>
    </div>
    <div class="page-header">
        <h2>Add New Advisor</h2>
        <a href="manage_advisors.php" class="btn-cancel"><i class="bi bi-arrow-left"></i> Back</a>
    </div>
    <?php if ($message): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    <div class="form-card">
        <form method="POST" action="">
            <div class="form-group">
                <label>Matrix Number</label>
                <input type="text" name="matrix" required>
            </div>
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="name" required>
            </div>
            <div class="form-group">
                <label>UTM Email</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Second Email (optional)</label>
                <input type="email" name="second_email">
            </div>
            <div class="form-group">
                <label>Phone Number</label>
                <input type="text" name="phone" required>
            </div>
            <div class="form-group">
                <label>Faculty</label>
                <select name="faculty">
                    <option value="Faculty Of SPACE">Faculty Of SPACE</option>
                    <option value="Faculty Of Computer Science">Faculty Of Computer Science</option>
                </select>
            </div>
            <div class="form-group">
                <label>Department</label>
                <select name="department">
                    <option value="Computer Science">Computer Science</option>
                    <option value="Mathematics">Mathematics</option>
                    <option value="Sports Science">Sports Science</option>
                    <option value="Electrical Engineering">Electrical Engineering</option>
                    <option value="Pengajian Islam">Pengajian Islam</option>
                </select>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" required>
            </div>
            <button type="submit" class="btn-submit"><i class="bi bi-person-plus"></i> Add Advisor</button>
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