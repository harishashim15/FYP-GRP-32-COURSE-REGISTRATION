<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

include("db_connect.php");

$user_id = $_SESSION['user_id'];
$registration_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$registration_id) {
    die("No registration ID provided.");
}

// Fetch student info
$student_query = "SELECT u.user_name, u.matrix_number, u.utm_email, u.second_email, u.phone,
                         s.programme, s.year, s.semester, s.ic_number, s.address, s.advisor_id
                  FROM users u
                  LEFT JOIN students s ON u.user_id = s.user_id
                  WHERE u.user_id = '$user_id'";
$student_result = mysqli_query($conn, $student_query);
$student = mysqli_fetch_assoc($student_result);
$student_name = $student ? $student['user_name'] : "Student";
$matrix_number = $student ? $student['matrix_number'] : "";
$programme = $student ? $student['programme'] : "Computer Science";
$year = $student ? $student['year'] : "2";
$ic_number = $student && $student['ic_number'] ? $student['ic_number'] : "Not provided";
$address = $student && $student['address'] ? $student['address'] : "Not provided";
$phone = $student && $student['phone'] ? $student['phone'] : "Not provided";
$email = $student && $student['utm_email'] ? $student['utm_email'] : "Not provided";

// Fetch registration details
$reg_query = "SELECT cr.id, cr.submission_date, cr.status, cr.session, cr.reviewed_at, 
                     cr.advisor_remarks, u.user_name as reviewed_by
              FROM course_registrations cr
              LEFT JOIN users u ON cr.reviewed_by = u.user_id
              WHERE cr.id = $registration_id AND cr.student_id = $user_id";
$reg_result = mysqli_query($conn, $reg_query);
$registration = mysqli_fetch_assoc($reg_result);

if (!$registration) {
    die("Registration not found.");
}

// Fetch registered courses
$courses_query = "SELECT rc.subject_code, s.subject_name, s.credits, rc.section
                  FROM registration_courses rc
                  JOIN subjects s ON rc.subject_code = s.subject_code
                  WHERE rc.registration_id = $registration_id";
$courses_result = mysqli_query($conn, $courses_query);
$courses = [];
$total_credits = 0;
while ($row = mysqli_fetch_assoc($courses_result)) {
    $courses[] = $row;
    $total_credits += $row['credits'];
}

$total_credits_formatted = str_pad($total_credits, 3, '0', STR_PAD_LEFT);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Registration Slip - UTM Student</title>
    <link rel="icon" type="image/png" href="images/logoWebsite.png"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        body {
            background: #f8f6f4;
        }
        
        /* SIDEBAR STYLES */
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
        
        .main-content {
            margin-left: 280px;
            padding: 30px;
            transition: margin-left 0.3s ease;
        }
        .main-content.expanded { margin-left: 0; }
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
        
        .page-header { margin-bottom: 20px; }
        .page-header h2 { font-size: 28px; font-weight: 700; color: #670019; }
        
        /* ========== SLIP CONTAINER - FIXED FOR PDF ========== */
        .slip-container {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            max-width: 100%;
            margin: 0 auto;
            width: 100%;
        }
        
        .slip-header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #670019;
        }
        .slip-header img {
            width: 50px;
            margin-bottom: 5px;
        }
        .slip-header h3 {
            color: #670019;
            font-size: 16px;
            font-weight: 700;
        }
        .slip-header p {
            color: #666;
            font-size: 10px;
        }
        
        .section-title {
            color: #670019;
            font-size: 12px;
            font-weight: 600;
            margin: 12px 0 8px 0;
            padding-bottom: 3px;
            border-bottom: 1px solid #eee;
        }
        
        /* SIMPLE ROW LAYOUT - NO CUTOFF */
        .info-row {
            margin-bottom: 6px;
            overflow: hidden;
        }
        .info-label {
            float: left;
            width: 120px;
            font-size: 11px;
            color: #888;
            padding: 3px 0;
        }
        .info-value {
            margin-left: 120px;
            font-size: 11px;
            color: #333;
            font-weight: 500;
            padding: 3px 0;
            word-wrap: break-word;
        }
        
        /* TABLE - FIXED WIDTH */
        .courses-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            table-layout: fixed;
        }
        .courses-table th {
            background: #f8f6f4;
            padding: 6px 4px;
            text-align: left;
            border: 1px solid #ddd;
            color: #670019;
        }
        .courses-table td {
            padding: 5px 4px;
            border: 1px solid #ddd;
            word-wrap: break-word;
        }
        .courses-table th:nth-child(1) { width: 8%; }
        .courses-table th:nth-child(2) { width: 20%; }
        .courses-table th:nth-child(3) { width: 47%; }
        .courses-table th:nth-child(4) { width: 10%; }
        .courses-table th:nth-child(5) { width: 15%; }
        
        .total-credits {
            text-align: right;
            font-size: 11px;
            font-weight: 600;
            color: #670019;
            margin-top: 8px;
            padding-top: 5px;
            border-top: 1px solid #eee;
        }
        
        .verification-row {
            margin-bottom: 6px;
            overflow: hidden;
        }
        .verification-label {
            float: left;
            width: 120px;
            font-size: 11px;
            color: #888;
        }
        .verification-value {
            margin-left: 120px;
            font-size: 11px;
            color: #333;
            word-wrap: break-word;
        }
        
        .signature-row {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px dashed #ccc;
        }
        .signature-box {
            text-align: center;
            width: 45%;
        }
        .signature-box span {
            font-size: 9px;
            color: #888;
        }
        .signature-line {
            margin-top: 5px;
            border-top: 1px dashed #999;
            padding-top: 15px;
        }
        
        .footer {
            margin-top: 15px;
            text-align: center;
            font-size: 8px;
            color: #999;
        }
        
        .print-btn, .back-btn {
            padding: 12px 30px;
            border-radius: 14px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            border: none;
        }
        .print-btn {
            background: linear-gradient(to right, #670019, #8b0022);
            color: white;
        }
        .print-btn:hover {
            background: linear-gradient(to right, #8b0022, #a80028);
            transform: translateY(-2px);
        }
        .back-btn {
            background: #6c757d;
            color: white;
        }
        
        @media (max-width: 992px) {
            .sidebar { transform: translateX(-280px); }
            .main-content { margin-left: 0; }
        }
        
        /* PDF PRINT STYLES - NO CUTOFF */
        @media print {
            .sidebar, .topbar, .print-btn, .back-btn, .logout {
                display: none !important;
            }
            .main-content {
                margin-left: 0 !important;
                padding: 0 !important;
            }
            .slip-container {
                box-shadow: none;
                padding: 15px;
                max-width: 100%;
                width: 100%;
            }
            body {
                background: white;
                margin: 0;
                padding: 0;
            }
            .info-label {
                width: 100px;
            }
            .info-value {
                margin-left: 100px;
            }
            .courses-table {
                font-size: 9px;
            }
        }
    </style>
</head>
<body>

<div class="sidebar" id="sidebar">
    <div class="logo"><img src="images/utmlogo.png" alt="UTM"><div class="system-title">COURSE REGISTRATION SYSTEM</div></div>
    <div class="menu">
        <a href="student_dashboard.html"><i class="bi bi-house-fill"></i> Dashboard</a>
        <a href="student_profile.html"><i class="bi bi-person-fill"></i> Profile</a>
        <a href="student_courses.html"><i class="bi bi-book-fill"></i> Courses</a>
        <a href="student_register.html"><i class="bi bi-journal-text"></i> Register</a>
        <a href="student_registration_history.html"><i class="bi bi-clock-history"></i> Registration History</a>
        <a href="student_my_registration.html"><i class="bi bi-file-earmark-text-fill"></i> My Registration</a>
        <a href="student_change_password.html"><i class="bi bi-lock-fill"></i> Change Password</a>
    </div>
    <div class="logout"><a href="#" onclick="event.preventDefault(); localStorage.clear(); window.location.href='index.html';"><i class="bi bi-box-arrow-right"></i> Logout</a></div>
</div>

<div class="main-content" id="mainContent">
    <div class="topbar">
        <button class="toggle-btn" onclick="toggleSidebar()"><i class="bi bi-list"></i></button>
        <div class="profile-box" onclick="location.href='student_profile.html'">
            <i class="bi bi-bell fs-5"></i>
            <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" alt="Profile">
            <div><h6 class="mb-0"><?php echo htmlspecialchars($student_name); ?></h6><small>Student</small></div>
        </div>
    </div>

    <div class="page-header"><h2>Registration Slip</h2></div>

    <div class="slip-container" id="slipContainer">
        <div class="slip-header">
            <img src="images/utmlogo.png" alt="UTM Logo">
            <h3>COURSE REGISTRATION SLIP</h3>
            <p>Universiti Teknologi Malaysia</p>
        </div>
        
        <div class="section-title">STUDENT INFORMATION</div>
        <div class="info-row"><div class="info-label">Name</div><div class="info-value"><?php echo htmlspecialchars($student_name); ?></div></div>
        <div class="info-row"><div class="info-label">Matric No.</div><div class="info-value"><?php echo htmlspecialchars($matrix_number); ?></div></div>
        <div class="info-row"><div class="info-label">IC/Passport</div><div class="info-value"><?php echo htmlspecialchars($ic_number); ?></div></div>
        <div class="info-row"><div class="info-label">Programme</div><div class="info-value"><?php echo htmlspecialchars($programme); ?></div></div>
        <div class="info-row"><div class="info-label">Year</div><div class="info-value"><?php echo htmlspecialchars($year); ?></div></div>
        <div class="info-row"><div class="info-label">Email</div><div class="info-value"><?php echo htmlspecialchars($email); ?></div></div>
        <div class="info-row"><div class="info-label">Phone</div><div class="info-value"><?php echo htmlspecialchars($phone); ?></div></div>
        <div class="info-row"><div class="info-label">Address</div><div class="info-value"><?php echo htmlspecialchars($address); ?></div></div>
        
        <div class="section-title">REGISTRATION DETAILS</div>
        <div class="info-row"><div class="info-label">Session</div><div class="info-value"><?php echo htmlspecialchars($registration['session'] ?? '2025/2026 - Semester 2'); ?></div></div>
        <div class="info-row"><div class="info-label">Submitted Date</div><div class="info-value"><?php echo date('d-m-Y h:i A', strtotime($registration['submission_date'])); ?></div></div>
        <div class="info-row"><div class="info-label">Approved Date</div><div class="info-value"><?php echo $registration['reviewed_at'] ? date('d-m-Y h:i A', strtotime($registration['reviewed_at'])) : '-'; ?></div></div>
        <div class="info-row"><div class="info-label">Approved By</div><div class="info-value"><?php echo htmlspecialchars($registration['reviewed_by'] ?? '-'); ?></div></div>
        <div class="info-row"><div class="info-label">Status</div><div class="info-value"><span style="background:#d4edda; color:#155724; padding:2px 10px; border-radius:12px;">APPROVED</span></div></div>
        
        <div class="section-title">REGISTERED COURSES</div>
        <table class="courses-table">
            <thead>
                <tr><th>#</th><th>Code</th><th>Course Title</th><th>Cr</th><th>Sec</th></tr>
            </thead>
            <tbody>
                <?php if (count($courses) > 0): ?>
                    <?php $counter = 1; foreach ($courses as $course): ?>
                    <tr>
                        <td><?php echo $counter++; ?></td>
                        <td><?php echo htmlspecialchars($course['subject_code']); ?></td>
                        <td><?php echo htmlspecialchars($course['subject_name']); ?></strong></td>
                        <td><?php echo $course['credits']; ?></td>
                        <td><?php echo htmlspecialchars($course['section'] ?? 'TBD'); ?></strong></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="text-center">No courses found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        <div class="total-credits">TOTAL CREDIT: <?php echo $total_credits_formatted; ?></div>
        
        <div class="section-title">VERIFICATION FROM ACADEMIC ADVISOR</div>
        <div class="verification-row"><div class="verification-label">Verified By</div><div class="verification-value"><?php echo htmlspecialchars($registration['reviewed_by'] ?? '_________________'); ?></div></div>
        <div class="verification-row"><div class="verification-label">Date Verified</div><div class="verification-value"><?php echo $registration['reviewed_at'] ? date('d-m-Y', strtotime($registration['reviewed_at'])) : '_________________'; ?></div></div>
        <div class="verification-row"><div class="verification-label">Remarks</div><div class="verification-value"><?php echo htmlspecialchars($registration['advisor_remarks'] ?? '-'); ?></div></div>
        
        <div class="signature-row">
            <div class="signature-box"><span>STUDENT SIGNATURE</span><div class="signature-line"></div></div>
            <div class="signature-box"><span>ADVISOR SIGNATURE</span><div class="signature-line"></div></div>
        </div>
        
        <div class="footer">DATE: <?php echo date('d-m-Y'); ?> | Please check your details. Corrections can be made at your faculty.</div>
    </div>

    <div class="d-flex gap-3 mt-3">
        <button class="print-btn" id="printBtn"><i class="bi bi-printer"></i> Print Registration Slip (PDF)</button>
        <a href="student_registration_history.html" class="back-btn"><i class="bi bi-arrow-left"></i> Back to History</a>
    </div>
</div>

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

    document.getElementById('printBtn').addEventListener('click', function() {
        const element = document.getElementById('slipContainer');
        const originalText = this.innerHTML;
        this.innerHTML = '<i class="bi bi-hourglass-split"></i> Generating PDF...';
        this.disabled = true;
        
        const opt = {
            margin: [0.3, 0.3, 0.3, 0.3],
            filename: 'Registration_Slip_<?php echo $registration_id; ?>.pdf',
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { scale: 2, letterRendering: true, useCORS: true },
            jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait' }
        };
        
        html2pdf().set(opt).from(element).save().then(() => {
            this.innerHTML = originalText;
            this.disabled = false;
        });
    });
</script>
</body>
</html>