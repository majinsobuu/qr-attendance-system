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
    <title><?php echo $course["course_code"]; ?> - Course Dashboard - QR Attend</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<?php include "navbar.php"; ?>

<div class="container">
    <div class="card" style="margin-bottom: 2rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
            <div>
                <h2 style="margin-bottom: 0.5rem; color: var(--accent-glow);">
                    <i class="fa-solid fa-graduation-cap"></i> <?php echo htmlspecialchars($course["course_code"]); ?>
                </h2>
                <h4 style="color: var(--text-muted); margin: 0; font-weight: 500;">
                    <?php echo htmlspecialchars($course["course_title"]); ?>
                </h4>
            </div>
            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center;">
                <?php
                    // Check if there is a live session
                    $live_session_id = null;
                    foreach ($sessions as $s) {
                        if (strtotime($s["expires_at"]) > time()) {
                            $live_session_id = $s["session_id"];
                            break;
                        }
                    }
                ?>
                
                <?php if ($live_session_id): ?>
                    <a class="button button-warning" href="active_session.php?session_id=<?php echo $live_session_id; ?>" style="color: #000; box-shadow: 0 4px 15px rgba(245, 158, 11, 0.4);">
                        <i class="fa-solid fa-satellite-dish fa-beat"></i> Resume Active Session
                    </a>
                <?php else: ?>
                    <a class="button button-success" href="create_session.php?course_id=<?php echo $course_id; ?>">
                        <i class="fa-solid fa-plus-circle"></i> Create Session
                    </a>
                <?php endif; ?>
                
                <a class="button button-primary" href="course_attendance.php?course_id=<?php echo $course_id; ?>">
                    <i class="fa-solid fa-list-check"></i> Overall Attendance
                </a>
            </div>
        </div>
    </div>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <h3 style="margin: 0;"><i class="fa-solid fa-clock-rotate-left"></i> Recent Sessions</h3>
        <span class="badge"><i class="fa-solid fa-layer-group"></i> Total: <?php echo count($sessions); ?></span>
    </div>

    <div class="card" style="padding: 1.5rem;">
        <?php if (count($sessions) > 0): ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 80px;"><i class="fa-solid fa-hashtag"></i> No.</th>
                            <th><i class="fa-solid fa-calendar-days"></i> Session Details</th>
                            <th style="width: 180px; text-align: center;"><i class="fa-solid fa-wrench"></i> Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sessions as $session): ?>
                        <tr>
                            <td style="font-weight: bold; color: var(--text-muted);"><?php echo $count++; ?></td>
                            <td>
                                <?php 
                                    $timestamp = strtotime($session["expires_at"]);
                                    $expirationTime = strtotime($session["expires_at"]);
                                    
                                    $datePart = date("Y-m-d", $timestamp);
                                    $timePart = date("H:i:s", $timestamp);
                                    
                                    if (time() > $expirationTime) {
                                        echo "<span style='color: var(--text-main); font-weight: 500;'>Created on " . $datePart . "</span>";
                                        echo "<br><span style='color: var(--danger); font-size: 0.85rem;'><i class='fa-solid fa-circle-xmark'></i> Expired at " . $timePart . "</span>";
                                    } else {
                                        echo "<span style='color: var(--text-main); font-weight: 500;'>Created on " . $datePart . "</span>";
                                        echo "<br><span style='color: var(--success); font-size: 0.85rem;'><i class='fa-solid fa-circle-check'></i> Active until " . $timePart . "</span>";
                                    }
                                ?>
                            </td>
                            <td style="text-align: center;">
                                <a class="button button-sm button-primary" href="view_attendance.php?session_id=<?php echo $session["session_id"]; ?>">
                                    <i class="fa-solid fa-eye"></i> View Records
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 2rem; color: var(--text-muted);">
                <i class="fa-solid fa-clipboard-list" style="font-size: 3rem; margin-bottom: 1rem; color: rgba(255,255,255,0.1);"></i>
                <p>No attendance sessions created yet.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="../assets/js/main.js"></script>
</body>
</html>