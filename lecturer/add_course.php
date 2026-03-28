<?php
session_start();
require_once "../config/db.php";

if (!isset($_SESSION["lecturer_id"])) {
    die("Access denied.");
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $course_code = trim($_POST["course_code"]);
    $course_title = trim($_POST["course_title"]);
    $lecturer_id = $_SESSION["lecturer_id"];

    if (empty($course_code) || empty($course_title)) {
        $message = "All fields are required.";
    } else {

        $stmt = $pdo->prepare("
            INSERT INTO courses (lecturer_id, course_code, course_title)
            VALUES (?, ?, ?)
        ");

        $stmt->execute([$lecturer_id, $course_code, $course_title]);

        $message = "Course added successfully.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Course</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<h2>Add Course</h2>

<?php include "navbar.php"; ?>
<div class="container">
<p><?php echo $message; ?></p>
<div class="card">
<form method="POST">
    <input type="text" name="course_code" placeholder="Course Code"><br><br>
    <input type="text" name="course_title" placeholder="Course Title"><br><br>
    <button  class="button" type="submit">Add Course</button>
</form>
</div>
</div>
<script src="../assets/js/main.js"></script>
</body>
</html>