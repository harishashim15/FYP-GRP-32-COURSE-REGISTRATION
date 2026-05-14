<?php
require_once 'config.php';

$advisor_matrix = 'AA0001';

$sql = "SELECT * FROM students WHERE advisor_matrix = ? ORDER BY name";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $advisor_matrix);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$students = mysqli_fetch_all($result, MYSQLI_ASSOC);

$total_students = count($students);
$completed_count = 0;
$pending_registration_count = 0;
$pending_approval_count = 0;

foreach ($students as $student) {
    $sql_status = "SELECT status FROM registrations WHERE student_matrix = ? LIMIT 1";
    $stmt_status = mysqli_prepare($conn, $sql_status);
    mysqli_stmt_bind_param($stmt_status, "s", $student['matrix']);
    mysqli_stmt_execute($stmt_status);
    $result_status = mysqli_stmt_get_result($stmt_status);
    $reg = mysqli_fetch_assoc($result_status);
    
    if ($reg) {
        if ($reg['status'] == 'approved') $completed_count++;
        elseif ($reg['status'] == 'pending') $pending_registration_count++;
        else $pending_approval_count++;
    } else {
        $pending_registration_count++;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Students - UTM Academic Advisor</title>
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
        .page-header { background: #f7f2ee; border-radius: 25px; padding: 35px 40px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; border: 1px solid #eee; }
        .page-header h2 { font-size: 34px; font-weight: 700; color: #670019; }
        .page-header p { color: #666; margin-top: 8px; font-size: 15px; }
        .page-header img { width: 180px; }
        .search-bar { display: flex; gap: 12px; margin-bottom: 25px; }
        .search-bar input { flex: 1; padding: 12px 20px; border: 1.5px solid #e0d6d6; border-radius: 25px; font-size: 14px; outline: none; background: white; }
        .search-bar input:focus { border-color: #670019; box-shadow: 0 0 0 4px rgba(103,0,25,0.08); }
        .search-bar button { padding: 12px 25px; background: linear-gradient(to right, #670019, #8b0022); color: white; border: none; border-radius: 25px; cursor: pointer; font-size: 14px; font-weight: 500; }
        .students-table { background: white; border-radius: 25px; padding: 25px; box-shadow: 0px 4px 15px rgba(0,0,0,0.05); }
        .students-table h3 { color: #670019; font-weight: 700; font-size: 20px; margin-bottom: 20px; }
        .students-table table { width: 100%; border-collapse: collapse; }
        .students-table th { text-align: left; padding: 12px 12px; background: #f8f6f4; color: #670019; font-weight: 600; font-size: 13px; }
        .students-table td { padding: 13px 12px; border-bottom: 1px solid #eee; font-size: 13px; }
        .status-badge { padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 500; display: inline-block; }
        .status-active { background: #d4edda; color: #155724; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-approval { background: #ffe0e0; color: #b00020; }
        .view-btn { background: none; border: 1.5px solid #670019; color: #670019; padding: 5px 15px; border-radius: 20px; cursor: pointer; font-size: 12px; font-weight: 500; }
        .view-btn:hover { background: #670019; color: white; }
        .summary-strip { display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; }
        .strip-item { background: white; border-radius: 14px; padding: 10px 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); font-size: 13px; display: flex; align-items: center; gap: 8px; }
        .strip-item strong { color: #670019; font-size: 16px; font-weight: 700; }
        .strip-dot { width: 10px; height: 10px; border-radius: 50%; display: inline-block; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo"><img src="images/utmlogo.png" alt="UTM Logo"><div class="system-title">COURSE REGISTRATION SYSTEM</div></div>
        <div class="menu">
            <a href="advisor_dashboard.php"><i class="bi bi-house-fill"></i> Dashboard</a>
            <a href="#" class="active"><i class="bi bi-people-fill"></i> My Students</a>
            <a href="advisor_registrations.php"><i class="bi bi-file-earmark-text-fill"></i> Registrations</a>
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
            <div><h2>My Students 👩‍🎓</h2><p>View and manage all students under your guidance</p></div>
            <img src="https://cdn-icons-png.flaticon.com/512/3976/3976626.png" alt="Students">
        </div>

        <div class="summary-strip">
            <div class="strip-item"><span class="strip-dot" style="background:#670019;"></span>Total &nbsp;<strong><?php echo $total_students; ?></strong>&nbsp; students</div>
            <div class="strip-item"><span class="strip-dot" style="background:#2e7d32;"></span>Completed &nbsp;<strong><?php echo $completed_count; ?></strong></div>
            <div class="strip-item"><span class="strip-dot" style="background:#856404;"></span>Pending Registration &nbsp;<strong><?php echo $pending_registration_count; ?></strong></div>
            <div class="strip-item"><span class="strip-dot" style="background:#b00020;"></span>Pending Approval &nbsp;<strong><?php echo $pending_approval_count; ?></strong></div>
        </div>

        <div class="search-bar">
            <input type="text" id="searchInput" placeholder="Search by name or matrix..." oninput="filterTable()">
            <button onclick="filterTable()"><i class="bi bi-search"></i> Search</button>
        </div>

        <div class="students-table">
            <h3><i class="bi bi-people-fill me-2"></i>Student List</h3>
            <div style="overflow-x: auto;">
                <table class="table">
                    <thead><tr><th>Student Name</th><th>Matrix</th><th>Programme</th><th>Year</th><th>Status</th><th>Action</th></tr></thead>
                    <tbody id="studentTableBody">
                        <?php if (!empty($students)): ?>
                            <?php foreach ($students as $student): ?>
                                <?php
                                $sql_status = "SELECT status FROM registrations WHERE student_matrix = ? LIMIT 1";
                                $stmt_status = mysqli_prepare($conn, $sql_status);
                                mysqli_stmt_bind_param($stmt_status, "s", $student['matrix']);
                                mysqli_stmt_execute($stmt_status);
                                $result_status = mysqli_stmt_get_result($stmt_status);
                                $reg = mysqli_fetch_assoc($result_status);
                                
                                if ($reg) {
                                    if ($reg['status'] == 'approved') { $status_class = 'status-active'; $status_text = 'Completed'; }
                                    elseif ($reg['status'] == 'pending') { $status_class = 'status-pending'; $status_text = 'Pending Registration'; }
                                    else { $status_class = 'status-approval'; $status_text = 'Pending Approval'; }
                                } else { $status_class = 'status-pending'; $status_text = 'Pending Registration'; }
                                ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($student['name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($student['matrix']); ?></td>
                                    <td><?php echo htmlspecialchars($student['programme']); ?></td>
                                    <td>Year <?php echo $student['year']; ?></td>
                                    <td><span class="status-badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span></td>
                                    <td><button class="view-btn" onclick="location.href='advisor_student_details.php?matrix=<?php echo $student['matrix']; ?>'"><i class="bi bi-eye me-1"></i>View Details</button></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center">No students found</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function filterTable() {
            const query = document.getElementById('searchInput').value.toLowerCase();
            const rows = document.querySelectorAll('#studentTableBody tr');
            rows.forEach(row => { const text = row.textContent.toLowerCase(); row.style.display = text.includes(query) ? '' : 'none'; });
        }
        (function() { if (localStorage.getItem('sidebarCollapsed') === 'true') { document.querySelector('.sidebar').classList.add('collapsed'); document.querySelector('.main-content').classList.add('expanded'); } })();
        function toggleSidebar() { const sidebar = document.querySelector('.sidebar'); const main = document.querySelector('.main-content'); sidebar.classList.toggle('collapsed'); main.classList.toggle('expanded'); localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed')); }
    </script>
</body>
</html>