<?php
session_start();
require_once "../config/db.php";
require_once "../phpqrcode/qrlib.php";

if (!isset($_SESSION["lecturer_id"])) {
    header("Location: login.php");
    exit;
}

$message = "";
$qrPath = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $lat = $_POST["lat"];
    $lon = $_POST["lon"];

    if (!empty($lat) && !empty($lon)) {

        $token = bin2hex(random_bytes(16));
        $expiry = date("Y-m-d H:i:s", strtotime("+2 hours"));

        $stmt = $pdo->prepare("INSERT INTO sessions (lecturer_id, session_token, lecturer_lat, lecturer_lon, expires_at) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $_SESSION["lecturer_id"],
            $token,
            $lat,
            $lon,
            $expiry
        ]);

        $attendanceURL = "http://192.168.137.1/attendance_system/student/mark_attendance.php?token=" . $token;

        $qrPath = "../qrcodes/" . $token . ".png";
        QRcode::png($attendanceURL, $qrPath);

        $message = "Session created successfully.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
</head>
<body>

<h2>Welcome, <?php echo $_SESSION["lecturer_name"]; ?></h2>

<form method="POST" id="sessionForm">
    <input type="hidden" name="lat" id="lat">
    <input type="hidden" name="lon" id="lon">
    <button type="button" onclick="createSession()">Create Attendance Session</button>
</form>

<p><?php echo $message; ?></p>

<?php if ($qrPath != ""): ?>
    <h3>Scan QR Code:</h3>
    <img src="<?php echo $qrPath; ?>">
<?php endif; ?>

<br><br>
<a href="logout.php">Logout</a>

<script>
function createSession() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            document.getElementById("lat").value = position.coords.latitude;
            document.getElementById("lon").value = position.coords.longitude;
            document.getElementById("sessionForm").submit();
        });
    } else {
        alert("Geolocation not supported.");
    }
}
</script>

</body>
</html>