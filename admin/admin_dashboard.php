<?php
session_start();

// 1. Include database connection
include '../db_connect.php';

// 2. Security check: Only admin allowed
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.html");
    exit();
}

// 3. Fetch dynamic counts
// Note: You need tables 'students', 'advisors', 'subjects' in your DB
$students_count = 0;
$advisors_count = 0;
$subjects_count = 0;

if ($conn) {
    // Count students from 'users' table if role = 'student'
    $stmt1 = $conn->prepare("SELECT COUNT(*) as total FROM users WHERE role = 'student'");
    $stmt1->execute();
    $result1 = $stmt1->get_result();
    $students_count = $result1->fetch_assoc()['total'];

    // Count advisors from 'users' table if role = 'advisor'
    $stmt2 = $conn->prepare("SELECT COUNT(*) as total FROM users WHERE role = 'advisor'");
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    $advisors_count = $result2->fetch_assoc()['total'];

    // Count subjects – You need a 'subjects' table. This will return 0 if table doesn't exist.
    $subjects_check = $conn->query("SHOW TABLES LIKE 'subjects'");
    if ($subjects_check->num_rows > 0) {
        $stmt3 = $conn->prepare("SELECT COUNT(*) as total FROM subjects");
        $stmt3->execute();
        $result3 = $stmt3->get_result();
        $subjects_count = $result3->fetch_assoc()['total'];
    }
}

// 4. Get admin name
$user_id = $_SESSION['user_id'];
$name_query = "SELECT name FROM users WHERE id = ?";
$stmt_name = $conn->prepare($name_query);
$stmt_name->bind_param("i", $user_id);
$stmt_name->execute();
$name_result = $stmt_name->get_result();
$admin_name = $name_result->fetch_assoc()['name'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Portal</title>
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* ---------- RESET ---------- */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            display: flex;
            background-color: #f4f6f9;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* ---------- SIDEBAR ---------- */
        .sidebar {
            width: 250px;
            background-color: #7A0D2A; /* Dark maroon */
            color: white;
            display: flex;
            flex-direction: column;
            padding: 20px 0;
            position: fixed;
            height: 100%;
            left: 0;
            top: 0;
            z-index: 1000;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }

        .sidebar h1 {
            font-size: 24px;
            font-weight: 600;
            padding: 0 25px 30px 25px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar nav ul {
            list-style: none;
            padding: 20px 15px;
        }

        .sidebar nav ul li {
            margin-bottom: 12px;
        }

        .sidebar nav ul li a {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            transition: 0.3s ease;
            font-size: 16px;
        }

        .sidebar nav ul li a i {
            margin-right: 15px;
            width: 20px;
            text-align: center;
        }

        .sidebar nav ul li a:hover {
            background-color: rgba(255,255,255,0.1);
        }

        .sidebar nav ul li a.active {
            background-color: #DE9E1F; /* Orange gold */
            color: #fff;
            font-weight: 500;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .sidebar nav ul li a.logout {
            margin-top: 60px;
            border-top: 1px solid rgba(255,255,255,0.1);
            padding-top: 20px;
            border-radius: 0;
        }

        /* ---------- MAIN CONTENT ---------- */
        .main-content {
            margin-left: 250px;
            width: calc(100% - 250px);
            padding: 30px;
            background-color: #f4f6f9;
        }

        /* ---------- TOP CARDS ---------- */
        .welcome-card {
            background: white;
            padding: 40px 30px;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            margin-bottom: 40px;
        }

        .welcome-card h2 {
            font-size: 32px;
            color: #1f2937;
            margin-bottom: 8px;
        }

        .welcome-card h2 span {
            display: inline-block;
        }

        .welcome-card p {
            color: #6b7280;
            font-size: 16px;
        }

        /* ---------- STATS CARDS ---------- */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 30px 20px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            transition: transform 0.2s ease;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.08);
        }

        .stat-card i {
            font-size: 40px;
            margin-bottom: 10px;
            display: block;
        }

        .stat-icon-student { color: #F59E0B; }
        .stat-icon-advisor { color: #E53E3E; }
        .stat-icon-subject { color: #047857; }

        .stat-card h3 {
            font-size: 42px;
            color: #7A0D2A;
            margin: 10px 0 5px 0;
            font-weight: 700;
        }

        .stat-card p {
            color: #4b5563;
            font-weight: 500;
            font-size: 16px;
            margin: 0;
        }

        /* ---------- RESPONSIVE ---------- */
        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
            }
            .main-content {
                margin-left: 200px;
                padding: 20px;
            }
        }
        @media (max-width: 576px) {
            body {
                flex-direction: column;
            }
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                padding: 15px;
            }
            .main-content {
                margin-left: 0;
                width: 100%;
            }
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

    <!-- LEFT SIDEBAR -->
    <aside class="sidebar">
        <h1>Admin Portal</h1>
        <nav>
            <ul>
                <li><a href="admin_dashboard.php" class="active"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="profile.php"><i class="fas fa-user"></i> Profile</a></li>

                <!-- Changed: 'Add Student' → 'Manage Students' -->
                <li><a href="manage_students.php"><i class="fas fa-user-graduate"></i> Manage Students</a></li>

                <!-- Changed: 'Add Advisor' → 'Manage Advisors' -->
                <li><a href="manage_advisors.php"><i class="fas fa-users"></i> Manage Advisors</a></li>

                <!-- Changed: 'Add Subject' → 'Manage Subjects' -->
                <li><a href="manage_subjects.php"><i class="fas fa-book"></i> Manage Subjects</a></li>

                <li><a href="../forgot_password.php"><i class="fas fa-key"></i> Forgot Password</a></li>
                <li><a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </nav>
    </aside>

    <!-- MAIN CONTENT -->
    <div class="main-content">

        <!-- WELCOME CARD -->
        <div class="welcome-card">
            <h2>Welcome Admin 👋</h2>
            <p>Manage students, advisors and subjects here.</p>
        </div>

        <!-- STATS CARDS -->
        <div class="stats-grid">

            <div class="stat-card">
                <i class="fas fa-users stat-icon-student"></i>
                <h3><?php echo number_format($students_count); ?></h3>
                <p>Students Registered</p>
            </div>

            <div class="stat-card">
                <i class="fas fa-user-tie stat-icon-advisor"></i>
                <h3><?php echo number_format($advisors_count); ?></h3>
                <p>Academic Advisors</p>
            </div>

            <div class="stat-card">
                <i class="fas fa-book-open stat-icon-subject"></i>
                <h3><?php echo number_format($subjects_count); ?></h3>
                <p>Subjects Registered</p>
            </div>

        </div>

    </div>

</body>
</html>