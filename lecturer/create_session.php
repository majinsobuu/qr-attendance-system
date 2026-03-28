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
$message = "";

// End session action from the End Session button (redirect to course dashboard)
if (isset($_GET["end_session_id"])) {
    $end_session_id = intval($_GET["end_session_id"]);

    $stmt = $pdo->prepare("UPDATE sessions SET expires_at = NOW() WHERE session_id = ? AND lecturer_id = ?");
    $stmt->execute([$end_session_id, $_SESSION["lecturer_id"]]);

    header("Location: course_dashboard.php?course_id=" . $course_id);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $lecturer_lat = $_POST["lat"];
    $lecturer_lon = $_POST["lon"];
    $radius = $_POST["radius"];

    if (empty($lecturer_lat) || empty($lecturer_lon)) {
        $message = "Location is required.";
    } else {

        $stmt = $pdo->prepare("
            INSERT INTO sessions 
            (course_id, session_token, lecturer_id, lecturer_lat, lecturer_lon, radius, expires_at)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        $lecturer_id = $_SESSION["lecturer_id"];
        $token = bin2hex(random_bytes(16));
        $expiry = date("Y-m-d H:i:s", strtotime("+2 hours"));

        $stmt->execute([
            $course_id,
            $token,
            $lecturer_id,
            $lecturer_lat,
            $lecturer_lon,
            $radius,
            $expiry
        ]);

        $session_id = $pdo->lastInsertId();

        include "../phpqrcode/qrlib.php";

            $attendanceURL = "http://localhost/attendance_system/student/mark_attendance.php?session_id=" . $session_id;
            $viewattendanceURL = "http://localhost/attendance_system/lecturer/view_attendance.php?session_id=" . $session_id; 
            // Create QR code file
            $qrFile = "../qrcodes/session_" . $session_id . ".png";

            QRcode::png($attendanceURL, $qrFile, QR_ECLEVEL_L, 5);

            // Display QR
            $message = "
                <p>Session created successfully.</p>
                <p>Scan this QR code:</p>
                <img src='../qrcodes/session_$session_id.png'><br><br>
                <p>Or open link:</p>
                <a href='$attendanceURL'>$attendanceURL</a><br><br>
                <form method='GET' action='create_session.php'>
                    <input type='hidden' name='course_id' value='$course_id'>
                    <input type='hidden' name='end_session_id' value='$session_id'>
                    <button class='button' type='submit'>End Session and Go to Course Dashboard</button>
                </form>
                <p><a class='button' target='_blank' href='$viewattendanceURL'>View Attendance Records</a></p>

            ";
        }
}
echo "<script>showToast('Session Created Successfully');</script>";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Session</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<?php include "navbar.php"; ?>
<h2>Create Attendance Session</h2>
<p><?php echo $message; ?></p>
<div class="container">
    <div class="card">
            <form method="POST" id="sessionForm"
                onsubmit="event.preventDefault(); createSession();">
                
                <input type="hidden" name="lat" id="lat">
                <input type="hidden" name="lon" id="lon">
                
                <label>Allowed Radius (meters):</label><br>
                <input type="number" name="radius" value="50"><br><br>
                
                <button class="button" type="submit">Create Session</button>
        </form>
    </div>
</div>

<script>
    function createSession() {

        if (!navigator.geolocation) {
        alert("Geolocation not supported.");
        return;
    }

    navigator.geolocation.getCurrentPosition(
        function(position) {
            
            document.getElementById("lat").value =
            position.coords.latitude;
            
            document.getElementById("lon").value =
                position.coords.longitude;
                
            document.getElementById("sessionForm").submit();
        },
        function() {
            alert("Location access denied.");
        }
    );
}
</script>
<script src="../assets/js/main.js"></script>
</body>
</html>