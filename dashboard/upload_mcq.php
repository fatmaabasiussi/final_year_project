<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'scholar') {
    header("Location: ../index.php");
    exit;
}
require_once __DIR__ . '/../includes/functions.php';
$db = Database::getInstance()->getConnection();
$scholar_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $course_id = $_POST['course_id'];
    $question = $_POST['question'];
    $option_a = $_POST['option_a'];
    $option_b = $_POST['option_b'];
    $option_c = $_POST['option_c'];
    $option_d = $_POST['option_d'];
    $correct = $_POST['correct'];

   $stmt = $db->prepare("INSERT INTO mcq_questions (course_id, scholar_id, question, option_a, option_b, option_c, option_d, correct_option) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
   $stmt->bind_param("iissssss", $course_id, $scholar_id, $question, $option_a, $option_b, $option_c, $option_d, $correct);
    $stmt->execute();
    $success = "Swali limeongezwa kwa mafanikio!";
}

// Fetch scholar's courses
$courses = $db->query("SELECT id, title FROM courses WHERE scholar_id = $scholar_id");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Upload MCQ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="container mt-4">
    <h3>Ongeza MCQ kwa Kozi</h3>
    <?php if (!empty($success)) echo "<div class='alert alert-success'>$success</div>"; ?>
    <form method="POST">
        <div class="mb-3">
            <label>Kozi</label>
            <select name="course_id" class="form-control" required>
                <option value="">-- Chagua Kozi --</option>
                <?php while ($row = $courses->fetch_assoc()): ?>
                    <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['title']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="mb-3">
            <label>Swali</label>
            <input type="text" name="question" class="form-control" required />
        </div>
        <div class="mb-3">
            <label>Chaguo A</label>
            <input type="text" name="option_a" class="form-control" required />
        </div>
        <div class="mb-3">
            <label>Chaguo B</label>
            <input type="text" name="option_b" class="form-control" required />
        </div>
        <div class="mb-3">
            <label>Chaguo C</label>
            <input type="text" name="option_c" class="form-control" />
        </div>
        <div class="mb-3">
            <label>Chaguo D</label>
            <input type="text" name="option_d" class="form-control" />
        </div>
        <div class="mb-3">
            <label>Jibu Sahihi</label>
            <select name="correct" class="form-control" required>
                <option value="">-- Chagua --</option>
                <option value="A">A</option>
                <option value="B">B</option>
                <option value="C">C</option>
                <option value="D">D</option>
            </select>
        </div>
        <button class="btn btn-success">Ongeza Swali</button>
    </form>
</body>
</html>
