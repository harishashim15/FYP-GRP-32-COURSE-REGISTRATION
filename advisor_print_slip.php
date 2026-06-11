<?php
date_default_timezone_set('Asia/Kuala_Lumpur');

session_start();

// Check if advisor is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

include("db_connect.php");

$advisor_id = $_SESSION['user_id'];

// Get registration_id from URL
$registration_id = isset($_GET['registration_id']) ? (int)$_GET['registration_id'] : (isset($_GET['id']) ? (int)$_GET['id'] : 0);

if (!$registration_id) {
    die("No registration ID provided.");
}

// Fetch registration details with student info
$query = "SELECT 
            cr.id as registration_id,
            cr.submission_date,
            cr.status,
            cr.advisor_remarks,
            cr.reviewed_at,
            cr.session,
            s.user_id as student_id,
            s.matrix_number,
            s.user_name as student_name,
            s.utm_email as student_email,
            s.phone,
            s.programme,
            s.year,
            s.semester,
            s.ic_number,
            s.address,
            u.user_name as advisor_name
          FROM course_registrations cr
          JOIN students s ON cr.student_id = s.user_id
          LEFT JOIN users u ON cr.reviewed_by = u.user_id
          WHERE cr.id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $registration_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$registration = mysqli_fetch_assoc($result);

if (!$registration) {
    die("Registration not found.");
}

// Fetch courses for this registration
$courses_query = "SELECT rc.subject_code, s.subject_name, s.credits, rc.section
                  FROM registration_courses rc
                  JOIN subjects s ON rc.subject_code = s.subject_code
                  WHERE rc.registration_id = ?";
$stmt = mysqli_prepare($conn, $courses_query);
mysqli_stmt_bind_param($stmt, "i", $registration_id);
mysqli_stmt_execute($stmt);
$courses_result = mysqli_stmt_get_result($stmt);
$courses = [];
$total_credits = 0;
while ($row = mysqli_fetch_assoc($courses_result)) {
    $courses[] = $row;
    $total_credits += $row['credits'];
}

$total_credits_formatted = str_pad($total_credits, 3, '0', STR_PAD_LEFT);

// Get matrix number for filename
$matrix_number = $registration['matrix_number'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Registration_Slip_<?php echo $matrix_number; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', 'Segoe UI', Arial, sans-serif;
        }
        
        body {
            background: white;
            padding: 0.15in;
        }
        
        .registration-slip {
            max-width: 100%;
            margin: 0 auto;
        }
        
        .slip-header {
            text-align: center;
            margin-bottom: 12px;
            padding-bottom: 6px;
            border-bottom: 2px solid #670019;
        }
        .slip-header img {
            width: 38px;
            margin-bottom: 3px;
        }
        .slip-header h3 {
            color: #670019;
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 2px;
        }
        .slip-header p {
            color: #666;
            font-size: 9px;
            margin: 0;
        }
        
        .section-title {
            color: #670019;
            font-size: 11px;
            font-weight: 600;
            margin: 8px 0 5px 0;
            padding-bottom: 3px;
            border-bottom: 1px solid #eee;
        }
        
        .info-row {
            margin-bottom: 3px;
            overflow: hidden;
            clear: both;
        }
        .info-label {
            float: left;
            width: 105px;
            font-size: 9px;
            color: #666;
            padding: 2px 0;
        }
        .info-value {
            margin-left: 105px;
            font-size: 9px;
            color: #333;
            font-weight: 500;
            padding: 2px 0;
            word-wrap: break-word;
        }
        
        .courses-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
            margin-top: 3px;
        }
        .courses-table th {
            background: #f5f5f5;
            padding: 5px 6px;
            text-align: left;
            border: 1px solid #ddd;
            color: #670019;
            font-size: 8px;
        }
        .courses-table td {
            padding: 4px 6px;
            border: 1px solid #ddd;
            font-size: 8px;
        }
        
        .total-credits {
            text-align: right;
            font-size: 9px;
            font-weight: 600;
            color: #670019;
            margin-top: 5px;
            padding-top: 3px;
            border-top: 1px solid #eee;
        }
        
        .verification-row {
            margin-bottom: 3px;
            overflow: hidden;
            clear: both;
        }
        .verification-label {
            float: left;
            width: 105px;
            font-size: 9px;
            color: #666;
        }
        .verification-value {
            margin-left: 105px;
            font-size: 9px;
            color: #333;
        }
        
        .footer {
            margin-top: 8px;
            text-align: center;
            font-size: 7px;
            color: #999;
        }
        
        /* Critical for one page PDF */
        @media print {
            body {
                padding: 0;
            }
            @page {
                size: A4;
                margin: 0.1in;
            }
            .registration-slip {
                page-break-after: avoid;
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="registration-slip" id="printSection">
        <div class="slip-header">
            <img src="images/logoutm.png" alt="UTM Logo">
            <h3>COURSE REGISTRATION SLIP</h3>
            <p>Universiti Teknologi Malaysia</p>
        </div>
        
        <div class="section-title">STUDENT INFORMATION</div>
        <div class="info-row"><div class="info-label">Name</div><div class="info-value"><?php echo htmlspecialchars($registration['student_name']); ?></div></div>
        <div class="info-row"><div class="info-label">Matric No.</div><div class="info-value"><?php echo htmlspecialchars($registration['matrix_number']); ?></div></div>
        <div class="info-row"><div class="info-label">IC/Passport</div><div class="info-value"><?php echo htmlspecialchars($registration['ic_number'] ?? 'Not provided'); ?></div></div>
        <div class="info-row"><div class="info-label">Programme</div><div class="info-value"><?php echo htmlspecialchars($registration['programme']); ?></div></div>
        <div class="info-row"><div class="info-label">Year</div><div class="info-value"><?php echo $registration['year']; ?></div></div>
        <div class="info-row"><div class="info-label">Email</div><div class="info-value"><?php echo htmlspecialchars($registration['student_email']); ?></div></div>
        <div class="info-row"><div class="info-label">Phone</div><div class="info-value"><?php echo htmlspecialchars($registration['phone']); ?></div></div>
        <div class="info-row"><div class="info-label">Address</div><div class="info-value"><?php echo htmlspecialchars($registration['address'] ?? 'Not provided'); ?></div></div>
        
        <div class="section-title">REGISTRATION DETAILS</div>
        <div class="info-row"><div class="info-label">Session</div><div class="info-value"><?php echo htmlspecialchars($registration['session'] ?? '2025/2026 - Semester 2'); ?></div></div>
        <div class="info-row"><div class="info-label">Submitted Date</div><div class="info-value"><?php echo date('d-m-Y h:i A', strtotime($registration['submission_date'])); ?></div></div>
        <div class="info-row"><div class="info-label">Approved Date</div><div class="info-value"><?php echo $registration['reviewed_at'] ? date('d-m-Y h:i A', strtotime($registration['reviewed_at'])) : '-'; ?></div></div>
        <div class="info-row"><div class="info-label">Approved By</div><div class="info-value"><?php echo htmlspecialchars($registration['advisor_name'] ?? '-'); ?></div></div>
        <div class="info-row"><div class="info-label">Status</div><div class="info-value"><span style="background:#d4edda; color:#155724; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600;"><?php echo strtoupper($registration['status']); ?></span></div></div>
        
        <div class="section-title">REGISTERED COURSES</div>
        <table class="courses-table">
            <thead>
                <tr><th>#</th><th>Course Code</th><th>Course Name</th><th>Credits</th><th>Section</th></tr>
            </thead>
            <tbody>
                <?php if (count($courses) > 0): ?>
                    <?php $counter = 1; foreach ($courses as $course): ?>
                    <tr>
                        <td><?php echo $counter++; ?></td>
                        <td><strong><?php echo htmlspecialchars($course['subject_code']); ?></strong></td>
                        <td><?php echo htmlspecialchars($course['subject_name']); ?></strong></td>
                        <td><?php echo $course['credits']; ?></td>
                        <td><?php echo htmlspecialchars($course['section'] ?? 'TBD'); ?></strong></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <td><td colspan="5" style="text-align: center;">No courses found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        <div class="total-credits">Total Credit Hours: <?php echo $total_credits_formatted; ?></div>
        
        <div class="section-title">VERIFICATION FROM ACADEMIC ADVISOR</div>
        <div class="verification-row"><div class="verification-label">Verified By</div><div class="verification-value"><?php echo htmlspecialchars($registration['advisor_name'] ?? '_________________'); ?></div></div>
        <div class="verification-row"><div class="verification-label">Date Verified</div><div class="verification-value"><?php echo $registration['reviewed_at'] ? date('d-m-Y', strtotime($registration['reviewed_at'])) : '_________________'; ?></div></div>
        <div class="verification-row"><div class="verification-label">Remarks</div><div class="verification-value"><?php echo htmlspecialchars($registration['advisor_remarks'] ?? '-'); ?></div></div>
        
        <div class="footer">DATE: <?php echo date('d-m-Y'); ?> | This is an official registration slip. Please keep for your records.</div>
    </div>

    <script>
        // Auto print when page loads - EXACT same as student version
        window.onload = function() {
            window.print();
            window.onafterprint = function() {
                window.close();
            };
        };
    </script>
</body>
</html>