<?php
session_start();

// Ruhusu Scholar pekee
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'scholar') {
    header("Location: ../login.php");
    exit;
}

require_once '../inc/db.php';

$scholar_id = $_SESSION['user_id'];

// Chukua submissions zote zinazohusiana na scholar huyu
$query = "SELECT 
            s.id AS submission_id, 
            u.name AS student_name, 
            c.title AS course_title, 
            s.score, 
            COUNT(q.id) AS total_questions,
            s.submitted_at
          FROM student_mcq_submissions s
          JOIN users u ON s.student_id = u.id
          JOIN courses c ON s.course_id = c.id
          JOIN mcq_questions q ON q.course_id = c.id
          WHERE c.scholar_id = $scholar_id
          GROUP BY s.id
          ORDER BY s.submitted_at DESC";

$result = $db->query($query);
?>

<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <title>Matokeo ya MCQ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #e8f5e9;
            font-family: 'Segoe UI', sans-serif;
        }
        .container {
            margin-top: 50px;
        }
        h3 {
            margin-bottom: 30px;
            color: #2e7d32;
        }
        table th {
            background-color: #43a047;
            color: white;
        }
    </style>
</head>
<body>
<div class="container">
    <h3 class="text-center">Matokeo ya Maswali ya MCQ (Wanafunzi)</h3>

    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Mwanafunzi</th>
                    <th>Kozi</th>
                    <th>Alama</th>
                    <th>Maswali</th>
                    <th>Tarehe ya Kuwasilisha</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): 
                    $i = 1;
                    while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $i++; ?></td>
                        <td><?= htmlspecialchars($row['student_name']); ?></td>
                        <td><?= htmlspecialchars($row['course_title']); ?></td>
                        <td><?= $row['score']; ?></td>
                        <td><?= $row['total_questions']; ?></td>
                        <td><?= date('d M Y H:i', strtotime($row['submitted_at'])); ?></td>
                    </tr>
                <?php endwhile; else: ?>
                    <tr>
                        <td colspan="6" class="text-center">Hakuna matokeo bado.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
