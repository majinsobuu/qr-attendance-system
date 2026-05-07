<?php
session_start();
require_once "../config/db.php";

if (!isset($_SESSION["lecturer_id"])) {
    die("Access denied.");
}

if (!isset($_GET["session_id"]) && !isset($_GET["end_session_id"])) {
    die("Session not specified.");
}

// Handle End Session Action
if (isset($_GET["end_session_id"])) {
    $end_session_id = intval($_GET["end_session_id"]);

    // Delete the final QR code file to fulfill 'expired codes should be deleted'
    $qrFilePath = "../qrcodes/session_" . $end_session_id . ".png";
    if (file_exists($qrFilePath)) {
        unlink($qrFilePath);
    }

    // Get the course_id before ending the session
    $stmt_get = $pdo->prepare("SELECT course_id FROM sessions WHERE session_id = ? AND lecturer_id = ?");
    $stmt_get->execute([$end_session_id, $_SESSION["lecturer_id"]]);
    $course = $stmt_get->fetch();
    $course_id = $course ? $course['course_id'] : 0;

    // Proceed to expire the session
    $stmt_update = $pdo->prepare("UPDATE sessions SET expires_at = NOW() WHERE session_id = ? AND lecturer_id = ?");
    $stmt_update->execute([$end_session_id, $_SESSION["lecturer_id"]]);
    
    // Redirect to course dashboard
    header("Location: course_dashboard.php?course_id=" . $course_id);
    exit;
}

$session_id = $_GET["session_id"];

// Verify session belongs to lecturer and is active
$stmt = $pdo->prepare("SELECT * FROM sessions WHERE session_id = ? AND lecturer_id = ?");
$stmt->execute([$session_id, $_SESSION["lecturer_id"]]);
$session_data = $stmt->fetch();

if (!$session_data) {
    die("Invalid session or access denied.");
}

$course_id = $session_data['course_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Active Attendance Session - QR Attend</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .qr-container {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            display: inline-block;
            margin-bottom: 1.5rem;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            position: relative;
        }
        
        .timer-bar {
            width: 100%;
            height: 4px;
            background: rgba(0,0,0,0.1);
            border-radius: 2px;
            margin-top: 10px;
            overflow: hidden;
        }
        
        .timer-fill {
            height: 100%;
            background: var(--accent-blue);
            width: 100%;
            transition: width 1s linear;
        }
    </style>
</head>
<body>

<?php include "navbar.php"; ?>

<div class="page-center" style="min-height: calc(100vh - 70px); padding-top: 2rem; justify-content: flex-start;">
    <div style="text-align: center; margin-bottom: 1.5rem;">
        <div style="font-size: 2.5rem; color: var(--success); margin-bottom: 0.5rem; text-shadow: 0 0 15px rgba(16, 185, 129, 0.4);">
            <i class="fa-solid fa-satellite-dish fa-beat" style="--fa-animation-duration: 2s;"></i>
        </div>
        <h2>Active Session Broadcast</h2>
        <p>Students must scan this code quickly to register their attendance.</p>
    </div>
    
    <div class="card" style="width: 100%; max-width: 500px; text-align: center;">
        
        <div class="qr-container">
            <img id="qrImage" src="" alt="Session QR Code" style="width: 250px; height: 250px; border-radius: 8px;">
            <div class="timer-bar">
                <div class="timer-fill" id="timerFill"></div>
            </div>
        </div>
        
        <p style="font-weight: 600; color: var(--accent-glow); margin-bottom: 0.5rem;" id="timerText">
            Refreshes in: 15s
        </p>
        <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1.5rem;">
            This dynamic QR code rotates every 15 seconds to prevent circumvention.
        </p>

        <div style="display: flex; gap: 1rem; flex-direction: column;">
            <a href="view_attendance.php?session_id=<?php echo $session_id; ?>" target="_blank" class="button button-primary">
                <i class="fa-solid fa-users"></i> Live Attendance View
            </a>
            
            <a href="active_session.php?end_session_id=<?php echo $session_id; ?>" class="button button-danger" onclick="return confirm('Are you sure you want to end this session? The QR will be permanently destroyed.');">
                <i class="fa-solid fa-power-off"></i> End Session Securely
            </a>
        </div>
    </div>
</div>

<script src="../assets/js/main.js"></script>
<script>
    const sessionId = <?php echo $session_id; ?>;
    const qrImage = document.getElementById('qrImage');
    const timerFill = document.getElementById('timerFill');
    const timerText = document.getElementById('timerText');
    
    let secondsLeft = 15;
    
    function fetchNewQR() {
        // Initial state before fetch
        qrImage.style.opacity = '0.5';
        
        const formData = new FormData();
        formData.append('session_id', sessionId);
        
        fetch('ajax_refresh_qr.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                qrImage.src = data.qr_url;
                qrImage.style.opacity = '1';
                
                // Reset timer
                secondsLeft = 15;
                timerFill.style.transition = 'none';
                timerFill.style.width = '100%';
                
                // Allow browser to render non-transition width before starting transition
                setTimeout(() => {
                    timerFill.style.transition = 'width 15s linear';
                    timerFill.style.width = '0%';
                }, 50);
                
                // Optional: show quick toast
                // showToast('Refreshed securely', 'success');
            } else {
                showToast(data.message || 'Failed to refresh QR', 'danger');
                if(data.message === 'Session expired or not found') {
                    window.location.href = 'course_dashboard.php?course_id=<?php echo $course_id; ?>';
                }
            }
        })
        .catch(err => {
            console.error(err);
            showToast('Network error while refreshing', 'danger');
        });
    }

    // Call immediately to load first QR
    fetchNewQR();

    // Setup interval for refreshing QR
    setInterval(fetchNewQR, 15000);
    
    // Setup interval for countdown text
    setInterval(() => {
        secondsLeft--;
        if(secondsLeft < 0) secondsLeft = 0;
        timerText.innerText = `Refreshes in: ${secondsLeft}s`;
    }, 1000);
</script>
</body>
</html>
