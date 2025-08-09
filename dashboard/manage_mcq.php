<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit;
}

require_once __DIR__ . '/../includes/functions.php';
$conn = Database::getInstance()->getConnection();

// Ongeza au hariri maswali itakapotumwa fom
$message = '';

// Ongeza swala jipya
if (isset($_POST['add_mcq'])) {
    $course_id = intval($_POST['course_id']);
    $scholar_id = intval($_POST['scholar_id']);
    $question = $conn->real_escape_string($_POST['question']);
    $option_a = $conn->real_escape_string($_POST['option_a']);
    $option_b = $conn->real_escape_string($_POST['option_b']);
    $option_c = $conn->real_escape_string($_POST['option_c']);
    $option_d = $conn->real_escape_string($_POST['option_d']);
    $correct_option = $_POST['correct_option'];

    $sql = "INSERT INTO mcq_questions (course_id, scholar_id, question, option_a, option_b, option_c, option_d, correct_option)
            VALUES ($course_id, $scholar_id, '$question', '$option_a', '$option_b', '$option_c', '$option_d', '$correct_option')";

    if ($conn->query($sql)) {
        $message = "Swali la MCQ limeongezwa kikamilifu.";
    } else {
        $message = "Hitilafu: " . $conn->error;
    }
}

// Futa swali
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM mcq_questions WHERE id = $id");
    header("Location: manage_mcq.php");
    exit;
}

// Pata maswali yote ya MCQ
$mcqs = $conn->query("SELECT m.*, c.title AS course_title, u.name AS scholar_name FROM mcq_questions m JOIN courses c ON m.course_id = c.id JOIN users u ON m.scholar_id = u.id ORDER BY m.created_at DESC");

// Pata kozi zote na masheikh kwa dropdown
$courses = $conn->query("SELECT id, title FROM courses");
$scholars = $conn->query("SELECT id, name FROM users WHERE role = 'scholar'");
?>

<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Simamia Maswali ya MCQ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
</head>
<body>
<div class="container py-4">
    <h1 class="mb-4">Simamia Maswali ya MCQ</h1>

    <?php if ($message): ?>
        <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <!-- Fomu ya kuongeza swali jipya -->
    <form method="POST" class="mb-5">
        <div class="mb-3">
            <label class="form-label">Kozi</label>
            <select name="course_id" class="form-select" required>
                <option value="">Chagua kozi</option>
                <?php while($c = $courses->fetch_assoc()): ?>
                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['title']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Msheikh (Scholar)</label>
            <select name="scholar_id" class="form-select" required>
                <option value="">Chagua msheikh</option>
                <?php while($s = $scholars->fetch_assoc()): ?>
                    <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Swali</label>
            <textarea name="question" class="form-control" rows="3" required></textarea>
        </div>
        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <input type="text" name="option_a" placeholder="Jibu A" class="form-control" required />
            </div>
            <div class="col-md-6">
                <input type="text" name="option_b" placeholder="Jibu B" class="form-control" required />
            </div>
            <div class="col-md-6">
                <input type="text" name="option_c" placeholder="Jibu C" class="form-control" required />
            </div>
            <div class="col-md-6">
                <input type="text" name="option_d" placeholder="Jibu D" class="form-control" required />
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">Jibu Sahihi</label>
            <select name="correct_option" class="form-select" required>
                <option value="">Chagua jibu sahihi</option>
                <option value="A">A</option>
                <option value="B">B</option>
                <option value="C">C</option>
                <option value="D">D</option>
            </select>
        </div>
        <button type="submit" name="add_mcq" class="btn btn-success">Ongeza Swali la MCQ</button>
    </form>

    <!-- Orodha ya maswali yote -->
    <h3>Maswali ya MCQ yaliyopo</h3>
    <table class="table table-bordered table-striped">
        <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>Kozi</th>
                <th>Msheikh</th>
                <th>Swali</th>
                <th>Jibu Sahihi</th>
                <th>Tarehe</th>
                <th>Kitendo</th>
            </tr>
        </thead>
        <tbody>
            <?php while($m = $mcqs->fetch_assoc()): ?>
            <tr>
                <td><?= $m['id'] ?></td>
                <td><?= htmlspecialchars($m['course_title']) ?></td>
                <td><?= htmlspecialchars($m['scholar_name']) ?></td>
                <td><?= htmlspecialchars(substr($m['question'], 0, 50)) ?>...</td>
                <td><?= htmlspecialchars($m['correct_option']) ?></td>
                <td><?= date('d M Y', strtotime($m['created_at'])) ?></td>
                <td>
                    <a href="edit_mcq.php?id=<?= $m['id'] ?>" class="btn btn-sm btn-primary">Hariri</a>
                    <a href="manage_mcq.php?delete=<?= $m['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Una hakika unataka kufuta swali hili?')">Futa</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <a href="admin.php" class="btn btn-secondary mt-3"><i class="fas fa-arrow-left"></i> Rudi Dashboard</a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
