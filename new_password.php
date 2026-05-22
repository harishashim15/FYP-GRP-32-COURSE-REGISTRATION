<?php
/**
 * new_password.php
 * Handles token validation and password update.
 */

session_start();
error_reporting(0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errors.log');

// ── DATABASE CONNECTION (fypdb3) ─────────────────────────────────────────────
$host    = 'localhost';
$db      = 'fypdb3';
$user    = 'root';
$pass    = '';
$charset = 'utf8mb4';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$db;charset=$charset",
        $user, $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die('Database connection failed.');
}

// ── GET TOKEN FROM URL ──────────────────────────────────────────────────────
$token   = isset($_GET['token']) ? trim($_GET['token']) : '';
$invalid = false;
$email   = '';
$error   = '';
$success = false;

// Validate token
if ($token === '') {
    $invalid = true;
    $error = 'No reset token provided.';
} else {
    $stmt = $pdo->prepare("SELECT email, expires_at FROM password_resets WHERE token = ? LIMIT 1");
    $stmt->execute([$token]);
    $row = $stmt->fetch();

    if (!$row) {
        $invalid = true;
        $error = 'Invalid or expired reset link.';
    } elseif (strtotime($row['expires_at']) < time()) {
        $invalid = true;
        $error = 'This reset link has expired. Please request a new one.';
    } else {
        $email = $row['email'];
    }
}

// ── HANDLE PASSWORD UPDATE ───────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$invalid && $email) {
    $newPass = trim($_POST['new_password'] ?? '');
    $confirm = trim($_POST['confirm_password'] ?? '');

    if (strlen($newPass) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($newPass !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        // Find the user by either utm_email or second_email (the email used in reset)
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE utm_email = :email OR second_email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if ($user) {
            // Update password (plain text – matches your existing system)
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE user_id = ?");
            $stmt->execute([$newPass, $user['user_id']]);
            // Delete the used token
            $pdo->prepare("DELETE FROM password_resets WHERE token = ?")->execute([$token]);
            $success = true;
        } else {
            $error = 'User not found.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Reset Password - UTM Course Registration</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="images/logoWebsite.png"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.7.2/animate.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/hamburgers/1.1.3/hamburgers.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" type="text/css" href="css/util.css">
    <link rel="stylesheet" type="text/css" href="css/main.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        .password-requirements { font-size: 12px; color: #999; margin-top: 5px; text-align: left; padding-left: 30px; }
        .toggle-eye { position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #999; font-size: 18px; background: none; border: none; padding: 0; line-height: 1; z-index: 10; transition: color 0.2s; }
        .toggle-eye:hover { color: #670019; }
        .wrap-input100 { position: relative; }
        .alert-custom { border-radius: 14px; padding: 14px 20px; margin-bottom: 20px; }
        .alert-success-custom { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger-custom { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>
<div class="limiter">
    <div class="container-login100">
        <div class="wrap-login100">
            <div class="login100-pic js-tilt" data-tilt>
                <img src="images/logoWebsite.png" alt="IMG">
            </div>

            <?php if ($success): ?>
                <!-- Success Message -->
                <div class="login100-form">
                    <span class="login100-form-title tracking-in-expand">Password Reset</span>
                    <div class="alert-custom alert-success-custom text-center">
                        <i class="bi bi-check-circle-fill fs-4"></i>
                        <p class="mt-2 mb-0">Your password has been changed successfully.</p>
                    </div>
                    <div class="text-center p-t-20">
                        <a class="txt2" href="index.html">Back to Login</a>
                    </div>
                </div>
            <?php elseif ($invalid || $error): ?>
                <!-- Error Message -->
                <div class="login100-form">
                    <span class="login100-form-title tracking-in-expand">Error</span>
                    <div class="alert-custom alert-danger-custom text-center">
                        <i class="bi bi-exclamation-triangle-fill fs-4"></i>
                        <p class="mt-2 mb-0"><?php echo htmlspecialchars($error); ?></p>
                    </div>
                    <div class="text-center p-t-20">
                        <a class="txt2" href="forgot_password.html">Request New Link</a><br><br>
                        <a class="txt2" href="index.html">Back to Login</a>
                    </div>
                </div>
            <?php else: ?>
                <!-- Reset Password Form -->
                <form class="login100-form validate-form" method="POST">
                    <span class="login100-form-title tracking-in-expand">Reset Password</span>

                    <div class="wrap-input100 validate-input" data-validate="New password is required">
                        <input class="input100" type="password" name="new_password" id="new_password" placeholder="New Password" required>
                        <span class="focus-input100"></span>
                        <span class="symbol-input100"><i class="fa fa-lock" aria-hidden="true"></i></span>
                        <button type="button" class="toggle-eye" onclick="toggleVisibility('new_password', this)"><i class="bi bi-eye"></i></button>
                    </div>

                    <div class="wrap-input100 validate-input" data-validate="Please confirm your password">
                        <input class="input100" type="password" name="confirm_password" id="confirm_password" placeholder="Confirm Password" required>
                        <span class="focus-input100"></span>
                        <span class="symbol-input100"><i class="fa fa-lock" aria-hidden="true"></i></span>
                        <button type="button" class="toggle-eye" onclick="toggleVisibility('confirm_password', this)"><i class="bi bi-eye"></i></button>
                    </div>

                    <div class="password-requirements">
                        <i class="fa fa-info-circle"></i> Password must be at least 8 characters
                    </div>

                    <div class="container-login100-form-btn" style="margin-top: 20px;">
                        <button type="submit" class="login100-form-btn">Reset Password</button>
                    </div>

                    <div class="text-center p-t-136">
                        <a class="txt2" href="index.html">Back to Login</a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/tilt.js/1.2.1/tilt.jquery.min.js"></script>
<script>
    $('.js-tilt').tilt({ scale: 1.1 });

    function toggleVisibility(inputId, btn) {
        var input = document.getElementById(inputId);
        var icon = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.className = 'bi bi-eye-slash';
        } else {
            input.type = 'password';
            icon.className = 'bi bi-eye';
        }
    }

    // Client-side validation
    document.querySelector('form')?.addEventListener('submit', function(e) {
        var newPass = document.getElementById('new_password').value;
        var confirmPass = document.getElementById('confirm_password').value;
        if (newPass !== confirmPass) {
            e.preventDefault();
            alert('Passwords do not match!');
        } else if (newPass.length < 8) {
            e.preventDefault();
            alert('Password must be at least 8 characters!');
        }
    });
</script>
</body>
</html>