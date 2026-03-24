<?php
require_once "../config/db.php";

if (!isset($_GET["session_id"])) {
    die("Invalid session.");
}

$session_id = $_GET["session_id"];

// Fetch session details
$stmt = $pdo->prepare("SELECT * FROM sessions WHERE session_id = ?");
$stmt->execute([$session_id]);
$session = $stmt->fetch();

if (!$session) {
    die("Session not found.");
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $matric = trim($_POST["matric"]);
    $student_name = trim($_POST["student_name"]);
    $student_level = trim($_POST["student_level"]);
    $student_lat = $_POST["lat"];
    $student_lon = $_POST["lon"];
    $device_id = $_POST["device_id"];

    if (empty($matric) || empty($student_name) || empty($student_level)) {
        $message = "All fields are required.";
    }
    elseif (empty($student_lat) || empty($student_lon)) {
        $message = "Location access required.";
    }
    else {

        function calculateDistance($lat1, $lon1, $lat2, $lon2) {
            $earth_radius = 6371000;

            $dLat = deg2rad($lat2 - $lat1);
            $dLon = deg2rad($lon2 - $lon1);

            $a = sin($dLat/2) * sin($dLat/2) +
                 cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
                 sin($dLon/2) * sin($dLon/2);

            $c = 2 * atan2(sqrt($a), sqrt(1-$a));

            return $earth_radius * $c;
        }

        $distance = calculateDistance(
            $session["lecturer_lat"],
            $session["lecturer_lon"],
            $student_lat,
            $student_lon
        );

        // Check radius
        $radius = 30; // meters

        if ($distance > $radius) {
            $message = "You are outside the allowed range.";
        }
        else {

            // Prevent duplicate device
            $checkDevice = $pdo->prepare("
                SELECT attendance_id FROM attendance 
                WHERE session_id = ? AND device_id = ?
            ");
            $checkDevice->execute([$session_id, $device_id]);

            if ($checkDevice->rowCount() > 0) {
                $message = "This device has already registered.";
            }
            else {

                // Prevent duplicate matric
                $checkMatric = $pdo->prepare("SELECT attendance_id FROM attendance 
                WHERE session_id = ? AND matric_no = ?");
                $checkMatric->execute([$session_id, $matric]);

                if ($checkMatric->rowCount() > 0) {
                    $message = "Matric number already registered.";
                }
                else {

                    // Insert attendance
                    $insert = $pdo->prepare("INSERT INTO attendance (session_id, student_name, student_level, matric_no, device_id, student_lat, student_lon, distance)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    $insert->execute([
                        $session_id,
                        $student_name,
                        $student_level,
                        $matric,
                        $device_id,
                        $student_lat,
                        $student_lon,
                        $distance
                    ]);

                    $message = "Attendance recorded successfully.";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Mark Attendance</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>


<?php if ($message != ""): ?>
    <p><strong><?php echo $message; ?></strong></p>
<?php endif; ?>

<div class="container">
    <div class="card">
<form method="POST" id="attendanceForm" 
onsubmit="event.preventDefault(); submitAttendance();">

    <input type="text" name="matric" placeholder="Matric Number" required><br><br>

    <input type="text" name="student_name" placeholder="Full Name" required><br><br>

    <input type="text" name="student_level" placeholder="Level (e.g. 300)" required><br><br>

    <input type="hidden" name="lat" id="lat">
    <input type="hidden" name="lon" id="lon">
    <input type="hidden" name="device_id" id="device_id">

    <button class="button" type="submit">Submit Attendance</button>
</form>
</div>
</div>

<script src="../assets/script.js"></script>
<script>
function submitAttendance() {

    let deviceId = localStorage.getItem("device_id");

    if (!deviceId) {
        deviceId = Date.now() + "_" + Math.random();
        localStorage.setItem("device_id", deviceId);
    }

    document.getElementById("device_id").value = deviceId;

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

            document.getElementById("attendanceForm").submit();
        },
        function() {
            alert("Location access denied.");
        }
    );
}
</script>
</body>
</html>