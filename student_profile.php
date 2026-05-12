<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

include("db.php");

$user_id = $_SESSION['user_id'];
$result = mysqli_query($conn, "SELECT * FROM users WHERE id = '$user_id'");
$user = mysqli_fetch_assoc($result);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - UTM Student</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background: #f8f6f4;
            overflow-x: hidden;
        }
        
        /* Sidebar Styles (same as dashboard) */
        .sidebar {
            width: 280px;
            height: 100vh;
            background: linear-gradient(to bottom, #670019, #8b0022);
            position: fixed;
            padding: 30px 20px;
            color: white;
            transition: transform 0.3s ease;
            z-index: 1000;
        }
        
        .sidebar.collapsed {
            transform: translateX(-280px);
        }
        
        .logo {
            text-align: center;
            margin-bottom: 50px;
        }
        
        .logo img {
            width: 130px;
        }
        
        .system-title {
            color: #ffc107;
            font-size: 16px;
            font-weight: 600;
            margin-top: 12px;
        }
        
        .menu a {
            display: flex;
            align-items: center;
            gap: 15px;
            text-decoration: none;
            color: white;
            padding: 12px 20px;
            border-radius: 14px;
            margin-bottom: 12px;
            transition: 0.3s;
            font-size: 16px;
        }
        
        .menu a:hover,
        .menu .active {
            background: linear-gradient(to right, #f4a000, #e08700);
        }
        
        .menu i {
            font-size: 20px;
        }
        
        .logout {
            position: absolute;
            bottom: 30px;
            width: calc(100% - 40px);
            left: 20px;
        }
        
        .logout a {
            display: flex;
            align-items: center;
            gap: 15px;
            text-decoration: none;
            color: white;
            padding: 12px 20px;
            border-radius: 14px;
            transition: 0.3s;
            font-size: 16px;
            background: rgba(255, 255, 255, 0.1);
        }
        
        .logout a:hover {
            background: linear-gradient(to right, #f4a000, #e08700);
        }
        
        /* Main Content */
        .main-content {
            margin-left: 280px;
            padding: 30px;
            transition: margin-left 0.3s ease;
        }
        
        .main-content.expanded {
            margin-left: 0;
        }
        
        /* Topbar */
        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            background: white;
            padding: 15px 25px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .toggle-btn {
            background: none;
            border: none;
            font-size: 22px;
            color: #333;
            cursor: pointer;
            padding: 0;
            line-height: 1;
        }
        
        .profile-box {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .profile-box img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        /* Page Header */
        .page-header {
            margin-bottom: 30px;
        }
        
        .page-header h2 {
            color: #670019;
            font-weight: 700;
            font-size: 34px;
        }
        
        /* Profile Card */
        .profile-card {
            background: white;
            border-radius: 25px;
            padding: 40px;
            box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.05);
        }
        
        .profile-pic {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .profile-pic img {
            width: 130px;
            height: 130px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #670019;
            padding: 3px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            font-weight: 500;
            margin-bottom: 8px;
            color: #333;
            font-size: 14px;
        }
        
        .form-group input {
            width: 100%;
            padding: 13px 15px;
            border: 1.5px solid #e0d6d6;
            border-radius: 14px;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
            transition: 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #670019;
            box-shadow: 0 0 0 4px rgba(103, 0, 25, 0.08);
        }
        
        .form-group input:disabled {
            background: #f5f5f5;
            color: #999;
        }
        
        .row-custom {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .save-btn {
            background: linear-gradient(to right, #670019, #8b0022);
            color: white;
            border: none;
            padding: 13px 35px;
            border-radius: 14px;
            cursor: pointer;
            font-size: 15px;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
            margin-top: 20px;
            transition: 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .save-btn:hover {
            background: linear-gradient(to right, #8b0022, #a80028);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(103, 0, 25, 0.25);
        }
        
        .change-photo {
            margin-top: 10px;
            display: inline-block;
            color: #670019;
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
        }
        
        .change-photo:hover {
            text-decoration: underline;
        }
        
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-280px);
            }
            .main-content {
                margin-left: 0;
            }
            .row-custom {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar" id="sidebar">
    <div class="logo">
        <img src="images/utmlogo.png" alt="UTM Logo">
        <div class="system-title">COURSE REGISTRATION SYSTEM</div>
    </div>
    <div class="menu">
        <a href="student_dashboard.php">
            <i class="bi bi-house-fill"></i>
            Dashboard
        </a>
        <a href="student_profile.php" class="active">
            <i class="bi bi-person-fill"></i>
            Profile
        </a>
        <a href="student_courses.php">
            <i class="bi bi-book-fill"></i>
            Courses
        </a>
        <a href="student_registration.php">
            <i class="bi bi-file-earmark-text-fill"></i>
            My Registration
        </a>
        <a href="student_change_password.php">
            <i class="bi bi-lock-fill"></i>
            Change Password
        </a>
    </div>
    <div class="logout">
        <a href="logout.php">
            <i class="bi bi-box-arrow-right"></i>
            Logout
        </a>
    </div>
</div>

<!-- MAIN CONTENT -->
<div class="main-content" id="mainContent">
    <!-- TOPBAR -->
    <div class="topbar">
        <button class="toggle-btn" onclick="toggleSidebar()">
            <i class="bi bi-list"></i>
        </button>
        <div class="profile-box">
            <i class="bi bi-bell fs-5"></i>
            <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" alt="Profile">
            <div>
                <h6 class="mb-0"><?php echo htmlspecialchars($user['name']); ?></h6>
                <small class="text-muted">Student</small>
            </div>
        </div>
    </div>

    <!-- PAGE HEADER -->
    <div class="page-header">
        <h2>My Profile</h2>
        <p>View and update your personal information</p>
    </div>

    <!-- PROFILE CARD -->
    <div class="profile-card">
        <div class="profile-pic">
            <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" alt="Profile Photo">
            <div>
                <a href="#" class="change-photo"><i class="bi bi-camera-fill me-1"></i> Change Photo</a>
            </div>
        </div>
        
        <form action="update_profile.php" method="POST">
            <div class="row-custom">
                <div class="form-group">
                    <label><i class="bi bi-person-fill me-1"></i> Full Name</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>">
                </div>
                <div class="form-group">
                    <label><i class="bi bi-card-text"></i> Student ID</label>
                    <input type="text" value="<?php echo htmlspecialchars($user['id']); ?>" disabled>
                </div>
                <div class="form-group">
                    <label><i class="bi bi-envelope-fill"></i> Email Address</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>">
                </div>
                <div class="form-group">
                    <label><i class="bi bi-telephone-fill"></i> Phone Number</label>
                    <input type="text" name="phone" value="+60 12-345 6789">
                </div>
                <div class="form-group">
                    <label><i class="bi bi-bookmark-fill"></i> Programme</label>
                    <input type="text" value="Diploma in Computer Science" disabled>
                </div>
                <div class="form-group">
                    <label><i class="bi bi-flag-fill"></i> Role</label>
                    <input type="text" value="Student" disabled>
                </div>
            </div>
            <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
            <button type="submit" class="save-btn"><i class="bi bi-save"></i> Save Changes</button>
        </form>
    </div>
</div>

<script>
    (function() {
        if (localStorage.getItem('sidebarCollapsed') === 'true') {
            document.querySelector('.sidebar').classList.add('collapsed');
            document.querySelector('.main-content').classList.add('expanded');
        }
    })();

    function toggleSidebar() {
        const sidebar = document.querySelector('.sidebar');
        const main = document.querySelector('.main-content');
        sidebar.classList.toggle('collapsed');
        main.classList.toggle('expanded');
        localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
    }
</script>
</body>
</html>