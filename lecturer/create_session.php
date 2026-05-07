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
        // Initial token (will be quickly replaced by AJAX rotating logic on the active session page)
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

        // Redirect to the live active session visualizer!
        header("Location: active_session.php?session_id=" . $session_id);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Attendance Session - QR Attend</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<?php include "navbar.php"; ?>

<div class="container">
    <div style="max-width: 600px; margin: 2rem auto;">
        <div style="text-align: center; margin-bottom: 2rem;">
            <div style="font-size: 2.5rem; color: var(--accent-glow); margin-bottom: 0.5rem;">
                <i class="fa-solid fa-tower-broadcast"></i>
            </div>
            <h2>Create Attendance Session</h2>
            <p>Start a new session and securely broadcast dynamic QR codes for students.</p>
        </div>
        
        <?php if (!empty($message)): ?>
            <div class="card" style="text-align: center; border: 1px solid var(--danger); box-shadow: 0 0 15px rgba(239, 68, 68, 0.2); color: var(--danger);">
                <i class="fa-solid fa-triangle-exclamation"></i> <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <div class="card">
            <form method="POST" id="sessionForm" onsubmit="event.preventDefault(); createSession();">
                
                <input type="hidden" name="lat" id="lat">
                <input type="hidden" name="lon" id="lon">
                
                <label for="radius"><i class="fa-solid fa-map-location-dot" style="margin-right: 5px;"></i> Allowed Radius (meters)</label>
                <input type="number" name="radius" id="radius" value="50" min="10" required style="margin-top: 0.5rem; margin-bottom: 1rem;">
                
                <button class="button button-primary" type="submit" style="width: 100%;">
                    <i class="fa-solid fa-satellite-dish"></i> Broadcast Session
                </button>
                
                <div style="text-align: center; margin-top: 1.5rem; font-size: 0.85rem; color: var(--text-muted);">
                    <i class="fa-solid fa-circle-info"></i> Make sure to allow location access when prompted.
                </div>
            </form>
        </div>
        
        <div style="text-align: center; margin-top: 1.5rem;">
            <a href="course_dashboard.php?course_id=<?php echo $course_id; ?>" style="color: var(--text-muted);">
                <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>
</div>

<script>
    function createSession() {
        if (!navigator.geolocation) {
            alert("Geolocation not supported by your browser.");
            return;
        }

        const btn = document.querySelector('#sessionForm button[type="submit"]');
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Getting Location & Starting...';
        btn.disabled = true;

        navigator.geolocation.getCurrentPosition(
            function(position) {
                document.getElementById("lat").value = position.coords.latitude;
                document.getElementById("lon").value = position.coords.longitude;
                document.getElementById("sessionForm").submit();
            },
            function(error) {
                btn.innerHTML = '<i class="fa-solid fa-satellite-dish"></i> Broadcast Session';
                btn.disabled = false;
                alert("Location access denied or unavailable. Please enable location services.");
            },
            { timeout: 10000, enableHighAccuracy: true }
        );
    }
</script>
<script src="../assets/js/main.js"></script>
</body>
</html>