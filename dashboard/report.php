<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

$conn = new mysqli("localhost", "root", "1234", "religion_db");

$users = $conn->query("SELECT COUNT(*) AS count FROM users WHERE role = 'user'")->fetch_assoc()['count'];
$scholars = $conn->query("SELECT COUNT(*) AS count FROM users WHERE role = 'scholar'")->fetch_assoc()['count'];
$courses = $conn->query("SELECT COUNT(*) AS count FROM courses")->fetch_assoc()['count'];
$questions = $conn->query("SELECT COUNT(*) AS count FROM questions")->fetch_assoc()['count'];
$mcqs = $conn->query("SELECT COUNT(*) AS count FROM mcq_questions")->fetch_assoc()['count'] ?? 0;

$courses_list = $conn->query("SELECT c.title, c.created_at, c.status, u.name AS scholar FROM courses c JOIN users u ON c.scholar_id = u.id ORDER BY c.created_at DESC LIMIT 10");
$questions_list = $conn->query("SELECT q.question, u.name AS user_name, q.created_at FROM questions q JOIN users u ON q.user_id = u.id ORDER BY q.created_at DESC LIMIT 5");

// MCQ questions with scholar name
$mcq_list = $conn->query("SELECT m.question, u.name AS scholar_name, m.created_at FROM mcq_questions m JOIN users u ON m.scholar_id = u.id ORDER BY m.created_at DESC LIMIT 10");

// All users list with join, login, logout
$all_users = $conn->query("SELECT name, role, created_at, last_login, last_logout FROM users ORDER BY created_at DESC LIMIT 10");
?>
<!DOCTYPE html>
<html lang="sw">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Ripoti ya Mfumo</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body { background: #f0f8f5; font-family: Arial, sans-serif; }
    .card-title { color: #198754; font-weight: bold; }
    @media print {
      .print-btn {
        display: none !important;
      }
    }
  </style>
</head>
<body>
<div class="container-fluid mt-4">
  <div class="row mb-4">
    <div class="col-12 col-md-8">
      <h2 class="fw-bold">Ripoti ya Mfumo wa Know Your Religion</h2>
    </div>
    <div class="col-12 col-md-4 text-md-end text-start mt-3 mt-md-0">
      <button class="btn btn-success print-btn" onclick="window.print()">
        <i class="bi bi-printer"></i> Chapisha Ripoti
      </button>
    </div>
  </div>

  <!-- Kadi za Takwimu -->
  <div class="row g-4 mb-4">
    <div class="col-md-3 col-6">
      <div class="card shadow-sm border-success">
        <div class="card-body text-center">
          <h5 class="card-title">Watumiaji</h5>
          <h3><?= $users ?></h3>
        </div>
      </div>
    </div>
    <div class="col-md-3 col-6">
      <div class="card shadow-sm border-success">
        <div class="card-body text-center">
          <h5 class="card-title">Masheikh</h5>
          <h3><?= $scholars ?></h3>
        </div>
      </div>
    </div>
    <div class="col-md-3 col-6">
      <div class="card shadow-sm border-success">
        <div class="card-body text-center">
          <h5 class="card-title">Kozi</h5>
          <h3><?= $courses ?></h3>
        </div>
      </div>
    </div>
    <div class="col-md-3 col-6">
      <div class="card shadow-sm border-success">
        <div class="card-body text-center">
          <h5 class="card-title">Maswali</h5>
          <h3><?= $questions ?></h3>
        </div>
      </div>
    </div>
    <div class="col-md-3 col-6">
      <div class="card shadow-sm border-success">
        <div class="card-body text-center">
          <h5 class="card-title">MCQ</h5>
          <h3><?= $mcqs ?></h3>
        </div>
      </div>
    </div>
  </div>

  <!-- Kozi Zilizowekwa -->
  <div class="card shadow-sm mb-4">
    <div class="card-body">
      <h4 class="card-title mb-3">Kozi Zilizowekwa na Masheikh</h4>
      <div class="table-responsive">
        <table class="table table-bordered table-hover">
          <thead class="table-success">
            <tr>
              <th>Sheikh</th>
              <th>Kichwa cha Kozi</th>
              <th>Tarehe</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php while($c = $courses_list->fetch_assoc()): ?>
              <tr>
                <td><?= htmlspecialchars($c['scholar']) ?></td>
                <td><?= htmlspecialchars($c['title']) ?></td>
                <td><?= date('d M Y', strtotime($c['created_at'])) ?></td>
                <td><?= ucfirst($c['status']) ?></td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Orodha ya Watumiaji -->
  <div class="card shadow-sm mb-4">
    <div class="card-body">
      <h4 class="card-title mb-3">Orodha ya Watumiaji</h4>
      <div class="table-responsive">
        <table class="table table-bordered table-hover">
          <thead class="table-success">
            <tr>
              <th>Jina</th>
              <th>Nafasi</th>
              <th>Aliyojiunga</th>
              <th>Mara ya Mwisho Kuingia</th>
              <th>Mara ya Mwisho Kutoka</th>
            </tr>
          </thead>
          <tbody>
            <?php while($u = $all_users->fetch_assoc()): ?>
              <tr>
                <td><?= htmlspecialchars($u['name']) ?></td>
                <td><?= htmlspecialchars(ucfirst($u['role'])) ?></td>
                <td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
                <td><?= $u['last_login'] ? date('d M Y H:i', strtotime($u['last_login'])) : '-' ?></td>
                <td><?= $u['last_logout'] ? date('d M Y H:i', strtotime($u['last_logout'])) : '-' ?></td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Maswali Mapya -->
  <div class="card shadow-sm mb-4">
    <div class="card-body">
      <h4 class="card-title mb-3">Maswali Mapya ya Wanafunzi</h4>
      <ul class="list-group">
        <?php while($q = $questions_list->fetch_assoc()): ?>
          <li class="list-group-item">
            <strong><?= htmlspecialchars($q['user_name']) ?></strong>: <?= htmlspecialchars($q['question']) ?> 
            <em>(<?= date('d M Y', strtotime($q['created_at'])) ?>)</em>
          </li>
        <?php endwhile; ?>
      </ul>
    </div>
  </div>

  <!-- Maswali ya MCQ -->
  <div class="card shadow-sm mb-4">
    <div class="card-body">
      <h4 class="card-title mb-3">Maswali ya MCQ</h4>
      <div class="table-responsive">
        <table class="table table-bordered table-hover">
          <thead class="table-success">
            <tr>
              <th>Swali</th>
              <th>Sheikh Aliyeyatengeneza</th>
              <th>Tarehe</th>
            </tr>
          </thead>
          <tbody>
            <?php while($m = $mcq_list->fetch_assoc()): ?>
              <tr>
                <td><?= htmlspecialchars($m['question']) ?></td>
                <td><?= htmlspecialchars($m['scholar_name']) ?></td>
                <td><?= date('d M Y', strtotime($m['created_at'])) ?></td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Hitimisho -->
  <div class="card shadow-sm mb-5">
    <div class="card-body">
      <h4 class="card-title">Hitimisho</h4>
      <p>Ripoti hii ni muhtasari wa shughuli kuu katika mfumo wa elimu ya Kiislamu - Know Your Religion. Tafadhali chapisha ripoti hii kwa kumbukumbu zako.</p>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
