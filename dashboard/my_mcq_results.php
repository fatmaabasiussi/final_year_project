<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'user') {
    header("Location: ../index.php");
    exit;
}

require_once __DIR__ . '/../includes/functions.php';
$conn = Database::getInstance()->getConnection();
$user_id = $_SESSION['user_id'];

// Pata matokeo ya user huyu
$results = $conn->query("SELECT s.*, c.title, c.id AS course_id
                         FROM student_mcq_submissions s
                         JOIN courses c ON s.course_id = c.id
                         WHERE s.student_id = $user_id
                         ORDER BY s.submitted_at DESC");
?>

<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <title>Matokeo ya MCQ</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="bg-light">
<div class="container mt-5">
    <h3 class="mb-4"> Matokeo Yangu ya Maswali ya Chaguo (MCQ)</h3>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">✅ Majibu yako yamehifadhiwa kikamilifu!</div>
    <?php endif; ?>

    <table class="table table-bordered bg-white shadow-sm">
        <thead class="table-success">
            <tr>
                <th>Kozi</th>
                <th>Alama</th>
                <th>Tarehe ya Kutuma</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $results->fetch_assoc()): ?>
                <?php
                    // Tafuta jumla ya maswali ya kozi hii
                    $course_id = $row['course_id'];
                    $count_result = $conn->query("SELECT COUNT(*) AS total FROM mcq_questions WHERE course_id = $course_id");
                    $total_questions = $count_result->fetch_assoc()['total'];

                    // Badge ya ufaulu
                    $score = $row['score'];
                    $percentage = ($total_questions > 0) ? ($score / $total_questions) * 100 : 0;
                    $badge = ($percentage >= 50) ? 'success' : 'danger';
                ?>
                <tr>
                    <td><?= htmlspecialchars($row['title']) ?></td>
                    <td>
                        <span class="badge bg-<?= $badge ?>"><?= $score ?> / <?= $total_questions ?></span>
                    </td>
                    <td><?= date("d M Y - H:i", strtotime($row['submitted_at'])) ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>
