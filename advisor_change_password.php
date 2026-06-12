<?php
require_once 'db_connect.php';

session_start();

// Get advisor ID from session
$advisor_id = $_SESSION['user_id'] ?? null;

// If not logged in, redirect to login
if (!$advisor_id) {
    header("Location: ../index.html");
    exit;
}

// Verify the user is actually an advisor
$sql_check = "SELECT role FROM users WHERE user_id = ?";
$stmt_check = mysqli_prepare($conn, $sql_check);
mysqli_stmt_bind_param($stmt_check, "i", $advisor_id);
mysqli_stmt_execute($stmt_check);
$result_check = mysqli_stmt_get_result($stmt_check);
$user_check = mysqli_fetch_assoc($result_check);

if (!$user_check || $user_check['role'] != 'advisor') {
    session_destroy();
    header("Location: ../index.html");
    exit;
}

// Get advisor password and name from users table
$sql = "SELECT password, user_name FROM users WHERE user_id = ? AND role = 'advisor'";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $advisor_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

$advisor_name = $user['user_name'] ?? 'Advisor';

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Verify current password using password_verify (supports both hashed and plain text for migration)
    $password_valid = false;
    
    // Check if stored password is hashed (starts with $2y$)
    if (str_starts_with($user['password'], '$2y$')) {
        // Hashed password - use password_verify
        $password_valid = password_verify($current_password, $user['password']);
    } else {
        // Plain text password (for migration) - direct comparison
        $password_valid = ($current_password === $user['password']);
    }
    
    if (!$password_valid) {
        $message = 'Current password is incorrect!';
        $message_type = 'danger';
    } elseif (strlen($new_password) < 8) {
        $message = 'New password must be at least 8 characters!';
        $message_type = 'danger';
    } elseif ($new_password !== $confirm_password) {
        $message = 'New passwords do not match!';
        $message_type = 'danger';
    } else {
        // Hash the new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        $sql = "UPDATE users SET password = ? WHERE user_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "si", $hashed_password, $advisor_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $message = 'Password updated successfully!';
            $message_type = 'success';
        } else {
            $message = 'Error updating password. Please try again.';
            $message_type = 'danger';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UTM Academic Advisor – Change Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background: #f8f6f4; overflow-x: hidden; }
        .sidebar { width: 280px; height: 100vh; background: linear-gradient(to bottom, #670019, #8b0022); position: fixed; padding: 30px 20px; color: white; transition: transform 0.3s ease; }
        .sidebar.collapsed { transform: translateX(-280px); }
        .logo { text-align: center; margin-bottom: 50px; }
        .logo img { width: 130px; }
        .system-title { color: white; font-size: 16px; font-weight: 600; margin-top: 12px; }
        .menu a { display: flex; align-items: center; gap: 15px; text-decoration: none; color: white; padding: 12px 20px; border-radius: 14px; margin-bottom: 12px; transition: 0.3s; font-size: 16px; }
        .menu a:hover, .menu .active { background: linear-gradient(to right, #f4a000, #e08700); }
        .menu i { font-size: 20px; }
        .logout { position: absolute; bottom: 30px; width: calc(100% - 40px); left: 20px; }
        .logout a { display: flex; align-items: center; gap: 15px; text-decoration: none; color: white; padding: 12px 20px; border-radius: 14px; transition: 0.3s; font-size: 16px; background: rgba(255,255,255,0.1); }
        .logout a:hover { background: linear-gradient(to right, #f4a000, #e08700); }
        .main-content { margin-left: 280px; padding: 30px; transition: margin-left 0.3s ease; }
        .main-content.expanded { margin-left: 0; }
        .topbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; background: white; padding: 15px 25px; border-radius: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .profile-box { display: flex; align-items: center; gap: 15px; cursor: pointer; }
        .profile-box img { width: 50px; height: 50px; border-radius: 50%; }
        .toggle-btn { background: none; border: none; font-size: 22px; color: #333; cursor: pointer; }
        .page-header { background: #f7f2ee; border-radius: 25px; padding: 35px 40px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 35px; border: 1px solid #eee; }
        .page-header h1 { font-size: 34px; font-weight: 700; color: #670019; }
        .page-header p { color: #666; margin-top: 8px; font-size: 15px; }
        .page-header img { width: 180px; }
        
        /* FULL-WIDTH FORM CARD */
        .form-card { 
            background: white; 
            border-radius: 25px; 
            padding: 40px; 
            box-shadow: 0px 4px 15px rgba(0,0,0,0.05); 
            width: 100%; 
        }
        
        .form-card h3 { color: #670019; font-weight: 700; font-size: 20px; margin-bottom: 25px; padding-bottom: 15px; border-bottom: 1px solid #f0e8e8; }
        .form-label { font-size: 14px; font-weight: 600; color: #333; margin-bottom: 8px; }
        .input-wrapper { position: relative; margin-bottom: 22px; }
        .input-wrapper .form-control { border: 1.5px solid #e0d6d6; border-radius: 14px; padding: 13px 50px 13px 20px; font-size: 14px; background: #fdfafa; width: 100%; }
        .input-wrapper .form-control:focus { outline: none; border-color: #670019; box-shadow: 0 0 0 4px rgba(103,0,25,0.08); }
        .input-wrapper .toggle-eye { position: absolute; right: 16px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #999; font-size: 18px; background: none; border: none; }
        .strength-bar-wrapper { margin-top: -10px; margin-bottom: 22px; }
        .strength-label { font-size: 12px; font-weight: 500; color: #888; margin-bottom: 5px; }
        .strength-bar { height: 6px; border-radius: 10px; background: #eee; overflow: hidden; }
        .strength-fill { height: 100%; border-radius: 10px; width: 0%; transition: width 0.4s, background 0.4s; }
        .requirements { background: #fdf8f8; border: 1px solid #f0e5e5; border-radius: 14px; padding: 18px 20px; margin-bottom: 25px; }
        .requirements p { font-size: 13px; font-weight: 600; color: #670019; margin-bottom: 10px; }
        .req-item { display: flex; align-items: center; gap: 9px; font-size: 13px; color: #888; margin-bottom: 6px; }
        .req-item i { font-size: 14px; }
        .req-item.met { color: #2e7d32; }
        .req-item.met i { color: #2e7d32; }
        .btn-save { background: linear-gradient(to right, #670019, #8b0022); color: white; border: none; padding: 13px 35px; border-radius: 14px; font-size: 15px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; }
        .btn-save:hover { background: linear-gradient(to right, #8b0022, #a80028); transform: translateY(-2px); }
        .btn-cancel { background: white; color: #670019; border: 1.5px solid #e0d6d6; padding: 13px 35px; border-radius: 14px; font-size: 15px; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; }
        .btn-cancel:hover { background: #fff7ef; border-color: #f4a000; }
        .alert-custom { border-radius: 14px; padding: 14px 20px; font-size: 14px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
        .alert-success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
        .alert-danger { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
        .tip-card { background: white; border-radius: 25px; padding: 30px; box-shadow: 0px 4px 15px rgba(0,0,0,0.05); max-width: 340px; height: fit-content; }
        .tip-card h4 { color: #670019; font-weight: 700; font-size: 17px; margin-bottom: 18px; padding-bottom: 12px; border-bottom: 1px solid #f0e8e8; }
        .tip-item { display: flex; gap: 13px; margin-bottom: 18px; }
        .tip-icon { width: 40px; height: 40px; border-radius: 12px; background: #fff2cc; color: #d48a00; display: flex; justify-content: center; align-items: center; font-size: 18px; flex-shrink: 0; }
        .tip-icon.green { background: #e4f7df; color: #2e7d32; }
        .tip-icon.red { background: #ffe0e0; color: #b00020; }
        .tip-text h6 { font-size: 13px; font-weight: 600; color: #333; margin-bottom: 3px; }
        .tip-text small { font-size: 12px; color: #888; }
        
        /* Layout for side-by-side */
        .d-flex-custom { display: flex; gap: 30px; flex-wrap: wrap; }
        .form-card-wrapper { flex: 1; min-width: 300px; }
        .tip-card-wrapper { width: 340px; }
        
        @media (max-width: 992px) {
            .tip-card-wrapper { width: 100%; }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo"><img src="images/utmlogo.png" alt="UTM Logo"><div class="system-title">COURSE REGISTRATION SYSTEM</div></div>
        <div class="menu">
            <a href="advisor_dashboard.php"><i class="bi bi-house-fill"></i> Dashboard</a>
            <a href="advisor_my_students.php"><i class="bi bi-people-fill"></i> My Students</a>
            <a href="advisor_registrations.php"><i class="bi bi-file-earmark-text-fill"></i> Registrations</a>
            <a href="advisor_profile.php"><i class="bi bi-person-fill"></i> Profile</a>
            <a href="#" class="active"><i class="bi bi-lock-fill"></i> Change Password</a>
        </div>
        <div class="logout"><a href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></div>
    </div>

    <div class="main-content">
        <div class="topbar">
            <button class="toggle-btn" onclick="toggleSidebar()"><i class="bi bi-list"></i></button>
            <div class="profile-box" onclick="location.href='advisor_profile.php'">
                <i class="bi bi-bell fs-5"></i>
                <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png">
                <div><h6 class="mb-0"><?php echo htmlspecialchars($advisor_name); ?></h6><small class="text-muted">Academic Advisor</small></div>
            </div>
        </div>

        <div class="page-header">
            <div><h1>Change Password</h1><p>Keep your account secure by updating your password regularly.</p></div>
        </div>

        <div class="d-flex-custom">
            <div class="form-card-wrapper">
                <div class="form-card">
                    <h3><i class="bi bi-shield-lock-fill me-2"></i>Update Your Password</h3>

                    <?php if ($message): ?>
                        <div class="alert-custom alert-<?php echo $message_type; ?>"><i class="bi bi-<?php echo $message_type == 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill'; ?> fs-5"></i><span><?php echo $message; ?></span></div>
                    <?php endif; ?>

                    <form method="POST" id="passwordForm">
                        <div>
                            <label class="form-label">Current Password</label>
                            <div class="input-wrapper">
                                <input type="password" class="form-control" name="current_password" id="currentPassword" placeholder="Enter your current password" required>
                                <button type="button" class="toggle-eye" onclick="toggleVisibility('currentPassword', this)"><i class="bi bi-eye"></i></button>
                            </div>
                        </div>
                        
                        <div>
                            <label class="form-label">New Password</label>
                            <div class="input-wrapper">
                                <input type="password" class="form-control" name="new_password" id="newPassword" placeholder="Enter your new password" oninput="checkStrength(this.value); checkRequirements(this.value);" required>
                                <button type="button" class="toggle-eye" onclick="toggleVisibility('newPassword', this)"><i class="bi bi-eye"></i></button>
                            </div>
                        </div>
                        
                        <div class="strength-bar-wrapper">
                            <div class="strength-label" id="strengthLabel">Password strength: —</div>
                            <div class="strength-bar"><div class="strength-fill" id="strengthFill"></div></div>
                        </div>
                        
                        <div class="requirements">
                            <p><i class="bi bi-info-circle me-1"></i> Password Requirements</p>
                            <div class="req-item" id="req-length"><i class="bi bi-circle"></i> At least 8 characters</div>
                            <div class="req-item" id="req-upper"><i class="bi bi-circle"></i> At least one uppercase letter (A–Z)</div>
                            <div class="req-item" id="req-lower"><i class="bi bi-circle"></i> At least one lowercase letter (a–z)</div>
                            <div class="req-item" id="req-number"><i class="bi bi-circle"></i> At least one number (0–9)</div>
                            <div class="req-item" id="req-special"><i class="bi bi-circle"></i> At least one special character (!@#$%^&*)</div>
                        </div>
                        
                        <div>
                            <label class="form-label">Confirm New Password</label>
                            <div class="input-wrapper">
                                <input type="password" class="form-control" name="confirm_password" id="confirmPassword" placeholder="Re-enter your new password" oninput="checkMatch()" required>
                                <button type="button" class="toggle-eye" onclick="toggleVisibility('confirmPassword', this)"><i class="bi bi-eye"></i></button>
                            </div>
                            <div id="matchMsg" style="font-size:12px; margin-top:-14px; margin-bottom:16px;"></div>
                        </div>
                        
                        <div class="d-flex gap-3 flex-wrap mt-2">
                            <button type="submit" class="btn-save"><i class="bi bi-shield-check"></i> Update Password</button>
                            <a href="advisor_dashboard.php" class="btn-cancel"><i class="bi bi-x-circle"></i> Cancel</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="tip-card-wrapper">
                <div class="tip-card">
                    <h4><i class="bi bi-lightbulb-fill me-2"></i>Security Tips</h4>
                    <div class="tip-item"><div class="tip-icon"><i class="bi bi-key-fill"></i></div><div class="tip-text"><h6>Use a unique password</h6><small>Don't reuse passwords from other sites or accounts.</small></div></div>
                    <div class="tip-item"><div class="tip-icon green"><i class="bi bi-calendar-check"></i></div><div class="tip-text"><h6>Change regularly</h6><small>Update your password every 3–6 months for better security.</small></div></div>
                    <div class="tip-item"><div class="tip-icon red"><i class="bi bi-exclamation-triangle-fill"></i></div><div class="tip-text"><h6>Never share your password</h6><small>UTM staff will never ask for your password via email or phone.</small></div></div>
                    <div class="tip-item"><div class="tip-icon"><i class="bi bi-shield-fill-check"></i></div><div class="tip-text"><h6>Mix characters</h6><small>Combine uppercase, lowercase, numbers, and symbols for a strong password.</small></div></div>
                    <div class="tip-item"><div class="tip-icon green"><i class="bi bi-incognito"></i></div><div class="tip-text"><h6>Avoid personal info</h6><small>Don't use your name, birthday, or student ID in your password.</small></div></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleVisibility(inputId, btn) { 
            const input = document.getElementById(inputId); 
            const icon = btn.querySelector('i'); 
            if (input.type === 'password') { 
                input.type = 'text'; 
                icon.className = 'bi bi-eye-slash'; 
            } else { 
                input.type = 'password'; 
                icon.className = 'bi bi-eye'; 
            } 
        }
        
        function checkStrength(val) { 
            const fill = document.getElementById('strengthFill'); 
            const label = document.getElementById('strengthLabel'); 
            let score = 0; 
            if (val.length >= 8) score++; 
            if (/[A-Z]/.test(val)) score++; 
            if (/[a-z]/.test(val)) score++; 
            if (/[0-9]/.test(val)) score++; 
            if (/[^A-Za-z0-9]/.test(val)) score++; 
            const levels = [
                { w: '0%', bg: '#eee', text: '—' }, 
                { w: '20%', bg: '#e74c3c', text: 'Very Weak' }, 
                { w: '40%', bg: '#e67e22', text: 'Weak' }, 
                { w: '60%', bg: '#f1c40f', text: 'Fair' }, 
                { w: '80%', bg: '#2ecc71', text: 'Strong' }, 
                { w: '100%', bg: '#27ae60', text: 'Very Strong' }
            ]; 
            const l = val.length === 0 ? levels[0] : levels[score]; 
            fill.style.width = l.w; 
            fill.style.background = l.bg; 
            label.textContent = `Password strength: ${l.text}`; 
        }
        
        function checkRequirements(val) { 
            const checks = { 
                'req-length': val.length >= 8, 
                'req-upper': /[A-Z]/.test(val), 
                'req-lower': /[a-z]/.test(val), 
                'req-number': /[0-9]/.test(val), 
                'req-special': /[^A-Za-z0-9]/.test(val) 
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
            if (cp === '') { 
                msg.textContent = ''; 
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
    </script>
</body>
</html>