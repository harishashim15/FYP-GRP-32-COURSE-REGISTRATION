<?php
require_once 'config.php';

$advisor_matrix = 'AA0001';

$sql = "SELECT * FROM users WHERE matrix = ? AND role = 'advisor'";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $advisor_matrix);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$advisor = mysqli_fetch_assoc($result);

if (!$advisor) {
    $sql = "INSERT INTO users (matrix, name, email, password, role, phone, department) VALUES ('AA0001', 'Miss Nurul Asyikin', 'nurul.asyikin@utm.my', '1234', 'advisor', '+60 12-345 6789', 'School of Professional & Continuing Education')";
    mysqli_query($conn, $sql);
    $sql = "SELECT * FROM users WHERE matrix = 'AA0001'";
    $result = mysqli_query($conn, $sql);
    $advisor = mysqli_fetch_assoc($result);
}

$update_message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $department = $_POST['department'] ?? '';
    
    $sql = "UPDATE users SET name = ?, email = ?, phone = ?, department = ? WHERE matrix = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sssss", $name, $email, $phone, $department, $advisor_matrix);
    
    if (mysqli_stmt_execute($stmt)) {
        $update_message = '<div class="alert alert-success">Profile updated successfully!</div>';
        $sql = "SELECT * FROM users WHERE matrix = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $advisor_matrix);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $advisor = mysqli_fetch_assoc($result);
    } else {
        $update_message = '<div class="alert alert-danger">Error updating profile.</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - UTM Academic Advisor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background: #f8f6f4; overflow-x: hidden; }
        .sidebar { width: 280px; height: 100vh; background: linear-gradient(to bottom, #670019, #8b0022); position: fixed; padding: 30px 20px; color: white; transition: transform 0.3s ease; }
        .sidebar.collapsed { transform: translateX(-280px); }
        .logo { text-align: center; margin-bottom: 50px; }
        .logo img { width: 130px; }
        .system-title { color: #ffc107; font-size: 16px; font-weight: 600; margin-top: 12px; }
        .menu a { display: flex; align-items: center; gap: 15px; text-decoration: none; color: white; padding: 12px 20px; border-radius: 14px; margin-bottom: 12px; transition: 0.3s; font-size: 16px; }
        .menu a:hover, .menu .active { background: linear-gradient(to right, #f4a000, #e08700); }
        .menu i { font-size: 20px; }
        .logout { position: absolute; bottom: 30px; width: calc(100% - 40px); left: 20px; }
        .logout a { display: flex; align-items: center; gap: 15px; text-decoration: none; color: white; padding: 12px 20px; border-radius: 14px; transition: 0.3s; font-size: 16px; background: rgba(255,255,255,0.1); }
        .logout a:hover { background: linear-gradient(to right, #f4a000, #e08700); }
        .main-content { margin-left: 280px; padding: 30px; transition: margin-left 0.3s ease; }
        .main-content.expanded { margin-left: 0; }
        .topbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; background: white; padding: 15px 25px; border-radius: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .profile-box { display: flex; align-items: center; gap: 15px; }
        .profile-box img { width: 50px; height: 50px; border-radius: 50%; }
        .toggle-btn { background: none; border: none; font-size: 22px; color: #333; cursor: pointer; }
        .page-header { margin-bottom: 30px; }
        .page-header h2 { color: #670019; font-weight: 700; }
        .profile-card { background: white; border-radius: 25px; padding: 30px; box-shadow: 0px 4px 15px rgba(0,0,0,0.05); }
        .profile-pic { text-align: center; margin-bottom: 25px; }
        .profile-pic img { width: 120px; height: 120px; border-radius: 50%; object-fit: cover; }
        .form-group { margin-bottom: 20px; }
        .form-group label { font-weight: 500; margin-bottom: 8px; color: #333; }
        .form-group input { width: 100%; padding: 12px 15px; border: 1px solid #ddd; border-radius: 12px; font-size: 14px; font-family: 'Poppins', sans-serif; }
        .form-group input:focus { outline: none; border-color: #670019; box-shadow: 0 0 0 4px rgba(103,0,25,0.08); }
        .form-group input:disabled { background: #f5f5f5; color: #999; }
        .save-btn { background: linear-gradient(to right, #670019, #8b0022); color: white; border: none; padding: 12px 30px; border-radius: 25px; cursor: pointer; font-size: 14px; font-weight: 500; margin-top: 10px; }
        .save-btn:hover { background: linear-gradient(to right, #8b0022, #a80028); transform: translateY(-2px); }
        .row-custom { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .alert { padding: 12px 20px; border-radius: 12px; margin-bottom: 20px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo"><img src="images/utmlogo.png" alt="UTM Logo"><div class="system-title">COURSE REGISTRATION SYSTEM</div></div>
        <div class="menu">
            <a href="advisor_dashboard.php"><i class="bi bi-house-fill"></i> Dashboard</a>
            <a href="advisor_my_students.php"><i class="bi bi-people-fill"></i> My Students</a>
            <a href="advisor_registrations.php"><i class="bi bi-file-earmark-text-fill"></i> Registrations</a>
            <a href="#" class="active"><i class="bi bi-person-fill"></i> Profile</a>
            <a href="advisor_change_password.php"><i class="bi bi-lock-fill"></i> Change Password</a>
        </div>
        <div class="logout"><a href="index.html"><i class="bi bi-box-arrow-right"></i> Logout</a></div>
    </div>

    <div class="main-content">
        <div class="topbar">
            <button class="toggle-btn" onclick="toggleSidebar()"><i class="bi bi-list"></i></button>
            <div class="profile-box">
                <i class="bi bi-bell fs-5"></i>
                <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png">
                <div><h6 class="mb-0"><?php echo htmlspecialchars($advisor['name']); ?></h6><small class="text-muted">Academic Advisor</small></div>
            </div>
        </div>

        <div class="page-header"><h2>My Profile</h2></div>
        
        <?php echo $update_message; ?>

        <div class="profile-card">
            <div class="profile-pic">
                <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png">
                <div class="mt-2"><a href="#" style="color: #670019;">Change Photo</a></div>
            </div>
            <form method="POST">
                <div class="row-custom">
                    <div class="form-group"><label>Full Name</label><input type="text" name="name" value="<?php echo htmlspecialchars($advisor['name']); ?>"></div>
                    <div class="form-group"><label>Matrix</label><input type="text" value="<?php echo htmlspecialchars($advisor['matrix']); ?>" disabled></div>
                    <div class="form-group"><label>Email Address</label><input type="email" name="email" value="<?php echo htmlspecialchars($advisor['email']); ?>"></div>
                    <div class="form-group"><label>Phone Number</label><input type="text" name="phone" value="<?php echo htmlspecialchars($advisor['phone'] ?? '+60 12-345 6789'); ?>"></div>
                    <div class="form-group"><label>Department</label><input type="text" name="department" value="<?php echo htmlspecialchars($advisor['department'] ?? 'School of Professional & Continuing Education'); ?>"></div>
                    <div class="form-group"><label>Role</label><input type="text" value="Academic Advisor" disabled></div>
                </div>
                <button type="submit" class="save-btn"><i class="bi bi-save"></i> Save Changes</button>
            </form>
        </div>
    </div>

    <script>
        (function() { if (localStorage.getItem('sidebarCollapsed') === 'true') { document.querySelector('.sidebar').classList.add('collapsed'); document.querySelector('.main-content').classList.add('expanded'); } })();
        function toggleSidebar() { const sidebar = document.querySelector('.sidebar'); const main = document.querySelector('.main-content'); sidebar.classList.toggle('collapsed'); main.classList.toggle('expanded'); localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed')); }
    </script>
</body>
</html>