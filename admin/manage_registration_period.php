<?php
session_start();
require_once '../db_connect.php';

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

$message = '';
$msg_type = '';

// Fetch current period (if any)
$current_period = null;
$result = $conn->query("SELECT * FROM semester_registration_periods LIMIT 1");
if ($result && $result->num_rows > 0) {
    $current_period = $result->fetch_assoc();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $session_semester = $_POST['session_semester'];

    if (empty($start_date) || empty($end_date)) {
        $message = "Start date and end date are required.";
        $msg_type = 'danger';
    } elseif (strtotime($start_date) > strtotime($end_date)) {
        $message = "Start date cannot be after end date.";
        $msg_type = 'danger';
    } else {
        // Clear existing periods and insert new one
        $conn->query("TRUNCATE TABLE semester_registration_periods");
        $stmt = $conn->prepare("INSERT INTO semester_registration_periods (session_semester, start_date, end_date, is_open) VALUES (?, ?, ?, 1)");
        $stmt->bind_param("sss", $session_semester, $start_date, $end_date);
        if ($stmt->execute()) {
            $message = "Registration period updated successfully. It will be open from $start_date to $end_date.";
            $msg_type = 'success';
        } else {
            $message = "Database error: " . $conn->error;
            $msg_type = 'danger';
        }
        $stmt->close();
    }
}

// Real-time status for display
$is_open = false;
if ($current_period) {
    $today = new DateTime();
    $start = new DateTime($current_period['start_date']);
    $end = new DateTime($current_period['end_date']);
    $is_open = ($today >= $start && $today <= $end);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Registration Period - Admin Portal</title>
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
        .system-title { color: #ffc107; font-size: 16px; font-weight: 600; margin-top: 12px; }
        .menu a {
            display: flex; align-items: center; gap: 15px;
            text-decoration: none; color: white; padding: 12px 20px;
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
        .page-header { margin-bottom: 30px; }
        .page-header h2 { color: #670019; font-weight: 700; }
        .form-card { background: white; border-radius: 25px; padding: 35px; max-width: 1500px; margin-top: 20px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 6px; font-weight: 500; color: #333; }
        .form-group input { width: 100%; padding: 10px 15px; border: 1px solid #ddd; border-radius: 12px; }
        .btn-submit { background: linear-gradient(to right, #670019, #8b0022); color: white; border: none; padding: 12px 30px; border-radius: 25px; font-weight: 600; cursor: pointer;position: center; display: block; margin: 0 auto; transition: 0.3s; }
        .btn-submit:hover { background: linear-gradient(to right, #8b0022, #a80028); transform: translateY(-2px); }
        .alert { padding: 12px 20px; border-radius: 20px; margin-bottom: 20px; }
        .alert-success { background: #d4edda; color: #155724; }
        .alert-danger { background: #f8d7da; color: #721c24; }
        .current-info { background: #f7f2ee; border-radius: 20px; padding: 20px; margin-bottom: 25px; }
        /* Custom Modal Styles */
        .custom-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 2000;
            justify-content: center;
            align-items: center;
        }
        .custom-modal-content {
            background: white;
            border-radius: 25px;
            max-width: 400px;
            width: 90%;
            padding: 30px;
            text-align: center;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
        .custom-modal-content i {
            font-size: 48px;
            color: #670019;
            margin-bottom: 15px;
        }
        .custom-modal-content h3 {
            color: #670019;
            margin-bottom: 10px;
        }
        .custom-modal-content p {
            color: #666;
            margin-bottom: 25px;
        }
        .custom-modal-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
        }
        .custom-modal-buttons button {
            padding: 10px 25px;
            border-radius: 25px;
            border: none;
            font-weight: 500;
            cursor: pointer;
            transition: 0.3s;
        }
        .btn-confirm {
            background: #670019;
            color: white;
        }
        .btn-confirm:hover {
            background: #8b0022;
        }
        .btn-cancel-modal {
            background: #6c757d;
            color: white;
        }
        .btn-cancel-modal:hover {
            background: #5a6268;
        }
        @media (max-width: 992px) {
            .sidebar { transform: translateX(-280px); }
            .main-content { margin-left: 0; width: 100%; }
        }
    </style>
</head>
<body>
<div class="sidebar">
    <div class="logo"><img src="../images/utmlogo.png" alt="UTM Logo"><div class="system-title">COURSE REGISTRATION SYSTEM</div></div>
    <div class="menu">
        <a href="admin_dashboard.php"><i class="bi bi-house-fill"></i> Dashboard</a>
        <a href="manage_students.php"><i class="bi bi-people-fill"></i> Manage Students</a>
        <a href="manage_advisors.php"><i class="bi bi-person-badge-fill"></i> Manage Advisors</a>
        <a href="manage_subjects.php"><i class="bi bi-book-fill"></i> Manage Subjects</a>
        <a href="manage_registration_period.php" class="active"><i class="bi bi-calendar-event"></i> Registration Period</a>
        <a href="profile.php"><i class="bi bi-person-fill"></i> Profile</a>
        <a href="../forgot_password.php"><i class="bi bi-key-fill"></i> Forgot Password</a>
    </div>
    <div class="logout"><a href="../index.html"><i class="bi bi-box-arrow-right"></i> Logout</a></div>
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
        <h2>Manage Registration Period</h2>
    </div>
    <?php if ($message): ?>
        <div class="alert alert-<?php echo $msg_type; ?>"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    <?php if ($current_period): ?>
    <div class="current-info">
        <strong>Current Registration Period:</strong><br>
        Session: <?php echo htmlspecialchars($current_period['session_semester']); ?><br>
        Dates: <?php echo date('d M Y', strtotime($current_period['start_date'])); ?> to <?php echo date('d M Y', strtotime($current_period['end_date'])); ?><br>
        Status: <?php echo $is_open ? '<span class="badge bg-success">Open (Real-time)</span>' : '<span class="badge bg-secondary">Closed (Real-time)</span>'; ?>
    </div>
    <?php endif; ?>
    <div class="form-card">
        <form method="POST" id="registrationPeriodForm">
            <div class="form-group">
                <label>Session Semester (e.g., 2025/2026-2)</label>
                <input type="text" name="session_semester" value="<?php echo $current_period ? htmlspecialchars($current_period['session_semester']) : '2025/2026-2'; ?>" required>
            </div>
            <div class="form-group">
                <label>Start Date</label>
                <input type="date" name="start_date" value="<?php echo $current_period ? $current_period['start_date'] : ''; ?>" required>
            </div>
            <div class="form-group">
                <label>End Date</label>
                <input type="date" name="end_date" value="<?php echo $current_period ? $current_period['end_date'] : ''; ?>" required>
            </div>
            <button type="button" class="btn-submit" id="showConfirmBtn">Save Registration Period</button>
        </form>
      
    </div>
</div>

<!-- Custom Modal -->
<div id="confirmModal" class="custom-modal">
    <div class="custom-modal-content">
        <i class="bi bi-calendar-check"></i>
        <h3>Confirm Registration Period</h3>
        <p>Are you sure you want to update the registration period?<br>This will overwrite the current period and affect student access.</p>
        <div class="custom-modal-buttons">
            <button class="btn-cancel-modal" id="cancelModalBtn">Cancel</button>
            <button class="btn-confirm" id="confirmModalBtn">Confirm</button>
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

    // Custom modal logic
    const modal = document.getElementById('confirmModal');
    const showBtn = document.getElementById('showConfirmBtn');
    const confirmBtn = document.getElementById('confirmModalBtn');
    const cancelBtn = document.getElementById('cancelModalBtn');
    const form = document.getElementById('registrationPeriodForm');

    showBtn.addEventListener('click', function() {
        modal.style.display = 'flex';
    });

    confirmBtn.addEventListener('click', function() {
        modal.style.display = 'none';
        form.submit();
    });

    cancelBtn.addEventListener('click', function() {
        modal.style.display = 'none';
    });

    // Close modal if user clicks outside the content
    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
</script>
</body>
</html>