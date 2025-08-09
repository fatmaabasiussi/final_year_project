<?php
session_start();

// Ruhusu user tu
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'user') {
    header("Location: ../index.php");
    exit;
}

require_once __DIR__ . '/../includes/functions.php';
$conn = Database::getInstance()->getConnection();

// Check for new answers (notification)
$user_id = $_SESSION['user_id'];
$notif_result = $conn->query("SELECT COUNT(*) as new_answers FROM questions WHERE user_id = $user_id AND status = 'answered' AND is_answer_seen = 0");
$new_answers = 0;
if ($notif_result) {
    $row = $notif_result->fetch_assoc();
    $new_answers = intval($row['new_answers']);
}
// Mark all seen after loading
$conn->query("UPDATE questions SET is_answer_seen = 1 WHERE user_id = $user_id AND status = 'answered' AND is_answer_seen = 0");

// Fetch all scholars for dropdown
$scholars = [];
$scholar_result = $conn->query("SELECT id, name FROM users WHERE role = 'scholar'");
while ($row = $scholar_result->fetch_assoc()) $scholars[] = $row;

$msg = ''; $msg_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'ask') {
    $question = trim($_POST['question'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $scholar_id = intval($_POST['scholar_id'] ?? 0);
    if (empty($question)) {
        $msg = "Tafadhali andika swali lako."; $msg_type = "danger";
    } elseif ($scholar_id == 0) {
        $msg = "Tafadhali chagua Sheikh."; $msg_type = "danger";
    } else {
        $sql = "INSERT INTO questions (user_id, scholar_id, question, category, status, created_at) VALUES (?, ?, ?, ?, 'pending', NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiss", $user_id, $scholar_id, $question, $category);
        if ($stmt->execute()) {
            $msg = "Swali lako limepokelewa."; $msg_type = "success";
        } else {
            $msg = "Tatizo limetokea."; $msg_type = "danger";
        }
    }
}

// Badili query ya kuchukua maswali: chukua tu yaliyojibiwa
$sql = "SELECT q.* FROM questions q WHERE q.user_id = ? AND q.status = 'answered' ORDER BY q.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$questions = [];
while ($row = $result->fetch_assoc()) $questions[] = $row;

$categories = ['aqeedah'=>'Aqeedah','fiqh'=>'Fiqh','hadith'=>'Hadith','tafsir'=>'Tafsir','seerah'=>'Seerah','other'=>'Nyinginezo'];

$conn->close();
?>

<!DOCTYPE html>
<html lang="sw">
<head>
  <meta charset="UTF-8">
  <title>Maswali Yangu</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

  <style>
    body {
      display: flex;
      overflow-x: hidden;
      margin: 0;
    }

    nav#sidebar {
      background: linear-gradient(to bottom, #0f2027, #203a43, #2c5364);
      min-height: 100vh;
      width: 260px;
      color: white;
    }

    #sidebar .nav-link {
      color: white;
      transition: 0.3s;
    }

    #sidebar .nav-link:hover {
      background-color: #198754;
      color: #fff !important;
    }

    .nav-link.active {
      background-color: #198754 !important;
    }

    .profile-icon {
      background: white;
      width: 80px;
      height: 80px;
      border-radius: 50%;
      display: flex;
      justify-content: center;
      align-items: center;
      margin: 0 auto;
    }

    #content {
      flex-grow: 1;
      background-color: #f8f9fa;
      padding-bottom: 50px;
    }

    #header {
      height: 60px;
      background: #198754;
      color: white;
      display: flex;
      align-items: center;
      padding: 0 20px;
      font-weight: 600;
      font-size: 1.25rem;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .question-card {
      background: white;
      border-radius: 8px;
      padding: 20px;
      margin-bottom: 20px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }

    .status-pending {
      background: #ffeeba;
      color: #856404;
      padding: 5px 10px;
      border-radius: 12px;
    }

    .status-answered {
      background: #d4edda;
      color: #155724;
      padding: 5px 10px;
      border-radius: 12px;
    }

    .ask-question-card {
      background: #fff;
      padding: 20px;
      border-radius: 8px;
      margin-bottom: 30px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
  </style>
</head>
<body>

<!-- Sidebar -->
  <nav id="sidebar" class="d-flex flex-column align-items-center text-white p-3">
    <div class="text-center mb-3">
      <div class="profile-icon mb-2">
        <i class="fas fa-user text-success" style="font-size: 2rem;"></i>
      </div>
      <h5 class="text-capitalize"><?= $_SESSION['name'] ?></h5>
      <div class="badge bg-success">Mtumiaji</div>
    </div>

    <ul class="nav flex-column w-100 mt-4">
      <li class="nav-item mb-2">
        <a href="user.php" class="nav-link d-flex align-items-center px-3 py-2 rounded <?= basename($_SERVER['PHP_SELF']) == 'user.php' ? 'active' : '' ?>">
          <i class="fas fa-home me-2"></i> Mwanzo
        </a>
      </li>
      <li class="nav-item mb-2">
        <a href="my_courses.php" class="nav-link d-flex align-items-center px-3 py-2 rounded <?= basename($_SERVER['PHP_SELF']) == 'my_courses.php' ? 'active' : '' ?>">
          <i class="fas fa-book me-2"></i> Kozi Zangu
        </a>
      </li>
    
      <li class="nav-item mb-2">
        <a href="my_mcq_results.php" class="nav-link d-flex align-items-center px-3 py-2 rounded <?= basename($_SERVER['PHP_SELF']) == 'my_mcq_results.php' ? 'active' : '' ?>">
          <i class="fas fa-chart-bar me-2"></i> Matokeo Yangu ya MCQ
        </a>
      </li>
      <li class="nav-item mb-2">
        <a href="my_questions.php" class="nav-link d-flex align-items-center px-3 py-2 rounded <?= basename($_SERVER['PHP_SELF']) == 'my_questions.php' ? 'active' : '' ?>">
          <i class="fas fa-question-circle me-2"></i> Maswali Yangu
        </a>
      </li>
      <li class="nav-item mb-2">
        <a href="profile.php" class="nav-link d-flex align-items-center px-3 py-2 rounded <?= basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : '' ?>">
          <i class="fas fa-user me-2"></i> Profaili
        </a>
      </li>
      <li class="nav-item mb-2">
        <a href="settings.php" class="nav-link d-flex align-items-center px-3 py-2 rounded <?= basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : '' ?>">
          <i class="fas fa-cog me-2"></i> Mipangilio
        </a>
      </li>
      <li class="nav-item mt-3">
        <a href="../logout.php" class="nav-link d-flex align-items-center px-3 py-2 rounded text-danger">
          <i class="fas fa-sign-out-alt me-2"></i> Toka
        </a>
      </li>
    </ul>
  </nav>


<!-- Content -->
<div id="content" class="d-flex flex-column flex-grow-1">
  <header id="header">
    <span>Maswali Yangu</span>
  </header>

  <main class="container-fluid py-4">
    <?php if ($msg): ?>
      <div class="alert alert-<?=$msg_type?>"><?=$msg?></div>
    <?php endif; ?>

    <!-- Notification badge at the top of main content -->
    <?php if ($new_answers > 0): ?>
      <div class="alert alert-success text-center fw-bold">Una majibu mapya <?= $new_answers ?> ya maswali yako!</div>
    <?php endif; ?>

    <div class="ask-question-card">
      <h5><i class="fas fa-question-circle me-2"></i>Uliza Swali</h5>
      <form method="POST" class="needs-validation" novalidate>
        <input type="hidden" name="action" value="ask">
        <div class="mb-3">
          <label>Mada</label>
          <select name="category" class="form-select" required>
            <option value="">-- Chagua --</option>
            <?php foreach ($categories as $value => $label): ?>
              <option value="<?=$value?>"><?=$label?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <!-- Scholar selection in the ask question form -->
        <div class="mb-3">
          <label>Chagua Sheikh</label>
          <select name="scholar_id" class="form-select" required>
            <option value="">-- Chagua Sheikh --</option>
            <?php foreach ($scholars as $scholar): ?>
              <option value="<?= $scholar['id'] ?>"><?= htmlspecialchars($scholar['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mb-3">
          <label>Swali Lako</label>
          <textarea name="question" class="form-control" rows="4" required></textarea>
        </div>
        <button type="submit" class="btn btn-success">Tuma Swali</button>
      </form>
    </div>

    <?php if (empty($questions)): ?>
      <div class="alert alert-info">Bado hujauliza maswali.</div>
    <?php else: ?>
      <?php foreach ($questions as $q): ?>
        <div class="question-card">
          <div class="d-flex justify-content-between">
            <span class="badge bg-secondary"><?=htmlspecialchars($categories[$q['category']] ?? 'Nyinginezo')?></span>
            <span class="<?=$q['status'] === 'answered' ? 'status-answered' : 'status-pending'?>">
                <?=$q['status'] === 'answered' ? 'Limejibiwa' : 'Linasubiri'?>
            </span>
          </div>
          <p class="mt-2"><?=nl2br(htmlspecialchars($q['question']))?></p>
          <?php if ($q['status'] === 'answered'): ?>
          <div class="mt-3 p-3 bg-light rounded">
            <strong>Jibu:</strong><br>
            <?=nl2br(htmlspecialchars($q['answer'] ?? ''))?>
          </div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
