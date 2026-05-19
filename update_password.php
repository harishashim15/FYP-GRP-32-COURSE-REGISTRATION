<?php
/**
 * update_password.php
 * Receives the new password form from new_password.php,
 * validates the token again, updates the password in users table,
 * then redirects to login.
 */

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

// ── ONLY ACCEPT POST ──────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.html');
    exit;
}

// ── GET INPUTS ────────────────────────────────────────────────────────────────
$token          = isset($_POST['token'])           ? trim($_POST['token'])           : '';
$email          = isset($_POST['email'])           ? trim($_POST['email'])           : '';
$new_password   = isset($_POST['new_password'])    ? $_POST['new_password']          : '';
$confirm_pass   = isset($_POST['confirm_password'])? $_POST['confirm_password']      : '';

// ── SERVER-SIDE VALIDATION ────────────────────────────────────────────────────
function redirectWithError($msg) {
    // Go back to login with an error flag (simple approach)
    header('Location: index.html?reset=error&msg=' . urlencode($msg));
    exit;
}

if ($token === '' || $email === '') {
    redirectWithError('Invalid request.');
}

if ($new_password === '') {
    redirectWithError('Password cannot be empty.');
}

if (strlen($new_password) < 6) {
    redirectWithError('Password must be at least 6 characters.');
}

if ($new_password !== $confirm_pass) {
    redirectWithError('Passwords do not match.');
}

// ── VALIDATE TOKEN AGAIN (security: prevent replay attacks) ───────────────────
$stmt = $pdo->prepare(
    "SELECT email, expires_at FROM password_resets
     WHERE token = :token AND email = :email LIMIT 1"
);
$stmt->execute([':token' => $token, ':email' => $email]);
$row = $stmt->fetch();

if (!$row) {
    redirectWithError('Invalid or already used reset link.');
}

if (strtotime($row['expires_at']) < time()) {
    redirectWithError('This reset link has expired. Please request a new one.');
}

// ── UPDATE PASSWORD IN USERS TABLE ────────────────────────────────────────────
// NOTE: Passwords in your DB are currently plain text (e.g. '1234').
// This saves the new password the same way (plain text) to stay consistent.
// When you're ready to add hashing, replace $new_password with password_hash($new_password, PASSWORD_BCRYPT)

$update = $pdo->prepare(
    "UPDATE users SET password = :password WHERE email = :email"
);
$update->execute([
    ':password' => $new_password,
    ':email'    => $email,
]);

// ── DELETE USED TOKEN ─────────────────────────────────────────────────────────
$pdo->prepare("DELETE FROM password_resets WHERE token = :token")
    ->execute([':token' => $token]);

// ── REDIRECT TO LOGIN WITH SUCCESS ────────────────────────────────────────────
header('Location: index.html?reset=success');
exit;
