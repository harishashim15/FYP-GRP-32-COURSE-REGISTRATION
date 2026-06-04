<?php
session_start();
require_once '../db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.html");
    exit();
}

// Fetch admin name from database
$admin_name = 'Admin';
$stmt = $conn->prepare("SELECT user_name FROM users WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $admin_name = $row['user_name'];
}
$stmt->close();

// Fetch counts
$students_count = 0;
$advisors_count = 0;
$subjects_count = 0;

$result = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'student'");
$students_count = $result->fetch_row()[0];
$result = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'advisor'");
$advisors_count = $result->fetch_row()[0];
$result = $conn->query("SELECT COUNT(*) FROM subjects");
$subjects_count = $result->fetch_row()[0];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - UTM Course Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="../images/logoWebsite.png"/>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background: #f8f6f4; overflow-x: hidden; }
        
        /* Sidebar (same as student dashboard) */
        .sidebar {
            width: 280px;
            height: 100vh;
            background: linear-gradient(to bottom, #670019, #8b0022);
            position: fixed;
            padding: 30px 20px;
            color: white;
            transition: transform 0.3s ease;
            z-index: 1000;
            overflow-y: auto;
        }
        .sidebar.collapsed { transform: translateX(-280px); }
        .logo { text-align: center; margin-bottom: 50px; }
        .logo img { width: 130px; }
        .system-title { color: white; font-size: 16px; font-weight: 600; margin-top: 12px; }
        .menu a {
            display: flex;
            align-items: center;
            gap: 15px;
            text-decoration: none;
            color: white;
            padding: 14px 20px;
            border-radius: 14px;
            margin-bottom: 8px;
            transition: 0.3s;
            font-size: 15px;
        }
        .menu a:hover, .menu .active { background: linear-gradient(to right, #f4a000, #e08700); }
        .menu i { font-size: 20px; width: 24px; }
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
            padding: 14px 20px;
            border-radius: 14px;
            transition: 0.3s;
            font-size: 15px;
            background: rgba(255,255,255,0.1);
        }
        .logout a:hover { background: linear-gradient(to right, #f4a000, #e08700); }
        
        /* Main Content */
        .main-content { margin-left: 280px; padding: 30px; transition: margin-left 0.3s ease; }
        .main-content.expanded { margin-left: 0; }
        
        /* Topbar */
        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            background: white;
            padding: 15px 25px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .toggle-btn { background: none; border: none; font-size: 22px; color: #333; cursor: pointer; }
        .profile-box { display: flex; align-items: center; gap: 15px; cursor: pointer; }
        .profile-box img { width: 50px; height: 50px; border-radius: 50%; object-fit: cover; }
        
        /* Hero */
        .hero {
            background: #f7f2ee;
            border-radius: 25px;
            padding: 35px 40px;
            margin-bottom: 35px;
            border: 1px solid #eee;
        }
        .hero h1 { font-size: 34px; font-weight: 700; color: #670019; }
        .hero p { color: #666; margin-top: 8px; font-size: 15px; }
        
        /* Dashboard Cards */
        .dashboard-card {
            border: none;
            border-radius: 25px;
            padding: 25px;
            box-shadow: 0px 4px 15px rgba(0,0,0,0.05);
            transition: 0.3s;
            height: 100%;
            background: white;
        }
        .dashboard-card:hover { transform: translateY(-5px); }
        .card-icon {
            width: 60px;
            height: 60px;
            border-radius: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 28px;
            margin-bottom: 15px;
        }
        .yellow { background: #fff2cc; color: #d48a00; }
        .red    { background: #ffe0e0; color: #b00020; }
        .green  { background: #e4f7df; color: #2e7d32; }
        .dashboard-card h2 { font-size: 36px; font-weight: 700; color: #670019; margin-bottom: 5px; }
        .dashboard-card h5 { font-size: 16px; font-weight: 600; margin-top: 5px; }
        .dashboard-card p { color: #666; margin-top: 5px; font-size: 13px; }
        
        /* Quick Actions */
        .quick-actions {
            background: white;
            border-radius: 25px;
            padding: 30px;
            margin-top: 35px;
        }
        .quick-actions h3 { color: #670019; font-weight: 700; margin-bottom: 25px; font-size: 20px; }
        .action-btn {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            border: 1px solid #eee;
            border-radius: 18px;
            margin-bottom: 15px;
            text-decoration: none;
            color: black;
            transition: 0.3s;
        }
        .action-btn:hover { background: #fff7ef; transform: translateX(5px); }
        .action-left { display: flex; align-items: center; gap: 15px; }
        .action-icon {
            width: 55px;
            height: 55px;
            border-radius: 15px;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #670019;
            color: white;
            font-size: 24px;
        }
        .action-left h5 { margin-bottom: 2px; font-size: 15px; font-weight: 600; }
        .action-left small { font-size: 11px; color: #888; }
        .bi-chevron-right { color: #670019; font-size: 18px; }
        
        @media (max-width: 992px) {
            .sidebar { transform: translateX(-280px); }
            .main-content { margin-left: 0; }
            .hero { text-align: center; }
        }
    </style>
</head>
<body>
<div class="sidebar">
    <div class="logo"><img src="../images/utmlogo.png" alt="UTM Logo"><div class="system-title">COURSE REGISTRATION SYSTEM</div></div>
    <div class="menu">
        <a href="admin_dashboard.php" class="active"><i class="bi bi-house-fill"></i> Dashboard</a>
        <a href="manage_students.php"><i class="bi bi-people-fill"></i> Manage Students</a>
        <a href="manage_advisors.php"><i class="bi bi-person-badge-fill"></i> Manage Advisors</a>
        <a href="manage_subjects.php"><i class="bi bi-book-fill"></i> Manage Subjects</a>
        <a href="manage_registration_period.php"><i class="bi bi-calendar-event"></i> Registration Period</a>
        <a href="../forgot_password.html"><i class="bi bi-key-fill"></i> Forgot Password</a>
    </div>
    <div class="logout">
        <a href="#" onclick="event.preventDefault(); localStorage.clear(); window.location.href='../index.html';"><i class="bi bi-box-arrow-right"></i> Logout</a>
    </div>
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
    <div class="hero">
        <h1>Welcome <?php echo htmlspecialchars(explode(' ', $admin_name)[0]); ?> 👋</h1>
        <p>Manage students, advisors, and course subjects.</p>
    </div>
    <div class="row g-4">
        <div class="col-md-4">
            <div class="dashboard-card">
                <div class="card-icon yellow"><i class="bi bi-people-fill"></i></div>
                <h2 id="studentsCount">0</h2>
                <h5>Total Students</h5>
                <p>Registered students</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="dashboard-card">
                <div class="card-icon red"><i class="bi bi-person-badge-fill"></i></div>
                <h2 id="advisorsCount">0</h2>
                <h5>Total Advisors</h5>
                <p>Academic advisors</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="dashboard-card">
                <div class="card-icon green"><i class="bi bi-book-fill"></i></div>
                <h2 id="subjectsCount">0</h2>
                <h5>Subjects</h5>
                <p>Available courses</p>
            </div>
        </div>
    </div>

    <!-- Quick Actions (same as student dashboard) -->
    <div class="quick-actions">
        <h3><i class="bi bi-lightning-charge-fill me-2"></i>Quick Actions</h3>
        <a href="manage_students.php" class="action-btn">
            <div class="action-left">
                <div class="action-icon"><i class="bi bi-people-fill"></i></div>
                <div>
                    <h5 class="mb-0">Manage Students</h5>
                    <small>Add, edit, or remove students</small>
                </div>
            </div>
            <i class="bi bi-chevron-right"></i>
        </a>
        <a href="manage_advisors.php" class="action-btn">
            <div class="action-left">
                <div class="action-icon"><i class="bi bi-person-badge-fill"></i></div>
                <div>
                    <h5 class="mb-0">Manage Advisors</h5>
                    <small>View and manage academic advisors</small>
                </div>
            </div>
            <i class="bi bi-chevron-right"></i>
        </a>
        <a href="manage_subjects.php" class="action-btn">
            <div class="action-left">
                <div class="action-icon"><i class="bi bi-book-fill"></i></div>
                <div>
                    <h5 class="mb-0">Manage Subjects</h5>
                    <small>Add, edit, or remove course subjects</small>
                </div>
            </div>
            <i class="bi bi-chevron-right"></i>
        </a>
    </div>
</div>
<script src="../js/common.js"></script>
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

    // Count-up animation function (same as student dashboard)
    function animateNumber(element, target, duration = 1000) {
        let start = 0;
        const increment = target / (duration / 16);
        let current = start;
        const updateNumber = () => {
            current += increment;
            if (current < target) {
                element.innerText = Math.floor(current);
                requestAnimationFrame(updateNumber);
            } else {
                element.innerText = target;
            }
        };
        updateNumber();
    }

    // Animate the stat cards
    const studentsTarget = <?php echo $students_count; ?>;
    const advisorsTarget = <?php echo $advisors_count; ?>;
    const subjectsTarget = <?php echo $subjects_count; ?>;
    
    animateNumber(document.getElementById('studentsCount'), studentsTarget);
    animateNumber(document.getElementById('advisorsCount'), advisorsTarget);
    animateNumber(document.getElementById('subjectsCount'), subjectsTarget);

    // Default password check (same as student dashboard)
    async function checkDefaultPassword() {
        try {
            const res = await apiGet('auth/check_default_password.php');
            if (res.is_default) {
                showDefaultPasswordModal();
            }
        } catch(e) {
            console.error('Failed to check default password:', e);
        }
    }

    function showDefaultPasswordModal() {
        let modal = document.getElementById('defaultPasswordModal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'defaultPasswordModal';
            modal.style.position = 'fixed';
            modal.style.top = '0';
            modal.style.left = '0';
            modal.style.width = '100%';
            modal.style.height = '100%';
            modal.style.backgroundColor = 'rgba(0,0,0,0.5)';
            modal.style.display = 'flex';
            modal.style.alignItems = 'center';
            modal.style.justifyContent = 'center';
            modal.style.zIndex = '2000';
            modal.innerHTML = `
                <div style="background: white; border-radius: 25px; padding: 30px; max-width: 400px; text-align: center; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
                    <i class="bi bi-shield-exclamation" style="font-size: 48px; color: #f4a000;"></i>
                    <h3 style="margin-top: 15px; color: #670019;">Default Password Detected</h3>
                    <p style="margin-top: 10px; color: #666;">For security reasons, please change your default password immediately.</p>
                    <div style="margin-top: 20px; display: flex; gap: 15px; justify-content: center;">
                        <button onclick="document.getElementById('defaultPasswordModal').remove();" style="background: #6c757d; color: white; border: none; padding: 10px 25px; border-radius: 25px; cursor: pointer;">Remind Me Later</button>
                        <button onclick="window.location.href='change_password.php';" style="background: #670019; color: white; border: none; padding: 10px 25px; border-radius: 25px; cursor: pointer;">Change Password Now</button>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
        }
    }

    checkDefaultPassword();
</script>
</body>
</html>