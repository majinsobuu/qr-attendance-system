<?php
require_once "../config/db.php";

$course_id = $_GET["course_id"];

// Get total sessions
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM sessions WHERE course_id = ?");
$stmt->execute([$course_id]);
$totalSessions = $stmt->fetch()["total"];

// Get student data
$stmt = $pdo->prepare("
    SELECT 
        a.matric_no,
        a.student_name,
        a.student_level,
        COUNT(a.attendance_id) as attended
    FROM attendance a
    JOIN sessions s ON a.session_id = s.session_id
    WHERE s.course_id = ?
    GROUP BY a.matric_no
");
$stmt->execute([$course_id]);

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=course_attendance.xlsx");

echo "Matric No\tName\tLevel\tAttended\tTotal\tPercentage\tEligibility\n";

while ($row = $stmt->fetch()) {
    $percentage = ($totalSessions > 0) ? ($row["attended"] / $totalSessions) * 100 : 0;
    $eligibility = ($percentage >= 75) ? "Eligible" : "Not Eligible";

    echo "{$row['matric_no']}\t{$row['student_name']}\t{$row['student_level']}\t{$row['attended']}\t{$totalSessions}\t" . round($percentage,2) . "%\t$eligibility\n";
}