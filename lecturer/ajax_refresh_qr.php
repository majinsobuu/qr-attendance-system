<?php
session_start();
require_once "../config/db.php";
require_once "../phpqrcode/qrlib.php";

header('Content-Type: application/json');

if (!isset($_SESSION["lecturer_id"])) {
    echo json_encode(["status" => "error", "message" => "Access denied"]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $session_id = isset($_POST["session_id"]) ? intval($_POST["session_id"]) : 0;
    
    if (!$session_id) {
        echo json_encode(["status" => "error", "message" => "Invalid session ID"]);
        exit;
    }

    // Verify session belongs to lecturer and is active
    $stmt = $pdo->prepare("SELECT * FROM sessions WHERE session_id = ? AND lecturer_id = ? AND expires_at > NOW()");
    $stmt->execute([$session_id, $_SESSION["lecturer_id"]]);
    $session = $stmt->fetch();

    if (!$session) {
        echo json_encode(["status" => "error", "message" => "Session expired or not found"]);
        exit;
    }

    // Generate new token
    $newToken = bin2hex(random_bytes(16));

    // Update DB
    $update = $pdo->prepare("UPDATE sessions SET session_token = ? WHERE session_id = ?");
    $update->execute([$newToken, $session_id]);

    // Generate dynamic QR URL natively agnostic to local or production environments
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $base_dir = dirname(dirname($_SERVER['SCRIPT_NAME'])); 
    if ($base_dir === '/' || $base_dir === '\\') {
        $base_dir = '';
    }
    $base_url = $protocol . "://" . $host . $base_dir;
    
    $attendanceURL = $base_url . "/student/mark_attendance.php?session_id=" . $session_id . "&token=" . $newToken;

    // We overwrite the same QR code file to keep things clean
    $qrFile = "../qrcodes/session_" . $session_id . ".png";
    QRcode::png($attendanceURL, $qrFile, QR_ECLEVEL_L, 5);

    // Return the URL and a cache-busting timestamp
    echo json_encode([
        "status" => "success",
        "qr_url" => "../qrcodes/session_" . $session_id . ".png?t=" . time(),
        "link" => $attendanceURL
    ]);
    exit;
}
?>
