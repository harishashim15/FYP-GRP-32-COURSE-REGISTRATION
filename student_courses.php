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

// Fetch all courses
$courses_query = "SELECT * FROM courses";
$courses_result = mysqli_query($conn, $courses_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courses - UTM Student</title>
    
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
        
        .search-bar {
            display: flex;
            gap: 12px;
            margin-bottom: 25px;
        }
        
        .search-bar input {
            flex: 1;
            padding: 12px 20px;
            border: 1.5px solid #e0d6d6;
            border-radius: 25px;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
            outline: none;
            transition: 0.3s;
        }
        
        .search-bar input:focus {
            border-color: #670019;
            box-shadow: 0 0 0 4px rgba(103, 0, 25, 0.08);
        }
        
        .search-bar button {
            padding: 12px 25px;
            background: linear-gradient(to right, #670019, #8b0022);
            color: white;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            font-weight: 500;
            transition: 0.3s;
        }
        
        .search-bar button:hover {
            background: linear-gradient(to right, #8b0022, #a80028);
        }
        
        .courses-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
        }
        
        .course-card {
            background: white;
            border-radius: 25px;
            padding: 25px;
            box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.05);
            transition: 0.3s;
            border: 1px solid #f0e8e8;
        }
        
        .course-card:hover {
            transform: translateY(-5px);
            box-shadow: 0px 8px 25px rgba(0, 0, 0, 0.1);
        }
        
        .course-code {
            background: #670019;
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
            margin-bottom: 15px;
        }
        
        .course-card h4 {
            color: #670019;
            font-weight: 700;
            margin-bottom: 10px;
            font-size: 18px;
        }
        
        .course-info {
            display: flex;
            gap: 15px;
            margin: 15px 0;
            padding: 12px 0;
            border-top: 1px solid #f0e8e8;
            border-bottom: 1px solid #f0e8e8;
        }
        
        .course-info-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: #666;
        }
        
        .register-btn {
            width: 100%;
            background: linear-gradient(to right, #670019, #8b0022);
            color: white;
            border: none;
            padding: 12px;
            border-radius: 14px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 15px;
            text-align: center;
            display: inline-block;
            text-decoration: none;
        }
        
        .register-btn:hover {
            background: linear-gradient(to right, #8b0022, #a80028);
            transform: translateY(-2px);
            color: white;
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
            .courses-grid {
                grid-template-columns: 1fr;
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
        <a href="student_courses.php" class="active">
            <i class="bi bi-book-fill"></i>
            Courses
        </a>
        <a href="student_registration.php">
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
            <h2>Available Courses</h2>
            <p>Browse and register for courses for the current semester</p>
        </div>
    </div>

    <div class="search-bar">
        <input type="text" id="searchInput" placeholder="Search by course name or code..." onkeyup="filterCourses()">
        <button onclick="filterCourses()"><i class="bi bi-search"></i> Search</button>
    </div>

    <div class="courses-grid" id="coursesGrid">
        <?php while ($course = mysqli_fetch_assoc($courses_result)): ?>
        <div class="course-card" data-name="<?php echo strtolower($course['course_name']); ?>" data-code="<?php echo strtolower($course['course_code']); ?>">
            <span class="course-code"><?php echo htmlspecialchars($course['course_code']); ?></span>
            <h4><?php echo htmlspecialchars($course['course_name']); ?></h4>
            <div class="course-info">
                <div class="course-info-item">
                    <i class="bi bi-clock"></i>
                    <span>3 Credit Hours</span>
                </div>
                <div class="course-info-item">
                    <i class="bi bi-people"></i>
                    <span>45 Students</span>
                </div>
            </div>
            <a href="register_course.php?id=<?php echo $course['course_code']; ?>" class="register-btn" onclick="return confirm('Register for <?php echo addslashes($course['course_name']); ?>?')">
                <i class="bi bi-plus-circle me-1"></i> Register for Course
            </a>
        </div>
        <?php endwhile; ?>
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

    function filterCourses() {
        const searchTerm = document.getElementById('searchInput').value.toLowerCase();
        const courses = document.querySelectorAll('.course-card');
        
        courses.forEach(course => {
            const name = course.getAttribute('data-name');
            const code = course.getAttribute('data-code');
            
            if (name.includes(searchTerm) || code.includes(searchTerm)) {
                course.style.display = 'block';
            } else {
                course.style.display = 'none';
            }
        });
    }
</script>
</body>
</html>