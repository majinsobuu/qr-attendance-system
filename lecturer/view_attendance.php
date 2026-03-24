<?php
session_start();
require_once "../config/db.php";

if (!isset($_SESSION["lecturer_id"])) {
    die("Access denied.");
}

if (!isset($_GET["session_id"])) {
    die("Session not specified.");
}

$session_id = $_GET["session_id"];



// Fetch attendance records
$stmt = $pdo->prepare("
    SELECT * FROM attendance 
    WHERE session_id = ?
    ORDER BY time_in DESC
");
$stmt->execute([$session_id]);
$records = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Session Attendance</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<?php include "navbar.php"; ?>
<div class="container">
    <div class="card">
    <h2>Session Attendance Records</h2>
    
    <table border="1" cellpadding="10">
        <tr>
    <th>Matric No</th>
    <th>Name</th>
    <th>Level</th>
    <th>Latitude</th>
    <th>Longitude</th>
    <th>Distance (m)</th>
    <th>Date & Time</th>
</tr>

<?php foreach ($records as $row): ?>
<tr>
    <td><?php echo $row["matric_no"]; ?></td>
    <td><?php echo $row["student_name"]; ?></td>
    <td><?php echo $row["student_level"]; ?></td>
    <td><?php echo $row["student_lat"]; ?></td>
    <td><?php echo $row["student_lon"]; ?></td>
    <td><?php echo round($row["distance"], 2); ?></td>
    <td><?php echo $row["time_in"]; ?></td>
</tr>
<?php endforeach; ?>

</table>
</div>

<br><br>
<a href="export_session_excel.php?session_id=<?php echo $session_id; ?>" class="button">
    Export Session (Excel)
</a>
</div>
<script src="../assets/js/main.js"></script>
</body>
</html>