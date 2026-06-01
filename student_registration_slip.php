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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Registration Slip - UTM Student</title>
    <link rel="icon" type="image/png" href="images/logoWebsite.png"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background: #f8f6f4; overflow-x: hidden; }
        
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
        
        .page-header { margin-bottom: 30px; }
        .page-header h2 { font-size: 34px; font-weight: 700; color: #670019; }
        
        /* Registration Slip Styles - PDF Optimized */
        .registration-slip {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0px 4px 15px rgba(0,0,0,0.05);
            margin-bottom: 30px;
            max-width: 100%;
            overflow-x: auto;
        }
        
        .slip-header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #670019;
        }
        
        .slip-header .logo-img {
            max-width: 60px;
            margin-bottom: 10px;
        }
        
        .slip-header h3 {
            color: #670019;
            font-weight: 700;
            font-size: 18px;
            margin-bottom: 3px;
        }
        
        .slip-header .university-name {
            color: #333;
            font-size: 12px;
            font-weight: 500;
        }
        
        .info-section {
            margin-bottom: 20px;
        }
        
        .info-section h4 {
            color: #670019;
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid #ddd;
        }
        
        /* Two column layout for PDF */
        .info-row {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 8px;
            border-bottom: 1px solid #f0f0f0;
            padding: 6px 0;
        }
        
        .info-label {
            width: 30%;
            font-size: 12px;
            color: #888;
            font-weight: 500;
        }
        
        .info-value {
            width: 70%;
            font-size: 12px;
            color: #333;
            font-weight: 500;
        }
        
        .courses-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }
        .courses-table th {
            background: #f8f6f4;
            padding: 8px 6px;
            text-align: left;
            color: #670019;
            font-weight: 600;
            border: 1px solid #ddd;
        }
        .courses-table td {
            padding: 6px;
            border: 1px solid #ddd;
        }
        
        .total-credits {
            text-align: right;
            margin-top: 10px;
            padding-top: 8px;
            border-top: 1px solid #ddd;
            font-weight: 600;
            color: #670019;
            font-size: 12px;
        }
        
        .verification-section {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
        }
        
        .verification-row {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 10px;
        }
        
        .verification-label {
            width: 25%;
            font-size: 11px;
            color: #888;
        }
        
        .verification-value {
            width: 75%;
            font-size: 12px;
            color: #333;
            font-weight: 500;
        }
        
        .signature-line {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px dashed #ccc;
        }
        
        .signature-row {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
        }
        
        .signature-item {
            text-align: center;
            width: 45%;
        }
        
        .signature-item span {
            font-size: 11px;
            color: #888;
        }
        
        .signature-line-dash {
            margin-top: 5px;
            border-top: 1px dashed #999;
            width: 100%;
        }
        
        .footer-note {
            margin-top: 20px;
            font-size: 8px;
            color: #999;
            text-align: center;
            line-height: 1.3;
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
        
        /* Print/PDF specific styles */
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
                padding: 10px;
                margin: 0;
                border-radius: 0;
            }
            body { 
                background: white; 
                margin: 0;
                padding: 0;
            }
            .info-label { width: 25%; }
            .info-value { width: 75%; }
            .courses-table { font-size: 9px; }
            .courses-table th, .courses-table td { padding: 4px; }
            .footer-note { font-size: 7px; }
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
            <img src="images/utmlogo.png" alt="UTM Logo" class="logo-img" style="width: 55px;">
            <h3>COURSE REGISTRATION SLIP</h3>
            <div class="university-name">Universiti Teknologi Malaysia</div>
        </div>
        
        <div class="info-section">
            <h4>STUDENT INFORMATION</h4>
            <div class="info-row">
                <div class="info-label">NAME</div>
                <div class="info-value"><?php echo htmlspecialchars($student_name); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">MATRIC NO.</div>
                <div class="info-value"><?php echo htmlspecialchars($matrix_number); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">IC/PASSPORT</div>
                <div class="info-value"><?php echo htmlspecialchars($ic_number); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">PROGRAMME</div>
                <div class="info-value"><?php echo htmlspecialchars($programme); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">YEAR</div>
                <div class="info-value"><?php echo htmlspecialchars($year); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">EMAIL</div>
                <div class="info-value"><?php echo htmlspecialchars($email); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">PHONE</div>
                <div class="info-value"><?php echo htmlspecialchars($phone); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">ADDRESS</div>
                <div class="info-value"><?php echo nl2br(htmlspecialchars($address)); ?></div>
            </div>
        </div>
        
        <div class="info-section">
            <h4>REGISTRATION DETAILS</h4>
            <div class="info-row">
                <div class="info-label">SESSION/SEMESTER</div>
                <div class="info-value"><?php echo htmlspecialchars($registration['session'] ?? '2025/2026 - Semester 2'); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">SUBMITTED DATE</div>
                <div class="info-value"><?php echo date('d-M-Y', strtotime($registration['submission_date'])); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">APPROVED DATE</div>
                <div class="info-value"><?php echo $registration['reviewed_at'] ? date('d-M-Y', strtotime($registration['reviewed_at'])) : '-'; ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">APPROVED BY</div>
                <div class="info-value"><?php echo htmlspecialchars($registration['reviewed_by'] ?? '-'); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">STATUS</div>
                <div class="info-value"><span class="status-badge" style="background:#d4edda; color:#155724; padding:2px 10px; border-radius:15px;">APPROVED</span></div>
            </div>
        </div>
        
        <div class="info-section">
            <h4>REGISTERED COURSES</h4>
            <table class="courses-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>CODE</th>
                        <th>COURSE TITLE</th>
                        <th>CR</th>
                        <th>SEC</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($courses) > 0): ?>
                        <?php $counter = 1; foreach ($courses as $course): ?>
                        <tr>
                            <td><?php echo $counter++; ?></td>
                            <td><?php echo htmlspecialchars($course['subject_code']); ?></td>
                            <td><?php echo htmlspecialchars($course['subject_name']); ?></td>
                            <td><?php echo $course['credits']; ?></td>
                            <td><?php echo htmlspecialchars($course['section'] ?? 'TBD'); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center">No courses found</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <div class="total-credits">TOTAL CREDIT REGISTERED: <?php echo $total_credits_formatted; ?></div>
        </div>
        
        <div class="verification-section">
            <h4>VERIFICATION FROM ACADEMIC ADVISOR</h4>
            <div class="verification-row">
                <div class="verification-label">VERIFIED BY</div>
                <div class="verification-value"><?php echo htmlspecialchars($registration['reviewed_by'] ?? '_________________________'); ?></div>
            </div>
            <div class="verification-row">
                <div class="verification-label">DATE VERIFIED</div>
                <div class="verification-value"><?php echo $registration['reviewed_at'] ? date('d-M-Y', strtotime($registration['reviewed_at'])) : '_________________________'; ?></div>
            </div>
            <div class="verification-row">
                <div class="verification-label">REMARKS</div>
                <div class="verification-value"><?php echo htmlspecialchars($registration['advisor_remarks'] ?? '-'); ?></div>
            </div>
        </div>
        
        <div class="signature-line">
            <div class="signature-row">
                <div class="signature-item">
                    <span>STUDENT SIGNATURE</span>
                    <div class="signature-line-dash"></div>
                </div>
                <div class="signature-item">
                    <span>ADVISOR SIGNATURE</span>
                    <div class="signature-line-dash"></div>
                </div>
            </div>
        </div>
        
        <div class="footer-note">
            <strong>DATE: <?php echo date('d-M-Y'); ?></strong><br>
            # PLEASE CHECK YOUR NAME AND ADDRESS. CORRECTIONS CAN BE MADE AT YOUR FACULTY/SCHOOL.
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
        
        // Show loading
        const originalText = this.innerHTML;
        this.innerHTML = '<i class="bi bi-hourglass-split"></i> Generating PDF...';
        this.disabled = true;
        
        const opt = {
            margin: [0.5, 0.5, 0.5, 0.5],
            filename: 'Registration_Slip_<?php echo $registration_id; ?>_<?php echo date('Y-m-d'); ?>.pdf',
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