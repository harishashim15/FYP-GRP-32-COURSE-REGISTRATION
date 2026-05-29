<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

include("db_connect.php");

// Fetch student name from database
$user_id = $_SESSION['user_id'];
$user_query = "SELECT name FROM users WHERE id = '$user_id'";
$user_result = mysqli_query($conn, $user_query);
$user = mysqli_fetch_assoc($user_result);
$student_name = $user ? $user['name'] : "Student";

// Handle remove from cart
if (isset($_GET['remove'])) {
    $course_code = mysqli_real_escape_string($conn, $_GET['remove']);
    $delete = "DELETE FROM registration_cart WHERE user_id = '$user_id' AND course_code = '$course_code'";
    mysqli_query($conn, $delete);
    header("Location: student_register.php");
    exit();
}

// Handle section update (AJAX)
if (isset($_POST['update_section'])) {
    $course_code = mysqli_real_escape_string($conn, $_POST['course_code']);
    $section = mysqli_real_escape_string($conn, $_POST['section']);
    $update = "UPDATE registration_cart SET section = '$section' WHERE user_id = '$user_id' AND course_code = '$course_code'";
    mysqli_query($conn, $update);
    echo json_encode(['success' => true]);
    exit();
}

// Handle submit registration
if (isset($_POST['submit_registration'])) {
    $session = "2025/2026 - Semester 2";
    $submitted_date = date('Y-m-d H:i:s');
    
    // Get cart items
    $cart_query = "SELECT * FROM registration_cart WHERE user_id = '$user_id'";
    $cart_result = mysqli_query($conn, $cart_query);
    
    $success = true;
    while ($cart_item = mysqli_fetch_assoc($cart_result)) {
        $course_code = $cart_item['course_code'];
        $section = $cart_item['section'] ?: 'TBD';
        
        $insert = "INSERT INTO registrations (user_id, course_id, status, section, submitted_date, session) 
                   VALUES ('$user_id', '$course_code', 'pending', '$section', '$submitted_date', '$session')";
        if (!mysqli_query($conn, $insert)) {
            $success = false;
        }
    }
    
    if ($success) {
        // Clear cart
        $clear_cart = "DELETE FROM registration_cart WHERE user_id = '$user_id'";
        mysqli_query($conn, $clear_cart);
        $message = "Registration submitted successfully! Your advisor will review it.";
        $message_type = "success";
    } else {
        $message = "Error submitting registration. Please try again.";
        $message_type = "error";
    }
}

// Get cart items with course details
$cart_query = "SELECT c.*, rc.section 
               FROM registration_cart rc 
               JOIN courses c ON rc.course_code = c.course_code 
               WHERE rc.user_id = '$user_id'";
$cart_result = mysqli_query($conn, $cart_query);
$cart_items = [];
$total_credits = 0;
while ($row = mysqli_fetch_assoc($cart_result)) {
    $cart_items[] = $row;
    $total_credits += 3; // Each course is 3 credits
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - UTM Student</title>
    
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
        
        .page-header h1 {
            font-size: 34px;
            font-weight: 700;
            color: #670019;
        }
        
        .page-header p {
            color: #666;
            margin-top: 8px;
            font-size: 15px;
        }
        
        .info-card {
            background: #fff7ef;
            border-left: 4px solid #f4a000;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 25px;
        }
        
        .info-card p {
            margin: 0;
            color: #856404;
            font-size: 14px;
        }
        
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
        
        .section-select {
            padding: 8px 12px;
            border: 1.5px solid #e0d6d6;
            border-radius: 10px;
            font-size: 13px;
            font-family: 'Poppins', sans-serif;
            background: white;
            cursor: pointer;
        }
        
        .section-select:focus {
            outline: none;
            border-color: #670019;
        }
        
        .remove-btn {
            background: none;
            border: none;
            color: #dc3545;
            font-size: 20px;
            cursor: pointer;
            transition: 0.3s;
        }
        
        .remove-btn:hover {
            color: #c82333;
            transform: scale(1.1);
        }
        
        .total-row {
            background: #f8f6f4;
            font-weight: 600;
        }
        
        .total-row td {
            font-weight: 600;
            color: #670019;
        }
        
        .submit-btn {
            background: linear-gradient(to right, #2e7d32, #1b5e20);
            color: white;
            border: none;
            padding: 15px 40px;
            border-radius: 14px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 20px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        
        .submit-btn:hover {
            background: linear-gradient(to right, #1b5e20, #0d3b0f);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(46, 125, 50, 0.3);
        }
        
        .empty-cart {
            text-align: center;
            padding: 60px 20px;
        }
        
        .empty-cart i {
            font-size: 64px;
            color: #ddd;
            margin-bottom: 20px;
        }
        
        .empty-cart h4 {
            color: #666;
            margin-bottom: 15px;
        }
        
        .empty-cart a {
            color: #670019;
            font-weight: 500;
            text-decoration: none;
        }
        
        .alert-custom {
            border-radius: 14px;
            padding: 14px 20px;
            font-size: 14px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert-success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        
        .alert-error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
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
        <a href="student_register.php" class="active">
            <i class="bi bi-journal-text"></i>
            Register
        </a>
        <a href="student_registration_history.php">
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
            <h1>Register</h1>
            <p>Review your added courses and select sections before submitting</p>
        </div>
    </div>

    <?php if (isset($message)): ?>
    <div class="alert-custom alert-<?php echo $message_type; ?>">
        <i class="bi bi-<?php echo $message_type == 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill'; ?> fs-5 me-2"></i>
        <span><?php echo $message; ?></span>
    </div>
    <?php endif; ?>

    <div class="info-card">
        <p><i class="bi bi-info-circle-fill me-2"></i> Please select a section for each course before submitting. Your registration will be sent to your academic advisor for approval.</p>
    </div>

    <div class="registration-table">
        <h3>Your Added Courses</h3>
        
        <?php if (count($cart_items) > 0): ?>
        <div style="overflow-x: auto;">
            <form method="POST" id="registrationForm">
                <table>
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Course Code</th>
                            <th>Course Name</th>
                            <th>Credit Hours</th>
                            <th>Section *</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $counter = 1; foreach ($cart_items as $item): ?>
                        <tr data-course="<?php echo htmlspecialchars($item['course_code']); ?>">
                            <td><?php echo $counter++; ?></td>
                            <td><strong><?php echo htmlspecialchars($item['course_code']); ?></strong></td>
                            <td><?php echo htmlspecialchars($item['course_name']); ?></td>
                            <td>3</td>
                            <td>
                                <select class="section-select" data-course="<?php echo htmlspecialchars($item['course_code']); ?>">
                                    <option value="">Select Section</option>
                                    <option value="1" <?php echo $item['section'] == '1' ? 'selected' : ''; ?>>Section 1</option>
                                    <option value="2" <?php echo $item['section'] == '2' ? 'selected' : ''; ?>>Section 2</option>
                                    <option value="3" <?php echo $item['section'] == '3' ? 'selected' : ''; ?>>Section 3</option>
                                    <option value="4" <?php echo $item['section'] == '4' ? 'selected' : ''; ?>>Section 4</option>
                                </select>
                            </td>
                            <td>
                                <button type="button" class="remove-btn" onclick="removeCourse('<?php echo htmlspecialchars($item['course_code']); ?>')">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <tr class="total-row">
                            <td colspan="3"><strong>Total Credit Hours:</strong></td>
                            <td colspan="3"><strong><?php echo $total_credits; ?></strong></td>
                        </tr>
                    </tbody>
                </table>
            </form>
        </div>
        
        <div class="text-center mt-4">
            <button type="button" class="submit-btn" onclick="submitRegistration()">
                <i class="bi bi-send-check"></i> Submit Registration
            </button>
        </div>
        <?php else: ?>
        <div class="empty-cart">
            <i class="bi bi-cart"></i>
            <h4>No courses added yet</h4>
            <p>Go to the Courses page to add courses to your registration cart.</p>
            <a href="student_courses.php"><i class="bi bi-arrow-right-circle me-1"></i> Browse Courses</a>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
    // Save section selection
    document.querySelectorAll('.section-select').forEach(select => {
        select.addEventListener('change', function() {
            const courseCode = this.dataset.course;
            const section = this.value;
            
            fetch('student_register.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'update_section=1&course_code=' + encodeURIComponent(courseCode) + '&section=' + encodeURIComponent(section)
            });
        });
    });
    
    function removeCourse(courseCode) {
        if (confirm('Are you sure you want to remove this course from your registration?')) {
            window.location.href = 'student_register.php?remove=' + encodeURIComponent(courseCode);
        }
    }
    
    function submitRegistration() {
        // Check if all sections are selected
        const selects = document.querySelectorAll('.section-select');
        let allSelected = true;
        let missingCourses = [];
        
        selects.forEach(select => {
            if (!select.value) {
                allSelected = false;
                const row = select.closest('tr');
                const courseName = row.querySelector('td:nth-child(3)').innerText;
                missingCourses.push(courseName);
            }
        });
        
        if (!allSelected) {
            alert('Please select a section for all courses before submitting.\n\nMissing sections for:\n- ' + missingCourses.join('\n- '));
            return;
        }
        
        if (confirm('Are you sure you want to submit this registration for approval? You cannot modify it after submission.')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'student_register.php';
            
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'submit_registration';
            input.value = '1';
            
            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        }
    }
    
    (function() {
        if (localStorage.getItem('sidebarCollapsed') === 'true') {
            document.querySelector('.sidebar').classList.add('collapsed');
            document.querySelector('.main-content').class.classList.add('expanded');
        }
    })();
    
    function toggleSidebar() {
        const sidebar = document.querySelector('.sidebar');
        const main = document.querySelector('.main-content');
        sidebar.classList.toggle('collapsed');
        main.classList.toggle('expanded');
        localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
    }
    
    // Auto-hide alert after 4 seconds
    setTimeout(function() {
        var alert = document.querySelector('.alert-custom');
        if (alert) {
            alert.style.opacity = '0';
            setTimeout(function() {
                alert.style.display = 'none';
            }, 500);
        }
    }, 4000);
</script>
</body>
</html>