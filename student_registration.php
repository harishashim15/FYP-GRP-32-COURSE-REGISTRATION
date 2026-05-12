<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

include("db.php");

$user_id = $_SESSION['user_id'];

// Fetch user's registered courses with status
$query = "SELECT r.course_id, r.status, c.course_name, c.course_code 
          FROM registrations r 
          JOIN courses c ON r.course_id = c.course_code 
          WHERE r.user_id = '$user_id'";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Registration - UTM Student</title>
    
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
        
        /* Sidebar Styles */
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
        
        /* Main Content */
        .main-content {
            margin-left: 280px;
            padding: 30px;
            transition: margin-left 0.3s ease;
        }
        
        .main-content.expanded {
            margin-left: 0;
        }
        
        /* Topbar */
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
        }
        
        .profile-box img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        /* Page Header */
        .page-header {
            background: #f7f2ee;
            border-radius: 25px;
            padding: 35px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
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
        
        .page-header img {
            width: 150px;
        }
        
        /* Summary Strip */
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
        
        /* Registration Table */
        .registration-table {
            background: white;
            border-radius: 25px;
            padding: 30px;
            box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.05);
        }
        
        .registration-table h3 {
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
        
        /* Status Badges */
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
        
        /* Empty State */
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
        
        .empty-state a {
            color: #670019;
            font-weight: 500;
            text-decoration: none;
        }
        
        .empty-state a:hover {
            text-decoration: underline;
        }
        
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-280px);
            }
            .main-content {
                margin-left: 0;
            }
            .page-header {
                flex-direction: column;
                text-align: center;
            }
            .page-header img {
                margin-top: 20px;
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

<!-- SIDEBAR -->
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
        <a href="student_registration.php" class="active">
            <i class="bi bi-file-earmark-text-fill"></i>
            My Registration
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

<!-- MAIN CONTENT -->
<div class="main-content" id="mainContent">
    <!-- TOPBAR -->
    <div class="topbar">
        <button class="toggle-btn" onclick="toggleSidebar()">
            <i class="bi bi-list"></i>
        </button>
        <div class="profile-box">
            <i class="bi bi-bell fs-5"></i>
            <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" alt="Profile">
            <div>
                <h6 class="mb-0">Student</h6>
                <small class="text-muted">Registration Status</small>
            </div>
        </div>
    </div>

    <!-- PAGE HEADER -->
    <div class="page-header">
        <div>
            <h2>My Registration</h2>
            <p>Track your course registration requests and their status</p>
        </div>
        <img src="https://cdn-icons-png.flaticon.com/512/2889/2889676.png" alt="Registration">
    </div>

    <!-- SUMMARY STRIP -->
    <div class="summary-strip">
        <div class="strip-item">
            <span class="strip-dot" style="background: #670019;"></span>
            Total &nbsp;<strong id="totalCount">0</strong>&nbsp; courses
        </div>
        <div class="strip-item">
            <span class="strip-dot" style="background: #856404;"></span>
            Pending &nbsp;<strong id="pendingCount">0</strong>
        </div>
        <div class="strip-item">
            <span class="strip-dot" style="background: #155724;"></span>
            Approved &nbsp;<strong id="approvedCount">0</strong>
        </div>
        <div class="strip-item">
            <span class="strip-dot" style="background: #721c24;"></span>
            Rejected &nbsp;<strong id="rejectedCount">0</strong>
        </div>
    </div>

    <!-- REGISTRATION TABLE -->
    <div class="registration-table">
        <h3><i class="bi bi-journal-text me-2"></i>Course Registration Status</h3>
        <div style="overflow-x: auto;">
            <table>
                <thead>
                    <tr>
                        <th>Course Code</th>
                        <th>Course Name</th>
                        <th>Registration Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="registrationTableBody">
                    <?php 
                    $has_results = false;
                    while ($row = mysqli_fetch_assoc($result)): 
                        $has_results = true;
                    ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($row['course_code']); ?></strong></td>
                        <td><?php echo htmlspecialchars($row['course_name']); ?></td>
                        <td><?php echo date('d M Y'); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo $row['status']; ?>">
                                <?php echo ucfirst($row['status']); ?>
                            </span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php if (!$has_results): ?>
                    <tr>
                        <td colspan="4">
                            <div class="empty-state">
                                <i class="bi bi-inbox"></i>
                                <h4>No courses registered yet</h4>
                                <p>You haven't registered for any courses this semester.</p>
                                <a href="student_courses.php"><i class="bi bi-arrow-right-circle me-1"></i> Browse Courses</a>
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

    // Calculate summary counts
    function updateSummaryCounts() {
        const rows = document.querySelectorAll('#registrationTableBody tr');
        let total = 0, pending = 0, approved = 0, rejected = 0;
        
        rows.forEach(row => {
            const statusCell = row.querySelector('.status-badge');
            if (statusCell) {
                total++;
                const status = statusCell.innerText.toLowerCase().trim();
                if (status === 'pending') pending++;
                else if (status === 'approved') approved++;
                else if (status === 'rejected') rejected++;
            }
        });
        
        document.getElementById('totalCount').innerText = total;
        document.getElementById('pendingCount').innerText = pending;
        document.getElementById('approvedCount').innerText = approved;
        document.getElementById('rejectedCount').innerText = rejected;
    }
    
    updateSummaryCounts();
</script>
</body>
</html>