<?php
session_start();
include '../db_connect.php';

// Security: Only Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.html");
    exit();
}

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: manage_students.php?msg=Invalid student ID.");
    exit();
}

$student_id = intval($_GET['id']);
$message = '';
$msg_type = '';

// Fetch student data
$stmt = $conn->prepare("SELECT id, Matrix, name, email FROM users WHERE id = ? AND role = 'student'");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: manage_students.php?msg=Student not found.");
    exit();
}

$student = $result->fetch_assoc();
$stmt->close();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $matrix = trim($_POST['matrix']);
    $name   = trim($_POST['name']);
    $email  = trim($_POST['email']);
    $password = $_POST['password'];

    $error = false;

    // Check for duplicate Matrix (excluding current student)
    if (!is_numeric($matrix) || $matrix <= 0) {
        $message = "Matrix number must be a positive number.";
        $msg_type = 'danger';
        $error = true;
    }

    if (!$error) {
        $check_stmt = $conn->prepare("SELECT id FROM users WHERE Matrix = ? AND id != ?");
        $check_stmt->bind_param("ii", $matrix, $student_id);
        $check_stmt->execute();
        $result_check = $check_stmt->get_result();
        if ($result_check->num_rows > 0) {
            $message = "Matrix number already registered.";
            $msg_type = 'danger';
            $error = true;
        }
        $check_stmt->close();
    }

    // Check for duplicate Email (excluding current student)
    if (!$error) {
        $check_email = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $check_email->bind_param("si", $email, $student_id);
        $check_email->execute();
        $result_email = $check_email->get_result();
        if ($result_email->num_rows > 0) {
            $message = "Email already registered.";
            $msg_type = 'danger';
            $error = true;
        }
        $check_email->close();
    }

    // Update
    if (!$error) {
        if (!empty($password)) {
            // Update with new password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $update_stmt = $conn->prepare("UPDATE users SET Matrix = ?, name = ?, email = ?, password = ? WHERE id = ?");
            $update_stmt->bind_param("isssi", $matrix, $name, $email, $hashed_password, $student_id);
        } else {
            // Keep existing password
            $update_stmt = $conn->prepare("UPDATE users SET Matrix = ?, name = ?, email = ? WHERE id = ?");
            $update_stmt->bind_param("issi", $matrix, $name, $email, $student_id);
        }

        if ($update_stmt->execute()) {
            header("Location: manage_students.php?msg=Student updated successfully.");
            exit();
        } else {
            $message = "Error updating student: " . $conn->error;
            $msg_type = 'danger';
        }
        $update_stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student - Admin Portal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { display: flex; background-color: #f4f6f9; min-height: 100vh; }
        .sidebar { width: 250px; background-color: #7A0D2A; color: white; display: flex; flex-direction: column; padding: 20px 0; position: fixed; height: 100%; left: 0; top: 0; z-index: 1000; }
        .sidebar h1 { font-size: 24px; font-weight: 600; padding: 0 25px 30px 25px; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar nav ul { list-style: none; padding: 20px 15px; }
        .sidebar nav ul li { margin-bottom: 12px; }
        .sidebar nav ul li a { display: flex; align-items: center; text-decoration: none; color: white; padding: 12px 20px; border-radius: 8px; transition: 0.3s ease; font-size: 16px; }
        .sidebar nav ul li a i { margin-right: 15px; width: 20px; text-align: center; }
        .sidebar nav ul li a:hover { background-color: rgba(255,255,255,0.1); }
        .sidebar nav ul li a.active { background-color: #DE9E1F; color: #fff; font-weight: 500; }
        .sidebar nav ul li a.logout { margin-top: 60px; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 20px; border-radius: 0; }
        .main-content { margin-left: 250px; width: calc(100% - 250px); padding: 30px; background-color: #f4f6f9; }
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .page-header h2 { color: #1f2937; }
        .btn-cancel { background-color: #6b7280; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; transition: 0.2s; }
        .btn-cancel:hover { background-color: #4b5563; color: white; }
        .form-card { background: white; border-radius: 16px; padding: 30px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); max-width: 600px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 6px; color: #4b5563; font-weight: 500; }
        .form-group input { width: 100%; padding: 10px 14px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 15px; transition: 0.2s; }
        .form-group input:focus { outline: none; border-color: #7A0D2A; box-shadow: 0 0 0 3px rgba(122, 13, 42, 0.1); }
        .form-group .error-text { color: #dc2626; font-size: 14px; margin-top: 4px; display: block; }
        .btn-submit { background-color: #7A0D2A; color: white; padding: 12px 30px; border: none; border-radius: 8px; font-size: 16px; cursor: pointer; transition: 0.2s; font-weight: 500; }
        .btn-submit:hover { background-color: #5c0920; }
        .alert { padding: 12px 20px; border-radius: 8px; margin-bottom: 20px; }
        .alert-danger { background-color: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .alert-success { background-color: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        @media (max-width: 768px) { .sidebar { width: 200px; } .main-content { margin-left: 200px; } }
        @media (max-width: 576px) { body { flex-direction: column; } .sidebar { width: 100%; height: auto; position: relative; } .main-content { margin-left: 0; width: 100%; } }
    </style>
</head>
<body>
    <aside class="sidebar">
        <h1>Admin Portal</h1>
        <nav>
            <ul>
                <li><a href="admin_dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="profile.php"><i class="fas fa-user"></i> Profile</a></li>
                <li><a href="manage_students.php" class="active"><i class="fas fa-user-graduate"></i> Manage Students</a></li>
                <li><a href="manage_advisors.php"><i class="fas fa-users"></i> Manage Advisors</a></li>
                <li><a href="manage_subjects.php"><i class="fas fa-book"></i> Manage Subjects</a></li>
                <li><a href="../forgot_password.php"><i class="fas fa-key"></i> Forgot Password</a></li>
                <li><a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </nav>
    </aside>
    <div class="main-content">
        <div class="page-header">
            <h2>Edit Student</h2>
            <a href="manage_students.php" class="btn-cancel"><i class="fas fa-arrow-left"></i> Back</a>
        </div>
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $msg_type; ?>"><?php echo $message; ?></div>
        <?php endif; ?>
        <div class="form-card">
            <form method="POST" action="">
                <div class="form-group">
                    <label for="matrix">Matrix Number</label>
                    <input type="number" id="matrix" name="matrix" value="<?php echo htmlspecialchars($student['Matrix']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($student['name']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($student['email']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="password">New Password (leave blank to keep current)</label>
                    <input type="password" id="password" name="password">
                </div>
                <button type="submit" class="btn-submit"><i class="fas fa-save"></i> Update Student</button>
            </form>
        </div>
    </div>
</body>
</html>