<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: manage_question.php");
    exit();
}
$id = (int)$_GET['id'];
require_once __DIR__ . '/../includes/functions.php';
$db = Database::getInstance()->getConnection();
$sql = "SELECT q.*, u.name as user_name, c.title as course_title
        FROM questions q
        JOIN users u ON q.user_id = u.id
        LEFT JOIN courses c ON q.course_id = c.id
        WHERE q.id = ?";
$stmt = $db->prepare($sql);
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    header("Location: manage_question.php");
    exit();
}
$q = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>View Question</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="bg-light">
<div class="container py-5">
    <a href="manage_question.php" class="btn btn-secondary mb-4">&larr; Rudi</a>
    <div class="card shadow">
        <div class="card-header bg-success text-white">
            <h4 class="mb-0">Swali</h4>
        </div>
        <div class="card-body">
            <p><strong>Mwanafunzi:</strong> <?= htmlspecialchars($q['user_name']) ?></p>
            <p><strong>Kozi:</strong> <?= htmlspecialchars($q['course_title'] ?? 'N/A') ?></p>
            <p><strong>Mada:</strong> <?= htmlspecialchars($q['category'] ?? '-') ?></p>
            <hr>
            <p><strong>Swali:</strong><br><?= nl2br(htmlspecialchars($q['question'])) ?></p>
            <hr>
            <p><strong>Jibu:</strong><br><?= $q['answer'] ? nl2br(htmlspecialchars($q['answer'])) : '<span class="text-warning">Bado halijajibiwa</span>' ?></p>
            <hr>
            <p><small class="text-muted">Iliulizwa: <?= date('d M Y H:i', strtotime($q['created_at'])) ?></small></p>
        </div>
    </div>
</div>
</body>
</html> 