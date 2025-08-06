<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'scholar') {
    header("Location: ../login.php");
    exit;
}
require_once '../inc/db.php';
$scholar_id = $_SESSION['user_id'];
$success = $error = "";

// Fetch scholar's courses
$courses = $db->query("SELECT id, title FROM courses WHERE scholar_id = $scholar_id");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id = $_POST['course_id'];
    $question = $db->real_escape_string($_POST['question']);
    $option_a = $db->real_escape_string($_POST['option_a']);
    $option_b = $db->real_escape_string($_POST['option_b']);
    $option_c = $db->real_escape_string($_POST['option_c']);
    $option_d = $db->real_escape_string($_POST['option_d']);
    $correct_option = $_POST['correct_option'];

    if ($course_id && $question && $option_a && $option_b && $option_c && $option_d && in_array($correct_option, ['A', 'B', 'C', 'D'])) {
        $stmt = $db->prepare("INSERT INTO mcq_questions (course_id, scholar_id, question, option_a, option_b, option_c, option_d, correct_option) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iissssss", $course_id, $scholar_id, $question, $option_a, $option_b, $option_c, $option_d, $correct_option);
        if ($stmt->execute()) {
            $success = "Swali limehifadhiwa kikamilifu!";
        } else {
            $error = "Tatizo limetokea: " . $stmt->error;
        }
    } else {
        $error = "Tafadhali jaza sehemu zote za swali.";
    }
}
?>
<!DOCTYPE html>
<html lang="sw">
<head>
  <meta charset="UTF-8">
  <title>Tunga MCQ - Sheikh</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
  <div class="container py-5">
    <h2 class="mb-4 text-success">Tunga Swali la MCQ</h2>

    <?php if ($success): ?>
      <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" class="border rounded p-4 bg-white shadow">
      <div class="mb-3">
        <label for="course" class="form-label">Chagua Kozi</label>
        <select name="course_id" class="form-select" required>
          <option value="">-- Chagua Kozi --</option>
          <?php while ($c = $courses->fetch_assoc()): ?>
            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['title']) ?></option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="mb-3">
        <label class="form-label">Swali</label>
        <textarea name="question" class="form-control" rows="3" required></textarea>
      </div>
      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label">A</label>
          <input type="text" name="option_a" class="form-control" required>
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">B</label>
          <input type="text" name="option_b" class="form-control" required>
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">C</label>
          <input type="text" name="option_c" class="form-control" required>
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">D</label>
          <input type="text" name="option_d" class="form-control" required>
        </div>
      </div>
      <div class="mb-3">
        <label class="form-label">Jibu Sahihi</label>
        <select name="correct_option" class="form-select" required>
          <option value="">-- Chagua --</option>
          <option value="A">A</option>
          <option value="B">B</option>
          <option value="C">C</option>
          <option value="D">D</option>
        </select>
      </div>
      <button type="submit" class="btn btn-success">Hifadhi Swali</button>
    </form>
  </div>
</body>
</html>
