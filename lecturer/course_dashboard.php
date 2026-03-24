<?php
session_start();
require_once "../config/db.php";

if (!isset($_SESSION["lecturer_id"])) {
    die("Access denied.");
}

if (!isset($_GET["course_id"])) {
    die("No course selected.");
}

$course_id = $_GET["course_id"];

// Get course info
$stmt = $pdo->prepare("SELECT * FROM courses WHERE course_id = ?");
$stmt->execute([$course_id]);
$course = $stmt->fetch();

if (!$course) {
    die("Course not found.");
}

// Get sessions for this course
$stmt = $pdo->prepare("
    SELECT * FROM sessions 
    WHERE course_id = ?
    ORDER BY expires_at ASC
");
$stmt->execute([$course_id]);
$sessions = $stmt->fetchAll();
$count = 1;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<?php include "navbar.php"; ?>


    <div class="container">
        <div class="card">
        <div class="nav-row">
            <div>
                <h2><?php echo $course["course_code"]; ?> - <?php echo $course["course_title"]; ?></h2>
      <div style="display:flex; gap:0.5rem; flex-wrap:wrap; align-items:center;">
        <a class="button" href="create_session.php?course_id=<?php echo $course_id; ?>">+ Create New Session</a>
        <a class="button" href="course_attendance.php?course_id=<?php echo $course_id; ?>">View Attendance</a>
        <button class="button" data-theme-toggle>Dark Mode</button>
      </div>
    </div>

    <div style="display:flex; align-items:center; gap:0.5rem; flex-wrap:wrap;">
        <br><br>
        
      <!-- <span class="status-pill" data-screen-size>Viewport</span> -->
      <!-- <span class="status-pill" data-timer>--:--:--</span> -->
</div>

  <!-- <div style="margin-top:1rem; display:flex; flex-wrap:wrap; gap:0.75rem; align-items:center; justify-content:space-between;">
      <label style="color:black; font-size:1.0rem;">Search Sessions:</label>
          <input data-search type="search" placeholder="Session / Date / status" style="padding:0.45rem 0.6rem; margin-left:0.25rem; border-radius:999px; border:1px solid rgba(148,163,184,0.4); background:rgba(15,23,42,0.7); color:#e2e8f0; min-width:220px;"> -->
    <p style="color:#94a3b8;">Showing sessions sorted by date</p>
</div>
<div class="container">
  <div class="table-responsive">
      <table>
          <thead>
              <tr>
                  <th>Session</th>
                  <th>Date</th>
                  <th>Action</th>
        </tr>
    </thead>
      <tbody>
          <?php foreach ($sessions as $session): ?>
            <tr>
        <td><?php echo $count++; ?></td>
        <td>
            <?php 
    // Convert the database string to a PHP timestamp
    $timestamp = strtotime($session["expires_at"]);
    
    $expirationTime = strtotime($session["expires_at"]);
    
    
    // Format the date and time separately
    $datePart = date("Y-m-d", $timestamp); // e.g., 2026-03-23
    $timePart = date("H:i:s", $timestamp); // e.g., 16:10:23
    

    // Check if the current time is past the expiration time
    if (time() > $expirationTime) {
        echo "Created on ", $datePart , " Expired at " . $timePart;
    } else {
        echo "Created on ", $datePart , " Expires at " . $timePart;
        }
        
        
        
        ?>
</td>
<td>
    <a href="view_attendance.php?session_id=<?php echo $session["session_id"]; ?>">
        View Attendance
    </a>
</td>
</tr>
<?php endforeach; ?>

</tbody>
</table>
</div>
</div>
</div>
</div>
          </div>
<script src="../assets/js/main.js"></script>
</body>
</html>