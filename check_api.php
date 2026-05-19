<?php
echo "<h1>API Check</h1>";
echo "<h2>Current directory: " . __DIR__ . "</h2>";

$files = [
    'api/config/database.php',
    'api/dashboard/student_stats.php',
    'api/registrations/period.php',
    'api/session.php'
];

foreach ($files as $file) {
    $full = __DIR__ . '/' . $file;
    if (file_exists($full)) {
        echo "<span style='color:green'>✓ Found: $file</span><br>";
    } else {
        echo "<span style='color:red'>✗ Missing: $file</span><br>";
    }
}

echo "<h2>Session:</h2>";
session_start();
echo "<pre>";
print_r($_SESSION);
echo "</pre>";
?>