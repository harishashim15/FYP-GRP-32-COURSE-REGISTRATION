<?php
/**
 * reset_password.php - For fypdb3
 * Handles AJAX requests from forgotpassword.js
 */

require_once __DIR__ . '/PHPMailer/src/Exception.php';
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ── DATABASE CONFIG (fypdb3) ─────────────────────────────────────────────────
define('DB_HOST',    'localhost');
define('DB_NAME',    'fypdb3');
define('DB_USER',    'root');
define('DB_PASS',    '');
define('DB_CHARSET', 'utf8mb4');

// ── RATE LIMITING ────────────────────────────────────────────────────────────
define('RATE_LIMIT_MAX',    5);
define('RATE_LIMIT_WINDOW', 15 * 60); // 15 minutes

header('Content-Type: text/plain; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('error');
}

$action = isset($_POST['action']) ? trim($_POST['action']) : '';
switch ($action) {
    case 'check_email':
        handleCheckEmail();
        break;
    default:
        http_response_code(400);
        exit('error');
}

// ── HANDLER ───────────────────────────────────────────────────────────────────
function handleCheckEmail() {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        exit('invalid_email');
    }

    $pdo = getConnection();

    if (isRateLimited($pdo, $_SERVER['REMOTE_ADDR'])) {
        exit('rate_limited');
    }

    // Query fypdb3.users by utm_email
    $stmt = $pdo->prepare("SELECT user_id, user_name FROM users WHERE second_email = :email LIMIT 1");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        exit('not_found');
    }

    $token   = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', time() + 3600); // 1 hour

    // Clear old tokens for this email
    $pdo->prepare("DELETE FROM password_resets WHERE email = :email")->execute([':email' => $email]);

    $pdo->prepare("INSERT INTO password_resets (email, token, expires_at, created_at) VALUES (:email, :token, :expires, NOW())")
        ->execute([':email' => $email, ':token' => $token, ':expires' => $expires]);

    $sent = sendResetEmail($email, $user['user_name'], $token);
    logAttempt($pdo, $_SERVER['REMOTE_ADDR']);

    if ($sent) {
        exit('found');
    } else {
        exit('mail_error');
    }
}

// ── DATABASE CONNECTION ───────────────────────────────────────────────────────
function getConnection(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', DB_HOST, DB_NAME, DB_CHARSET);
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            error_log('reset_password.php: DB connection failed – ' . $e->getMessage());
            exit('error');
        }
    }
    return $pdo;
}

// ── RATE LIMITING ─────────────────────────────────────────────────────────────
function isRateLimited(PDO $pdo, string $ip): bool {
    $since = date('Y-m-d H:i:s', time() - RATE_LIMIT_WINDOW);
    $stmt = $pdo->prepare("SELECT COUNT(*) AS cnt FROM reset_attempts WHERE ip = :ip AND attempted_at >= :since");
    $stmt->execute([':ip' => $ip, ':since' => $since]);
    return (int) $stmt->fetch()['cnt'] >= RATE_LIMIT_MAX;
}

function logAttempt(PDO $pdo, string $ip): void {
    $pdo->prepare("INSERT INTO reset_attempts (ip, attempted_at) VALUES (:ip, NOW())")->execute([':ip' => $ip]);
}

// ── EMAIL (PHPMailer) ─────────────────────────────────────────────────────────
function sendResetEmail(string $email, string $name, string $token): bool {
    // Change the URL to where new_password.php will be placed
    $resetUrl = 'http://localhost/FYP/FYP%20COURSE%20REGISTRATION/new_password.php?token=' . urlencode($token);

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'ariefiqmal2006@gmail.com';
        $mail->Password   = 'xill egye ginu ebig';   // Your App Password
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        $mail->setFrom('ariefiqmal2006@gmail.com', 'UTM SYSTEM ADMIN');
        $mail->addAddress($email, $name);
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset Request';
        $mail->Body    = "
            Hi <strong>{$name}</strong>,<br><br>
            We received a request to reset your password.<br>
            Click the link below to set a new password (expires in 1 hour):<br><br>
            <a href='{$resetUrl}'>{$resetUrl}</a><br><br>
            If you did not request this, you can safely ignore this email.<br><br>
            Regards,<br>
            <b>UTM SYSTEM ADMIN</b>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Email send failed: ' . $mail->ErrorInfo);
        return false;
    }
}
?>