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
    <title>Lecturer Registration</title>
</head>
<body>
    <h2>Lecturer Registration</h2>

    <?php 
        if ($message != "") echo "<p>$message</p>";
    ?>

    <form method="POST">
        <label>First Name:</label><br>
        <input type="text" name="first_name"><br><br>

        <label>Last Name:</label><br>
        <input type="text" name="last_name"><br><br>

        <label>Email:</label><br>
        <input type="email" name="email"><br><br>

        <label>Phone Number:</label><br>
        <input type="text" name="phone_number"><br><br>

        <label>Password:</label><br>
        <input type="password" name="password" id="password"><br>
        <button type="button" id="togglePassword">Show</button><br><br>

    <button type="submit">Register</button>
    </form>

    <p>Already have an account? <a href="login.php">Login here</a></p>
</body>
</html>

<script>
// Toggle password visibility for the registration form
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


