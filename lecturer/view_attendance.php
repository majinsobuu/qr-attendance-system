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
    <title>Session Attendance - QR Attend</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<?php include "navbar.php"; ?>
<div class="container">
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; margin-top: 1rem;">
        <h2 style="margin: 0;"><i class="fa-solid fa-users-viewfinder" style="color: var(--accent-blue); margin-right: 8px;"></i> Session Attendance</h2>
        <a href="export_session_excel.php?session_id=<?php echo $session_id; ?>" class="button button-success">
            <i class="fa-solid fa-file-excel"></i> Export (Excel)
        </a>
    </div>

    <div class="card" style="padding: 1.5rem;">
        <?php if (count($records) > 0): ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th><i class="fa-solid fa-id-card"></i> Matric No</th>
                            <th><i class="fa-solid fa-user"></i> Name</th>
                            <th><i class="fa-solid fa-layer-group"></i> Level</th>
                            <th><i class="fa-solid fa-location-dot"></i> Lat</th>
                            <th><i class="fa-solid fa-location-dot"></i> Lon</th>
                            <th><i class="fa-solid fa-ruler"></i> Distance (m)</th>
                            <th><i class="fa-solid fa-clock"></i> Date & Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($records as $row): ?>
                        <tr>
                            <td style="font-weight: 600; color: var(--accent-glow);"><?php echo htmlspecialchars($row["matric_no"]); ?></td>
                            <td><?php echo htmlspecialchars($row["student_name"]); ?></td>
                            <td><?php echo htmlspecialchars($row["student_level"]); ?></td>
                            <td style="font-size: 0.85em;"><?php echo htmlspecialchars($row["student_lat"]); ?></td>
                            <td style="font-size: 0.85em;"><?php echo htmlspecialchars($row["student_lon"]); ?></td>
                            <td>
                                <?php 
                                    $dist = round($row["distance"], 2);
                                    // Usually radius is 50. Highlight if close
                                    if ($dist <= 20) {
                                        echo "<span style='color: var(--success); font-weight: 600;'>" . $dist . "m</span>";
                                    } else if ($dist <= 50) {
                                        echo "<span style='color: var(--warning); font-weight: 600;'>" . $dist . "m</span>";
                                    } else {
                                        echo "<span style='color: var(--danger); font-weight: 600;'>" . $dist . "m</span>";
                                    }
                                ?>
                            </td>
                            <td style="font-size: 0.9em;"><?php echo htmlspecialchars($row["time_in"]); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 2rem; color: var(--text-muted);">
                <i class="fa-solid fa-user-slash" style="font-size: 3rem; margin-bottom: 1rem; color: rgba(255,255,255,0.1);"></i>
                <p>No students have marked attendance for this session yet.</p>
            </div>
        <?php endif; ?>
    </div>
    
    <div style="text-align: center; margin-top: 1rem;">
        <a href="javascript:history.back()" style="color: var(--text-muted);">
            <i class="fa-solid fa-arrow-left"></i> Back
        </a>
    </div>
</div>

<script src="../assets/js/main.js"></script>
</body>
</html>