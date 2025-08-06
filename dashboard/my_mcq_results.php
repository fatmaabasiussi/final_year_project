<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'user') {
    header("Location: ../login.php");
    exit;
}

$conn = new mysqli("localhost", "root", "1234", "religion_db");
$user_id = $_SESSION['user_id'];

$results = $conn->query("SELECT s.*, c.title 
                         FROM student_mcq_submissions s
                         JOIN courses c ON s.course_id = c.id
                         WHERE s.student_id = $user_id
                         ORDER BY s.submitted_at DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Matokeo ya MCQ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="container mt-5">
    <h3>Matokeo Yangu ya Maswali ya Chaguo (MCQ)</h3>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">Majibu yako yamehifadhiwa kikamilifu!</div>
    <?php endif; ?>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Kozi</th>
                <th>Alama</th>
                <th>Tarehe ya Kutuma</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $results->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['title'] ?></td>
                    <td><?= $row['score'] ?></td>
                    <td><?= date("d M Y - H:i", strtotime($row['submitted_at'])) ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>
