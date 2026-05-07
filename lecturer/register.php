<?php
session_start();
require_once "../config/db.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = trim($_POST["first_name"]);
    $last_name = trim($_POST["last_name"]);
    $email = trim($_POST["email"]);
    $phone_number = trim($_POST["phone_number"]);
    $password = trim($_POST["password"]);

    if (empty($first_name) || empty($last_name) || empty($email) || empty($phone_number) || empty($password)) {
        $message = "All fields are required.";
    } else {

        // Check if email already exists
        $stmt = $pdo->prepare("SELECT lecturer_id FROM lecturers WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->rowCount() > 0) {
            $message = "Email already registered.";
        } else {

            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert lecturer
            $stmt = $pdo->prepare("INSERT INTO lecturers (first_name, last_name, email, phone_number, password) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$first_name, $last_name, $email, $phone_number, $hashed_password]);

            $message = "Registration successful. You can now login.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lecturer Registration - QR Attend</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="page-center">
        <!-- Main Form Container -->
        <div style="width: 100%; max-width: 450px; text-align: center; margin-bottom: 2rem;">
            <div style="font-size: 2.5rem; font-weight: 700; color: var(--accent-glow); margin-bottom: 0.5rem; text-shadow: 0 0 15px rgba(56, 189, 248, 0.5);">
                <i class="fa-solid fa-user-plus"></i>
            </div>
            <h2>Create an Account</h2>
            <p>Join us to start taking attendance with ease.</p>
        </div>

        <div class="card" style="width: 100%; max-width: 450px;">
            <?php if ($message != "") echo "<div class='badge' style='background: rgba(14, 165, 233, 0.2); color: var(--accent-glow); text-align: center; margin-bottom: 1rem;'>$message</div>"; ?>

            <form method="POST">
                <div style="display: flex; gap: 1rem;">
                    <div style="flex: 1;">
                        <label for="first_name"><i class="fa-solid fa-address-card" style="margin-right: 5px;"></i> First Name</label>
                        <input type="text" name="first_name" id="first_name" placeholder="John" required>
                    </div>
                    <div style="flex: 1;">
                        <label for="last_name"><i class="fa-solid fa-address-card" style="margin-right: 5px;"></i> Last Name</label>
                        <input type="text" name="last_name" id="last_name" placeholder="Doe" required>
                    </div>
                </div>

                <label for="email" style="margin-top: 0.5rem;"><i class="fa-solid fa-envelope" style="margin-right: 5px;"></i> Email Address</label>
                <input type="email" name="email" id="email" placeholder="john.doe@example.com" required>
                
                <label for="phone_number" style="margin-top: 0.5rem;"><i class="fa-solid fa-phone" style="margin-right: 5px;"></i> Phone Number</label>
                <input type="text" name="phone_number" id="phone_number" placeholder="+1234567890" required>

                <label for="password" style="margin-top: 0.5rem;"><i class="fa-solid fa-lock" style="margin-right: 5px;"></i> Password</label>
                <div class="password-wrapper">
                    <input type="password" name="password" id="password" placeholder="Create a password..." required>
                    <button class="password-toggle" type="button" id="togglePassword"><i class="fa-solid fa-eye"></i></button>
                </div>
                
                <button class="button button-success" type="submit" style="margin-top: 1rem; width: 100%;"><i class="fa-solid fa-check-circle"></i> Register Now</button>
            </form>
        </div>

        <p class="mt-4" style="text-align: center;">Already have an account? <a href="login.php" style="font-weight: 600;">Login here <i class="fa-solid fa-arrow-right"></i></a></p>
    </div>

    <script src="../assets/js/main.js"></script>
    <script>
    // Toggle password visibility for the registration form
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


