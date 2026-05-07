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
    <title>Course Attendance - QR Attend</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<?php include "navbar.php"; ?>
<div class="container">

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; margin-top: 1rem;">
        <h2 style="margin: 0;"><i class="fa-solid fa-clipboard-check" style="color: var(--accent-blue); margin-right: 8px;"></i> Attendance Summary</h2>
        <a href="export_course_excel.php?course_id=<?php echo $course_id; ?>" class="button button-success">
            <i class="fa-solid fa-file-excel"></i> Export (Excel)
        </a>
    </div>
    
    <div class="card" style="padding: 1.5rem;">
        <?php if (count($students) > 0): ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th><i class="fa-solid fa-id-card"></i> Matric No</th>
                            <th><i class="fa-solid fa-user"></i> Student Name</th>   
                            <th><i class="fa-solid fa-layer-group"></i> Level</th>   
                            <th><i class="fa-solid fa-check-double"></i> Classes Attended</th>
                            <th><i class="fa-solid fa-list-ol"></i> Total Classes</th>
                            <th><i class="fa-solid fa-percent"></i> Percentage</th>
                            <th><i class="fa-solid fa-scale-balanced"></i> Eligibility</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): 
                            $percentage = ($totalSessions > 0) 
                            ? ($student["attended"] / $totalSessions) * 100 
                            : 0;
                            
                            $eligible = ($percentage >= 75) ? "Eligible" : "Not Eligible";
                        ?>
                        <tr>
                            <td style="font-weight: 600; color: var(--accent-glow);"><?php echo htmlspecialchars($student["matric_no"]); ?></td>
                            <td><?php echo htmlspecialchars($student["student_name"]);?></td>
                            <td><?php echo htmlspecialchars($student["student_level"]);?></td>   
                            <td style="text-align: center; font-weight: 600;"><?php echo htmlspecialchars($student["attended"]); ?></td>
                            <td style="text-align: center; color: var(--text-muted);"><?php echo $totalSessions; ?></td>
                            <td>
                                <?php echo round($percentage, 2) . "%"; ?>
                            </td>
                            <td>
                                <?php if ($eligible == 'Eligible'): ?>
                                    <span style="color: var(--success); font-weight: 600;"><i class="fa-solid fa-check-circle"></i> Eligible</span>
                                <?php else: ?>
                                    <span style="color: var(--danger); font-weight: 600;"><i class="fa-solid fa-xmark-circle"></i> Not Eligible</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 2rem; color: var(--text-muted);">
                <i class="fa-solid fa-user-slash" style="font-size: 3rem; margin-bottom: 1rem; color: rgba(255,255,255,0.1);"></i>
                <p>No students have registered attendance yet.</p>
            </div>
        <?php endif; ?>
    </div>
    
    <div style="text-align: center; margin-top: 1rem;">
        <a href="course_dashboard.php?course_id=<?php echo $course_id; ?>" style="color: var(--text-muted);">
            <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>

</div>

<script src="../assets/js/main.js"></script>
</body>
</html>