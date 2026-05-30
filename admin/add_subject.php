<?php
session_start();
include '../db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.html");
    exit();
}

$message = '';
$msg_type = '';

// Fetch admin name for topbar (optional)
$admin_name = 'Admin';
$stmt = $conn->prepare("SELECT user_name FROM users WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $admin_name = $row['user_name'];
}
$stmt->close();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $code = trim($_POST['code']);
    $name = trim($_POST['name']);
    $credits = intval($_POST['credits']);

    $error = false;

    if (empty($code) || empty($name) || empty($credits)) {
        $message = "All fields are required.";
        $msg_type = 'danger';
        $error = true;
    }

    if (!$error && ($credits < 1 || $credits > 5)) {
        $message = "Credits must be between 1 and 5.";
        $msg_type = 'danger';
        $error = true;
    }

    if (!$error) {
        // Check if subject code already exists
        $check = $conn->prepare("SELECT subject_code FROM subjects WHERE subject_code = ?");
        $check->bind_param("s", $code);
        $check->execute();
        $check_result = $check->get_result();
        if ($check_result->num_rows > 0) {
            // Set flag for modal (will be used in JavaScript)
            $duplicate = true;
        } else {
            $insert = $conn->prepare("INSERT INTO subjects (subject_code, subject_name, credits) VALUES (?, ?, ?)");
            $insert->bind_param("ssi", $code, $name, $credits);
            if ($insert->execute()) {
                header("Location: manage_subjects.php?msg=Subject added successfully.");
                exit();
            } else {
                $message = "Error: " . $conn->error;
                $msg_type = 'danger';
            }
            $insert->close();
        }
        $check->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Subject - Admin Portal</title>
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
            text-decoration: none; color: white; padding: 9px 20px;
            border-radius: 14px; margin-bottom: 12px; transition: 0.3s; font-size: 16px;
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
        .form-card { background: white; border-radius: 25px; padding: 35px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); max-width: 600px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 6px; font-weight: 500; color: #333; }
        .form-group input { width: 100%; padding: 10px 15px; border: 1px solid #ddd; border-radius: 12px; font-size: 14px; }
        .form-group input:focus { outline: none; border-color: #670019; box-shadow: 0 0 0 3px rgba(103,0,25,0.08); }
        .btn-submit { background: linear-gradient(to right, #670019, #8b0022); color: white; border: none; padding: 12px 30px; border-radius: 25px; font-weight: 600; cursor: pointer; transition: 0.3s; }
        .btn-submit:hover { background: linear-gradient(to right, #8b0022, #a80028); transform: translateY(-2px); }
        .alert { padding: 12px 20px; border-radius: 20px; margin-bottom: 20px; }
        .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
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
            color: #dc3545;
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
        <a href="admin_dashboard.php" ><i class="bi bi-house-fill"></i> Dashboard</a>
        <a href="manage_students.php"><i class="bi bi-people-fill"></i> Manage Students</a>
        <a href="manage_advisors.php" ><i class="bi bi-person-badge-fill"></i> Manage Advisors</a>
        <a href="manage_subjects.php" class="active"><i class="bi bi-book-fill"></i> Manage Subjects</a>
        <a href="manage_registration_period.php"><i class="bi bi-calendar-event"></i> Registration Period</a>
        <a href="../forgot_password.html"><i class="bi bi-key-fill"></i> Forgot Password</a>
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
        <h2>Add New Subject</h2>
        <a href="manage_subjects.php" class="btn-cancel"><i class="bi bi-arrow-left"></i> Back</a>
    </div>
    <?php if ($message): ?>
        <div class="alert alert-danger"><?php echo nl2br(htmlspecialchars($message)); ?></div>
    <?php endif; ?>
    <div class="form-card">
        <form method="POST" id="addSubjectForm">
            <div class="form-group">
                <label>Subject Code</label>
                <input type="text" name="code" placeholder="e.g., CSC301" required>
            </div>
            <div class="form-group">
                <label>Subject Name</label>
                <input type="text" name="name" placeholder="e.g., Database Systems" required>
            </div>
            <div class="form-group">
                <label>Credits</label>
                <input type="number" name="credits" min="1" max="5" required>
            </div>
            <button type="button" class="btn-submit" id="showConfirmBtn"><i class="bi bi-save"></i> Add Subject</button>
        </form>
    </div>
</div>

<!-- Custom Modal for Duplicate Subject -->
<div id="duplicateModal" class="custom-modal">
    <div class="custom-modal-content">
        <i class="bi bi-exclamation-triangle-fill"></i>
        <h3>Subject Already Exists</h3>
        <p>A subject with this code already exists in the system. Please use a different code.</p>
        <div class="custom-modal-buttons">
            <button class="btn-confirm" id="closeModalBtn">OK</button>
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

    const form = document.getElementById('addSubjectForm');
    const showBtn = document.getElementById('showConfirmBtn');
    const modal = document.getElementById('duplicateModal');
    const closeModalBtn = document.getElementById('closeModalBtn');

    showBtn.addEventListener('click', function() {
        // Validate fields
        const code = form.querySelector('input[name="code"]').value.trim();
        const name = form.querySelector('input[name="name"]').value.trim();
        const credits = form.querySelector('input[name="credits"]').value.trim();
        if (!code || !name || !credits) {
            alert('Please fill all fields.');
            return;
        }
        // Submit the form via AJAX to check for duplicate without page reload
        const formData = new FormData(form);
        fetch(window.location.href, {
            method: 'POST',
            body: formData
        }).then(response => response.text()).then(html => {
            // Check if the response contains the modal trigger flag
            if (html.includes('duplicate_modal_trigger')) {
                // This is a hack; better to use AJAX JSON response
                modal.style.display = 'flex';
            } else {
                // No duplicate, likely redirect or show success
                document.write(html);
            }
        }).catch(err => {
            console.error(err);
            alert('An error occurred. Please try again.');
        });
    });

    closeModalBtn.addEventListener('click', function() {
        modal.style.display = 'none';
    });

    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
</script>
</body>
</html>