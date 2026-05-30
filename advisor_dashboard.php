<?php
require_once 'db_connect.php';

session_start();

// Get advisor ID from session (after login)
$advisor_id = 1;

// Get total students under this advisor
$sql = "SELECT COUNT(*) as total FROM students WHERE advisor_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $advisor_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$total_students = mysqli_fetch_assoc($result)['total'];

// Get pending approvals count (count distinct students with pending registrations)
$sql = "SELECT COUNT(DISTINCT cr.student_id) as pending 
        FROM course_registrations cr 
        WHERE cr.status = 'pending' 
        AND cr.student_id IN (SELECT user_id FROM students WHERE advisor_id = ?)";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $advisor_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$pending_count = mysqli_fetch_assoc($result)['pending'];

// Get approved registrations this week
$sql = "SELECT COUNT(*) as approved 
        FROM course_registrations cr 
        WHERE cr.status = 'approved' 
        AND cr.student_id IN (SELECT user_id FROM students WHERE advisor_id = ?)
        AND WEEK(cr.submission_date) = WEEK(CURDATE())";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $advisor_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$approved_count = mysqli_fetch_assoc($result)['approved'];

// Get recent pending registration requests
$sql = "SELECT DISTINCT u.user_id, u.matrix_number, u.user_name,
        (SELECT COUNT(*) FROM registration_courses rc 
         JOIN course_registrations cr2 ON rc.registration_id = cr2.id 
         WHERE cr2.student_id = u.user_id) as course_count,
        cr.status, cr.submission_date
        FROM course_registrations cr
        JOIN users u ON cr.student_id = u.user_id
        WHERE cr.status = 'pending' 
        AND cr.student_id IN (SELECT user_id FROM students WHERE advisor_id = ?)
        ORDER BY cr.submission_date DESC LIMIT 5";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $advisor_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$pending_requests = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Get recent approved registrations
$sql_approved = "SELECT DISTINCT u.user_id, u.matrix_number, u.user_name, cr.status
        FROM course_registrations cr
        JOIN users u ON cr.student_id = u.user_id
        WHERE cr.status = 'approved' 
        AND cr.student_id IN (SELECT user_id FROM students WHERE advisor_id = ?)
        ORDER BY cr.submission_date DESC LIMIT 5";
$stmt_approved = mysqli_prepare($conn, $sql_approved);
mysqli_stmt_bind_param($stmt_approved, "i", $advisor_id);
mysqli_stmt_execute($stmt_approved);
$result_approved = mysqli_stmt_get_result($stmt_approved);
$approved_requests = mysqli_fetch_all($result_approved, MYSQLI_ASSOC);

// Get advisor name
$sql_advisor = "SELECT user_name FROM users WHERE user_id = ?";
$stmt_advisor = mysqli_prepare($conn, $sql_advisor);
mysqli_stmt_bind_param($stmt_advisor, "i", $advisor_id);
mysqli_stmt_execute($stmt_advisor);
$result_advisor = mysqli_stmt_get_result($stmt_advisor);
$advisor = mysqli_fetch_assoc($result_advisor);
$advisor_name = $advisor ? $advisor['user_name'] : 'Miss Nurul Asyikin';

// Merge requests
$all_requests = array_merge($pending_requests, $approved_requests);
$all_requests = array_slice($all_requests, 0, 6);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UTM Academic Advisor Dashboard</title>
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
        .profile-box { display: flex; align-items: center; gap: 15px; cursor: pointer; }
        .profile-box img { width: 50px; height: 50px; border-radius: 50%; }
        .toggle-btn { background: none; border: none; font-size: 22px; color: #333; cursor: pointer; }
        .hero { background: #f7f2ee; border-radius: 25px; padding: 40px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 35px; border: 1px solid #eee; }
        .hero h1 { font-size: 40px; font-weight: 700; color: #670019; }
        .hero p { color: #666; margin-top: 10px; font-size: 16px; }
        .hero img { width: 200px; }
        .dashboard-card { border: none; border-radius: 25px; padding: 25px; box-shadow: 0px 4px 15px rgba(0,0,0,0.05); transition: 0.3s; height: 100%; background: white; }
        .dashboard-card:hover { transform: translateY(-5px); }
        .card-icon { width: 60px; height: 60px; border-radius: 20px; display: flex; align-items: center; justify-content: center; font-size: 28px; margin-bottom: 15px; }
        .yellow { background: #fff2cc; color: #d48a00; }
        .red { background: #ffe0e0; color: #b00020; }
        .green { background: #e4f7df; color: #2e7d32; }
        .dashboard-card h2 { font-size: 36px; font-weight: 700; color: #670019; margin-bottom: 5px; }
        .dashboard-card h5 { font-size: 16px; font-weight: 600; margin-top: 5px; }
        .dashboard-card p { color: #666; margin-top: 5px; font-size: 13px; }
        .period-card { background: white; border-radius: 25px; padding: 20px; box-shadow: 0px 4px 15px rgba(0,0,0,0.05); margin-bottom: 35px; }
        .period-card h4 { color: #670019; font-weight: 600; margin-bottom: 15px; font-size: 18px; }
        .period-status { display: inline-block; padding: 5px 12px; background: #d4edda; color: #155724; border-radius: 20px; font-size: 12px; margin-bottom: 15px; }
        .pending-requests { background: white; border-radius: 25px; padding: 25px; margin-top: 35px; box-shadow: 0px 4px 15px rgba(0,0,0,0.05); }
        .pending-requests h3 { color: #670019; font-weight: 700; margin-bottom: 20px; font-size: 20px; }
        .status-badge { padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 500; display: inline-block; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-approved { background: #d4edda; color: #155724; }
        .status-rejected { background: #f8d7da; color: #721c24; }
        .btn-approve { background: #d4edda; color: #155724; border: none; padding: 5px 12px; border-radius: 20px; font-size: 11px; margin-right: 5px; cursor: pointer; }
        .btn-reject { background: #f8d7da; color: #721c24; border: none; padding: 5px 12px; border-radius: 20px; font-size: 11px; cursor: pointer; }
        .action-link { color: #670019; text-decoration: none; font-weight: 500; font-size: 13px; }
        .table-custom { width: 100%; border-collapse: collapse; }
        .table-custom th { text-align: left; padding: 10px 8px; border-bottom: 1px solid #eee; color: #666; font-size: 12px; }
        .table-custom td { padding: 10px 8px; border-bottom: 1px solid #eee; font-size: 13px; }
        .quick-actions { background: white; border-radius: 25px; padding: 25px; margin-top: 35px; box-shadow: 0px 4px 15px rgba(0,0,0,0.05); }
        .quick-actions h3 { color: #670019; font-weight: 700; margin-bottom: 20px; font-size: 20px; }
        .action-btn { display: flex; justify-content: space-between; align-items: center; padding: 15px; border: 1px solid #eee; border-radius: 18px; margin-bottom: 12px; text-decoration: none; color: black; transition: 0.3s; }
        .action-btn:hover { background: #fff7ef; }
        .action-left { display: flex; align-items: center; gap: 15px; }
        .action-icon { width: 45px; height: 45px; border-radius: 15px; display: flex; align-items: center; justify-content: center; background: #670019; color: white; font-size: 20px; }
        .action-left h5 { font-size: 15px; margin-bottom: 2px; }
        .action-left small { font-size: 11px; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo"><img src="images/utmlogo.png" alt="UTM Logo"><div class="system-title">COURSE REGISTRATION SYSTEM</div></div>
        <div class="menu">
            <a href="advisor_dashboard.php" class="active"><i class="bi bi-house-fill"></i> Dashboard</a>
            <a href="advisor_my_students.php"><i class="bi bi-people-fill"></i> My Students</a>
            <a href="advisor_registrations.php"><i class="bi bi-file-earmark-text-fill"></i> Registrations</a>
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

        <div class="hero">
            <div>
                <h1>Welcome back, <?php echo htmlspecialchars($advisor_name); ?>!</h1>
                <p>Manage your students' course registrations here.</p>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="dashboard-card">
                    <div class="card-icon yellow"><i class="bi bi-people-fill"></i></div>
                    <h2><?php echo $total_students; ?></h2>
                    <h5>Total Students</h5>
                    <p>Students under your guidance</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="dashboard-card">
                    <div class="card-icon red"><i class="bi bi-clock-history"></i></div>
                    <h2><?php echo $pending_count; ?></h2>
                    <h5>Pending Approvals</h5>
                    <p>Waiting for your review</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="dashboard-card">
                    <div class="card-icon green"><i class="bi bi-check-circle"></i></div>
                    <h2><?php echo $approved_count; ?></h2>
                    <h5>Approved</h5>
                    <p>Registrations approved this week</p>
                </div>
            </div>
        </div>

        <div class="period-card">
            <h4><i class="bi bi-calendar-event"></i> Registration Period</h4>
            <span class="period-status">🟢 Currently open</span>
            <div class="mt-3">
                <strong>1 May – 15 May 2026</strong>
                <div class="mt-2">
                    <span class="badge bg-light text-dark me-1">Semester 2</span>
                    <span class="badge bg-light text-dark me-1">2025/2026</span>
                    <span class="badge bg-success">Active</span>
                </div>
            </div>
        </div>

        <div class="pending-requests">
            <h3><i class="bi bi-clock-history"></i> Recent Registration Requests</h3>
            <div style="overflow-x: auto;">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Matrix</th>
                            <th>Courses</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($all_requests)): ?>
                            <?php foreach ($all_requests as $request): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($request['user_name']); ?></strong><br><small><?php echo htmlspecialchars($request['matrix_number']); ?></small></td>
                                    <td><?php echo htmlspecialchars($request['matrix_number']); ?></td>
                                    <td><?php echo isset($request['course_count']) ? $request['course_count'] : '0'; ?> courses</small></td>
                                    <td><span class="status-badge status-<?php echo $request['status']; ?>"><?php echo ucfirst($request['status']); ?></span></small></td>
                                    <td>
                                        <?php if ($request['status'] == 'pending'): ?>
                                            <button class="btn-approve" onclick="location.href='advisor_verify_registration.php?id=<?php echo $request['user_id']; ?>'"><i class="bi bi-check"></i> Approve</button>
                                            <button class="btn-reject" onclick="location.href='advisor_verify_registration.php?id=<?php echo $request['user_id']; ?>'"><i class="bi bi-x"></i> Reject</button>
                                        <?php else: ?>
                                            <a href="advisor_verify_registration.php?id=<?php echo $request['user_id']; ?>" class="action-link">View Details</a>
                                        <?php endif; ?>
                                     </small>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center">No registration requests found</small></td>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="text-center mt-3"><a href="advisor_registrations.php" class="action-link">View all requests →</a></div>
        </div>

        <div class="quick-actions">
            <h3><i class="bi bi-lightning-charge"></i> Quick Actions</h3>
            <a href="advisor_my_students.php" class="action-btn"><div class="action-left"><div class="action-icon"><i class="bi bi-people-fill"></i></div><div><h5 class="mb-0">View All Students</h5><small>See all students under your guidance</small></div></div><i class="bi bi-chevron-right"></i></a>
            <a href="advisor_registrations.php" class="action-btn"><div class="action-left"><div class="action-icon"><i class="bi bi-file-earmark-text-fill"></i></div><div><h5 class="mb-0">Review Registrations</h5><small>Approve or reject student registrations</small></div></div><i class="bi bi-chevron-right"></i></a>
            <a href="advisor_profile.php" class="action-btn"><div class="action-left"><div class="action-icon"><i class="bi bi-person-fill"></i></div><div><h5 class="mb-0">My Profile</h5><small>Update your personal information</small></div></div><i class="bi bi-chevron-right"></i></a>
        </div>
    </div>

    <script>
        (function() { if (localStorage.getItem('sidebarCollapsed') === 'true') { document.querySelector('.sidebar').classList.add('collapsed'); document.querySelector('.main-content').classList.add('expanded'); } })();
        function toggleSidebar() { const sidebar = document.querySelector('.sidebar'); const main = document.querySelector('.main-content'); sidebar.classList.toggle('collapsed'); main.classList.toggle('expanded'); localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed')); }
    </script>
</body>
</html>