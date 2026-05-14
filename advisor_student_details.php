<?php
require_once 'config.php';

$student_matrix = isset($_GET['matrix']) ? $_GET['matrix'] : '';

if (empty($student_matrix)) {
    header("Location: advisor_my_students.php");
    exit;
}

$sql = "SELECT * FROM students WHERE matrix = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $student_matrix);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$student = mysqli_fetch_assoc($result);

if (!$student) {
    header("Location: advisor_my_students.php");
    exit;
}

$sql_status = "SELECT status FROM registrations WHERE student_matrix = ? LIMIT 1";
$stmt_status = mysqli_prepare($conn, $sql_status);
mysqli_stmt_bind_param($stmt_status, "s", $student_matrix);
mysqli_stmt_execute($stmt_status);
$result_status = mysqli_stmt_get_result($stmt_status);
$reg = mysqli_fetch_assoc($result_status);
$reg_status = $reg ? $reg['status'] : 'pending';

$sql_courses = "SELECT c.*, r.status as reg_status FROM registrations r JOIN courses c ON r.course_id = c.id WHERE r.student_matrix = ?";
$stmt_courses = mysqli_prepare($conn, $sql_courses);
mysqli_stmt_bind_param($stmt_courses, "s", $student_matrix);
mysqli_stmt_execute($stmt_courses);
$result_courses = mysqli_stmt_get_result($stmt_courses);
$courses = mysqli_fetch_all($result_courses, MYSQLI_ASSOC);

$semester_history = [
    ['session' => '2024/2025-1', 'programme' => '1 / DSPD', 'noSem' => 1, 'regDate' => '27 Jul 2024', 'activeCode' => 'A-Active', 'cpa' => '3.67'],
    ['session' => '2024/2025-2', 'programme' => '1 / DSPD', 'noSem' => 2, 'regDate' => '26 Dec 2024', 'activeCode' => 'A-Active', 'cpa' => '3.70'],
    ['session' => '2024/2025-3', 'programme' => '1 / DSPD', 'noSem' => 3, 'regDate' => '16 May 2025', 'activeCode' => 'A-Active', 'cpa' => '3.76'],
    ['session' => '2025/2026-1', 'programme' => '2 / DSPD', 'noSem' => 4, 'regDate' => '10 Jul 2025', 'activeCode' => 'A-Active', 'cpa' => '3.77'],
    ['session' => '2025/2026-2', 'programme' => '2 / DSPD', 'noSem' => 5, 'regDate' => '08 Dec 2025', 'activeCode' => 'A-Active', 'cpa' => '3.83'],
    ['session' => '2025/2026-3', 'programme' => '2 / DSPD', 'noSem' => 6, 'regDate' => '06 May 2026', 'activeCode' => 'A-Active', 'cpa' => ''],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Details - UTM Academic Advisor</title>
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
        .profile-card { background: white; border-radius: 20px; padding: 25px; margin-bottom: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .profile-header { display: flex; align-items: center; gap: 25px; flex-wrap: wrap; }
        .student-avatar { width: 80px; height: 80px; border-radius: 50%; background: #670019; color: white; display: flex; align-items: center; justify-content: center; font-size: 32px; font-weight: 600; }
        .student-basic h3 { margin: 0 0 5px 0; font-size: 22px; }
        .student-basic p { margin: 0; color: #666; font-size: 14px; }
        .student-details-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee; }
        .detail-item { display: flex; align-items: center; gap: 10px; }
        .detail-icon { width: 35px; height: 35px; background: #f8f6f4; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #670019; font-size: 16px; }
        .detail-text small { display: block; color: #999; font-size: 11px; }
        .detail-text strong { font-size: 14px; }
        .courses-card { background: white; border-radius: 20px; padding: 25px; margin-bottom: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .courses-card h4 { color: #670019; font-weight: 600; margin-bottom: 20px; }
        .sem-info-strip { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; margin-bottom: 25px; }
        .sem-info-item label { display: block; font-size: 12px; color: #888; margin-bottom: 6px; font-weight: 500; }
        .sem-info-item .sem-info-val { background: #f0f4f8; border: 1px solid #dce3ea; border-radius: 10px; padding: 10px 14px; font-size: 14px; color: #333; font-weight: 500; }
        .table-controls { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; flex-wrap: wrap; gap: 10px; }
        .table-controls-left { display: flex; align-items: center; gap: 8px; font-size: 13px; color: #555; }
        .table-controls-left select { padding: 4px 10px; border: 1px solid #ddd; border-radius: 8px; font-size: 13px; }
        .table-controls-right input { padding: 5px 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 13px; outline: none; }
        .table-controls-right input:focus { border-color: #670019; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: center; padding: 11px 12px; background: #f0f4f8; color: #555; font-weight: 600; font-size: 13px; border: 1px solid #dce3ea; }
        td { padding: 11px 12px; border: 1px solid #eee; font-size: 13px; text-align: center; color: #333; }
        .table-footer { display: flex; justify-content: space-between; align-items: center; margin-top: 15px; flex-wrap: wrap; gap: 10px; }
        .table-footer small { font-size: 13px; color: #666; }
        .pagination-btns { display: flex; gap: 6px; align-items: center; }
        .page-btn { padding: 5px 14px; border: 1px solid #ddd; border-radius: 8px; background: white; font-size: 13px; cursor: pointer; }
        .page-btn.active { background: #670019; color: white; border-color: #670019; }
        .action-buttons { background: white; border-radius: 20px; padding: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); display: flex; gap: 15px; flex-wrap: wrap; }
        .btn-back { background: #6c757d; color: white; border: none; padding: 10px 25px; border-radius: 25px; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn-review { background: #670019; color: white; border: none; padding: 10px 25px; border-radius: 25px; cursor: pointer; text-decoration: none; display: inline-block; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="logo"><img src="images/utmlogo.png" alt="UTM Logo"><div class="system-title">COURSE REGISTRATION SYSTEM</div></div>
        <div class="menu">
            <a href="advisor_dashboard.php"><i class="bi bi-house-fill"></i> Dashboard</a>
            <a href="advisor_my_students.php" class="active"><i class="bi bi-people-fill"></i> My Students</a>
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
            <button class="back-btn" onclick="history.back()"><i class="bi bi-arrow-left"></i></button>
            <h2>Student Details</h2>
            <span class="status-badge status-<?php echo $reg_status; ?>"><?php echo ucfirst($reg_status); ?></span>
        </div>

        <div class="profile-card">
            <div class="profile-header">
                <div class="student-avatar"><?php echo strtoupper(substr($student['name'], 0, 2)); ?></div>
                <div class="student-basic">
                    <h3><?php echo htmlspecialchars($student['name']); ?></h3>
                    <p><i class="bi bi-envelope"></i> <?php echo htmlspecialchars($student['email']); ?></p>
                </div>
            </div>
            <div class="student-details-grid">
                <div class="detail-item"><div class="detail-icon"><i class="bi bi-card-text"></i></div><div class="detail-text"><small>Matrix</small><strong><?php echo htmlspecialchars($student['matrix']); ?></strong></div></div>
                <div class="detail-item"><div class="detail-icon"><i class="bi bi-mortarboard"></i></div><div class="detail-text"><small>Programme</small><strong><?php echo htmlspecialchars($student['programme']); ?></strong></div></div>
                <div class="detail-item"><div class="detail-icon"><i class="bi bi-calendar"></i></div><div class="detail-text"><small>Year</small><strong>Year <?php echo $student['year']; ?></strong></div></div>
                <div class="detail-item"><div class="detail-icon"><i class="bi bi-person-badge"></i></div><div class="detail-text"><small>Advisor Matrix</small><strong><?php echo htmlspecialchars($student['advisor_matrix']); ?></strong></div></div>
            </div>
        </div>

        <div class="courses-card">
            <h4><i class="bi bi-book"></i> Registered Courses (<?php echo count($courses); ?>)</h4>
            <?php if (!empty($courses)): ?>
                <table class="table">
                    <thead><tr><th>Course Code</th><th>Course Name</th><th>Credit Hours</th><th>Status</th></tr></thead>
                    <tbody>
                        <?php foreach ($courses as $course): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($course['course_code']); ?></td>
                                <td><?php echo htmlspecialchars($course['course_name']); ?></small>
                                <td><?php echo $course['credit_hours']; ?></small>
                                <td><span class="status-badge status-<?php echo $course['reg_status']; ?>"><?php echo ucfirst($course['reg_status']); ?></span></small>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-center text-muted">No courses registered for current semester.</p>
            <?php endif; ?>
        </div>

        <div class="courses-card">
            <h4><i class="bi bi-journal-text"></i> Semester History</h4>
            <div class="sem-info-strip">
                <div class="sem-info-item"><label>Current Semester</label><div class="sem-info-val">202520262</div></div>
                <div class="sem-info-item"><label>Year / Programme</label><div class="sem-info-val"><?php echo $student['year']; ?> / DSPD</div></div>
                <div class="sem-info-item"><label>Active Code</label><div class="sem-info-val">A - Active</div></div>
            </div>
            <div style="font-size:14px; font-weight:600; margin-bottom:10px;">Semester Histories</div>
            <div class="table-controls">
                <div class="table-controls-left">Show <select id="entriesSelect" onchange="renderTable()"><option value="5">5</option><option value="10" selected>10</option><option value="25">25</option></select> entries</div>
                <div class="table-controls-right">Search: <input type="text" id="searchInput" oninput="renderTable()"></div>
            </div>
            <div style="overflow-x:auto;"><table id="semTable"><thead><tr><th>Session Semester</th><th>Programme</th><th>No. Semester</th><th>Course Reg. Date</th><th>Active Code</th><th>CPA</th></tr></thead><tbody id="semTableBody"></tbody></table></div>
            <div class="table-footer"><small id="tableInfo"></small><div class="pagination-btns" id="pagination"></div></div>
        </div>

        <div class="action-buttons">
            <a href="advisor_my_students.php" class="btn-back"><i class="bi bi-arrow-left"></i> Back to Students</a>
            <a href="advisor_verify_registration.php?matrix=<?php echo $student['matrix']; ?>" class="btn-review"><i class="bi bi-pencil"></i> Review Registration</a>
        </div>
    </div>

    <script>
        const semData = <?php echo json_encode($semester_history); ?>;
        let currentPage = 1;
        function renderTable() {
            const query = document.getElementById('searchInput').value.toLowerCase();
            const perPage = parseInt(document.getElementById('entriesSelect').value);
            const filtered = semData.filter(r => Object.values(r).join(' ').toLowerCase().includes(query));
            const totalPages = Math.max(1, Math.ceil(filtered.length / perPage));
            if (currentPage > totalPages) currentPage = totalPages;
            const start = (currentPage - 1) * perPage;
            const pageData = filtered.slice(start, start + perPage);
            document.getElementById('semTableBody').innerHTML = pageData.map(r => `<tr><td>${r.session}</td><td>${r.programme}</td><td>${r.noSem}</td><td>${r.regDate}</td><td>${r.activeCode}</td><td>${r.cpa}</td></tr>`).join('');
            const from = filtered.length === 0 ? 0 : start + 1;
            const to = Math.min(start + perPage, filtered.length);
            document.getElementById('tableInfo').textContent = `Showing ${from} to ${to} of ${filtered.length} entries`;
            const pg = document.getElementById('pagination');
            pg.innerHTML = '';
            const prev = document.createElement('button'); prev.className = 'page-btn'; prev.textContent = 'Previous'; prev.disabled = currentPage === 1; prev.onclick = () => { currentPage--; renderTable(); }; pg.appendChild(prev);
            for (let i = 1; i <= totalPages; i++) { const btn = document.createElement('button'); btn.className = 'page-btn' + (i === currentPage ? ' active' : ''); btn.textContent = i; btn.onclick = (function(p) { return () => { currentPage = p; renderTable(); }; })(i); pg.appendChild(btn); }
            const next = document.createElement('button'); next.className = 'page-btn'; next.textContent = 'Next'; next.disabled = currentPage === totalPages; next.onclick = () => { currentPage++; renderTable(); }; pg.appendChild(next);
        }
        renderTable();
        (function() { if (localStorage.getItem('sidebarCollapsed') === 'true') { document.querySelector('.sidebar').classList.add('collapsed'); document.querySelector('.main-content').classList.add('expanded'); } })();
        function toggleSidebar() { const sidebar = document.querySelector('.sidebar'); const main = document.querySelector('.main-content'); sidebar.classList.toggle('collapsed'); main.classList.toggle('expanded'); localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed')); }
    </script>
</body>
</html>