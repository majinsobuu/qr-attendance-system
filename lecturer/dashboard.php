<?php
session_start();
require_once "../config/db.php";
require_once "../phpqrcode/qrlib.php";

if (!isset($_SESSION["lecturer_id"])) {
    header("Location: login.php");
    exit;
}

$message = "";
$qrPath = "";


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - QR Attend</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<?php include "navbar.php"; ?>
<div class="container">
    <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 2rem; margin-top: 2rem;">
        <div style="width: 60px; height: 60px; background: linear-gradient(135deg, var(--accent-blue), var(--accent-purple)); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: #fff; box-shadow: 0 4px 15px rgba(14, 165, 233, 0.4);">
            <i class="fa-solid fa-user-tie"></i>
        </div>
        <div>
            <h1 style="margin-bottom: 0;">Welcome, <?php echo $_SESSION["lecturer_name"]; ?>!</h1>
            <p style="margin-bottom: 0;">Here is your attendance overview.</p>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
        <div class="card" style="text-align: center; padding: 3rem 2rem;">
            <div style="font-size: 3rem; color: var(--accent-glow); margin-bottom: 1rem;">
                <i class="fa-solid fa-chalkboard-user"></i>
            </div>
            <h3 style="margin-bottom: 0.5rem;">Manage Courses</h3>
            <p style="font-size: 0.9rem;">View, add, or edit your courses and generate attendance QR codes.</p>
            <a class="button button-primary" href="courses.php" style="margin-top: 1rem; width: 100%;"><i class="fa-solid fa-arrow-right"></i> Go to Courses</a>
        </div>
        
        <div class="card" style="text-align: center; padding: 3rem 2rem;">
            <div style="font-size: 3rem; color: var(--success); margin-bottom: 1rem;">
                <i class="fa-solid fa-plus-circle"></i>
            </div>
            <h3 style="margin-bottom: 0.5rem;">Add New Course</h3>
            <p style="font-size: 0.9rem;">Create a new course to start tracking student attendance today.</p>
            <a class="button button-success" href="add_course.php" style="margin-top: 1rem; width: 100%;"><i class="fa-solid fa-plus"></i> Add Course</a>
        </div>
    </div>
    
    <div style="text-align: center; margin-top: 2rem;">
        <a class="button button-danger" href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
    </div>
</div>

<script>
function createSession() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            document.getElementById("lat").value = position.coords.latitude;
            document.getElementById("lon").value = position.coords.longitude;
            document.getElementById("sessionForm").submit();
        });
    } else {
        alert("Geolocation not supported.");
    }
}
</script>
<script src="../assets/js/main.js"></script>
</body>
</html>