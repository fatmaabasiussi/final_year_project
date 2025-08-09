<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit;
}

require_once __DIR__ . '/../includes/functions.php';
$conn = Database::getInstance()->getConnection();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: manage_mcq.php");
    exit;
}

$mcq_id = intval($_GET['id']);
$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $question = $conn->real_escape_string($_POST['question']);
    $option_a = $conn->real_escape_string($_POST['option_a']);
    $option_b = $conn->real_escape_string($_POST['option_b']);
    $option_c = $conn->real_escape_string($_POST['option_c']);
    $option_d = $conn->real_escape_string($_POST['option_d']);
    $correct_option = $_POST['correct_option'];

    // Simple validation
    if (!$question || !$option_a || !$option_b || !$option_c || !$option_d || !in_array($correct_option, ['A', 'B', 'C', 'D'])) {
        $error = "Tafadhali jaza maswali yote na chagua jibu sahihi.";
    } else {
        $sql = "UPDATE mcq_questions SET question=?, option_a=?, option_b=?, option_c=?, option_d=?, correct_option=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssi", $question, $option_a, $option_b, $option_c, $option_d, $correct_option, $mcq_id);
        if ($stmt->execute()) {
            $message = "Swali limefanikiwa kusasishwa.";
        } else {
            $error = "Hitilafu: " . $conn->error;
        }
        $stmt->close();
    }
}

// Pata swali la MCQ kulingana na id
$result = $conn->query("SELECT * FROM mcq_questions WHERE id = $mcq_id");
$mcq = $result->fetch_assoc();

if (!$mcq) {
    header("Location: manage_mcq.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Hariri Swali la MCQ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<div class="container py-4">
    <h1 class="mb-4">Hariri Swali la MCQ</h1>

    <?php if ($message): ?>
        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Swali</label>
            <textarea name="question" class="form-control" rows="3" required><?= htmlspecialchars($mcq['question']) ?></textarea>
        </div>
        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <input type="text" name="option_a" class="form-control" placeholder="Jibu A" required value="<?= htmlspecialchars($mcq['option_a']) ?>" />
            </div>
            <div class="col-md-6">
                <input type="text" name="option_b" class="form-control" placeholder="Jibu B" required value="<?= htmlspecialchars($mcq['option_b']) ?>" />
            </div>
            <div class="col-md-6">
                <input type="text" name="option_c" class="form-control" placeholder="Jibu C" required value="<?= htmlspecialchars($mcq['option_c']) ?>" />
            </div>
            <div class="col-md-6">
                <input type="text" name="option_d" class="form-control" placeholder="Jibu D" required value="<?= htmlspecialchars($mcq['option_d']) ?>" />
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">Jibu Sahihi</label>
            <select name="correct_option" class="form-select" required>
                <option value="">Chagua jibu sahihi</option>
                <option value="A" <?= $mcq['correct_option'] === 'A' ? 'selected' : '' ?>>A</option>
                <option value="B" <?= $mcq['correct_option'] === 'B' ? 'selected' : '' ?>>B</option>
                <option value="C" <?= $mcq['correct_option'] === 'C' ? 'selected' : '' ?>>C</option>
                <option value="D" <?= $mcq['correct_option'] === 'D' ? 'selected' : '' ?>>D</option>
            </select>
        </div>

        <a href="manage_mcq.php" class="btn btn-secondary">Rudi nyuma</a>
        <button type="submit" class="btn btn-primary">Hifadhi Mabadiliko</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
