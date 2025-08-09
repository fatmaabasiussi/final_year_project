<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'user') {
    header("Location: ../index.php");
    exit;
}

require_once __DIR__ . '/../includes/functions.php';
$conn = Database::getInstance()->getConnection();

$student_id = $_SESSION['user_id'];
$course_id = $_POST['course_id'];
$answers = $_POST['answers'];

$score = 0;
$total = 0;

foreach ($answers as $question_id => $user_answer) {
    $total++;
    $query = $conn->query("SELECT correct_option FROM mcq_questions WHERE id = $question_id");
    $correct = $query->fetch_assoc()['correct_option'];
    if (strtoupper($user_answer) == strtoupper($correct)) {
        $score++;
    }
}

// Hifadhi score
$conn->query("INSERT INTO student_mcq_submissions (student_id, course_id, score) 
              VALUES ($student_id, $course_id, $score)");

header("Location: my_mcq_results.php?success=1");
exit;
?>
