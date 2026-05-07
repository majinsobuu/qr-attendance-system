<?php
session_start();
require_once "../config/db.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $identifier = trim($_POST["identifier"]); // email or phone
    $password = trim($_POST["password"]);

    if (empty($identifier) || empty($password)) {
        $message = "All fields are required.";
    } else {

        $stmt = $pdo->prepare("SELECT * FROM lecturers WHERE email = ? OR phone_number = ?");
        $stmt->execute([$identifier, $identifier]);
        $lecturer = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($lecturer && password_verify($password, $lecturer["password"])) {

            // Set basic session identifiers
            $_SESSION["lecturer_id"] = $lecturer["lecturer_id"];

            // Prefer a full_name field if present; otherwise build from first/last or email
            if (!empty($lecturer['first_name'])) {
                $_SESSION['lecturer_name'] = $lecturer['first_name'];
            } else {
                $full = trim((string)($lecturer['first_name'] ?? '') . ' ' . (string)($lecturer['last_name'] ?? ''));
                $_SESSION['lecturer_name'] = $full !== '' ? $full : ($lecturer['email'] ?? '');
            }

            // Store first name explicitly for easier display on the dashboard
            if (!empty($lecturer['first_name'])) {
                $_SESSION['first_name'] = $lecturer['first_name'];
            } else {
                $parts = preg_split('/\s+/', trim($_SESSION['lecturer_name']));
                $_SESSION['first_name'] = $parts[0] ?? $_SESSION['lecturer_name'];
            }

            header("Location: dashboard.php");
            exit;

        } else {
            $message = "Invalid credentials.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lecturer Login - QR Attend</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="page-center">
        <!-- Main Form Container -->
        <div style="width: 100%; max-width: 400px; text-align: center; margin-bottom: 2rem;">
            <div style="font-size: 2.5rem; font-weight: 700; color: var(--accent-glow); margin-bottom: 0.5rem; text-shadow: 0 0 15px rgba(56, 189, 248, 0.5);">
                <i class="fa-solid fa-qrcode"></i>
            </div>
            <h2>Lecturer Login</h2>
            <p>Welcome back! Please login to your account.</p>
        </div>

        <div class="card" style="width: 100%; max-width: 400px;">
            <?php if ($message != "") echo "<div class='badge' style='background: rgba(239, 68, 68, 0.2); color: var(--danger); text-align: center; margin-bottom: 1rem;'>$message</div>"; ?>

            <form method="POST">
                
                <label for="identifier"><i class="fa-solid fa-user" style="margin-right: 5px;"></i> Email or Phone</label>
                <input type="text" name="identifier" id="identifier" placeholder="Enter your email or phone..." required>

                <label for="password" style="margin-top: 0.5rem;"><i class="fa-solid fa-lock" style="margin-right: 5px;"></i> Password</label>
                <div class="password-wrapper">
                    <input type="password" name="password" id="password" placeholder="Enter password..." required>
                    <button class="password-toggle" type="button" id="togglePassword"><i class="fa-solid fa-eye"></i></button>
                </div>

                <button class="button button-primary" type="submit" style="margin-top: 1rem; width: 100%;"><i class="fa-solid fa-right-to-bracket"></i> Login</button>
            </form>
        </div>

        <p class="mt-4" style="text-align: center;">Don't have an account? <a href="register.php" style="font-weight: 600;">Register here <i class="fa-solid fa-arrow-right"></i></a></p>
    </div>

    <script src="../assets/js/main.js"></script>
    <script>
    // Toggle password visibility for the login form
    document.addEventListener('DOMContentLoaded', function () {
        var toggle = document.getElementById('togglePassword');
        var pwd = document.getElementById('password');
        var icon = toggle.querySelector('i');
        if (!toggle || !pwd) return;

        toggle.addEventListener('click', function () {
            var type = pwd.getAttribute('type') === 'password' ? 'text' : 'password';
            pwd.setAttribute('type', type);
            if (type === 'password') {
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            } else {
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            }
        });
    });
    </script>
</body>
</html>