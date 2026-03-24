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
    <title>Lecturer Login</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <h2>Lecturer Login</h2>

    <div class="card">
        <?php if ($message != "") echo "<p>$message</p>"; ?>
    

    <form method="POST">
        <label>Email or Phone:</label><br>
        <input type="text" name="identifier"><br><br>

    <label>Password:</label><br>
    <input type="password" name="password" id="password">
    <button class= "button" type="button" id="togglePassword">Show</button><br><br>

    <button class= "button" type="submit">Login</button>
    </form>
</div>
    <p>Don't have an account? <a href="register.php">Register here</a></p>
    <script src="../assets/script.js"></script>
    <script>
    // Toggle password visibility for the login form
    document.addEventListener('DOMContentLoaded', function () {
        var toggle = document.getElementById('togglePassword');
        var pwd = document.getElementById('password');
        if (!toggle || !pwd) return;

        toggle.addEventListener('click', function () {
            var type = pwd.getAttribute('type') === 'password' ? 'text' : 'password';
            pwd.setAttribute('type', type);
            toggle.textContent = type === 'password' ? 'Show' : 'Hide';
        });
    });
    </script>
</body>
</html>