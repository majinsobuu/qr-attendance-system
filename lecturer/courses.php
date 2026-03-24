<?php
session_start();
require_once "../config/db.php";

if (!isset($_SESSION["lecturer_id"])) {
    die("Access denied.");
}

$lecturer_id = $_SESSION["lecturer_id"];

$stmt = $pdo->prepare("
    SELECT * FROM courses WHERE lecturer_id = ?
");
$stmt->execute([$lecturer_id]);
$courses = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Courses</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<?php include "navbar.php"; ?>
<div class="container">
    
    <h2>My Courses</h2>
    
    <a href="add_course.php">+ Add New Course</a><br><br>
    
    <div class="card">
    <table border="1" cellpadding="10">
        <tr>
    <th>Course Code</th>
    <th>Course Title</th>
    <th>Action</th>
</tr>

<?php foreach ($courses as $course): ?>
<tr>
    <td><?php echo $course["course_code"]; ?></td>
    <td><?php echo $course["course_title"]; ?></td>
    <td>
        <a href="course_dashboard.php?course_id=<?php echo $course["course_id"]; ?>">
            Manage
        </a>
    </td>
</tr>
<?php endforeach; ?>

</table>
</div>
</div>

<script src="../assets/js/main.js"></script>
</body>
</html>