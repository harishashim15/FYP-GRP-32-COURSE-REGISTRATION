<?php
include("db.php");
$result = mysqli_query($conn,"SELECT * FROM courses");
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<div class="container mt-5">
<h3>Courses</h3>

<?php while($row = mysqli_fetch_assoc($result)){ ?>
    <div class="card p-3 mb-2">
        <h5><?php echo $row['course_name']; ?></h5>
        <p><?php echo $row['course_code']; ?></p>

        <a href="register_course.php?id=<?php echo $row['id']; ?>" class="btn btn-success">Register</a>
    </div>
<?php } ?>

</div>