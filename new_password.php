<?php
/**
 * new_password.php
 * User lands here from the reset link in their email.
 * Validates the token, then shows the new password form.
 */

require_once __DIR__ . '/PHPMailer/src/Exception.php';
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';

// ── DB CONFIG ─────────────────────────────────────────────────────────────────
$host    = 'localhost';
$db      = 'fypdb3';
$user    = 'root';
$pass    = '';
$charset = 'utf8mb4';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$db;charset=$charset",
        $user, $pass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    die('Database connection failed.');
}

// ── VALIDATE TOKEN ────────────────────────────────────────────────────────────
$token   = isset($_GET['token']) ? trim($_GET['token']) : '';
$invalid = false;
$email   = '';

if ($token === '') {
    $invalid = true;
} else {
    $stmt = $pdo->prepare(
        "SELECT email, expires_at FROM password_resets
         WHERE token = :token LIMIT 1"
    );
    $stmt->execute([':token' => $token]);
    $row = $stmt->fetch();

    if (!$row) {
        $invalid = true; // token not found
    } elseif (strtotime($row['expires_at']) < time()) {
        $invalid = true; // token expired
    } else {
        $email = $row['email'];
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

    <style>
        .field-error-msg {
            color: #e74c3c;
            font-size: 13px;
            display: block;
            margin-top: 5px;
            padding-left: 5px;
        }
        #new-pass-error {
            color: #e74c3c;
            text-align: center;
            margin-top: 12px;
            font-size: 14px;
            display: none;
        }
        .invalid-token-box {
            text-align: center;
            padding: 30px 20px;
        }
        .invalid-token-box i {
            font-size: 52px;
            color: #e74c3c;
            display: block;
            margin-bottom: 15px;
        }
        .invalid-token-box p {
            color: #555;
            font-size: 15px;
            line-height: 1.7;
        }
    </style>
</head>
<body>

<div class="limiter">
    <div class="container-login100">
        <div class="wrap-login100">

            <div class="login100-pic js-tilt" data-tilt>
                <img src="images/logoWebsite.png" alt="IMG">
            </div>

            <?php if ($invalid): ?>

                <!-- ── INVALID / EXPIRED TOKEN ── -->
                <div class="login100-form">
                    <span class="login100-form-title tracking-in-expand">
                        Link Expired
                    </span>
                    <div class="invalid-token-box">
                        <i class="fa fa-times-circle"></i>
                        <p>
                            This password reset link is <strong>invalid or has expired</strong>.<br><br>
                            Reset links are only valid for <strong>1 hour</strong>.<br>
                            Please request a new one.
                        </p>
                    </div>
                    <div class="container-login100-form-btn">
                        <a href="forgot_password.html" class="login100-form-btn"
                           style="display:flex; align-items:center; justify-content:center; text-decoration:none;">
                            Request New Link
                        </a>
                    </div>
                    <div class="text-center p-t-12" style="margin-top:15px;">
                        <a class="txt2" href="index.html">Back to Login</a>
                    </div>
                </div>

            <?php else: ?>

                <!-- ── NEW PASSWORD FORM ── -->
                <form id="new-password-form" class="login100-form validate-form"
                      action="update_password.php" method="POST">

                    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                    <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">

                    <span class="login100-form-title tracking-in-expand">
                        Reset Password
                    </span>

                    <!-- New Password -->
                    <div class="wrap-input100 validate-input" data-validate="Password is required">
                        <input class="input100" type="password" name="new_password"
                               id="new_password" placeholder="New Password">
                        <span class="focus-input100"></span>
                        <span class="symbol-input100">
                            <i class="fa fa-lock" aria-hidden="true"></i>
                        </span>
                    </div>

                    <!-- Confirm Password -->
                    <div class="wrap-input100 validate-input" data-validate="Please confirm your password">
                        <input class="input100" type="password" name="confirm_password"
                               id="confirm_password" placeholder="Confirm New Password">
                        <span class="focus-input100"></span>
                        <span class="symbol-input100">
                            <i class="fa fa-lock" aria-hidden="true"></i>
                        </span>
                    </div>

                    <p id="new-pass-error"></p>

                    <div class="container-login100-form-btn">
                        <button type="submit" id="new-pass-btn" class="login100-form-btn">
                            Reset Password
                        </button>
                    </div>

                    <div class="text-center p-t-12" style="margin-top:15px;">
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

    $('#new-password-form').on('submit', function (e) {
        var newPass     = $('#new_password').val();
        var confirmPass = $('#confirm_password').val();
        var $err        = $('#new-pass-error');

        $err.hide();

        if (newPass === '') {
            e.preventDefault();
            $err.text('Please enter a new password.').show();
            return;
        }

        if (newPass.length < 6) {
            e.preventDefault();
            $err.text('Password must be at least 6 characters.').show();
            return;
        }

        if (newPass !== confirmPass) {
            e.preventDefault();
            $err.text('Passwords do not match.').show();
            return;
        }

        // All good — let the form submit normally to update_password.php
        $('#new-pass-btn').prop('disabled', true).text('Saving…');
    });
</script>

</body>
</html>
