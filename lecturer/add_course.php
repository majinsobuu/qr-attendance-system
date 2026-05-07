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
    <title>Add Course - QR Attend</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include "navbar.php"; ?>
    
    <div class="container">
        <div style="max-width: 500px; margin: 2rem auto;">
            
            <div style="text-align: center; margin-bottom: 2rem;">
                <div style="font-size: 2.5rem; color: var(--success); margin-bottom: 0.5rem;">
                    <i class="fa-solid fa-folder-plus"></i>
                </div>
                <h2>Add a New Course</h2>
                <p>Register a new course code and title.</p>
            </div>

            <?php if ($message != ""): ?>
                <div class="badge" style="background: rgba(16, 185, 129, 0.2); color: var(--success); text-align: center; margin-bottom: 1.5rem; display: block; padding: 0.8rem;">
                    <i class="fa-solid fa-circle-check"></i> <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <form method="POST">
                    <label for="course_code"><i class="fa-solid fa-barcode" style="margin-right: 5px;"></i> Course Code</label>
                    <input type="text" name="course_code" id="course_code" placeholder="e.g. CSC101" required>
                    
                    <label for="course_title" style="margin-top: 0.5rem;"><i class="fa-solid fa-heading" style="margin-right: 5px;"></i> Course Title</label>
                    <input type="text" name="course_title" id="course_title" placeholder="e.g. Introduction to Computer Science" required>
                    
                    <button class="button button-success" type="submit" style="margin-top: 1.5rem; width: 100%;">
                        <i class="fa-solid fa-save"></i> Save Course
                    </button>
                </form>
            </div>
            
            <div style="text-align: center; margin-top: 1.5rem;">
                <a href="courses.php" style="color: var(--text-muted);"><i class="fa-solid fa-arrow-left"></i> Back to Courses</a>
            </div>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
</body>
</html>