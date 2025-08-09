<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'user') {
    header("Location: ../index.php");
    exit;
}

require_once __DIR__ . '/../includes/functions.php';
$conn = Database::getInstance()->getConnection();

if (!isset($_GET['course_id'])) {
    echo "<div style='padding: 20px; color: red;'>Tafadhali chagua kozi kwanza.</div>";
    exit;
}

$course_id = $_GET['course_id'];
$user_id = $_SESSION['user_id'];

// Hakikisha kama mwanafunzi amesajiliwa kwenye kozi hii
$check = $conn->query("SELECT * FROM enrollments WHERE user_id = $user_id AND course_id = $course_id");
if ($check->num_rows == 0) {
    echo "<div style='padding: 20px; color: red;'>Hujaruhusiwa kufikia maswali ya kozi hii.</div>";
    exit;
}

$questions = $conn->query("SELECT * FROM mcq_questions WHERE course_id = $course_id");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Maswali ya MCQ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="container mt-5">
    <h3>Jibu Maswali ya Kozi</h3>
    <form action="submit_mcq.php" method="POST">
        <input type="hidden" name="course_id" value="<?= $course_id ?>">

        <?php
        $i = 1;
        while ($row = $questions->fetch_assoc()) {
            echo "<div class='mb-3'>";
            echo "<p><strong>$i. {$row['question']}</strong></p>";
            foreach (['A', 'B', 'C', 'D'] as $opt) {
                $label = $row["option_" . strtolower($opt)];
                if ($label) {
                    echo "<div class='form-check'>
                            <input class='form-check-input' type='radio' name='answers[{$row['id']}]' value='$opt' required>
                            <label class='form-check-label'>$opt. $label</label>
                          </div>";
                }
            }
            echo "</div>";
            $i++;
        }
        ?>

        <button type="submit" class="btn btn-success">Tuma Majibu</button>
    </form>
</body>
</html>
