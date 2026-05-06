<?php
include("db.php");
session_start();

if(!isset($_SESSION['user_id'])){
    header("Location: index.html");
    exit();
}

$id = $_SESSION['user_id'];

$query = mysqli_query($conn,"
SELECT courses.course_name, registrations.status 
FROM registrations 
JOIN courses ON registrations.course_id = courses.id 
WHERE registrations.user_id='$id'
");
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<div class="container mt-5">
<h3>My Registration</h3>

<?php while($row = mysqli_fetch_assoc($query)){ ?>
    <p><?php echo $row['course_name']; ?> - <?php echo $row['status']; ?></p>
<?php } ?>

<a href="student_dashboard.php" class="btn btn-secondary mt-3">Back</a>
</div>