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

// Fetch student info with additional details
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

// Fetch registered courses for this registration
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Slip - UTM Student</title>
    <link rel="icon" type="image/png" href="images/logoWebsite.png"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background: #f8f6f4; }
        
        .sidebar {
            width: 280px; height: 100vh;
            background: linear-gradient(to bottom, #670019, #8b0022);
            position: fixed; padding: 30px 20px; color: white;
            transition: transform 0.3s ease; z-index: 1000;
            overflow-y: auto;
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
        
        .main-content { margin-left: 280px; padding: 30px; transition: margin-left 0.3s ease; }
        .main-content.expanded { margin-left: 0; }
        .topbar {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 30px; background: white; padding: 15px 25px;
            border-radius: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .toggle-btn { background: none; border: none; font-size: 22px; color: #333; cursor: pointer; }
        .profile-box { display: flex; align-items: center; gap: 15px; cursor: pointer; }
        .profile-box img { width: 50px; height: 50px; border-radius: 50%; object-fit: cover; }
        
        .page-header { margin-bottom: 15px; }
        .page-header h2 { font-size: 24px; font-weight: 700; color: #670019; }
        
        /* Registration Slip Styles - EXTRA COMPACT FOR ONE PAGE */
        .registration-slip {
            background: white;
            border-radius: 10px;
            padding: 12px;
            box-shadow: 0px 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 15px;
        }
        
        .slip-header {
            text-align: center;
            margin-bottom: 10px;
            padding-bottom: 8px;
            border-bottom: 2px solid #670019;
        }
        
        .slip-header .logo-img {
            max-width: 40px;
            margin-bottom: 3px;
        }
        
        .slip-header h3 {
            color: #670019;
            font-weight: 700;
            font-size: 14px;
            margin-bottom: 2px;
        }
        
        .slip-header .university-name {
            color: #333;
            font-size: 9px;
        }
        
        .info-section {
            margin-bottom: 8px;
        }
        
        .info-section h4 {
            color: #670019;
            font-weight: 600;
            font-size: 11px;
            margin-bottom: 5px;
            padding-bottom: 2px;
            border-bottom: 1px solid #ddd;
        }
        
        /* Two column layout - COMPACT */
        .info-grid-2col {
            display: flex;
            flex-wrap: wrap;
        }
        
        .info-item {
            width: 50%;
            margin-bottom: 4px;
        }
        
        .info-label {
            font-size: 9px;
            color: #888;
            display: inline-block;
            width: 85px;
        }
        
        .info-value {
            font-size: 10px;
            color: #333;
            font-weight: 500;
            display: inline-block;
        }
        
        .courses-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
        }
        .courses-table th {
            background: #f8f6f4;
            padding: 4px 3px;
            text-align: left;
            color: #670019;
            font-weight: 600;
            border: 1px solid #ddd;
        }
        .courses-table td {
            padding: 3px;
            border: 1px solid #ddd;
        }
        
        .total-credits {
            text-align: right;
            margin-top: 4px;
            padding-top: 3px;
            border-top: 1px solid #ddd;
            font-weight: 600;
            color: #670019;
            font-size: 9px;
        }
        
        .verification-section {
            margin-top: 8px;
            padding-top: 5px;
            border-top: 1px solid #ddd;
        }
        
        .verification-item {
            margin-bottom: 3px;
        }
        
        .verification-label {
            font-size: 9px;
            color: #888;
            width: 85px;
            display: inline-block;
        }
        
        .verification-value {
            font-size: 10px;
            color: #333;
            font-weight: 500;
            display: inline-block;
        }
        
        .signature-line {
            margin-top: 8px;
            padding-top: 5px;
            border-top: 1px dashed #ccc;
        }
        
        .signature-row {
            display: flex;
            justify-content: space-between;
        }
        
        .signature-item {
            text-align: center;
            width: 45%;
        }
        
        .signature-item span {
            font-size: 8px;
            color: #888;
        }
        
        .signature-line-dash {
            margin-top: 2px;
            border-top: 1px dashed #999;
        }
        
        .footer-note {
            margin-top: 8px;
            font-size: 7px;
            color: #999;
            text-align: center;
        }
        
        .print-btn {
            background: linear-gradient(to right, #670019, #8b0022);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 14px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .print-btn:hover {
            background: linear-gradient(to right, #8b0022, #a80028);
            transform: translateY(-2px);
        }
        
        .back-btn {
            background: #6c757d;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 14px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        @media (max-width: 992px) {
            .sidebar { transform: translateX(-280px); }
            .main-content { margin-left: 0; }
        }
        
        /* Print/PDF - FORCED ONE PAGE */
        @media print {
            .sidebar, .topbar, .print-btn, .back-btn, .logout { 
                display: none !important; 
            }
            .main-content { 
                margin-left: 0 !important; 
                padding: 0 !important; 
            }
            .registration-slip { 
                box-shadow: none; 
                padding: 8px;
                margin: 0;
                border-radius: 0;
                page-break-after: avoid;
                page-break-inside: avoid;
                break-inside: avoid;
            }
            body { 
                background: white; 
                margin: 0;
                padding: 0;
            }
            .info-item { width: 50%; }
            .info-label { width: 80px; }
            .courses-table { font-size: 8px; }
            .courses-table th, .courses-table td { padding: 2px; }
            .footer-note { font-size: 6px; }
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

    <div class="registration-slip" id="slipContainer">
        <div class="slip-header">
            <img src="images/utmlogo.png" alt="UTM Logo" class="logo-img">
            <h3>COURSE REGISTRATION SLIP</h3>
            <div class="university-name">Universiti Teknologi Malaysia</div>
        </div>
        
        <div class="info-section">
            <h4>STUDENT INFORMATION</h4>
            <div class="info-grid-2col">
                <div class="info-item"><span class="info-label">NAME:</span><span class="info-value"><?php echo htmlspecialchars($student_name); ?></span></div>
                <div class="info-item"><span class="info-label">MATRIC NO.:</span><span class="info-value"><?php echo htmlspecialchars($matrix_number); ?></span></div>
                <div class="info-item"><span class="info-label">IC/PASSPORT:</span><span class="info-value"><?php echo htmlspecialchars($ic_number); ?></span></div>
                <div class="info-item"><span class="info-label">PROGRAMME:</span><span class="info-value"><?php echo htmlspecialchars($programme); ?></span></div>
                <div class="info-item"><span class="info-label">YEAR:</span><span class="info-value"><?php echo htmlspecialchars($year); ?></span></div>
                <div class="info-item"><span class="info-label">EMAIL:</span><span class="info-value"><?php echo htmlspecialchars($email); ?></span></div>
                <div class="info-item"><span class="info-label">PHONE:</span><span class="info-value"><?php echo htmlspecialchars($phone); ?></span></div>
                <div class="info-item"><span class="info-label">ADDRESS:</span><span class="info-value"><?php echo htmlspecialchars(substr($address, 0, 35)); ?></span></div>
            </div>
        </div>
        
        <div class="info-section">
            <h4>REGISTRATION DETAILS</h4>
            <div class="info-grid-2col">
                <div class="info-item"><span class="info-label">SESSION:</span><span class="info-value"><?php echo htmlspecialchars($registration['session'] ?? '2025/2026 - Sem 2'); ?></span></div>
                <div class="info-item"><span class="info-label">SUBMITTED:</span><span class="info-value"><?php echo date('d-m-Y', strtotime($registration['submission_date'])); ?></span></div>
                <div class="info-item"><span class="info-label">APPROVED:</span><span class="info-value"><?php echo $registration['reviewed_at'] ? date('d-m-Y', strtotime($registration['reviewed_at'])) : '-'; ?></span></div>
                <div class="info-item"><span class="info-label">APPROVED BY:</span><span class="info-value"><?php echo htmlspecialchars($registration['reviewed_by'] ?? '-'); ?></span></div>
                <div class="info-item"><span class="info-label">STATUS:</span><span class="info-value" style="background:#d4edda; color:#155724; padding:2px 6px; border-radius:10px; font-size:9px;">APPROVED</span></div>
            </div>
        </div>
        
        <div class="info-section">
            <h4>REGISTERED COURSES</h4>
            <table class="courses-table">
                <thead><tr><th>#</th><th>CODE</th><th>COURSE TITLE</th><th>CR</th><th>SEC</th></tr></thead>
                <tbody>
                    <?php if (count($courses) > 0): ?>
                        <?php $counter = 1; foreach ($courses as $course): ?>
                        <tr>
                            <td><?php echo $counter++; ?></td>
                            <td><?php echo htmlspecialchars($course['subject_code']); ?></td>
                            <td><?php echo htmlspecialchars(substr($course['subject_name'], 0, 28)); ?></td>
                            <td><?php echo $course['credits']; ?></td>
                            <td><?php echo htmlspecialchars($course['section'] ?? 'TBD'); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center">No courses found</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <div class="total-credits">TOTAL CREDIT: <?php echo $total_credits_formatted; ?></div>
        </div>
        
        <div class="verification-section">
            <h4>VERIFICATION FROM ACADEMIC ADVISOR</h4>
            <div><span class="verification-label">VERIFIED BY:</span><span class="verification-value"><?php echo htmlspecialchars($registration['reviewed_by'] ?? '_________________'); ?></span></div>
            <div><span class="verification-label">DATE:</span><span class="verification-value"><?php echo $registration['reviewed_at'] ? date('d-m-Y', strtotime($registration['reviewed_at'])) : '_________________'; ?></span></div>
            <div><span class="verification-label">REMARKS:</span><span class="verification-value"><?php echo htmlspecialchars($registration['advisor_remarks'] ?? '-'); ?></span></div>
        </div>
        
        <div class="signature-line">
            <div class="signature-row">
                <div class="signature-item"><span>STUDENT SIGNATURE</span><div class="signature-line-dash"></div></div>
                <div class="signature-item"><span>ADVISOR SIGNATURE</span><div class="signature-line-dash"></div></div>
            </div>
        </div>
        
        <div class="footer-note">
            DATE: <?php echo date('d-m-Y'); ?> | PLEASE CHECK YOUR DETAILS. CORRECTIONS AT YOUR FACULTY.
        </div>
    </div>

    <div class="d-flex gap-3">
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
    (function() { if (localStorage.getItem('sidebarCollapsed') === 'true') { document.querySelector('.sidebar').classList.add('collapsed'); document.querySelector('.main-content').classList.add('expanded'); } })();

    document.getElementById('printBtn').addEventListener('click', function() {
        const element = document.getElementById('slipContainer');
        
        const originalText = this.innerHTML;
        this.innerHTML = '<i class="bi bi-hourglass-split"></i> Generating PDF...';
        this.disabled = true;
        
        const opt = {
            margin: [0.2, 0.2, 0.2, 0.2],
            filename: 'Registration_Slip_<?php echo $registration_id; ?>_<?php echo date('Y-m-d'); ?>.pdf',
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { scale: 2, letterRendering: true, useCORS: true },
            jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait' },
            pagebreak: { mode: 'avoid-all' }
        };
        
        html2pdf().set(opt).from(element).save().then(() => {
            this.innerHTML = originalText;
            this.disabled = false;
        });
    });
</script>
</body>
</html>