<?php
require_once 'db_connect.php';

session_start();

// Get student matrix from URL
$student_matrix = isset($_GET['matrix']) ? $_GET['matrix'] : '';

if (empty($student_matrix)) {
    header("Location: advisor_my_students.php");
    exit;
}

// Get student information (JOIN with users table)
$sql = "SELECT s.*, u.matrix_number, u.user_name, u.utm_email 
        FROM students s
        JOIN users u ON s.user_id = u.user_id
        WHERE u.matrix_number = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $student_matrix);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$student = mysqli_fetch_assoc($result);

if (!$student) {
    header("Location: advisor_my_students.php");
    exit;
}

// Get student's registration status from course_registrations
$sql_status = "SELECT status FROM course_registrations WHERE student_id = ? LIMIT 1";
$stmt_status = mysqli_prepare($conn, $sql_status);
mysqli_stmt_bind_param($stmt_status, "i", $student['user_id']);
mysqli_stmt_execute($stmt_status);
$result_status = mysqli_stmt_get_result($stmt_status);
$reg = mysqli_fetch_assoc($result_status);
$reg_status = $reg ? $reg['status'] : 'pending';

// Get student's registered courses from registration_courses table
$sql_courses = "SELECT sub.subject_code, sub.subject_name, sub.credits, rc.section, cr.status as reg_status
                FROM course_registrations cr 
                JOIN registration_courses rc ON cr.id = rc.registration_id
                JOIN subjects sub ON rc.subject_code = sub.subject_code
                WHERE cr.student_id = ?";
$stmt_courses = mysqli_prepare($conn, $sql_courses);
mysqli_stmt_bind_param($stmt_courses, "i", $student['user_id']);
mysqli_stmt_execute($stmt_courses);
$result_courses = mysqli_stmt_get_result($stmt_courses);
$courses = mysqli_fetch_all($result_courses, MYSQLI_ASSOC);

// Get semester history from student_semesters table
$sql_semester = "SELECT 
                    session_semester as session, 
                    programme, 
                    no_semester as noSem, 
                    DATE_FORMAT(reg_date, '%d %b %Y') as regDate, 
                    active_code as activeCode, 
                    cpa 
                 FROM student_semesters 
                 WHERE student_id = ? 
                 ORDER BY no_semester ASC";
$stmt_semester = mysqli_prepare($conn, $sql_semester);
mysqli_stmt_bind_param($stmt_semester, "i", $student['user_id']);
mysqli_stmt_execute($stmt_semester);
$result_semester = mysqli_stmt_get_result($stmt_semester);
$semester_history = mysqli_fetch_all($result_semester, MYSQLI_ASSOC);

// Get advisor name for topbar
$advisor_id = $_SESSION['user_id'] ?? 1;
$sql_advisor = "SELECT user_name FROM users WHERE user_id = ?";
$stmt_advisor = mysqli_prepare($conn, $sql_advisor);
mysqli_stmt_bind_param($stmt_advisor, "i", $advisor_id);
mysqli_stmt_execute($stmt_advisor);
$result_advisor = mysqli_stmt_get_result($stmt_advisor);
$advisor = mysqli_fetch_assoc($result_advisor);
$advisor_name = $advisor ? $advisor['user_name'] : 'Miss Nurul Asyikin';

// Get current semester info from semester_registration_periods
$sql_current = "SELECT * FROM semester_registration_periods WHERE is_open = 1 LIMIT 1";
$result_current = mysqli_query($conn, $sql_current);
$current_period = mysqli_fetch_assoc($result_current);

if ($current_period) {
    $session_semester = $current_period['session_semester'];
    $session_parts = explode('-', $session_semester);
    $current_session = $session_parts[0] ?? '2025/2026';
    $current_semester_num = $session_parts[1] ?? '2';
    $current_semester_code = str_replace('-', '', $session_semester);
    $current_year_programme = $student['year'] . ' / DSPD';
    $active_code = 'A - Active';
} else {
    $current_semester_code = "20252026" . $student['year'];
    $current_session = '2025/2026';
    $current_semester_num = '2';
    $current_year_programme = $student['year'] . ' / DSPD';
    $active_code = 'A - Active';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Details - UTM Academic Advisor</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background: #f8f6f4; overflow-x: hidden; }

        /* SIDEBAR */
        .sidebar {
            width: 280px; height: 100vh;
            background: linear-gradient(to bottom, #670019, #8b0022);
            position: fixed; padding: 30px 20px; color: white;
            transition: transform 0.3s ease;
        }
        .sidebar.collapsed { transform: translateX(-280px); }
        .logo { text-align: center; margin-bottom: 50px; }
        .logo img { width: 130px; }
        .system-title { color: white; font-size: 16px; font-weight: 600; margin-top: 12px; }
        .menu a {
            display: flex; align-items: center; gap: 15px;
            text-decoration: none; color: white; padding: 12px 20px;
            border-radius: 14px; margin-bottom: 12px; transition: 0.3s; font-size: 16px;
        }
        .menu a:hover, .menu .active { background: linear-gradient(to right, #f4a000, #e08700); }
        .menu i { font-size: 20px; }
        .logout {
            position: absolute; bottom: 30px;
            width: calc(100% - 40px); left: 20px;
        }
        .logout a {
            display: flex; align-items: center; gap: 15px;
            text-decoration: none; color: white; padding: 12px 20px;
            border-radius: 14px; transition: 0.3s; font-size: 16px;
            background: rgba(255,255,255,0.1);
        }
        .logout a:hover { background: linear-gradient(to right, #f4a000, #e08700); }

        /* MAIN */
        .main-content { margin-left: 280px; padding: 30px; transition: margin-left 0.3s ease; }
        .main-content.expanded { margin-left: 0; }
        .topbar {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 30px; background: white; padding: 15px 25px;
            border-radius: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .toggle-btn { background: none; border: none; font-size: 22px; color: #333; cursor: pointer; }
        .profile-box { display: flex; align-items: center; gap: 15px; cursor: pointer; }
        .profile-box img { width: 50px; height: 50px; border-radius: 50%; }

        /* PAGE HEADER */
        .page-header { 
            display: flex; 
            align-items: center; 
            gap: 15px;
            margin-bottom: 25px; 
            flex-wrap: wrap; 
        }
        .page-header h2 { color: #670019; font-weight: 700; margin: 0; }

        /* STATUS BADGE */
        .status-badge { display: inline-block; padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 500; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-approved { background: #d4edda; color: #155724; }
        .status-rejected { background: #f8d7da; color: #721c24; }

        /* PROFILE CARD */
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

        /* COURSES CARD */
        .courses-card { background: white; border-radius: 20px; padding: 25px; margin-bottom: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .courses-card h4 { color: #670019; font-weight: 600; margin-bottom: 20px; }

        /* REGISTERED COURSES TABLE */
        .registered-table { width: 100%; border-collapse: collapse; }
        .registered-table th { 
            text-align: center; 
            padding: 12px; 
            background: #f8f6f4; 
            color: #670019; 
            font-weight: 600; 
            font-size: 13px;
        }
        .registered-table td { 
            padding: 12px; 
            border-bottom: 1px solid #eee; 
            font-size: 13px;
            text-align: center;
        }

        /* SEMESTER INFO STRIP */
        .sem-info-strip { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; margin-bottom: 25px; }
        .sem-info-item label { display: block; font-size: 12px; color: #888; margin-bottom: 6px; font-weight: 500; }
        .sem-info-item .sem-info-val { background: #f0f4f8; border: 1px solid #dce3ea; border-radius: 10px; padding: 10px 14px; font-size: 14px; color: #333; font-weight: 500; }

        /* SEMESTER HISTORY TABLE */
        .table-controls { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; flex-wrap: wrap; gap: 10px; }
        .table-controls-left select, .table-controls-right input { padding: 4px 10px; border: 1px solid #ddd; border-radius: 8px; font-family: 'Poppins', sans-serif; font-size: 13px; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: center; padding: 11px 12px; background: #f0f4f8; color: #555; font-weight: 600; font-size: 13px; border: 1px solid #dce3ea; }
        td { padding: 11px 12px; border: 1px solid #eee; font-size: 13px; text-align: center; color: #333; }
        tbody tr:hover td { background: #fdf9f7; }

        /* PAGINATION */
        .table-footer { display: flex; justify-content: space-between; align-items: center; margin-top: 15px; flex-wrap: wrap; gap: 10px; }
        .pagination-btns { display: flex; gap: 6px; align-items: center; }
        .page-btn { padding: 5px 14px; border: 1px solid #ddd; border-radius: 8px; background: white; font-size: 13px; cursor: pointer; }
        .page-btn.active { background: #670019; color: white; border-color: #670019; }

        /* ACTION BUTTONS - SIDE BY SIDE */
        .action-buttons {
            background: white;
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        .btn-back {
            background: #6c757d;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 500;
            font-size: 14px;
            transition: 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }
        .btn-back:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }
.btn-review {
    background: linear-gradient(to right, #670019, #8b0022);
    color: white;
    border: none;
    padding: 12px 30px;
    border-radius: 25px;
    cursor: pointer;
    font-weight: 500;
    font-size: 14px;
    transition: 0.3s;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
}
.btn-review:hover {
    background: linear-gradient(to right, #8b0022, #a80028);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(103,0,25,0.25);
}
    </style>
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <div class="logo">
        <img src="images/utmlogo.png" alt="UTM Logo">
        <div class="system-title">COURSE REGISTRATION SYSTEM</div>
    </div>
    <div class="menu">
        <a href="advisor_dashboard.php"><i class="bi bi-house-fill"></i> Dashboard</a>
        <a href="advisor_my_students.php" class="active"><i class="bi bi-people-fill"></i> My Students</a>
        <a href="advisor_registrations.php"><i class="bi bi-file-earmark-text-fill"></i> Registrations</a>
        <a href="advisor_profile.php"><i class="bi bi-person-fill"></i> Profile</a>
        <a href="advisor_change_password.php"><i class="bi bi-lock-fill"></i> Change Password</a>
    </div>
    <div class="logout">
        <a href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
    </div>
</div>

<!-- MAIN -->
<div class="main-content">
    <div class="topbar">
        <button class="toggle-btn" onclick="toggleSidebar()"><i class="bi bi-list"></i></button>
        <div class="profile-box" onclick="location.href='advisor_profile.php'">
            <i class="bi bi-bell fs-5"></i>
            <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png">
            <div><h6 class="mb-0"><?php echo htmlspecialchars($advisor_name); ?></h6><small class="text-muted">Academic Advisor</small></div>
        </div>
    </div>

    <!-- PAGE HEADER -->
    <div class="page-header">
        <h2>Student Details</h2>
        <span class="status-badge status-<?php echo $reg_status; ?>"><?php echo ucfirst($reg_status); ?></span>
    </div>

    <!-- Student Profile Card -->
    <div class="profile-card">
        <div class="profile-header">
            <div class="student-avatar"><?php echo strtoupper(substr($student['user_name'], 0, 2)); ?></div>
            <div class="student-basic">
                <h3><?php echo htmlspecialchars($student['user_name']); ?></h3>
                <p><i class="bi bi-envelope"></i> <?php echo htmlspecialchars($student['utm_email']); ?></p>
            </div>
        </div>
        <div class="student-details-grid">
            <div class="detail-item"><div class="detail-icon"><i class="bi bi-card-text"></i></div><div class="detail-text"><small>Student ID</small><strong><?php echo htmlspecialchars($student['matrix_number']); ?></strong></div></div>
            <div class="detail-item"><div class="detail-icon"><i class="bi bi-mortarboard"></i></div><div class="detail-text"><small>Programme</small><strong><?php echo htmlspecialchars($student['programme']); ?></strong></div></div>
            <div class="detail-item"><div class="detail-icon"><i class="bi bi-calendar"></i></div><div class="detail-text"><small>Year</small><strong>Year <?php echo $student['year']; ?></strong></div></div>
            <div class="detail-item"><div class="detail-icon"><i class="bi bi-person-badge"></i></div><div class="detail-text"><small>Advisor ID</small><strong><?php echo $student['advisor_id']; ?></strong></div></div>
        </div>
    </div>

    <!-- REGISTERED COURSES SECTION -->
    <div class="courses-card">
        <h4><i class="bi bi-book"></i> Registered Courses (<?php echo count($courses); ?>)</h4>
        <?php if (!empty($courses)): ?>
            <table class="registered-table">
                <thead>
                    <tr>
                        <th>Subject Code</th>
                        <th>Subject Name</th>
                        <th>Credits</th>
                        <th>Section</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($courses as $course): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($course['subject_code']); ?></td>
                            <td><?php echo htmlspecialchars($course['subject_name']); ?></td>
                            <td><?php echo $course['credits']; ?></td>
                            <td><?php echo htmlspecialchars($course['section'] ?? '-'); ?></td>
                            <td><span class="status-badge status-<?php echo $course['reg_status']; ?>"><?php echo ucfirst($course['reg_status']); ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="text-center text-muted py-3">No courses registered for current semester.</div>
        <?php endif; ?>
    </div>

    <!-- Semester History Card -->
    <div class="courses-card">
        <h4><i class="bi bi-journal-text"></i> Semester History</h4>

        <!-- Current Semester Info -->
        <div class="sem-info-strip">
            <div class="sem-info-item"><label>Current Semester</label><div class="sem-info-val"><?php echo $current_semester_code; ?></div></div>
            <div class="sem-info-item"><label>Year / Programme</label><div class="sem-info-val"><?php echo $current_year_programme; ?></div></div>
            <div class="sem-info-item"><label>Active Code</label><div class="sem-info-val"><?php echo $active_code; ?></div></div>
        </div>

        <!-- Semester Histories -->
        <div style="font-size:14px; font-weight:600; color:#333; margin-bottom:10px;">Semester Histories</div>
        <div class="table-controls">
            <div class="table-controls-left">Show <select id="entriesSelect" onchange="renderTable()"><option value="5">5</option><option value="10" selected>10</option><option value="25">25</option></select> entries</div>
            <div class="table-controls-right">Search: <input type="text" id="searchInput" placeholder="" oninput="renderTable()"></div>
        </div>
        <div style="overflow-x:auto;">
            <table id="semTable">
                <thead>
                    <tr>
                        <th>Session Semester</th>
                        <th>Programme</th>
                        <th>No. Semester</th>
                        <th>Course Reg. Date</th>
                        <th>Active Code</th>
                        <th>CPA</th>
                    </tr>
                </thead>
                <tbody id="semTableBody"></tbody>
            </table>
        </div>
        <div class="table-footer"><small id="tableInfo"></small><div class="pagination-btns" id="pagination"></div></div>
    </div>

    <!-- Action Buttons - Side by Side -->
    <div class="action-buttons">
        <a href="advisor_my_students.php" class="btn-back"><i class="bi bi-arrow-left"></i> Back to Students</a>
        <a href="advisor_verify_registration.php?matrix=<?php echo $student['matrix_number']; ?>" class="btn-review"><i class="bi bi-pencil"></i> Review Registration</a>
    </div>
</div>

<script>
    // Semester history data from database
    var semData = <?php echo json_encode($semester_history); ?>;
    var currentPage = 1;

    function toggleSidebar() {
        var sidebar = document.querySelector('.sidebar');
        var main = document.querySelector('.main-content');
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

    function renderTable() {
        var query = document.getElementById('searchInput').value.toLowerCase();
        var perPage = parseInt(document.getElementById('entriesSelect').value);
        
        var filtered = [];
        for (var i = 0; i < semData.length; i++) {
            var values = Object.values(semData[i]).join(' ').toLowerCase();
            if (values.indexOf(query) !== -1) {
                filtered.push(semData[i]);
            }
        }
        
        var totalPages = Math.max(1, Math.ceil(filtered.length / perPage));
        if (currentPage > totalPages) currentPage = totalPages;
        var start = (currentPage - 1) * perPage;
        var pageData = filtered.slice(start, start + perPage);

        var tbody = document.getElementById('semTableBody');
        tbody.innerHTML = '';
        for (var i = 0; i < pageData.length; i++) {
            var r = pageData[i];
            // Show '-' if CPA is 0, 0.00, null, or empty, otherwise show the CPA value
            var cpaValue = (r.cpa && r.cpa != 0 && r.cpa != '0.00') ? r.cpa : '-';
            var row = '<tr>';
            row += '<td>' + escapeHtml(r.session || '-') + '</td>';
            row += '<td>' + escapeHtml(r.programme || '-') + '</td>';
            row += '<td>' + (r.noSem || '-') + '</td>';
            row += '<td>' + escapeHtml(r.regDate || '-') + '</td>';
            row += '<td>' + escapeHtml(r.activeCode || '-') + '</td>';
            row += '<td>' + cpaValue + '</td>';
            row += '</tr>';
            tbody.innerHTML += row;
        }

        var from = filtered.length === 0 ? 0 : start + 1;
        var to = Math.min(start + perPage, filtered.length);
        document.getElementById('tableInfo').textContent = 'Showing ' + from + ' to ' + to + ' of ' + filtered.length + ' entries';

        var pg = document.getElementById('pagination');
        pg.innerHTML = '';
        
        var prev = document.createElement('button');
        prev.className = 'page-btn';
        prev.textContent = 'Previous';
        prev.disabled = currentPage === 1;
        prev.onclick = function() { currentPage--; renderTable(); };
        pg.appendChild(prev);

        for (var i = 1; i <= totalPages; i++) {
            var btn = document.createElement('button');
            btn.className = 'page-btn' + (i === currentPage ? ' active' : '');
            btn.textContent = i;
            btn.onclick = (function(p) { return function() { currentPage = p; renderTable(); }; })(i);
            pg.appendChild(btn);
        }

        var next = document.createElement('button');
        next.className = 'page-btn';
        next.textContent = 'Next';
        next.disabled = currentPage === totalPages;
        next.onclick = function() { currentPage++; renderTable(); };
        pg.appendChild(next);
    }

    function escapeHtml(str) {
        if (!str) return '';
        return str.replace(/[&<>]/g, function(m) {
            if (m === '&') return '&amp;';
            if (m === '<') return '&lt;';
            if (m === '>') return '&gt;';
            return m;
        });
    }

    renderTable();
</script>
</body>
</html>