<?php
session_start();
require_once "../config/db.php";

if (!isset($_SESSION["lecturer_id"])) {
    die("Access denied.");
}

if (!isset($_GET["course_id"])) {
    die("Course not specified.");
}

$course_id = $_GET["course_id"];

// Total sessions
$stmt = $pdo->prepare("
    SELECT COUNT(*) as total_sessions 
    FROM sessions 
    WHERE course_id = ?
");
$stmt->execute([$course_id]);
$totalSessions = $stmt->fetch()["total_sessions"];

// Get all students attendance
$stmt = $pdo->prepare("
    SELECT a.matric_no, 
    a.student_name,
    a.student_level,
    COUNT(a.attendance_id) as attended

    FROM attendance a
    JOIN sessions s ON a.session_id = s.session_id
    WHERE s.course_id = ?
    GROUP BY a.matric_no
");
$stmt->execute([$course_id]);
$students = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Attendance</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<?php include "navbar.php"; ?>
<div class="container">

    <h2>Attendance Summary</h2>
    
    <div class="card">
    <table border="1" cellpadding="10">
        <tr>
    <th>Matric No</th>
    <th>Student Name</th>   
    <th>Level</th>   
    <th>Classes Attended</th>
    <th>Total Classes</th>
    <th>Percentage</th>
    <th>Eligibility</th>
</tr>

<?php foreach ($students as $student): 
    $percentage = ($totalSessions > 0) 
    ? ($student["attended"] / $totalSessions) * 100 
    : 0;
    
    $eligible = ($percentage >= 75) ? "Eligible" : "Not Eligible";
    ?>

<tr>
    <td><?php echo $student["matric_no"]; ?></td>
    <td><?php echo $student["student_name"];?></td>
    <td><?php echo $student["student_level"];?></td>   
    <td><?php echo $student["attended"]; ?></td>
    <td><?php echo $totalSessions; ?></td>
    <td><?php echo round($percentage, 2) . "%"; ?></td>
    <td style="color: <?php echo ($eligible == 'Eligible') ? 'green' : 'red'; ?>">
        <?php echo $eligible; ?>
    </td>
</tr>

<?php endforeach; ?>

</table>
</div>

<br><br>
<a href="export_course_excel.php?course_id=<?php echo $course_id; ?>" class="button">
    Export to Excel
</a>
</div>

<script src="../assets/js/main.js"></script>
</body>
</html>