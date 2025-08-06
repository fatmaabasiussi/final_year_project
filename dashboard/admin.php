<?php
session_start();

// Ruhusu admin tu
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

$conn = new mysqli("localhost", "root", "1234", "religion_db");

// Fetch data
$users = $conn->query("SELECT * FROM users WHERE role = 'user'");
$scholars = $conn->query("SELECT * FROM users WHERE role = 'scholar'");
$courses = $conn->query("SELECT c.*, u.name AS scholar FROM courses c JOIN users u ON c.scholar_id = u.id");
$questions = $conn->query("SELECT q.*, u.name AS user_name, c.title AS course_title FROM questions q JOIN users u ON q.user_id = u.id LEFT JOIN courses c ON q.course_id = c.id ORDER BY q.created_at DESC");

// Get stats
$stats = [
    'users' => $users->num_rows,
    'scholars' => $scholars->num_rows,
    'courses' => $courses->num_rows,
    'questions' => $conn->query("SELECT COUNT(*) as count FROM questions")->fetch_assoc()['count']
];
?>
<!DOCTYPE html>
<html lang="sw">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
  <style>
    body {
      display: flex;
      overflow-x: hidden;
      margin: 0;
      background-color: #e6f0e9; /* Light greenish bg, tofauti na user */
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    nav#sidebar {
      background: linear-gradient(180deg, #004d00, #006600);
      min-height: 100vh;
      width: 350px;
      color: white;
      padding: 2rem 1.2rem;
      box-shadow: 3px 0 10px rgba(0, 0, 0, 0.15);
    }

    #sidebar .nav-link {
      color: #d1e7dd;
      transition: background-color 0.3s, color 0.3s;
      padding-left: 1.5rem;
      padding-right: 1.5rem;
      font-weight: 600;
      letter-spacing: 0.02em;
    }

    #sidebar .nav-link:hover {
      background-color: #198754; /* Bootstrap green */
      color: white !important;
      text-decoration: none;
    }

    .nav-link.active {
      background-color: #14532d;
      color: #fff !important;
      border-left: 5px solid #198754;
    }

    .profile-icon {
      background: white;
      width: 90px;
      height: 90px;
      border-radius: 50%;
      display: flex;
      justify-content: center;
      align-items: center;
      margin: 0 auto 12px;
      box-shadow: 0 0 10px rgba(25, 135, 84, 0.7);
    }

    .profile-icon i {
      font-size: 2.5rem;
      color: #198754;
    }

    #content {
      flex-grow: 1;
      background-color: #fff;
      padding-bottom: 50px;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      box-shadow: inset 0 0 20px #d4edda;
    }

    #header {
      height: 65px;
      background: #198754;
      color: white;
      display: flex;
      align-items: center;
      padding: 0 25px;
      font-weight: 700;
      font-size: 1.4rem;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.12);
      letter-spacing: 0.03em;
    }

    main {
      padding: 30px 50px;
      flex-grow: 1;
      overflow-y: auto;
    }

    .stats-card {
      border-radius: 15px;
      padding: 1.3rem 1.8rem;
      color: white;
      box-shadow: 0 4px 15px rgb(25 135 84 / 0.3);
      transition: transform 0.3s ease;
      font-weight: 600;
    }
    .stats-card:hover {
      transform: translateY(-7px);
      box-shadow: 0 8px 25px rgb(25 135 84 / 0.5);
    }

    .stats-icon {
      font-size: 2.8rem;
      opacity: 0.85;
    }

    .recent-item {
      padding: 1.2rem;
      border-bottom: 1px solid #d1e7dd;
      background-color: #f4fff8;
      border-radius: 8px;
      margin-bottom: 10px;
      box-shadow: inset 0 0 7px #c3e6cb;
      transition: background-color 0.25s;
    }
    .recent-item:hover {
      background-color: #d1e7dd;
    }
    .recent-item:last-child {
      margin-bottom: 0;
      border-bottom: none;
    }
  </style>
</head>
<body>
<!-- Upau wa upande (Sidebar) -->
<nav id="sidebar" class="d-flex flex-column align-items-center text-white">
  <div class="text-center mb-5">
    <div class="profile-icon mb-3">
      <i class="fas fa-user-shield"></i>
    </div>
    <h4 class="text-capitalize"><?= htmlspecialchars($_SESSION['name']) ?></h4>
    <div class="badge bg-success fs-6 mt-1">Msimamizi</div>
  </div>

  <ul class="nav flex-column w-100">
    <li class="nav-item mb-3">
      <a href="admin.php" class="nav-link d-flex align-items-center rounded <?= basename($_SERVER['PHP_SELF']) == 'admin.php' ? 'active' : '' ?>">
        <i class="fas fa-tachometer-alt me-3"></i> Dashibodi
      </a>
    </li>
    <li class="nav-item mb-3">
      <a href="manage_user.php" class="nav-link d-flex align-items-center rounded <?= basename($_SERVER['PHP_SELF']) == 'manage_user.php' ? 'active' : '' ?>">
        <i class="fas fa-users me-3"></i> Simamia Watumiaji
      </a>
    </li>
    <li class="nav-item mb-3">
      <a href="manage_course.php" class="nav-link d-flex align-items-center rounded <?= basename($_SERVER['PHP_SELF']) == 'manage_course.php' ? 'active' : '' ?>">
        <i class="fas fa-book me-3"></i> Simamia Kozi
      </a>
    </li>
    <li class="nav-item mb-3">
      <a href="manage_question.php" class="nav-link d-flex align-items-center rounded <?= basename($_SERVER['PHP_SELF']) == 'manage_question.php' ? 'active' : '' ?>">
        <i class="fas fa-question-circle me-3"></i> Simamia Maswali
      </a>
    </li>
    <li class="nav-item mb-3">
  <a href="manage_mcq.php" class="nav-link d-flex align-items-center rounded <?= basename($_SERVER['PHP_SELF']) == 'manage_mcq.php' ? 'active' : '' ?>">
    <i class="fas fa-list-alt me-3"></i> Simamia Maswali ya MCQ
  </a>
</li>
    <li class="nav-item mb-3">
      <a href="admin_profile.php" class="nav-link d-flex align-items-center rounded <?= basename($_SERVER['PHP_SELF']) == 'admin_profile.php' ? 'active' : '' ?>">
        <i class="fas fa-user me-3"></i> Profaili
      </a>
    </li>
    <li class="nav-item mb-3">
      <a href="admin_setting.php" class="nav-link d-flex align-items-center rounded <?= basename($_SERVER['PHP_SELF']) == 'admin_setting.php' ? 'active' : '' ?>">
        <i class="fas fa-cog me-3"></i> Mipangilio
      </a>
    </li>
    <li class="nav-item mt-auto">
      <a href="../logout.php" class="nav-link d-flex align-items-center rounded text-danger">
        <i class="fas fa-sign-out-alt me-3"></i> Toka
      </a>
    </li>
  </ul>
</nav>

<!-- Content Area -->
<div id="content" class="d-flex flex-column flex-grow-1">
  <!-- Header -->
  <header id="header">
    <span>Karibu, <?= htmlspecialchars($_SESSION['name']) ?></span>
  </header>

  <!-- Main Content -->
  <main>
    <h2>Dashboard Overview</h2>

    <div class="row g-4 my-4">
      <div class="col-md-3">
        <div class="card stats-card bg-success shadow-sm">
          <div class="d-flex justify-content-between align-items-center px-3 py-2">
            <div>
              <h5><?= $stats['users'] ?></h5>
              <small>Users</small>
            </div>
            <i class="fas fa-users stats-icon"></i>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card stats-card bg-primary shadow-sm">
          <div class="d-flex justify-content-between align-items-center px-3 py-2">
            <div>
              <h5><?= $stats['scholars'] ?></h5>
              <small>Scholars</small>
            </div>
            <i class="fas fa-user-tie stats-icon"></i>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card stats-card bg-warning shadow-sm">
          <div class="d-flex justify-content-between align-items-center px-3 py-2">
            <div>
              <h5><?= $stats['courses'] ?></h5>
              <small>Courses</small>
            </div>
            <i class="fas fa-book stats-icon"></i>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card stats-card bg-danger shadow-sm">
          <div class="d-flex justify-content-between align-items-center px-3 py-2">
            <div>
              <h5><?= $stats['questions'] ?></h5>
              <small>Questions</small>
            </div>
            <i class="fas fa-question-circle stats-icon"></i>
          </div>
        </div>
      </div>
    </div>

    <section class="mt-4">
      <h4>Maswali Mapya</h4>
      <div>
        <?php while($q = $questions->fetch_assoc()): ?>
          <div class="recent-item">
            <strong><?= htmlspecialchars($q['user_name']) ?></strong> aliuliza kuhusu 
            <em><?= htmlspecialchars($q['course_title'] ?? 'N/A') ?></em>: 
            <p><?= htmlspecialchars(substr($q['question'], 0, 120)) ?>...</p>
            <small class="text-muted"><?= date('d M Y H:i', strtotime($q['created_at'])) ?></small>
          </div>
        <?php endwhile; ?>
      </div>
    </section>
  </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
