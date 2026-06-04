<?php
require_once 'db_connect.php';

session_start();

// Get advisor ID from session - FIX: Remove default 1
$advisor_id = $_SESSION['user_id'] ?? null;

// If not logged in, redirect to login
if (!$advisor_id) {
    header("Location: ../index.html");
    exit;
}

// Verify the user is actually an advisor
$sql_check = "SELECT role FROM users WHERE user_id = ?";
$stmt_check = mysqli_prepare($conn, $sql_check);
mysqli_stmt_bind_param($stmt_check, "i", $advisor_id);
mysqli_stmt_execute($stmt_check);
$result_check = mysqli_stmt_get_result($stmt_check);
$user_check = mysqli_fetch_assoc($result_check);

if (!$user_check || $user_check['role'] != 'advisor') {
    session_destroy();
    header("Location: ../index.html");
    exit;
}

// Get student_id from URL
$student_id = isset($_GET['student_id']) ? (int)$_GET['student_id'] : 0;

if (empty($student_id)) {
    header("Location: advisor_registrations.php");
    exit;
}

// Get student info
$sql = "SELECT 
            s.user_id as student_id,
            s.matrix_number,
            s.user_name as student_name,
            s.utm_email as student_email,
            s.phone,
            s.programme,
            s.year,
            s.semester as current_semester
        FROM students s
        WHERE s.user_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $student_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$student = mysqli_fetch_assoc($result);

if (!$student) {
    header("Location: advisor_registrations.php");
    exit;
}

// Get ALL courses from ALL registrations for this student
$sql = "SELECT 
            sub.subject_code as code,
            sub.subject_name as name,
            sub.credits as credits,
            rc.section,
            cr.id as registration_id,
            cr.status as reg_status,
            cr.submission_date,
            cr.advisor_remarks
        FROM course_registrations cr
        JOIN registration_courses rc ON cr.id = rc.registration_id
        JOIN subjects sub ON rc.subject_code = sub.subject_code
        WHERE cr.student_id = ?
        ORDER BY cr.submission_date DESC, sub.subject_code";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $student_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$courses = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Get the status and remarks from the most recent registration
$reg_status = !empty($courses) ? $courses[0]['reg_status'] : 'pending';
$advisor_remarks = !empty($courses) ? $courses[0]['advisor_remarks'] : '';
$submission_date = !empty($courses) ? $courses[0]['submission_date'] : date('Y-m-d');

// Calculate total credits
$total_credits = 0;
foreach ($courses as $course) { 
    $total_credits += $course['credits']; 
}

// Handle approve/reject action via AJAX
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    $remarks = $_POST['remarks'] ?? '';
    $new_status = ($action == 'approve') ? 'approved' : 'rejected';
    $current_time = date('Y-m-d H:i:s');
    
    // Update ALL registrations for this student
    $sql = "UPDATE course_registrations SET status = ?, advisor_remarks = ?, reviewed_by = ?, reviewed_at = ? WHERE student_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssisi", $new_status, $remarks, $advisor_id, $current_time, $student_id);
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true, 'status' => $new_status, 'remarks' => $remarks]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . mysqli_error($conn)]);
    }
    exit;
}

// Get advisor name for topbar
$sql_advisor = "SELECT user_name FROM users WHERE user_id = ?";
$stmt_advisor = mysqli_prepare($conn, $sql_advisor);
mysqli_stmt_bind_param($stmt_advisor, "i", $advisor_id);
mysqli_stmt_execute($stmt_advisor);
$result_advisor = mysqli_stmt_get_result($stmt_advisor);
$advisor = mysqli_fetch_assoc($result_advisor);
$advisor_name = $advisor ? $advisor['user_name'] : 'Advisor';

// Get student initials for avatar
$name_parts = explode(' ', $student['student_name']);
$initials = '';
for ($i = 0; $i < count($name_parts) && $i < 2; $i++) {
    $initials .= strtoupper(substr($name_parts[$i], 0, 1));
}
if (empty($initials)) $initials = 'ST';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Registration - UTM Academic Advisor</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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

        /* STUDENT CARD */
        .student-card {
            background: white; border-radius: 20px; padding: 20px;
            display: flex; align-items: center; gap: 20px;
            margin-bottom: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .student-avatar {
            width: 60px; height: 60px; border-radius: 50%;
            background: #670019; color: white;
            display: flex; align-items: center; justify-content: center;
            font-size: 24px; font-weight: 600; flex-shrink: 0;
        }
        .student-info h3 { margin: 0 0 5px 0; font-size: 18px; }
        .student-info p { margin: 0; color: #666; font-size: 13px; }

        /* COURSES CARD */
        .courses-card {
            background: white; border-radius: 20px; padding: 25px;
            margin-bottom: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .courses-card h4 { color: #670019; font-weight: 600; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 12px; background: #f8f6f4; color: #670019; font-weight: 600; }
        td { padding: 12px; border-bottom: 1px solid #eee; }
        .total-credits { margin-top: 15px; text-align: right; font-weight: 600; color: #670019; }

        /* ACTION CARD */
        .action-card {
            background: white; border-radius: 20px; padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .form-group { margin-bottom: 20px; }
        .form-group label { font-weight: 500; margin-bottom: 8px; display: block; font-size: 14px; }
        .form-group textarea {
            width: 100%; padding: 12px; border: 1px solid #ddd;
            border-radius: 12px; resize: vertical;
            font-family: 'Poppins', sans-serif; font-size: 14px;
        }
        .form-group textarea:focus { outline: none; border-color: #670019; box-shadow: 0 0 0 4px rgba(103,0,25,0.08); }
        .btn-approve {
            background: #d4edda; color: #155724; border: none;
            padding: 12px 30px; border-radius: 25px; cursor: pointer;
            font-weight: 500;
            font-size: 14px;
            transition: 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-approve:hover { background: #c3e6cb; }
        .btn-reject {
            background: #f8d7da; color: #721c24; border: none;
            padding: 12px 30px; border-radius: 25px; cursor: pointer;
            font-weight: 500;
            font-size: 14px;
            transition: 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-reject:hover { background: #f5c6cb; }
        
        /* PDF Button */
        .btn-pdf {
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
        .btn-pdf:hover {
            background: linear-gradient(to right, #8b0022, #a80028);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(103,0,25,0.25);
        }
        
        /* Back Button */
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
        
        .alert-info {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .alert-warning {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            margin-top: 20px;
        }
        
        .remarks-text {
            margin-top: 10px;
            padding: 10px;
            background: #f8f6f4;
            border-radius: 10px;
            font-size: 13px;
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
        <a href="advisor_my_students.php"><i class="bi bi-people-fill"></i> My Students</a>
        <a href="advisor_registrations.php" class="active"><i class="bi bi-file-earmark-text-fill"></i> Registrations</a>
        <a href="advisor_profile.php"><i class="bi bi-person-fill"></i> Profile</a>
        <a href="advisor_change_password.php"><i class="bi bi-lock-fill"></i> Change Password</a>
    </div>
    <div class="logout">
        <a href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
    </div>
</div>

<!-- MAIN CONTENT -->
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
        <h2>Verify Registration — <?php echo htmlspecialchars($student['student_name']); ?></h2>
        <span class="status-badge status-<?php echo $reg_status; ?>" id="statusBadge"><?php echo ucfirst($reg_status); ?></span>
    </div>

    <div class="student-card">
        <div class="student-avatar"><?php echo $initials; ?></div>
        <div class="student-info">
            <h3><?php echo htmlspecialchars($student['student_name']); ?></h3>
            <p><?php echo htmlspecialchars($student['matrix_number']); ?> · <?php echo htmlspecialchars($student['programme']); ?> · Year <?php echo $student['year']; ?> · Sem <?php echo $student['current_semester']; ?></p>
        </div>
    </div>

    <div class="courses-card">
        <h4><i class="bi bi-book"></i> Registered courses (<?php echo count($courses); ?>)</h4>
        <table class="table">
            <thead>
                <tr>
                    <th>Course Code</th>
                    <th>Course Name</th>
                    <th>Credit Hours</th>
                    <th>Section</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($courses)): ?>
                    <?php foreach ($courses as $course): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($course['code']); ?></td>
                            <td><?php echo htmlspecialchars($course['name']); ?></td>
                            <td><?php echo $course['credits']; ?></td>
                            <td><?php echo htmlspecialchars($course['section'] ?? '01'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center">No courses found</small>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <div class="total-credits">Total credit hours: <strong><?php echo $total_credits; ?></strong></div>
    </div>

    <!-- PENDING ACTIONS -->
    <div id="pendingActions" style="<?php echo ($reg_status == 'pending') ? 'display: block;' : 'display: none;'; ?>">
        <div class="action-card">
            <div class="form-group">
                <label><i class="bi bi-chat"></i> Remarks (required for rejection, optional for approval)</label>
                <textarea id="remarksText" rows="3" placeholder="Add a note for the student..."></textarea>
            </div>
            <div class="action-buttons">
                <button onclick="submitAction('approve')" class="btn-approve"><i class="bi bi-check-circle"></i> Approve</button>
                <button onclick="submitAction('reject')" class="btn-reject"><i class="bi bi-x-circle"></i> Reject</button>
                <a href="advisor_registrations.php" class="btn-back"><i class="bi bi-arrow-left"></i> Back to Registrations</a>
            </div>
        </div>
    </div>

    <!-- APPROVED ACTIONS -->
    <div id="approvedActions" style="<?php echo ($reg_status == 'approved') ? 'display: block;' : 'display: none;'; ?>">
        <div class="action-card">
            <div class="alert-info">
                <i class="bi bi-check-circle-fill"></i> This registration has been <strong>APPROVED</strong>.
            </div>
            <?php if (!empty($advisor_remarks)): ?>
                <div class="remarks-text">
                    <strong>Advisor Remarks:</strong><br>
                    <?php echo nl2br(htmlspecialchars($advisor_remarks)); ?>
                </div>
            <?php endif; ?>
            <div class="action-buttons">
                <button onclick="printPDF()" class="btn-pdf">
                    <i class="bi bi-file-earmark-pdf"></i> Print Registration Slip (PDF)
                </button>
                <a href="advisor_registrations.php" class="btn-back">
                    <i class="bi bi-arrow-left"></i> Back to Registrations
                </a>
            </div>
        </div>
    </div>

    <!-- REJECTED ACTIONS -->
    <div id="rejectedActions" style="<?php echo ($reg_status == 'rejected') ? 'display: block;' : 'display: none;'; ?>">
        <div class="action-card">
            <div class="alert-warning">
                <i class="bi bi-x-circle-fill"></i> This registration has been <strong>REJECTED</strong>.
            </div>
            <?php if (!empty($advisor_remarks)): ?>
                <div class="remarks-text" style="background: #f8d7da;">
                    <strong>Advisor Remarks:</strong><br>
                    <?php echo nl2br(htmlspecialchars($advisor_remarks)); ?>
                </div>
            <?php endif; ?>
            <div class="action-buttons">
                <a href="advisor_registrations.php" class="btn-back">Back to Registrations</a>
            </div>
        </div>
    </div>
</div>

<script>
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

    function submitAction(action) {
        var remarks = document.getElementById('remarksText').value;
        
        if (action === 'reject' && !remarks.trim()) {
            Swal.fire({
                icon: 'warning',
                title: 'Remarks Required',
                text: 'Please provide a reason for rejecting this registration.',
                confirmButtonColor: '#670019'
            });
            return;
        }
        
        // Show loading
        Swal.fire({
            title: 'Processing...',
            text: 'Please wait while we process your request.',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        // Send AJAX request
        fetch(window.location.href, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: action,
                remarks: remarks
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                Swal.fire({
                    icon: 'success',
                    title: action === 'approve' ? 'Registration Approved!' : 'Registration Rejected!',
                    text: action === 'approve' ? 'The registration has been approved successfully.' : 'The registration has been rejected.',
                    confirmButtonColor: '#670019'
                }).then(() => {
                    // Update the page with new status
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.error || 'Failed to update registration. Please try again.',
                    confirmButtonColor: '#670019'
                });
            }
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred. Please try again.',
                confirmButtonColor: '#670019'
            });
        });
    }
    
function printPDF() {
    // Create a hidden iframe to load the slip and print directly
    var registrationId = <?php 
        $reg_id = !empty($courses) ? $courses[0]['registration_id'] : 0;
        echo $reg_id; 
    ?>;
    
    if (registrationId > 0) {
        // Show loading indicator
        Swal.fire({
            title: 'Preparing PDF...',
            text: 'Please wait while we prepare your registration slip.',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        // Create hidden iframe
        var iframe = document.createElement('iframe');
        iframe.style.display = 'none';
        iframe.src = 'advisor_print_slip.php?registration_id=' + registrationId;
        document.body.appendChild(iframe);
        
        iframe.onload = function() {
            // Close the loading alert
            Swal.close();
            
            // Print the iframe content
            iframe.contentWindow.print();
            
            // Remove iframe after print (optional)
            setTimeout(function() {
                document.body.removeChild(iframe);
            }, 1000);
        };
        
        iframe.onerror = function() {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Unable to generate registration slip. Please try again.',
                confirmButtonColor: '#670019'
            });
        };
    } else {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Unable to generate registration slip. Registration ID not found.',
            confirmButtonColor: '#670019'
        });
    }
}
</script>
</body>
</html>