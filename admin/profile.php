<?php
session_start();
require_once '../db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.html");
    exit();
}

// Fetch admin name for topbar
$admin_name = 'Admin';
$stmt = $conn->prepare("SELECT user_name FROM users WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $admin_name = $row['user_name'];
}
$stmt->close();

$message = '';
$msg_type = '';

// Handle profile update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = trim($_POST['full_name']);
    $utm_email = trim($_POST['utm_email']);
    $second_email = trim($_POST['second_email']);
    $phone = trim($_POST['phone']);

    $error = false;

    if (empty($full_name) || empty($utm_email)) {
        $message = "Full name and UTM email are required.";
        $msg_type = 'danger';
        $error = true;
    } elseif (!filter_var($utm_email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid UTM email format.";
        $msg_type = 'danger';
        $error = true;
    }

    if (!$error) {
        $stmt = $conn->prepare("UPDATE users SET user_name = ?, utm_email = ?, second_email = ?, phone = ? WHERE user_id = ?");
        $stmt->bind_param("ssssi", $full_name, $utm_email, $second_email, $phone, $_SESSION['user_id']);
        if ($stmt->execute()) {
            $message = "Profile updated successfully.";
            $msg_type = 'success';
            // Update displayed name
            $admin_name = $full_name;
        } else {
            $message = "Database error: " . $conn->error;
            $msg_type = 'danger';
        }
        $stmt->close();
    }
}

// Fetch current admin data
$admin_data = [];
$stmt = $conn->prepare("SELECT user_name, matrix_number, utm_email, second_email, phone, role FROM users WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $admin_data = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile - UTM Course Registration</title>
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
            text-decoration: none; color: white; padding: 9px 20px;
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
        .page-header { margin-bottom: 30px; }
        .page-header h2 { color: #670019; font-weight: 700; }
        .profile-card {
            background: white; border-radius: 25px; padding: 35px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
        .profile-pic { text-align: center; margin-bottom: 25px; }
        .profile-pic img { width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 4px solid #670019; padding: 3px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { font-weight: 500; margin-bottom: 8px; color: #333; font-size: 14px; }
        .form-group input {
            width: 100%; padding: 12px 15px; border: 1.5px solid #e0d6d6;
            border-radius: 14px; font-size: 14px; transition: 0.3s;
        }
        .form-group input:focus { outline: none; border-color: #670019; box-shadow: 0 0 0 4px rgba(103,0,25,0.08); }
        .form-group input:disabled { background: #f5f5f5; color: #999; }
        .row-custom { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .save-btn {
            background: linear-gradient(to right, #670019, #8b0022);
            color: white; border: none; padding: 12px 30px;
            border-radius: 25px; cursor: pointer; font-size: 14px;
            font-weight: 500; margin-top: 10px; transition: 0.3s;
        }
        .save-btn:hover {
            background: linear-gradient(to right, #8b0022, #a80028);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(103,0,25,0.25);
        }
        .alert-success {
            display: none; background: #d4edda; color: #155724;
            border-radius: 12px; padding: 12px 20px; margin-bottom: 20px;
        }
        .alert-danger {
            background: #f8d7da; color: #721c24;
            border-radius: 12px; padding: 12px 20px; margin-bottom: 20px;
        }
        @media (max-width: 992px) {
            .sidebar { transform: translateX(-280px); }
            .main-content { margin-left: 0; }
            .row-custom { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div class="sidebar">
    <div class="logo"><img src="../images/utmlogo.png" alt="UTM Logo"><div class="system-title">COURSE REGISTRATION SYSTEM</div></div>
   <div class="menu">
       <a href="admin_dashboard.php"><i class="bi bi-house-fill"></i> Dashboard</a>
        <a href="manage_students.php"><i class="bi bi-people-fill"></i> Manage Students</a>
        <a href="manage_advisors.php" ><i class="bi bi-person-badge-fill"></i> Manage Advisors</a>
        <a href="manage_subjects.php"><i class="bi bi-book-fill"></i> Manage Subjects</a>
        <a href="manage_registration_period.php"><i class="bi bi-calendar-event"></i> Registration Period</a>
        <a href="../forgot_password.html"><i class="bi bi-key-fill"></i> Forgot Password</a>
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
        <h2>My Profile</h2>
    </div>
    <div class="profile-card">
        <div class="profile-pic">
            <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" alt="Profile Photo">
            <div class="mt-2">
                <a href="#" id="changePhotoLink" style="color: #670019;">Change Photo</a>
            </div>
        </div>
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $msg_type; ?>"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <div id="successAlert" class="alert-success">
            <i class="bi bi-check-circle-fill me-2"></i> Profile updated successfully!
        </div>
        <form method="POST">
            <div class="row-custom">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="full_name" value="<?php echo htmlspecialchars($admin_data['user_name'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label>Staff ID (Matrix)</label>
                    <input type="text" value="<?php echo htmlspecialchars($admin_data['matrix_number'] ?? ''); ?>" disabled>
                </div>
                <div class="form-group">
                    <label>UTM Email</label>
                    <input type="email" name="utm_email" value="<?php echo htmlspecialchars($admin_data['utm_email'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label>Second Email</label>
                    <input type="email" name="second_email" value="<?php echo htmlspecialchars($admin_data['second_email'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="text" name="phone" value="<?php echo htmlspecialchars($admin_data['phone'] ?? ''); ?>">
                </div>
               <div class="form-group">
    <label>Role</label>
    <input type="text" value="<?php echo ucfirst(htmlspecialchars($admin_data['role'] ?? 'admin')); ?>" disabled>
</div>
            </div>
            <button type="submit" class="save-btn"><i class="bi bi-save"></i> Save Changes</button>
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

    // Show success alert after update (optional)
    <?php if ($message === "Profile updated successfully."): ?>
        document.getElementById('successAlert').style.display = 'block';
        setTimeout(() => {
            document.getElementById('successAlert').style.display = 'none';
        }, 4000);
    <?php endif; ?>

    document.getElementById('changePhotoLink')?.addEventListener('click', (e) => {
        e.preventDefault();
        alert('Photo upload feature will be implemented later');
    });
</script>
</body>
</html>