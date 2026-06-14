<?php
require_once 'db_connect.php';

session_start();

// Get advisor ID from session - FIX: Remove default 1
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

// Get advisor information from users table
$sql = "SELECT * FROM users WHERE user_id = ? AND role = 'advisor'";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $advisor_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$advisor = mysqli_fetch_assoc($result);

// Get additional advisor info from advisor table
$sql_advisor = "SELECT * FROM advisor WHERE user_id = ?";
$stmt_advisor = mysqli_prepare($conn, $sql_advisor);
mysqli_stmt_bind_param($stmt_advisor, "i", $advisor_id);
mysqli_stmt_execute($stmt_advisor);
$result_advisor = mysqli_stmt_get_result($stmt_advisor);
$advisor_details = mysqli_fetch_assoc($result_advisor);

// Handle POST request for updating profile (only second_email and phone)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $second_email = $_POST['second_email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    
    // Update users table phone
    $sql = "UPDATE users SET phone = ? WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "si", $phone, $advisor_id);
    mysqli_stmt_execute($stmt);
    
    // Update advisor table second_email only (department removed from update)
    $sql2 = "UPDATE advisor SET second_email = ? WHERE user_id = ?";
    $stmt2 = mysqli_prepare($conn, $sql2);
    mysqli_stmt_bind_param($stmt2, "si", $second_email, $advisor_id);
    mysqli_stmt_execute($stmt2);
    
    // Return JSON response for AJAX
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
    exit;
}

// Prepare profile data for display
$profile_data = [
    'full_name' => $advisor['user_name'] ?? $advisor_details['advisor_name'] ?? 'Advisor',
    'staff_id' => $advisor_details['matrix_number'] ?? $advisor['matrix_number'] ?? 'N/A',
    'email' => $advisor['utm_email'] ?? 'advisor@utm.my',
    'second_email' => $advisor_details['second_email'] ?? $advisor['second_email'] ?? '',
    'phone' => $advisor['phone'] ?? '',
    'department' => $advisor_details['department'] ?? 'Computer Science',
    'role' => 'advisor'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - UTM Academic Advisor</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background: #f8f6f4; overflow-x: hidden; }

        /* SIDEBAR */
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

        /* MAIN */
        .main-content { margin-left: 280px; padding: 30px; transition: margin-left 0.3s ease; }
        .main-content.expanded { margin-left: 0; }
        .topbar {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 30px; background: white; padding: 15px 25px;
            border-radius: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .toggle-btn { background: none; border: none; font-size: 22px; color: #333; cursor: pointer; }
        .profile-box { display: flex; align-items: center; gap: 15px; cursor: pointer; }
        .profile-box img { width: 50px; height: 50px; border-radius: 50%; }

        /* PAGE HEADER */
        .page-header { margin-bottom: 30px; }
        .page-header h2 { color: #670019; font-weight: 700; }

        /* PROFILE CARD */
        .profile-card {
            background: white; border-radius: 25px; padding: 30px;
            box-shadow: 0px 4px 15px rgba(0,0,0,0.05);
        }
        .profile-pic { text-align: center; margin-bottom: 25px; }
        .profile-pic img { width: 120px; height: 120px; border-radius: 50%; object-fit: cover; }
        .form-group { margin-bottom: 20px; }
        .form-group label { font-weight: 500; margin-bottom: 8px; color: #333; }
        .form-group input {
            width: 100%; padding: 12px 15px; border: 1px solid #ddd;
            border-radius: 12px; font-size: 14px; font-family: 'Poppins', sans-serif;
        }
        .form-group input:focus {
            outline: none; border-color: #670019;
            box-shadow: 0 0 0 4px rgba(103,0,25,0.08);
        }
        .form-group input:disabled {
            background: #f5f5f5; color: #999;
        }
        .row-custom {
            display: grid; grid-template-columns: 1fr 1fr; gap: 20px;
        }
        .save-btn {
            background: linear-gradient(to right, #670019, #8b0022);
            color: white; border: none; padding: 12px 30px;
            border-radius: 25px; cursor: pointer; font-size: 14px;
            font-weight: 500; margin-top: 10px; transition: 0.3s;
        }
        .save-btn:hover {
            background: linear-gradient(to right, #8b0022, #a80028);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(103,0,25,0.25);
        }
        .alert-success {
            display: none; background: #d4edda; color: #155724;
            border-radius: 12px; padding: 12px 20px; margin-bottom: 20px;
        }
        @media (max-width: 992px) {
            .row-custom { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <div class="logo">
        <img src="images/utmlogo.png" alt="UTM Logo">
        <div class="system-title">COURSE REGISTRATION SYSTEM</div>
    </div>
    <div class="menu">
        <a href="advisor_dashboard.php"><i class="bi bi-house-fill"></i> Dashboard</a>
        <a href="advisor_my_students.php"><i class="bi bi-people-fill"></i> My Students</a>
        <a href="advisor_registrations.php"><i class="bi bi-file-earmark-text-fill"></i> Registrations</a>
        <a href="advisor_profile.php" class="active"><i class="bi bi-person-fill"></i> Profile</a>
        <a href="advisor_change_password.php"><i class="bi bi-lock-fill"></i> Change Password</a>
    </div>
    <div class="logout">
        <a href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
    </div>
</div>

<!-- MAIN CONTENT -->
<div class="main-content">
    <div class="topbar">
        <button class="toggle-btn" onclick="toggleSidebar()"><i class="bi bi-list"></i></button>
        <div class="profile-box" onclick="location.href='advisor_profile.php'">
            <i class="bi bi-bell fs-5"></i>
            <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" alt="Profile">
            <div><h6 class="mb-0" id="advisorName"><?php echo htmlspecialchars($profile_data['full_name']); ?></h6><small class="text-muted">Academic Advisor</small></div>
        </div>
    </div>

    <div class="page-header">
        <h2>My Profile</h2>
    </div>

    <div class="profile-card">
        <div class="profile-pic">
            <img id="profilePic" src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" alt="Profile Photo">
            <div class="mt-2">

            </div>
        </div>

        <div id="successAlert" class="alert-success">
            <i class="bi bi-check-circle-fill me-2"></i> Profile updated successfully!
        </div>

        <div class="row-custom">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" id="fullName" value="<?php echo htmlspecialchars($profile_data['full_name']); ?>" disabled>
            </div>
            <div class="form-group">
                <label>Staff ID</label>
                <input type="text" id="staffId" value="<?php echo htmlspecialchars($profile_data['staff_id']); ?>" disabled>
            </div>
            <div class="form-group">
                <label>UTM Email</label>
                <input type="email" id="email" value="<?php echo htmlspecialchars($profile_data['email']); ?>" disabled>
            </div>
            <div class="form-group">
                <label>Second Email</label>
                <input type="email" id="secondEmail" value="<?php echo htmlspecialchars($profile_data['second_email']); ?>" placeholder="Second Email">
            </div>
            <div class="form-group">
                <label>Phone Number</label>
                <input type="text" id="phone" value="<?php echo htmlspecialchars($profile_data['phone']); ?>" placeholder="Phone Number">
            </div>
            <div class="form-group">
                <label>Department</label>
                <input type="text" id="department" value="<?php echo htmlspecialchars($profile_data['department']); ?>" disabled>
            </div>
            <div class="form-group">
                <label>Role</label>
                <input type="text" id="role" value="Academic Advisor" disabled>
            </div>
        </div>

        <button class="save-btn" id="saveBtn"><i class="bi bi-save"></i> Save Changes</button>
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

    // Save profile changes (only second_email and phone - department removed)
    document.getElementById('saveBtn').addEventListener('click', async () => {
        const formData = {
            second_email: document.getElementById('secondEmail').value,
            phone: document.getElementById('phone').value
        };
        
        try {
            const response = await fetch('advisor_profile.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams(formData)
            });
            
            const result = await response.json();
            if (result.success) {
                const alertDiv = document.getElementById('successAlert');
                alertDiv.style.display = 'block';
                setTimeout(() => {
                    alertDiv.style.display = 'none';
                }, 3000);
            } else {
                alert('Update failed: ' + (result.message || 'Unknown error'));
            }
        } catch (err) {
            alert('Update failed: ' + err.message);
        }
    });

    document.getElementById('changePhotoLink').addEventListener('click', (e) => {
        e.preventDefault();
        alert('Photo upload feature will be implemented later');
    });
</script>
</body>
</html>