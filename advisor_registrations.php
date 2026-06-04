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

// Get all registrations for students under this advisor
// This groups by student and shows ONE row per student
$sql = "SELECT 
            u.user_id as student_id,
            u.matrix_number as student_matrix,
            u.user_name as student_name,
            COUNT(DISTINCT rc.id) as course_count,
            MIN(cr.submission_date) as submitted_date,
            cr.status,
            -- Get the latest registration_id for this student
            (SELECT id FROM course_registrations 
             WHERE student_id = s.user_id 
             ORDER BY submission_date DESC LIMIT 1) as registration_id
        FROM course_registrations cr
        JOIN students s ON cr.student_id = s.user_id
        JOIN users u ON s.user_id = u.user_id
        LEFT JOIN registration_courses rc ON cr.id = rc.registration_id
        WHERE s.advisor_id = ?
        GROUP BY u.user_id, u.matrix_number, u.user_name, cr.status
        ORDER BY cr.submission_date DESC";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $advisor_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$registrations = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Calculate pending count
$pending_count = 0;
foreach ($registrations as $reg) {
    if ($reg['status'] == 'pending') $pending_count++;
}

// Get advisor name for topbar
$sql_advisor = "SELECT user_name FROM users WHERE user_id = ?";
$stmt_advisor = mysqli_prepare($conn, $sql_advisor);
mysqli_stmt_bind_param($stmt_advisor, "i", $advisor_id);
mysqli_stmt_execute($stmt_advisor);
$result_advisor = mysqli_stmt_get_result($stmt_advisor);
$advisor = mysqli_fetch_assoc($result_advisor);
$advisor_name = $advisor ? $advisor['user_name'] : 'Advisor';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrations - UTM Academic Advisor</title>
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
        .system-title { color: white; font-size: 16px; font-weight: 600; margin-top: 12px; }
        .menu a { display: flex; align-items: center; gap: 15px; text-decoration: none; color: white; padding: 12px 20px; border-radius: 14px; margin-bottom: 12px; transition: 0.3s; font-size: 16px; }
        .menu a:hover, .menu .active { background: linear-gradient(to right, #f4a000, #e08700); }
        .menu i { font-size: 20px; }
        .logout { position: absolute; bottom: 30px; width: calc(100% - 40px); left: 20px; }
        .logout a { display: flex; align-items: center; gap: 15px; text-decoration: none; color: white; padding: 12px 20px; border-radius: 14px; transition: 0.3s; font-size: 16px; background: rgba(255,255,255,0.1); }
        .logout a:hover { background: linear-gradient(to right, #f4a000, #e08700); }
        .main-content { margin-left: 280px; padding: 30px; transition: margin-left 0.3s ease; }
        .main-content.expanded { margin-left: 0; }
        .topbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; background: white; padding: 15px 25px; border-radius: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .profile-box { display: flex; align-items: center; gap: 15px; cursor: pointer; }
        .profile-box img { width: 50px; height: 50px; border-radius: 50%; }
        .toggle-btn { background: none; border: none; font-size: 22px; color: #333; cursor: pointer; }
        .page-header { margin-bottom: 30px; }
        .page-header h2 { color: #670019; font-weight: 700; }
        .page-header p { color: #666; margin-top: 5px; }
        .filter-tabs { display: flex; gap: 15px; margin-bottom: 25px; }
        .filter-tab { padding: 8px 25px; background: white; border: 1px solid #ddd; border-radius: 25px; cursor: pointer; font-size: 14px; }
        .filter-tab.active { background: #670019; color: white; border-color: #670019; }
        .registrations-table { background: white; border-radius: 25px; padding: 25px; box-shadow: 0px 4px 15px rgba(0,0,0,0.05); }
        .registrations-table table { width: 100%; border-collapse: collapse; }
        .registrations-table th { text-align: left; padding: 12px 12px; background: #f8f6f4; color: #670019; font-weight: 600; font-size: 13px; }
        .registrations-table td { padding: 13px 12px; border-bottom: 1px solid #eee; font-size: 13px; }
        .status-badge { padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 500; display: inline-block; }
        .status-approved { background: #d4edda; color: #155724; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-rejected { background: #f8d7da; color: #721c24; }
        .review-btn { background: #670019; color: white; border: none; padding: 5px 20px; border-radius: 20px; cursor: pointer; font-size: 12px; font-weight: 500; }
        .review-btn:hover { background: #8b0022; }
        .view-btn { background: none; border: 1.5px solid #670019; color: #670019; padding: 5px 20px; border-radius: 20px; cursor: pointer; font-size: 12px; font-weight: 500; }
        .view-btn:hover { background: #670019; color: white; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo"><img src="images/utmlogo.png" alt="UTM Logo"><div class="system-title">COURSE REGISTRATION SYSTEM</div></div>
        <div class="menu">
            <a href="advisor_dashboard.php"><i class="bi bi-house-fill"></i> Dashboard</a>
            <a href="advisor_my_students.php"><i class="bi bi-people-fill"></i> My Students</a>
            <a href="#" class="active"><i class="bi bi-file-earmark-text-fill"></i> Registrations</a>
            <a href="advisor_profile.php"><i class="bi bi-person-fill"></i> Profile</a>
            <a href="advisor_change_password.php"><i class="bi bi-lock-fill"></i> Change Password</a>
        </div>
        <div class="logout"><a href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></div>
    </div>

    <div class="main-content">
        <div class="topbar">
            <button class="toggle-btn" onclick="toggleSidebar()"><i class="bi bi-list"></i></button>
            <div class="profile-box" onclick="location.href='advisor_profile.php'">
                <i class="bi bi-bell fs-5"></i>
                <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png">
                <div>
                    <h6 class="mb-0"><?php echo htmlspecialchars($advisor_name); ?></h6>
                    <small class="text-muted">Academic Advisor</small>
                </div>
            </div>
        </div>

        <div class="page-header">
            <h2>Student Registrations</h2>
            <p>Review and manage student course registrations (Pending: <?php echo $pending_count; ?>)</p>
        </div>

        <div class="filter-tabs">
            <div class="filter-tab active" data-filter="all">All</div>
            <div class="filter-tab" data-filter="pending">Pending</div>
            <div class="filter-tab" data-filter="approved">Approved</div>
            <div class="filter-tab" data-filter="rejected">Rejected</div>
        </div>

        <div class="registrations-table">
            <table class="table">
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Matrix</th>
                        <th>Courses</th>
                        <th>Submitted Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="regTableBody">
                    <?php if (!empty($registrations)): ?>
                        <?php foreach ($registrations as $reg): ?>
                            <tr data-status="<?php echo $reg['status']; ?>">
                                <td><?php echo htmlspecialchars($reg['student_name']); ?></td>
                                <td><?php echo htmlspecialchars($reg['student_matrix']); ?></td>
                                <td><?php echo $reg['course_count']; ?> courses</small></td>
                                <td><?php echo date('d M Y', strtotime($reg['submitted_date'])); ?></small></td>
                                <td><span class="status-badge status-<?php echo $reg['status']; ?>"><?php echo ucfirst($reg['status']); ?></span></small></td>
                                <td>
                                    <?php if ($reg['status'] == 'pending'): ?>
                                        <button class="review-btn" onclick="location.href='advisor_verify_registration.php?student_id=<?php echo $reg['student_id']; ?>'">Review</button>
                                    <?php else: ?>
                                        <button class="view-btn" onclick="location.href='advisor_verify_registration.php?student_id=<?php echo $reg['student_id']; ?>'">View</button>
                                    <?php endif; ?>
                                 </small>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center">No registrations found</small></td>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        const tabs = document.querySelectorAll('.filter-tab');
        tabs.forEach(tab => {
            tab.addEventListener('click', function () {
                tabs.forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                const filter = this.dataset.filter;
                document.querySelectorAll('#regTableBody tr').forEach(row => {
                    row.style.display = (filter === 'all' || row.dataset.status === filter) ? '' : 'none';
                });
            });
        });
        (function() { if (localStorage.getItem('sidebarCollapsed') === 'true') { document.querySelector('.sidebar').classList.add('collapsed'); document.querySelector('.main-content').classList.add('expanded'); } })();
        function toggleSidebar() { const sidebar = document.querySelector('.sidebar'); const main = document.querySelector('.main-content'); sidebar.classList.toggle('collapsed'); main.classList.toggle('expanded'); localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed')); }
    </script>
</body>
</html>