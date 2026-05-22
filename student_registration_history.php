<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

include("db.php");

// Fetch student name from database
$user_id = $_SESSION['user_id'];
$user_query = "SELECT name FROM users WHERE id = '$user_id'";
$user_result = mysqli_query($conn, $user_query);
$user = mysqli_fetch_assoc($user_result);
$student_name = $user ? $user['name'] : "Student";

// Fetch submitted registrations
$query = "SELECT r.*, c.course_name 
          FROM registrations r 
          JOIN courses c ON r.course_id = c.course_code 
          WHERE r.user_id = '$user_id' 
          ORDER BY r.submitted_date DESC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration History - UTM Student</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background: #f8f6f4;
            overflow-x: hidden;
        }
        
        .sidebar {
            width: 280px;
            height: 100vh;
            background: linear-gradient(to bottom, #670019, #8b0022);
            position: fixed;
            padding: 30px 20px;
            color: white;
            transition: transform 0.3s ease;
            z-index: 1000;
        }
        
        .sidebar.collapsed {
            transform: translateX(-280px);
        }
        
        .logo {
            text-align: center;
            margin-bottom: 50px;
        }
        
        .logo img {
            width: 130px;
        }
        
        .system-title {
            color: #ffc107;
            font-size: 16px;
            font-weight: 600;
            margin-top: 12px;
        }
        
        .menu a {
            display: flex;
            align-items: center;
            gap: 15px;
            text-decoration: none;
            color: white;
            padding: 12px 20px;
            border-radius: 14px;
            margin-bottom: 12px;
            transition: 0.3s;
            font-size: 16px;
        }
        
        .menu a:hover,
        .menu .active {
            background: linear-gradient(to right, #f4a000, #e08700);
        }
        
        .menu i {
            font-size: 20px;
        }
        
        .logout {
            position: absolute;
            bottom: 30px;
            width: calc(100% - 40px);
            left: 20px;
        }
        
        .logout a {
            display: flex;
            align-items: center;
            gap: 15px;
            text-decoration: none;
            color: white;
            padding: 12px 20px;
            border-radius: 14px;
            transition: 0.3s;
            font-size: 16px;
            background: rgba(255, 255, 255, 0.1);
        }
        
        .logout a:hover {
            background: linear-gradient(to right, #f4a000, #e08700);
        }
        
        .main-content {
            margin-left: 280px;
            padding: 30px;
            transition: margin-left 0.3s ease;
        }
        
        .main-content.expanded {
            margin-left: 0;
        }
        
        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            background: white;
            padding: 15px 25px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .toggle-btn {
            background: none;
            border: none;
            font-size: 22px;
            color: #333;
            cursor: pointer;
            padding: 0;
            line-height: 1;
        }
        
        .profile-box {
            display: flex;
            align-items: center;
            gap: 15px;
            cursor: pointer;
            transition: 0.3s;
        }
        
        .profile-box:hover {
            opacity: 0.8;
        }
        
        .profile-box img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .page-header {
            background: #f7f2ee;
            border-radius: 25px;
            padding: 35px 40px;
            margin-bottom: 30px;
            border: 1px solid #eee;
        }
        
        .page-header h2 {
            font-size: 34px;
            font-weight: 700;
            color: #670019;
        }
        
        .page-header p {
            color: #666;
            margin-top: 8px;
            font-size: 15px;
        }
        
        .summary-strip {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }
        
        .strip-item {
            background: white;
            border-radius: 14px;
            padding: 12px 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .strip-item strong {
            color: #670019;
            font-size: 18px;
            font-weight: 700;
        }
        
        .strip-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
        }
        
        .history-table {
            background: white;
            border-radius: 25px;
            padding: 30px;
            box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.05);
        }
        
        .history-table h3 {
            color: #670019;
            font-weight: 700;
            font-size: 20px;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0e8e8;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            text-align: left;
            padding: 12px 15px;
            background: #f8f6f4;
            color: #670019;
            font-weight: 600;
            font-size: 13px;
        }
        
        th:first-child {
            border-radius: 10px 0 0 10px;
        }
        
        th:last-child {
            border-radius: 0 10px 10px 0;
        }
        
        td {
            padding: 15px;
            border-bottom: 1px solid #eee;
            font-size: 14px;
            color: #333;
        }
        
        tbody tr:last-child td {
            border-bottom: none;
        }
        
        tbody tr:hover td {
            background: #fdf9f7;
        }
        
        .status-badge {
            padding: 5px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-approved {
            background: #d4edda;
            color: #155724;
        }
        
        .status-rejected {
            background: #f8d7da;
            color: #721c24;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }
        
        .empty-state i {
            font-size: 64px;
            color: #ddd;
            margin-bottom: 20px;
        }
        
        .empty-state h4 {
            color: #666;
            margin-bottom: 15px;
        }
        
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-280px);
            }
            .main-content {
                margin-left: 0;
            }
            .page-header {
                text-align: center;
            }
            table {
                font-size: 12px;
            }
            th, td {
                padding: 10px;
            }
        }
    </style>
</head>
<body>

<div class="sidebar" id="sidebar">
    <div class="logo">
        <img src="images/utmlogo.png" alt="UTM Logo">
        <div class="system-title">COURSE REGISTRATION SYSTEM</div>
    </div>
    <div class="menu">
        <a href="student_dashboard.php">
            <i class="bi bi-house-fill"></i>
            Dashboard
        </a>
        <a href="student_profile.php">
            <i class="bi bi-person-fill"></i>
            Profile
        </a>
        <a href="student_courses.php">
            <i class="bi bi-book-fill"></i>
            Courses
        </a>
        <a href="student_register.php">
            <i class="bi bi-journal-text"></i>
            Register
        </a>
        <a href="student_registration_history.php" class="active">
            <i class="bi bi-clock-history"></i>
            Registration History
        </a>
        <a href="student_change_password.php">
            <i class="bi bi-lock-fill"></i>
            Change Password
        </a>
    </div>
    <div class="logout">
        <a href="logout.php">
            <i class="bi bi-box-arrow-right"></i>
            Logout
        </a>
    </div>
</div>

<div class="main-content" id="mainContent">
    <div class="topbar">
        <button class="toggle-btn" onclick="toggleSidebar()">
            <i class="bi bi-list"></i>
        </button>
        <div class="profile-box" onclick="window.location.href='student_profile.php'">
            <i class="bi bi-bell fs-5"></i>
            <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" alt="Profile">
            <div>
                <h6 class="mb-0"><?php echo htmlspecialchars($student_name); ?></h6>
                <small class="text-muted">Student</small>
            </div>
        </div>
    </div>

    <div class="page-header">
        <div>
            <h2>Registration History</h2>
            <p>Track the status of your submitted course registrations</p>
        </div>
    </div>

    <?php
    // Calculate statistics
    $stats_query = "SELECT status, COUNT(*) as count FROM registrations WHERE user_id = '$user_id' GROUP BY status";
    $stats_result = mysqli_query($conn, $stats_query);
    $stats = ['pending' => 0, 'approved' => 0, 'rejected' => 0];
    while ($row = mysqli_fetch_assoc($stats_result)) {
        $stats[$row['status']] = $row['count'];
    }
    ?>

    <div class="summary-strip">
        <div class="strip-item">
            <span class="strip-dot" style="background: #670019;"></span>
            Total &nbsp;<strong><?php echo array_sum($stats); ?></strong>&nbsp; registrations
        </div>
        <div class="strip-item">
            <span class="strip-dot" style="background: #856404;"></span>
            Pending &nbsp;<strong><?php echo $stats['pending']; ?></strong>
        </div>
        <div class="strip-item">
            <span class="strip-dot" style="background: #155724;"></span>
            Approved &nbsp;<strong><?php echo $stats['approved']; ?></strong>
        </div>
        <div class="strip-item">
            <span class="strip-dot" style="background: #721c24;"></span>
            Rejected &nbsp;<strong><?php echo $stats['rejected']; ?></strong>
        </div>
    </div>

    <div class="history-table">
        <h3><i class="bi bi-journal-text me-2"></i>Submitted Registrations</h3>
        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th>Course Code</th>
                        <th>Course Name</th>
                        <th>Section</th>
                        <th>Session</th>
                        <th>Submitted Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $has_results = false;
                    while ($row = mysqli_fetch_assoc($result)): 
                        $has_results = true;
                    ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($row['course_id']); ?></strong></td>
                        <td><?php echo htmlspecialchars($row['course_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['section'] ?: 'TBD'); ?></td>
                        <td><?php echo htmlspecialchars($row['session'] ?: '2025/2026 - Semester 2'); ?></td>
                        <td><?php echo date('d M Y, h:i A', strtotime($row['submitted_date'])); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo $row['status']; ?>">
                                <?php echo ucfirst($row['status']); ?>
                            </span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php if (!$has_results): ?>
                    <tr>
                        <td colspan="6">
                            <div class="empty-state">
                                <i class="bi bi-inbox"></i>
                                <h4>No registration history found</h4>
                                <p>You haven't submitted any course registrations yet.</p>
                                <a href="student_courses.php" class="btn btn-primary mt-2" style="background: #670019; border: none;">Browse Courses</a>
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    (function() {
        if (localStorage.getItem('sidebarCollapsed') === 'true') {
            document.querySelector('.sidebar').classList.add('collapsed');
            document.querySelector('.main-content').classList.add('expanded');
        }
    })();

    function toggleSidebar() {
        const sidebar = document.querySelector('.sidebar');
        const main = document.querySelector('.main-content');
        sidebar.classList.toggle('collapsed');
        main.classList.toggle('expanded');
        localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
    }
</script>
</body>
</html>