<?php
session_start(); // Start session to track the user

$host = "sql207.infinityfree.com";
$user = "if0_41833955";
$pass = "PassFYP15"; // Recommendation: Move credentials to a protected config file
$db   = "if0_41833955_XXX";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed");
}

$name = $_POST['username']; 
$pass_input = $_POST['password'];

// Note: We fetch the hashed password and the role
$sql = "SELECT role, password FROM users WHERE name=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $name);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    // If using plain text (not recommended): if ($pass_input === $row['password'])
    // If using Hashing (Recommended): if (password_verify($pass_input, $row['password']))
    if ($pass_input === $row['password']) {
        $_SESSION['user_role'] = $row['role']; // Save to session
        echo $row['role'];
    } else {
        echo "invalid";
    }
} else {
    echo "invalid";
}

$conn->close();
?>