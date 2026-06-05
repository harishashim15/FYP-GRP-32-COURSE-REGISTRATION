<?php
session_start();
require_once '../db_connect.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require_once '../PHPMailer/src/Exception.php';
require_once '../PHPMailer/src/PHPMailer.php';
require_once '../PHPMailer/src/SMTP.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.html");
    exit();
}

// Fetch admin name
$admin_name = 'Admin';
$stmt = $conn->prepare("SELECT user_name FROM users WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $admin_name = $row['user_name'];
}
$stmt->close();

// Fetch advisors for dropdown
$advisors = [];
$advisor_query = "SELECT user_id, user_name FROM users WHERE role = 'advisor' ORDER BY user_name";
$advisor_result = $conn->query($advisor_query);
if ($advisor_result) {
    while ($row = $advisor_result->fetch_assoc()) {
        $advisors[] = $row;
    }
}

function sendAccountCreatedEmail($email, $name, $login_cred, $default_password) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'ariefiqmal2006@gmail.com';
        $mail->Password   = 'xill egye ginu ebig';
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        $mail->setFrom('ariefiqmal2006@gmail.com', 'UTM SYSTEM ADMIN');
        $mail->addAddress($email, $name);
        $mail->isHTML(true);
        $mail->Subject = 'Your Student Account Has Been Created';
        $mail->Body    = "
            <h3>Welcome to UTM Course Registration System</h3>
            <p>Dear <strong>{$name}</strong>,</p>
            <p>A student account has been created for you by the administrator.</p>
            <p><strong>Your login credentials:</strong><br>
            Login ID: <strong>{$login_cred}</strong><br>
            Temporary Password: <strong>{$default_password}</strong></p>
            <p>Please login using the link below and change your password after first login.</p>
            <p><a href='http://localhost/FYP/FYP%20COURSE%20REGISTRATION/index.html'>Click here to login</a></p>
            <p>Regards,<br>UTM SYSTEM ADMIN</p>
        ";
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email failed: " . $mail->ErrorInfo);
        return false;
    }
}

$message = '';
$msg_type = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $matrix = trim($_POST['matrix']);
    $name   = trim($_POST['name']);
    $utm_email = trim($_POST['utm_email']);
    $second_email = trim($_POST['second_email']);
    $phone  = trim($_POST['phone']);
    $ic_number = trim($_POST['ic_number']);
    $address = trim($_POST['address']);
    $programme = trim($_POST['programme']);
    $year = trim($_POST['year']);
    $semester = trim($_POST['semester']);
    $advisor_id = isset($_POST['advisor_id']) ? (int)$_POST['advisor_id'] : 0;

    $errors = [];

    if (empty($matrix)) $errors[] = "Matrix number is required.";
    if (empty($name)) $errors[] = "Full name is required.";
    if (empty($utm_email)) $errors[] = "UTM email is required.";
    if (empty($phone)) $errors[] = "Phone number is required.";
    if (empty($programme)) $errors[] = "Programme is required.";
    if (empty($year)) $errors[] = "Year is required.";
    if (empty($semester)) $errors[] = "Semester is required.";
    if ($advisor_id <= 0) $errors[] = "Advisor must be selected.";
    if (!filter_var($utm_email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid UTM email format.";
    if (!empty($second_email) && !filter_var($second_email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid second email format.";

    if (empty($errors)) {
        $check = $conn->prepare("SELECT user_id FROM users WHERE matrix_number = ?");
        $check->bind_param("s", $matrix);
        $check->execute();
        if ($check->get_result()->num_rows > 0) $errors[] = "Matrix number already exists.";
        $check->close();

        if (empty($errors)) {
            $check = $conn->prepare("SELECT user_id FROM users WHERE utm_email = ?");
            $check->bind_param("s", $utm_email);
            $check->execute();
            if ($check->get_result()->num_rows > 0) $errors[] = "UTM email already exists.";
            $check->close();
        }
    }

    if (!empty($errors)) {
        $message = implode("<br>", $errors);
        $msg_type = 'danger';
    } else {
        $default_password = 'pass1234';
        $hashed = password_hash($default_password, PASSWORD_DEFAULT);
        $login_cred = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $name));

        $stmt = $conn->prepare("INSERT INTO users (matrix_number, user_name, utm_email, second_email, phone, password, role, login_cred) VALUES (?, ?, ?, ?, ?, ?, 'student', ?)");
        $stmt->bind_param("sssssss", $matrix, $name, $utm_email, $second_email, $phone, $hashed, $login_cred);
        if ($stmt->execute()) {
            $user_id = $stmt->insert_id;

            $stmt2 = $conn->prepare("INSERT INTO students (user_id, matrix_number, user_name, utm_email, second_email, phone, ic_number, address, programme, year, semester, advisor_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt2->bind_param("issssssssssi", $user_id, $matrix, $name, $utm_email, $second_email, $phone, $ic_number, $address, $programme, $year, $semester, $advisor_id);
            if ($stmt2->execute()) {
                $recipient_email = !empty($second_email) ? $second_email : $utm_email;
                $email_sent = sendAccountCreatedEmail($recipient_email, $name, $login_cred, $default_password);
                $message = $email_sent ? "Student added successfully. Email sent to $recipient_email." : "Student added, but email could not be sent.";
                $msg_type = $email_sent ? 'success' : 'warning';
                header("Location: manage_students.php?msg=" . urlencode($message));
                exit();
            } else {
                $message = "Error inserting into students: " . $conn->error;
                $msg_type = 'danger';
            }
            $stmt2->close();
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
    <title>Add Student - Admin Portal</title>
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
        .system-title { color: white; font-size: 16px; font-weight: 600; margin-top: 12px; }
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
        .form-card { background: white; border-radius: 25px; padding: 35px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); max-width: 900px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 6px; font-weight: 500; color: #333; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 10px 15px; border: 1px solid #ddd; border-radius: 12px; font-size: 14px; }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus { outline: none; border-color: #670019; box-shadow: 0 0 0 3px rgba(103,0,25,0.08); }
        .row-custom { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .btn-submit { background: linear-gradient(to right, #670019, #8b0022); color: white; border: none; padding: 12px 30px; border-radius: 25px; font-weight: 600; cursor: pointer; transition: 0.3s; }
        .btn-submit:hover { background: linear-gradient(to right, #8b0022, #a80028); transform: translateY(-2px); }
        .alert { padding: 12px 20px; border-radius: 20px; margin-bottom: 20px; }
        .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .alert-warning { background: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
        @media (max-width: 992px) {
            .sidebar { transform: translateX(-280px); }
            .main-content { margin-left: 0; width: 100%; }
            .row-custom { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div class="sidebar">
    <div class="logo"><img src="../images/utmlogo.png" alt="UTM Logo"><div class="system-title">COURSE REGISTRATION SYSTEM</div></div>
    <div class="menu">
        <a href="admin_dashboard.php"><i class="bi bi-house-fill"></i> Dashboard</a>
        <a href="manage_students.php" class="active"><i class="bi bi-people-fill"></i> Manage Students</a>
        <a href="manage_advisors.php"><i class="bi bi-person-badge-fill"></i> Manage Advisors</a>
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
            <div><h6 class="mb-0"><?php echo htmlspecialchars($admin_name); ?></h6><small class="text-muted">Admin</small></div>
        </div>
    </div>
    <div class="page-header">
        <h2>Add New Student</h2>
        <a href="manage_students.php" class="btn-cancel"><i class="bi bi-arrow-left"></i> Back</a>
    </div>
    <?php if ($message): ?>
        <div class="alert alert-<?php echo $msg_type; ?>"><?php echo nl2br(htmlspecialchars($message)); ?></div>
    <?php endif; ?>
    <div class="form-card">
        <form method="POST">
            <div class="row-custom">
                <div class="form-group"><label>Matrix Number</label><input type="text" name="matrix" required></div>
                <div class="form-group"><label>Full Name</label><input type="text" name="name" required></div>
                <div class="form-group"><label>UTM Email</label><input type="email" name="utm_email" required></div>
                <div class="form-group"><label>Second Email (for notifications)</label><input type="email" name="second_email"></div>
                <div class="form-group"><label>Phone Number</label><input type="text" name="phone" required></div>
                <div class="form-group"><label>IC/Passport Number</label><input type="text" name="ic_number" placeholder="e.g., 000101-10-1234"></div>
            </div>
            <div class="form-group"><label>Address</label><textarea name="address" rows="2" placeholder="Home address"></textarea></div>
            <div class="row-custom">
                <div class="form-group"><label>Programme</label>
                    <select name="programme" required>
                        <option value="Computer Science">Computer Science</option>
                        <option value="Electrical Engineering">Electrical Engineering</option>
                        <option value="Sport Science">Sport Science</option>
                        <option value="Pengajian Islam">Pengajian Islam</option>
                    </select>
                </div>
                <div class="form-group"><label>Year</label>
                    <select name="year" required>
                        <option value="1">Year 1</option><option value="2">Year 2</option><option value="3">Year 3</option><option value="4">Year 4</option>
                    </select>
                </div>
                <div class="form-group"><label>Semester</label>
                    <select name="semester" required>
                        <option value="1">Semester 1</option><option value="2">Semester 2</option><option value="3">Semester 3</option>
                    </select>
                </div>
                <div class="form-group"><label>Advisor</label>
                    <select name="advisor_id" required>
                        <option value="">-- Select Advisor --</option>
                        <?php foreach ($advisors as $advisor): ?>
                            <option value="<?php echo $advisor['user_id']; ?>"><?php echo htmlspecialchars($advisor['user_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn-submit"><i class="bi bi-save"></i> Add Student</button>
        </form>
        <div class="mt-3 text-muted small">
            <i class="bi bi-info-circle"></i> The student will receive an email at their <strong>second email address</strong> (or UTM email if not provided) with login credentials and a temporary password (<strong>pass1234</strong>).
        </div>
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