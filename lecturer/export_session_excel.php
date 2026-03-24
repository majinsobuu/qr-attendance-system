<?php
require_once "../config/db.php";

$session_id = $_GET["session_id"];

$stmt = $pdo->prepare("
    SELECT * FROM attendance WHERE session_id = ?
");
$stmt->execute([$session_id]);

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=session_attendance.xlsx");

echo "Matric No\tName\tLevel\tLatitude\tLongitude\tDistance\n";

while ($row = $stmt->fetch()) {
    echo "{$row['matric_no']}\t{$row['student_name']}\t{$row['student_level']}\t{$row['student_lat']}\t{$row['student_lon']}\t{$row['distance']}\n";
}