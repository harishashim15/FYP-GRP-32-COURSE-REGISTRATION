<?php
session_start();
require_once '../db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.html");
    exit();
}

$admin_name = 'Admin';
$stmt = $conn->prepare("SELECT user_name FROM users WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $admin_name = $row['user_name'];
}
$stmt->close();

$advisors = [];
$advisor_query = "SELECT user_id, user_name FROM users WHERE role = 'advisor' ORDER BY user_name";
$advisor_result = $conn->query($advisor_query);
if ($advisor_result) {
    while ($row = $advisor_result->fetch_assoc()) {
        $advisors[] = $row;
    }
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: manage_students.php?msg=Invalid student ID.");
    exit();
}
$student_id = intval($_GET['id']);

$stmt = $conn->prepare("
    SELECT u.user_id, u.matrix_number, u.user_name, u.utm_email, u.second_email, u.phone,
           s.ic_number, s.address, s.programme, s.year, s.semester, s.advisor_id
    FROM users u
    LEFT JOIN students s ON u.user_id = s.user_id
    WHERE u.user_id = ? AND u.role = 'student'
");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    header("Location: manage_students.php?msg=Student not found.");
    exit();
}
$student = $result->fetch_assoc();
$stmt->close();

$message = '';
$msg_type = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $matrix = trim($_POST['matrix']);
    $name = trim($_POST['name']);
    $utm_email = trim($_POST['utm_email']);
    $second_email = trim($_POST['second_email']);
    $phone = trim($_POST['phone']);
    $ic_number = trim($_POST['ic_number']);
    $address = trim($_POST['address']);
    $programme = trim($_POST['programme']);
    $year = trim($_POST['year']);
    $semester = trim($_POST['semester']);
    $advisor_id = intval($_POST['advisor_id']);
    $password = $_POST['password'];

    $errors = [];

    if (empty($matrix) || empty($name) || empty($utm_email) || empty($phone) || empty($programme) || empty($year) || empty($semester) || $advisor_id <= 0) {
        $errors[] = "All fields are required.";
    } elseif (!filter_var($utm_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid UTM email format.";
    } elseif (!empty($second_email) && !filter_var($second_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid second email format.";
    } elseif (!empty($password) && strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters.";
    }

    if (empty($errors)) {
        $check = $conn->prepare("SELECT user_id FROM users WHERE matrix_number = ? AND user_id != ?");
        $check->bind_param("si", $matrix, $student_id);
        $check->execute();
        if ($check->get_result()->num_rows > 0) $errors[] = "Matrix number already exists.";
        $check->close();

        if (empty($errors)) {
            $check = $conn->prepare("SELECT user_id FROM users WHERE utm_email = ? AND user_id != ?");
            $check->bind_param("si", $utm_email, $student_id);
            $check->execute();
            if ($check->get_result()->num_rows > 0) $errors[] = "UTM email already exists.";
            $check->close();
        }
    }

    if (!empty($errors)) {
        $message = implode("<br>", $errors);
        $msg_type = 'danger';
    } else {
        if (!empty($password)) {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET matrix_number = ?, user_name = ?, utm_email = ?, second_email = ?, phone = ?, password = ? WHERE user_id = ?");
            $stmt->bind_param("ssssssi", $matrix, $name, $utm_email, $second_email, $phone, $hashed, $student_id);
        } else {
            $stmt = $conn->prepare("UPDATE users SET matrix_number = ?, user_name = ?, utm_email = ?, second_email = ?, phone = ? WHERE user_id = ?");
            $stmt->bind_param("sssssi", $matrix, $name, $utm_email, $second_email, $phone, $student_id);
        }
        if ($stmt->execute()) {
            $stmt2 = $conn->prepare("UPDATE students SET programme = ?, year = ?, semester = ?, advisor_id = ?, ic_number = ?, address = ? WHERE user_id = ?");
            $stmt2->bind_param("sssissi", $programme, $year, $semester, $advisor_id, $ic_number, $address, $student_id);
            $stmt2->execute();
            $stmt2->close();
            header("Location: manage_students.php?msg=Student updated successfully.");
            exit();
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
    <title>Edit Student - Admin Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Same styles as add_student.php */
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
        @media (max-width: 992px) {
            .sidebar { transform: translateX(-280px); }
            .main-content { margin-left: 0; width: 100%; }
            .row-custom { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div class="sidebar"><?php /* same sidebar as add_student.php */ ?></div>
<div class="main-content">
    <div class="topbar"><?php /* same topbar */ ?></div>
    <div class="page-header">
        <h2>Edit Student</h2>
        <a href="manage_students.php" class="btn-cancel"><i class="bi bi-arrow-left"></i> Back</a>
    </div>
    <?php if ($message): ?>
        <div class="alert alert-danger"><?php echo nl2br(htmlspecialchars($message)); ?></div>
    <?php endif; ?>
    <div class="form-card">
        <form method="POST">
            <div class="row-custom">
                <div class="form-group"><label>Matrix Number</label><input type="text" name="matrix" value="<?php echo htmlspecialchars($student['matrix_number']); ?>" required></div>
                <div class="form-group"><label>Full Name</label><input type="text" name="name" value="<?php echo htmlspecialchars($student['user_name']); ?>" required></div>
                <div class="form-group"><label>UTM Email</label><input type="email" name="utm_email" value="<?php echo htmlspecialchars($student['utm_email']); ?>" required></div>
                <div class="form-group"><label>Second Email</label><input type="email" name="second_email" value="<?php echo htmlspecialchars($student['second_email']); ?>"></div>
                <div class="form-group"><label>Phone Number</label><input type="text" name="phone" value="<?php echo htmlspecialchars($student['phone']); ?>" required></div>
                <div class="form-group"><label>IC/Passport Number</label><input type="text" name="ic_number" value="<?php echo htmlspecialchars($student['ic_number']); ?>"></div>
            </div>
            <div class="form-group"><label>Address</label><textarea name="address" rows="2"><?php echo htmlspecialchars($student['address']); ?></textarea></div>
            <div class="row-custom">
                <div class="form-group"><label>Programme</label>
                    <select name="programme" required>
                        <option value="Computer Science" <?php echo $student['programme'] == 'Computer Science' ? 'selected' : ''; ?>>Computer Science</option>
                        <option value="Electrical Engineering" <?php echo $student['programme'] == 'Electrical Engineering' ? 'selected' : ''; ?>>Electrical Engineering</option>
                        <option value="Sport Science" <?php echo $student['programme'] == 'Sport Science' ? 'selected' : ''; ?>>Sport Science</option>
                        <option value="Pengajian Islam" <?php echo $student['programme'] == 'Pengajian Islam' ? 'selected' : ''; ?>>Pengajian Islam</option>
                    </select>
                </div>
                <div class="form-group"><label>Year</label>
                    <select name="year" required>
                        <option value="1" <?php echo $student['year'] == '1' ? 'selected' : ''; ?>>Year 1</option>
                        <option value="2" <?php echo $student['year'] == '2' ? 'selected' : ''; ?>>Year 2</option>
                        <option value="3" <?php echo $student['year'] == '3' ? 'selected' : ''; ?>>Year 3</option>
                        <option value="4" <?php echo $student['year'] == '4' ? 'selected' : ''; ?>>Year 4</option>
                    </select>
                </div>
                <div class="form-group"><label>Semester</label>
                    <select name="semester" required>
                        <option value="1" <?php echo $student['semester'] == '1' ? 'selected' : ''; ?>>Semester 1</option>
                        <option value="2" <?php echo $student['semester'] == '2' ? 'selected' : ''; ?>>Semester 2</option>
                        <option value="3" <?php echo $student['semester'] == '3' ? 'selected' : ''; ?>>Semester 3</option>
                    </select>
                </div>
                <div class="form-group"><label>Advisor</label>
                    <select name="advisor_id" required>
                        <option value="">-- Select Advisor --</option>
                        <?php foreach ($advisors as $advisor): ?>
                            <option value="<?php echo $advisor['user_id']; ?>" <?php echo ($student['advisor_id'] == $advisor['user_id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($advisor['user_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group"><label>New Password (leave blank to keep current)</label><input type="password" name="password"></div>
            </div>
            <button type="submit" class="btn-submit"><i class="bi bi-save"></i> Update Student</button>
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
</script>
</body>
</html>