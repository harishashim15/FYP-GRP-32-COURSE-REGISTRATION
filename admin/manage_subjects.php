<?php
session_start();
require_once '../db_connect.php';  // This defines $conn

// Toggle subject hide/show (GET method)
if (isset($_GET['toggle_id']) && isset($_GET['action'])) {
    $code = $_GET['toggle_id'];
    $action = $_GET['action'];
    $new_hidden = ($action === 'hide') ? 1 : 0;
    $stmt = $conn->prepare("UPDATE subjects SET is_hidden = ? WHERE subject_code = ?");
    $stmt->bind_param("is", $new_hidden, $code);
    $stmt->execute();
    header("Location: manage_subjects.php?msg=Subject visibility updated.");
    exit();
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.html");
    exit();
}

// Fetch admin name
$admin_name = 'Admin';
$stmt = $conn->prepare("SELECT user_name FROM users WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $admin_name = $row['user_name'];
}
$stmt->close();

// Fetch distinct programmes for filter
$programmes = [];
$prog_result = $conn->query("SELECT DISTINCT programme FROM subjects WHERE programme IS NOT NULL AND programme != '' ORDER BY programme");
if ($prog_result) {
    while ($row = $prog_result->fetch_assoc()) {
        $programmes[] = $row['programme'];
    }
}

// Fetch all subjects
$subjects = [];
$query = "SELECT subject_code, subject_name, credits, programme, is_hidden FROM subjects ORDER BY subject_code";
$result = $conn->query($query);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $subjects[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Subjects - Admin Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="../images/logoWebsite.png"/>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background: #f8f6f4; overflow-x: hidden; }
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
            display: flex;
            align-items: center;
            gap: 15px;
            text-decoration: none;
            color: white;
            padding: 9px 20px;
            border-radius: 14px;
            margin-bottom: 12px;
            transition: 0.3s;
            font-size: 16px;
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
            border-radius: 14px; background: rgba(255,255,255,0.1);
        }
        .logout a:hover { background: linear-gradient(to right, #f4a000, #e08700); }
        .main-content { margin-left: 280px; padding: 30px; transition: margin-left 0.3s ease; }
        .main-content.expanded { margin-left: 0; }
        .topbar {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 30px; background: white; padding: 15px 25px;
            border-radius: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .toggle-btn { background: none; border: none; font-size: 22px; cursor: pointer; }
        .profile-box { display: flex; align-items: center; gap: 15px; cursor: pointer; }
        .profile-box img { width: 50px; height: 50px; border-radius: 50%; }
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .page-header h2 { color: #670019; font-weight: 700; }
        .btn-add {
            background: linear-gradient(to right, #670019, #8b0022);
            color: white; padding: 10px 20px; border-radius: 25px;
            text-decoration: none; display: inline-flex; align-items: center; gap: 8px;
            transition: 0.3s;
        }
        .btn-add:hover { background: linear-gradient(to right, #8b0022, #a80028); color: white; }
        .filter-row {
            display: flex;
            gap: 12px;
            margin-bottom: 20px;
            align-items: center;
            flex-wrap: wrap;
        }
        .filter-row select {
            flex: 1;
            padding: 10px 15px;
            border: 1.5px solid #e0d6d6;
            border-radius: 25px;
            background: white;
            font-size: 14px;
            cursor: pointer;
        }
        .filter-row button {
            padding: 10px 25px;
            border-radius: 25px;
            border: none;
            font-weight: 500;
            cursor: pointer;
            transition: 0.3s;
        }
        .btn-filter { background: #670019; color: white; }
        .btn-filter:hover { background: #8b0022; }
        .btn-reset { background: #6c757d; color: white; }
        .btn-reset:hover { background: #5a6268; }
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
            outline: none;
        }
        .search-bar input:focus { border-color: #670019; }
        .search-bar button {
            padding: 12px 25px;
            background: linear-gradient(to right, #670019, #8b0022);
            color: white;
            border: none;
            border-radius: 25px;
            cursor: pointer;
        }
        .table-card { background: white; border-radius: 25px; padding: 25px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; background: #f8f6f4; padding: 12px 15px; color: #670019; font-weight: 600; }
        td { padding: 12px 15px; border-bottom: 1px solid #eee; color: #333; }
        tr:hover { background: #fdf9f7; }

        /* ✅ Ensure Toggle & Edit buttons stay side by side */
        .action-cell {
            white-space: nowrap;
        }
        .action-btn {
            padding: 5px 12px;
            border-radius: 20px;
            text-decoration: none;
            font-size: 12px;
            display: inline-block;
            margin-right: 5px;
            transition: 0.3s;
            border: none;
            cursor: pointer;
        }
        .action-btn:last-child {
            margin-right: 0;
        }

        .btn-edit { background: #f4a000; color: white; }
        .btn-edit:hover { background: #e08700; color: white; }
        .btn-toggle { background: #670019; color: white; }
        .btn-toggle:hover { opacity: 0.8; }
        .alert { padding: 12px 20px; border-radius: 20px; margin-bottom: 20px; background: #d4edda; color: #155724; }
        .no-results { text-align: center; padding: 40px; color: #6c757d; }
        .status-badge { padding: 3px 10px; border-radius: 20px; font-size: 11px; display: inline-block; }
        .status-hidden { background: #dc3545; color: white; }
        .status-visible { background: #28a745; color: white; }
        @media (max-width: 992px) {
            .sidebar { transform: translateX(-280px); }
            .main-content { margin-left: 0; }
        }
        /* On very small screens, allow buttons to wrap but keep them side by side as much as possible */
        @media (max-width: 576px) {
            .action-cell {
                white-space: normal;
            }
            .action-btn {
                margin-bottom: 5px;
            }
        }
    </style>
</head>
<body>
<div class="sidebar">
    <div class="logo"><img src="../images/utmlogo.png" alt="UTM Logo"><div class="system-title">COURSE REGISTRATION SYSTEM</div></div>
    <div class="menu">
        <a href="admin_dashboard.php" ><i class="bi bi-house-fill"></i> Dashboard</a>
        <a href="manage_students.php"><i class="bi bi-people-fill"></i> Manage Students</a>
        <a href="manage_advisors.php" ><i class="bi bi-person-badge-fill"></i> Manage Advisors</a>
        <a href="manage_subjects.php" class="active"><i class="bi bi-book-fill"></i> Manage Subjects</a>
        <a href="manage_registration_period.php"><i class="bi bi-calendar-event"></i> Registration Period</a>
        <a href="admin_changepassword.php"><i class="bi bi-key-fill"></i> Change Password</a>
    </div>
    <div class="logout"><a href="../index.html"><i class="bi bi-box-arrow-right"></i> Logout</a></div>
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
    <div class="page-header">
        <h2>Manage Subjects</h2>
        <a href="add_subject.php" class="btn-add"><i class="bi bi-plus-circle"></i> Add Subject</a>
    </div>
    <?php if (isset($_GET['msg'])): ?>
        <div class="alert"><?php echo htmlspecialchars($_GET['msg']); ?></div>
    <?php endif; ?>

    <div class="filter-row">
        <select id="programmeSelect">
            <option value="">All Programmes</option>
            <?php foreach ($programmes as $prog): ?>
                <option value="<?php echo htmlspecialchars($prog); ?>"><?php echo htmlspecialchars($prog); ?></option>
            <?php endforeach; ?>
        </select>
        <button id="filterProgrammeBtn" class="btn-filter">Filter</button>
        <button id="resetFilterBtn" class="btn-reset">Reset</button>
    </div>

    <div class="search-bar">
        <input type="text" id="searchInput" placeholder="Search by subject code or name...">
        <button onclick="filterTable()">Search</button>
    </div>

    <div class="table-card">
        <div style="overflow-x: auto;">
            <table id="subjectsTable">
                <thead>
                    <tr><th>Subject Code</th><th>Subject Name</th><th>Credits</th><th>Programme</th><th>Status</th><th>Actions</th></tr>
                </thead>
                <tbody id="tableBody">
                    <?php foreach ($subjects as $s): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($s['subject_code']); ?></td>
                            <td><?php echo htmlspecialchars($s['subject_name']); ?></td>
                            <td><?php echo $s['credits']; ?></td>
                            <td><?php echo htmlspecialchars($s['programme'] ?? '-'); ?></td>
                            <td><span class="status-badge <?php echo $s['is_hidden'] ? 'status-hidden' : 'status-visible'; ?>"><?php echo $s['is_hidden'] ? 'Hidden' : 'Visible'; ?></span></td>
                            <td class="action-cell">
                                <a href="manage_subjects.php?toggle_id=<?php echo urlencode($s['subject_code']); ?>&action=<?php echo $s['is_hidden'] ? 'show' : 'hide'; ?>" class="action-btn btn-toggle" onclick="return confirm('Toggle visibility of this subject?')">
                                    <?php echo $s['is_hidden'] ? 'Show' : 'Hide'; ?>
                                </a>
                                <a href="edit_subject.php?code=<?php echo urlencode($s['subject_code']); ?>" class="action-btn btn-edit">Edit</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($subjects)): ?>
                        <tr><td colspan="6" class="text-center">No subjects found</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <div id="noResultsMsg" class="no-results" style="display: none;">No subjects match your filters.</div>
        </div>
    </div>
</div>

<script>
    let allSubjects = [];
    function initSubjects() {
        const rows = document.querySelectorAll('#subjectsTable tbody tr');
        allSubjects = [];
        rows.forEach(row => {
            const cells = row.cells;
            if (cells.length < 6) return;
            allSubjects.push({
                code: cells[0].innerText.trim(),
                name: cells[1].innerText.trim(),
                programme: cells[3].innerText.trim(),
                row: row
            });
        });
    }
    function filterTable() {
        const programme = document.getElementById('programmeSelect').value;
        const search = document.getElementById('searchInput').value.toLowerCase().trim();
        let visibleCount = 0;
        allSubjects.forEach(subj => {
            let match = true;
            if (programme && subj.programme !== programme) match = false;
            if (search && !subj.code.toLowerCase().includes(search) && !subj.name.toLowerCase().includes(search)) match = false;
            if (match) {
                subj.row.style.display = '';
                visibleCount++;
            } else {
                subj.row.style.display = 'none';
            }
        });
        document.getElementById('noResultsMsg').style.display = visibleCount === 0 ? 'block' : 'none';
    }
    function resetFilter() {
        document.getElementById('programmeSelect').value = '';
        document.getElementById('searchInput').value = '';
        filterTable();
    }
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

    document.addEventListener('DOMContentLoaded', () => {
        initSubjects();
        document.getElementById('filterProgrammeBtn').addEventListener('click', filterTable);
        document.getElementById('resetFilterBtn').addEventListener('click', resetFilter);
        document.getElementById('searchInput').addEventListener('keyup', filterTable);
        document.getElementById('programmeSelect').addEventListener('change', filterTable);
    });
</script>
</body>
</html>