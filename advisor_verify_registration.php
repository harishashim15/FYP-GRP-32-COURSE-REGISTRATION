<?php
require_once 'config.php';

$student_matrix = isset($_GET['matrix']) ? $_GET['matrix'] : '';

if (empty($student_matrix)) {
    header("Location: advisor_registrations.php");
    exit;
}

$sql = "SELECT * FROM students WHERE matrix = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $student_matrix);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$student = mysqli_fetch_assoc($result);

if (!$student) {
    header("Location: advisor_registrations.php");
    exit;
}

$sql = "SELECT c.*, r.status as reg_status, r.id as reg_id 
        FROM registrations r 
        JOIN courses c ON r.course_id = c.id 
        WHERE r.student_matrix = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $student_matrix);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$courses = mysqli_fetch_all($result, MYSQLI_ASSOC);

$total_credits = 0;
foreach ($courses as $course) { $total_credits += $course['credit_hours']; }
$reg_status = !empty($courses) ? $courses[0]['reg_status'] : 'pending';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    $remarks = $_POST['remarks'] ?? '';
    $new_status = ($action == 'approve') ? 'approved' : 'rejected';
    $message = ($action == 'approve') ? "Registration approved successfully!" : "Registration rejected!";
    
    $sql = "UPDATE registrations SET status = ?, advisor_remarks = ? WHERE student_matrix = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sss", $new_status, $remarks, $student_matrix);
    
    if (mysqli_stmt_execute($stmt)) {
        echo "<script>alert('$message'); window.location.href='advisor_registrations.php';</script>";
    } else {
        echo "<script>alert('Error updating registration');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Registration - UTM Academic Advisor</title>
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
        .page-header { display: flex; align-items: center; gap: 20px; margin-bottom: 25px; flex-wrap: wrap; }
        .back-btn { background: none; border: none; font-size: 24px; color: #670019; cursor: pointer; }
        .page-header h2 { color: #670019; font-weight: 700; margin: 0; }
        .status-badge { display: inline-block; padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 500; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-approved { background: #d4edda; color: #155724; }
        .status-rejected { background: #f8d7da; color: #721c24; }
        .student-card { background: white; border-radius: 20px; padding: 20px; display: flex; align-items: center; gap: 20px; margin-bottom: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .student-avatar { width: 60px; height: 60px; border-radius: 50%; background: #670019; color: white; display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: 600; flex-shrink: 0; }
        .student-info h3 { margin: 0 0 5px 0; font-size: 18px; }
        .student-info p { margin: 0; color: #666; font-size: 13px; }
        .courses-card { background: white; border-radius: 20px; padding: 25px; margin-bottom: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .courses-card h4 { color: #670019; font-weight: 600; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 12px; background: #f8f6f4; color: #670019; font-weight: 600; }
        td { padding: 12px; border-bottom: 1px solid #eee; }
        .total-credits { margin-top: 15px; text-align: right; font-weight: 600; color: #670019; }
        .action-card { background: white; border-radius: 20px; padding: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .form-group { margin-bottom: 20px; }
        .form-group label { font-weight: 500; margin-bottom: 8px; display: block; font-size: 14px; }
        .form-group textarea { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 12px; resize: vertical; font-family: 'Poppins', sans-serif; font-size: 14px; }
        .form-group textarea:focus { outline: none; border-color: #670019; box-shadow: 0 0 0 4px rgba(103,0,25,0.08); }
        .btn-approve { background: #d4edda; color: #155724; border: none; padding: 12px 30px; border-radius: 25px; cursor: pointer; font-weight: 500; font-size: 14px; }
        .btn-approve:hover { background: #c3e6cb; }
        .btn-reject { background: #f8d7da; color: #721c24; border: none; padding: 12px 30px; border-radius: 25px; cursor: pointer; font-weight: 500; font-size: 14px; }
        .btn-reject:hover { background: #f5c6cb; }
        .alert-info { background: #d1ecf1; color: #0c5460; padding: 12px; border-radius: 10px; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo"><img src="images/utmlogo.png" alt="UTM Logo"><div class="system-title">COURSE REGISTRATION SYSTEM</div></div>
        <div class="menu">
            <a href="advisor_dashboard.php"><i class="bi bi-house-fill"></i> Dashboard</a>
            <a href="advisor_my_students.php"><i class="bi bi-people-fill"></i> My Students</a>
            <a href="advisor_registrations.php" class="active"><i class="bi bi-file-earmark-text-fill"></i> Registrations</a>
            <a href="advisor_profile.php"><i class="bi bi-person-fill"></i> Profile</a>
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
                <div><h6 class="mb-0">Miss Nurul Asyikin</h6><small class="text-muted">Academic Advisor</small></div>
            </div>
        </div>

        <div class="page-header">
            <button class="back-btn" onclick="history.back()"><i class="bi bi-arrow-left"></i></button>
            <h2>Verify Registration — <?php echo htmlspecialchars($student['name']); ?></h2>
            <span class="status-badge status-<?php echo $reg_status; ?>"><?php echo ucfirst($reg_status); ?></span>
        </div>

        <div class="student-card">
            <div class="student-avatar"><?php echo strtoupper(substr($student['name'], 0, 2)); ?></div>
            <div class="student-info">
                <h3><?php echo htmlspecialchars($student['name']); ?></h3>
                <p><?php echo htmlspecialchars($student['matrix']); ?> · <?php echo htmlspecialchars($student['programme']); ?> · Year <?php echo $student['year']; ?> · Sem 3</p>
            </div>
        </div>

        <div class="courses-card">
            <h4><i class="bi bi-book"></i> Registered courses (<?php echo count($courses); ?>)</h4>
            <?php if (!empty($courses)): ?>
                <table class="table">
                    <thead><tr><th>Course Code</th><th>Course Name</th><th>Credit Hours</th><th>Section</th></tr></thead>
                    <tbody>
                        <?php $section = 1; foreach ($courses as $course): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($course['course_code']); ?></td>
                                <td><?php echo htmlspecialchars($course['course_name']); ?></small>
                                <td><?php echo $course['credit_hours']; ?></small>
                                <td>0<?php echo $section++; ?></small>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="total-credits">Total credit hours: <strong><?php echo $total_credits; ?></strong></div>
            <?php else: ?>
                <p class="text-center text-muted">No courses registered.</p>
            <?php endif; ?>
        </div>

        <?php if ($reg_status == 'pending'): ?>
            <form method="POST" class="action-card">
                <div class="form-group">
                    <label><i class="bi bi-chat"></i> Remarks (optional)</label>
                    <textarea name="remarks" rows="3" placeholder="Add a note for the student..."></textarea>
                </div>
                <div style="display: flex; gap: 15px;">
                    <button type="submit" name="action" value="approve" class="btn-approve"><i class="bi bi-check-circle"></i> Approve</button>
                    <button type="submit" name="action" value="reject" class="btn-reject"><i class="bi bi-x-circle"></i> Reject</button>
                </div>
            </form>
        <?php else: ?>
            <div class="action-card">
                <div class="alert-info">This registration has been <strong><?php echo $reg_status; ?></strong>.</div>
                <?php 
                $sql_remarks = "SELECT advisor_remarks FROM registrations WHERE student_matrix = ? LIMIT 1";
                $stmt_remarks = mysqli_prepare($conn, $sql_remarks);
                mysqli_stmt_bind_param($stmt_remarks, "s", $student_matrix);
                mysqli_stmt_execute($stmt_remarks);
                $result_remarks = mysqli_stmt_get_result($stmt_remarks);
                $remarks_row = mysqli_fetch_assoc($result_remarks);
                if ($remarks_row && !empty($remarks_row['advisor_remarks'])): 
                ?>
                    <div class="alert-info mt-2"><strong>Remarks:</strong> <?php echo htmlspecialchars($remarks_row['advisor_remarks']); ?></div>
                <?php endif; ?>
                <a href="advisor_registrations.php" class="btn btn-secondary mt-3">Back to Registrations</a>
            </div>
        <?php endif; ?>
    </div>

    <script>
        (function() { if (localStorage.getItem('sidebarCollapsed') === 'true') { document.querySelector('.sidebar').classList.add('collapsed'); document.querySelector('.main-content').classList.add('expanded'); } })();
        function toggleSidebar() { const sidebar = document.querySelector('.sidebar'); const main = document.querySelector('.main-content'); sidebar.classList.toggle('collapsed'); main.classList.toggle('expanded'); localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed')); }
    </script>
</body>
</html>