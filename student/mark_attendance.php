<?php
session_start();
require_once "../config/db.php";

if (!isset($_GET["session_id"])) {
    die("Invalid session.");
}

$session_id = intval($_GET["session_id"]);

// Fetch session details
$stmt = $pdo->prepare("SELECT * FROM sessions WHERE session_id = ? AND expires_at > NOW()");
$stmt->execute([$session_id]);
$session = $stmt->fetch();

if (!$session) {
    die("Session not found or has already expired.");
}

// Token validation logic (Dynamic Rotating QR)
$token_valid = false;
if (isset($_GET['token']) && $_GET['token'] === $session['session_token']) {
    $token_valid = true;
    $_SESSION['scanned_valid_for_' . $session_id] = true;
}

// If token isn't right now valid, and they didn't successfully scan a recent one, block them
if (!$token_valid && !isset($_SESSION['scanned_valid_for_' . $session_id])) {
    die(<<<HTML
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Access Denied - QR Attend</title>
        <link rel="stylesheet" href="../assets/css/style.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    </head>
    <body class="page-center">
        <div class="card" style="text-align: center; max-width: 400px; padding: 2rem;">
            <div style="font-size: 3rem; color: var(--danger); margin-bottom: 1rem;"><i class="fa-solid fa-circle-xmark"></i></div>
            <h2 style="color: var(--danger);">Expired QR Code</h2>
            <p style="color: var(--text-muted);">The QR code you scanned is no longer valid. Please scan the live QR code being broadcast by your lecturer right now.</p>
        </div>
    </body>
    </html>
HTML);
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

        // Check radius from session
        $radius = isset($session["radius"]) ? floatval($session["radius"]) : 30; // fallback

        if ($distance > $radius) {
            $message = "You are outside the allowed range ({$radius}m). Your distance: " . round($distance, 1) . "m";
        }
        else {

            // Prevent duplicate device
            $checkDevice = $pdo->prepare("
                SELECT attendance_id FROM attendance 
                WHERE session_id = ? AND device_id = ?
            ");
            $checkDevice->execute([$session_id, $device_id]);

            if ($checkDevice->rowCount() > 0) {
                $message = "This device has already registered attendance.";
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

                    $message = "Success: Attendance recorded securely.";
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

    <title>Mark Attendance - QR Attend</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<div class="page-center">
    <div style="width: 100%; max-width: 450px; text-align: center; margin-bottom: 2rem;">
        <div style="font-size: 2.5rem; font-weight: 700; color: var(--accent-glow); margin-bottom: 0.5rem; text-shadow: 0 0 15px rgba(56, 189, 248, 0.5);">
            <i class="fa-solid fa-clipboard-user"></i>
        </div>
        <h2>Mark Attendance</h2>
        <p>Please enter your details to register your presence.</p>
    </div>

    <div class="card" style="width: 100%; max-width: 450px;">
        <?php if ($message != ""): ?>
            <?php 
                $alertColor = (strpos(strtolower($message), 'success') !== false) || (strpos(strtolower($message), 'Success') !== false) ? 'var(--success)' : 'var(--danger)'; 
                $alertBg = (strpos(strtolower($message), 'success') !== false) || (strpos(strtolower($message), 'Success') !== false) ? 'rgba(16, 185, 129, 0.2)' : 'rgba(239, 68, 68, 0.2)';
            ?>
            <div class="badge" style="background: <?php echo $alertBg; ?>; color: <?php echo $alertColor; ?>; text-align: center; margin-bottom: 1.5rem; display: block; padding: 0.8rem;">
                <i class="fa-solid fa-circle-exclamation"></i> <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form method="POST" id="attendanceForm" onsubmit="event.preventDefault(); submitAttendance();">

            <label for="matric"><i class="fa-solid fa-id-card" style="margin-right: 5px;"></i> Matric Number</label>
            <input type="text" name="matric" id="matric" placeholder="e.g UG/19/2000" required>

            <label for="student_name" style="margin-top: 0.5rem;"><i class="fa-solid fa-user" style="margin-right: 5px;"></i> Full Name</label>
            <input type="text" name="student_name" id="student_name" placeholder="John Doe" required>

            <label for="student_level" style="margin-top: 0.5rem;"><i class="fa-solid fa-layer-group" style="margin-right: 5px;"></i> Level</label>
            <input type="text" name="student_level" id="student_level" placeholder="e.g. 300" required>

            <input type="hidden" name="lat" id="lat">
            <input type="hidden" name="lon" id="lon">
            <input type="hidden" name="device_id" id="device_id">

            <button class="button button-primary" type="submit" style="margin-top: 1rem; width: 100%;">
                <i class="fa-solid fa-location-arrow"></i> Submit Attendance
            </button>
        </form>
    </div>
</div>

<script src="../assets/js/main.js"></script>
<script>
function submitAttendance() {

    const btn = document.querySelector('#attendanceForm button[type="submit"]');
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Processing...';
    btn.disabled = true;

    let deviceId = localStorage.getItem("device_id");

    if (!deviceId) {
        deviceId = Date.now() + "_" + Math.random();
        localStorage.setItem("device_id", deviceId);
    }

    document.getElementById("device_id").value = deviceId;

    if (!navigator.geolocation) {
        alert("Geolocation not supported.");
        btn.innerHTML = '<i class="fa-solid fa-location-arrow"></i> Submit Attendance';
        btn.disabled = false;
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
            alert("Location access denied. We need your location to verify your attendance.");
            btn.innerHTML = '<i class="fa-solid fa-location-arrow"></i> Submit Attendance';
            btn.disabled = false;
        },
        { enableHighAccuracy: true }
    );
}
</script>
</body>
</html>