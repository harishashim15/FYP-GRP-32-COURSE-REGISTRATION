<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

include("db_connect.php");

// Fetch student name from database
$user_id = $_SESSION['user_id'];
$user_query = "SELECT name FROM users WHERE id = '$user_id'";
$user_result = mysqli_query($conn, $user_query);
$user = mysqli_fetch_assoc($user_result);
$student_name = $user ? $user['name'] : "Student";

$message = '';
$message_type = '';

if (isset($_POST['update'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Check current password
    $check_query = "SELECT * FROM users WHERE id = '$user_id' AND password = '$current_password'";
    $check_result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($check_result) > 0) {
        if ($new_password === $confirm_password) {
            if (strlen($new_password) >= 6) {
                $update = "UPDATE users SET password = '$new_password' WHERE id = '$user_id'";
                if (mysqli_query($conn, $update)) {
                    $message = "Your password has been updated successfully!";
                    $message_type = "success";
                } else {
                    $message = "Failed to update password. Please try again.";
                    $message_type = "error";
                }
            } else {
                $message = "New password must be at least 6 characters long.";
                $message_type = "error";
            }
        } else {
            $message = "New passwords do not match. Please make sure both passwords are identical.";
            $message_type = "error";
        }
    } else {
        $message = "Current password is incorrect. Please enter your correct current password.";
        $message_type = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password - UTM Student</title>
    
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
        
        .main-content {
            margin-left: 280px;
            padding: 30px;
            transition: margin-left 0.3s ease;
        }
        
        .main-content.expanded {
            margin-left: 0;
        }
        
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
            cursor: pointer;
            transition: 0.3s;
        }
        
        .profile-box:hover {
            opacity: 0.8;
        }
        
        .profile-box img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .page-header {
            background: #f7f2ee;
            border-radius: 25px;
            padding: 35px 40px;
            margin-bottom: 35px;
            border: 1px solid #eee;
        }
        
        .page-header h1 {
            font-size: 34px;
            font-weight: 700;
            color: #670019;
        }
        
        .page-header p {
            color: #666;
            margin-top: 8px;
            font-size: 15px;
        }
        
        .form-card {
            background: white;
            border-radius: 25px;
            padding: 40px;
            box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.05);
            max-width: 700px;
        }
        
        .form-card h3 {
            color: #670019;
            font-weight: 700;
            font-size: 20px;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0e8e8;
        }
        
        .form-label {
            font-size: 14px;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }
        
        .input-wrapper {
            position: relative;
            margin-bottom: 22px;
        }
        
        .input-wrapper .form-control {
            border: 1.5px solid #e0d6d6;
            border-radius: 14px;
            padding: 13px 50px 13px 20px;
            font-size: 14px;
            width: 100%;
            transition: 0.3s;
        }
        
        .input-wrapper .form-control:focus {
            outline: none;
            border-color: #670019;
            box-shadow: 0 0 0 4px rgba(103, 0, 25, 0.08);
        }
        
        .toggle-eye {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #999;
            font-size: 18px;
            background: none;
            border: none;
            padding: 0;
        }
        
        .toggle-eye:hover {
            color: #670019;
        }
        
        .strength-bar-wrapper {
            margin-top: -10px;
            margin-bottom: 22px;
        }
        
        .strength-label {
            font-size: 12px;
            font-weight: 500;
            color: #888;
            margin-bottom: 5px;
        }
        
        .strength-bar {
            height: 6px;
            border-radius: 10px;
            background: #eee;
            overflow: hidden;
        }
        
        .strength-fill {
            height: 100%;
            border-radius: 10px;
            width: 0%;
            transition: width 0.4s, background 0.4s;
        }
        
        .requirements {
            background: #fdf8f8;
            border: 1px solid #f0e5e5;
            border-radius: 14px;
            padding: 18px 20px;
            margin-bottom: 25px;
        }
        
        .requirements p {
            font-size: 13px;
            font-weight: 600;
            color: #670019;
            margin-bottom: 10px;
        }
        
        .req-item {
            display: flex;
            align-items: center;
            gap: 9px;
            font-size: 13px;
            color: #888;
            margin-bottom: 6px;
            transition: color 0.3s;
        }
        
        .req-item.met {
            color: #2e7d32;
        }
        
        .btn-save {
            background: linear-gradient(to right, #670019, #8b0022);
            color: white;
            border: none;
            padding: 13px 35px;
            border-radius: 14px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-save:hover {
            background: linear-gradient(to right, #8b0022, #a80028);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(103, 0, 25, 0.25);
        }
        
        .btn-cancel {
            background: white;
            color: #670019;
            border: 1.5px solid #e0d6d6;
            padding: 13px 35px;
            border-radius: 14px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-cancel:hover {
            background: #fff7ef;
            border-color: #f4a000;
            color: #670019;
        }
        
        .alert-custom {
            border-radius: 14px;
            padding: 14px 20px;
            font-size: 14px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert-success-custom {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        
        .alert-error-custom {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        
        .tip-card {
            background: white;
            border-radius: 25px;
            padding: 30px;
            box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.05);
            max-width: 340px;
            height: fit-content;
        }
        
        .tip-card h4 {
            color: #670019;
            font-weight: 700;
            font-size: 17px;
            margin-bottom: 18px;
            padding-bottom: 12px;
            border-bottom: 2px solid #f0e8e8;
        }
        
        .tip-item {
            display: flex;
            gap: 13px;
            margin-bottom: 18px;
        }
        
        .tip-icon {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: #fff2cc;
            color: #d48a00;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 18px;
            flex-shrink: 0;
        }
        
        .tip-icon.red {
            background: #ffe0e0;
            color: #b00020;
        }
        
        .tip-icon.green {
            background: #e4f7df;
            color: #2e7d32;
        }
        
        .tip-text h6 {
            font-size: 13px;
            font-weight: 600;
            color: #333;
            margin-bottom: 3px;
        }
        
        .tip-text small {
            font-size: 12px;
            color: #888;
            line-height: 1.5;
        }
        
        .match-message {
            font-size: 12px;
            margin-top: -14px;
            margin-bottom: 16px;
        }
        
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-280px);
            }
            .main-content {
                margin-left: 0;
            }
            .page-header {
                text-align: center;
            }
            .d-flex.gap-4 {
                flex-direction: column;
            }
            .tip-card {
                max-width: 100%;
            }
        }
    </style>
</head>
<body>

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
        <a href="student_profile.php">
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
        <a href="student_change_password.php" class="active">
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

<div class="main-content" id="mainContent">
    <div class="topbar">
        <button class="toggle-btn" onclick="toggleSidebar()">
            <i class="bi bi-list"></i>
        </button>
        <div class="profile-box" onclick="window.location.href='student_profile.php'">
            <i class="bi bi-bell fs-5"></i>
            <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" alt="Profile">
            <div>
                <h6 class="mb-0"><?php echo htmlspecialchars($student_name); ?></h6>
                <small class="text-muted">Change Password</small>
            </div>
        </div>
    </div>

    <div class="page-header">
        <div>
            <h1>Change Password</h1>
            <p>Keep your account secure by updating your password regularly.</p>
        </div>
    </div>

    <div class="d-flex gap-4 flex-wrap">
        <div class="form-card flex-grow-1">
            <h3><i class="bi bi-shield-lock-fill me-2"></i>Update Your Password</h3>
            
            <?php if ($message): ?>
            <div class="alert-custom alert-<?php echo $message_type; ?>-custom">
                <i class="bi bi-<?php echo $message_type == 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill'; ?> fs-5 me-2"></i>
                <span><?php echo $message; ?></span>
            </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div>
                    <label class="form-label">Current Password</label>
                    <div class="input-wrapper">
                        <input type="password" class="form-control" id="currentPassword" name="current_password" placeholder="Enter your current password" required>
                        <button type="button" class="toggle-eye" onclick="toggleVisibility('currentPassword', this)">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>
                
                <div>
                    <label class="form-label">New Password</label>
                    <div class="input-wrapper">
                        <input type="password" class="form-control" id="newPassword" name="new_password" placeholder="Enter your new password" oninput="checkStrength(this.value); checkRequirements(this.value);" required>
                        <button type="button" class="toggle-eye" onclick="toggleVisibility('newPassword', this)">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    
                    <div class="strength-bar-wrapper">
                        <div class="strength-label" id="strengthLabel">Password strength: </div>
                        <div class="strength-bar">
                            <div class="strength-fill" id="strengthFill"></div>
                        </div>
                    </div>
                    
                    <div class="requirements">
                        <p><i class="bi bi-info-circle me-1"></i> Password Requirements</p>
                        <div class="req-item" id="req-length">
                            <i class="bi bi-circle"></i>
                            At least 6 characters
                        </div>
                        <div class="req-item" id="req-upper">
                            <i class="bi bi-circle"></i>
                            At least one uppercase letter (A-Z)
                        </div>
                        <div class="req-item" id="req-lower">
                            <i class="bi bi-circle"></i>
                            At least one lowercase letter (a-z)
                        </div>
                        <div class="req-item" id="req-number">
                            <i class="bi bi-circle"></i>
                            At least one number (0-9)
                        </div>
                    </div>
                    
                    <div>
                        <label class="form-label">Confirm New Password</label>
                        <div class="input-wrapper">
                            <input type="password" class="form-control" id="confirmPassword" name="confirm_password" placeholder="Re-enter your new password" oninput="checkMatch()" required>
                            <button type="button" class="toggle-eye" onclick="toggleVisibility('confirmPassword', this)">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <div class="match-message" id="matchMsg"></div>
                    </div>
                    
                    <div class="d-flex gap-3 flex-wrap mt-2">
                        <button type="submit" name="update" class="btn-save">
                            <i class="bi bi-shield-check"></i>
                            Update Password
                        </button>
                        <a href="student_dashboard.php" class="btn-cancel">
                            <i class="bi bi-x-circle"></i>
                            Cancel
                        </a>
                    </div>
                </div>
            </form>
        </div>
        
        <div class="tip-card">
            <h4><i class="bi bi-lightbulb-fill me-2"></i>Security Tips</h4>
            <div class="tip-item">
                <div class="tip-icon">
                    <i class="bi bi-key-fill"></i>
                </div>
                <div class="tip-text">
                    <h6>Use a unique password</h6>
                    <small>Don't reuse passwords from other sites or accounts.</small>
                </div>
            </div>
            <div class="tip-item">
                <div class="tip-icon green">
                    <i class="bi bi-calendar-check"></i>
                </div>
                <div class="tip-text">
                    <h6>Change regularly</h6>
                    <small>Update your password every 3-6 months for better security.</small>
                </div>
            </div>
            <div class="tip-item">
                <div class="tip-icon red">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </div>
                <div class="tip-text">
                    <h6>Never share your password</h6>
                    <small>UTM staff will never ask for your password via email or phone.</small>
                </div>
            </div>
            <div class="tip-item">
                <div class="tip-icon">
                    <i class="bi bi-shield-fill-check"></i>
                </div>
                <div class="tip-text">
                    <h6>Mix characters</h6>
                    <small>Combine uppercase, lowercase, numbers, and symbols for a strong password.</small>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleVisibility(inputId, button) {
        const input = document.getElementById(inputId);
        const icon = button.querySelector('i');
        
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
    }
    
    function checkStrength(val) {
        const fill = document.getElementById('strengthFill');
        const label = document.getElementById('strengthLabel');
        let score = 0;
        
        if (val.length >= 6) score++;
        if (/[A-Z]/.test(val)) score++;
        if (/[a-z]/.test(val)) score++;
        if (/[0-9]/.test(val)) score++;
        
        const levels = {
            0: { w: '0%', bg: '#eee', text: '—' },
            1: { w: '25%', bg: '#e74c3c', text: 'Very Weak' },
            2: { w: '50%', bg: '#e67e22', text: 'Weak' },
            3: { w: '75%', bg: '#f1c40f', text: 'Fair' },
            4: { w: '100%', bg: '#2ecc71', text: 'Strong' }
        };
        
        const l = levels[score];
        fill.style.width = l.w;
        fill.style.backgroundColor = l.bg;
        label.textContent = 'Password strength: ' + l.text;
    }
    
    function checkRequirements(val) {
        const checks = {
            'req-length': val.length >= 6,
            'req-upper': /[A-Z]/.test(val),
            'req-lower': /[a-z]/.test(val),
            'req-number': /[0-9]/.test(val)
        };
        
        for (const [id, passed] of Object.entries(checks)) {
            const el = document.getElementById(id);
            const icon = el.querySelector('i');
            if (passed) {
                el.classList.add('met');
                icon.className = 'bi bi-check-circle-fill';
            } else {
                el.classList.remove('met');
                icon.className = 'bi bi-circle';
            }
        }
    }
    
    function checkMatch() {
        const np = document.getElementById('newPassword').value;
        const cp = document.getElementById('confirmPassword').value;
        const msg = document.getElementById('matchMsg');
        
        if (cp === "") {
            msg.innerHTML = "";
            return;
        }
        
        if (np === cp) {
            msg.innerHTML = '<span style="color:#2e7d32;"><i class="bi bi-check-circle-fill"></i> Passwords match</span>';
        } else {
            msg.innerHTML = '<span style="color:#b00020;"><i class="bi bi-x-circle-fill"></i> Passwords do not match</span>';
        }
    }
    
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
    
    setTimeout(function() {
        var alert = document.querySelector('.alert-custom');
        if (alert) {
            alert.style.opacity = '0';
            setTimeout(function() {
                alert.style.display = 'none';
            }, 500);
        }
    }, 4000);
</script>
</body>
</html>