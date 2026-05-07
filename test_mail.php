<?php
require_once __DIR__ . '/PHPMailer/src/Exception.php';
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {
    $mail->SMTPDebug  = 2;                // Shows full SMTP conversation
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'ariefiqmal2006@gmail.com';
    $mail->Password   = 'xill egye ginu ebig';   // your app password
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    $mail->setFrom('ariefiqmal2006@gmail.com', 'Haris Corp');
    $mail->addAddress('ariefiqmal2006@gmail.com'); // send to yourself to test
    $mail->Subject = 'PHPMailer Test';
    $mail->Body    = 'If you see this, email is working!';

    $mail->send();
    echo '<p style="color:green;">✅ Email sent successfully!</p>';

} catch (Exception $e) {
    echo '<p style="color:red;">❌ Email failed: ' . $mail->ErrorInfo . '</p>';
}