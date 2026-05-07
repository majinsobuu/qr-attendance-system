<?php
session_start();
require_once "../config/db.php";

if (!isset($_SESSION["lecturer_id"])) {
    die("Access denied.");
}

$lecturer_id = $_SESSION["lecturer_id"];

$stmt = $pdo->prepare("
    SELECT * FROM courses WHERE lecturer_id = ?
");
$stmt->execute([$lecturer_id]);
$courses = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Courses - QR Attend</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<?php include "navbar.php"; ?>
<div class="container">
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; margin-top: 1rem;">
        <h2 style="margin: 0;"><i class="fa-solid fa-book" style="color: var(--accent-blue); margin-right: 8px;"></i> My Courses</h2>
        <a class="button button-success" href="add_course.php"><i class="fa-solid fa-plus"></i> Add New Course</a>
    </div>
    
    <div class="card" style="padding: 1.5rem;">
        <?php if (count($courses) > 0): ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th><i class="fa-solid fa-barcode" style="margin-right: 5px;"></i> Course Code</th>
                            <th><i class="fa-solid fa-heading" style="margin-right: 5px;"></i> Course Title</th>
                            <th style="width: 120px; text-align: center;"><i class="fa-solid fa-wrench" style="margin-right: 5px;"></i> Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($courses as $course): ?>
                        <tr>
                            <td style="font-weight: 600; color: var(--accent-glow);"><?php echo htmlspecialchars($course["course_code"]); ?></td>
                            <td><?php echo htmlspecialchars($course["course_title"]); ?></td>
                            <td style="text-align: center;">
                                <a class="button button-sm button-primary" href="course_dashboard.php?course_id=<?php echo $course["course_id"]; ?>">
                                    <i class="fa-solid fa-gear"></i> Manage
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 2rem; color: var(--text-muted);">
                <i class="fa-solid fa-folder-open" style="font-size: 3rem; margin-bottom: 1rem; color: rgba(255,255,255,0.1);"></i>
                <p>You haven't added any courses yet.</p>
                <a class="button button-primary mt-4" href="add_course.php">Add Your First Course</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="../assets/js/main.js"></script>
</body>
</html>