<?php
session_start();
// Scholar only access
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'scholar') {
    header("Location: ../login.php");
    exit;
}
require_once '../inc/db.php';

$scholar_id = $_SESSION['user_id'];

// Stats
$courses = $db->query("SELECT COUNT(*) as count FROM courses WHERE scholar_id = $scholar_id")->fetch_assoc()['count'];
$questions = $db->query("SELECT COUNT(*) as count FROM questions q JOIN courses c ON q.course_id = c.id WHERE c.scholar_id = $scholar_id")->fetch_assoc()['count'];
$answers = $db->query("SELECT COUNT(*) as count FROM answers WHERE user_id = $scholar_id")->fetch_assoc()['count'];

// Scholar info
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param('i', $scholar_id);
$stmt->execute();
$scholar = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>My Profile - Sheikh Panel</title>
 <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
  <style>
    body {
      background: #f0f8f5;
      min-height: 100vh;
      margin: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      color: #155724;
    }
    .wrapper {
      display: flex;
      min-height: 100vh;
      flex-wrap: nowrap;
    }
    #sidebar {
      width: 260px;
      background: linear-gradient(180deg, #28a745, #1e7e34);
      color: #fff;
      padding: 2rem 1rem;
      display: flex;
      flex-direction: column;
      align-items: center;
      box-shadow: 3px 0 10px rgba(0,0,0,0.1);
      transition: width 0.3s ease;
    }
    #sidebar .avatar {
      width: 70px;
      height: 70px;
      border-radius: 50%;
      background: #c3e6cb;
      color: #155724;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 2.8rem;
      margin-bottom: 1rem;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    #sidebar .scholar-name {
      font-size: 1.5rem;
      font-weight: 700;
      margin-bottom: 0.1rem;
      text-align: center;
      text-shadow: 0 1px 2px rgba(0,0,0,0.2);
    }
    #sidebar .role-badge {
      font-size: 0.9rem;
      background: rgba(255 255 255 / 0.3);
      color: #e9f7ef;
      border-radius: 15px;
      padding: 0.4rem 1rem;
      margin-bottom: 2.5rem;
      font-weight: 600;
      letter-spacing: 1px;
      user-select: none;
    }
    #sidebar ul.nav {
      width: 100%;
      padding-left: 0;
      list-style: none;
      margin: 0;
      flex-grow: 1;
      display: flex;
      flex-direction: column;
      gap: 0.7rem;
    }
    #sidebar ul.nav li {
      width: 100%;
    }
    #sidebar ul.nav li a.nav-link {
      color: #d4f1d4;
      font-weight: 600;
      padding: 0.65rem 1rem;
      border-radius: 8px;
      display: flex;
      align-items: center;
      gap: 0.8rem;
      text-decoration: none;
      transition: background-color 0.3s ease, color 0.3s ease;
      user-select: none;
      box-shadow: inset 0 0 0 0 transparent;
    }
    #sidebar ul.nav li a.nav-link.active,
    #sidebar ul.nav li a.nav-link:hover,
    #sidebar ul.nav li a.nav-link:focus {
      background-color: #ffffff30;
      color: #e9f7ef;
      box-shadow: inset 5px 0 0 #fff;
      font-weight: 700;
    }

    #sidebar ul.nav li a.nav-link i {
      font-size: 1.25rem;
      min-width: 25px;
      text-align: center;
    }
    /* Content */
    #content {
      flex-grow: 1;
      display: flex;
      flex-direction: column;
      min-height: 100vh;
      background: #fff;
      padding-bottom: 2rem;
    }
    #header {
      height: 70px;
      background: #2bb14f;
      color: #fff;
      display: flex;
      align-items: center;
      padding: 0 2rem;
      font-weight: 600;
      font-size: 1.25rem;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      z-index: 10;
    }
    #header .header-avatar {
      width: 38px;
      height: 38px;
      border-radius: 50%;
      background: #d4f1d4;
      color: #09722e;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 1.2rem;
      margin-right: 0.8rem;
    }

    /* Stats row */
    .stats-row {
      display: flex;
      gap: 1.5rem;
      margin: 2rem 1rem 3rem;
      flex-wrap: wrap;
      padding: 0 1rem;
    }
    .stats-card {
      flex: 1 1 180px;
      min-width: 180px;
      background: #e6f1e9;
      border-radius: 12px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.06);
      padding: 1.3rem 1.2rem;
      display: flex;
      align-items: center;
      gap: 1rem;
      border: 1px solid #c1d9b8;
      transition: box-shadow 0.2s, transform 0.2s;
      color: #0b3d14;
    }
    .stats-card:hover {
      box-shadow: 0 6px 24px rgba(43,177,79,0.4);
      transform: translateY(-4px) scale(1.05);
    }
    .stats-icon {
      font-size: 2.1rem;
      opacity: 0.9;
      color: #09722e;
      background: #d4f1d4;
      border-radius: 50%;
      padding: 0.6rem;
      margin-right: 0.2rem;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    /* Profile header */
    .profile-header {
      display: flex;
      align-items: center;
      gap: 1.5rem;
      margin: 2rem 1rem 1rem;
    }
    .profile-header .avatar {
      width: 90px;
      height: 90px;
      border-radius: 50%;
      background: #d4f1d4;
      color: #09722e;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 3rem;
    }
    .profile-header h4 {
      margin-bottom: 0.3rem;
      color: #235723;
      font-weight: 700;
    }
    .profile-header .text-muted {
      font-size: 0.9rem;
      color: #6c757d;
    }
    .profile-header .badge {
      font-size: 0.85rem;
    }

    /* Profile card */
    .profile-card {
      max-width: 500px;
      margin: 0 auto 2rem;
      padding: 1.5rem 2rem;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.08);
      background: #fff;
      border: 1px solid #d4e8d4;
    }
    .profile-card h4 {
      color: #2bb14f;
      margin-bottom: 1rem;
      font-weight: 700;
    }
    .profile-list {
      list-style: none;
      padding: 0;
      margin: 0;
    }
    .profile-list li {
      padding: 0.75rem 0;
      border-bottom: 1px solid #d4e8d4;
      font-size: 1rem;
      color: #235723;
    }
    .profile-list li strong {
      display: inline-block;
      width: 100px;
      color: #09722e;
    }

    /* Responsive */
    @media (max-width: 900px) {
      .wrapper {
        flex-direction: column;
      }
      #sidebar {
        width: 100%;
        min-height: auto;
        padding: 1rem;
        flex-direction: row;
        justify-content: space-around;
        align-items: center;
      }
      #sidebar .avatar, #sidebar .scholar-name, #sidebar .role-badge {
        display: none;
      }
      #sidebar ul.nav {
        display: flex;
        flex-direction: row;
        gap: 1rem;
        flex-grow: 0;
      }
      #sidebar ul.nav li {
        margin-bottom: 0;
      }
      #content {
        min-height: auto;
        padding-bottom: 1rem;
      }
      #header {
        padding: 0 1rem;
        font-size: 1rem;
      }
      .stats-row {
        gap: 1rem;
        margin: 1rem 0 2rem;
        padding: 0 1rem;
      }
      .profile-card {
        max-width: 100%;
        margin: 0 1rem 2rem;
        padding: 1rem 1.2rem;
      }
      .profile-header {
        margin: 1rem 1rem 1rem;
      }
    }
  </style>
</head>
<body>
 <div class="wrapper">
    <nav id="sidebar" aria-label="Main navigation">
      <div class="avatar"><i class="fas fa-user"></i></div>
      <div class="scholar-name"><?= htmlspecialchars($scholar['name'] ?? 'Sheikh') ?></div>
      <span class="role-badge">Sheikh</span>
      <ul class="nav">
        <li><a href="scholar.php" class="nav-link"><i class="fas fa-home"></i> Kwenye Dashboard</a></li>
        <li><a href="upload_course.php" class="nav-link"><i class="fas fa-upload"></i> Pakia Kozi</a></li>
        <li><a href="scholar_questions.php" class="nav-link"><i class="fas fa-question-circle"></i> Angalia Maswali</a></li>
        <li><a href="upload_mcq.php" class="nav-link"><i class="fas fa-pen-nib"></i> Tunga Quiz</a></li>
        <li><a href="view_mcq_results.php" class="nav-link"><i class="fas fa-chart-bar"></i> Matokeo ya Quiz</a></li>
        <li><a href="scholar_profile.php" class="nav-link active" aria-current="page"><i class="fas fa-user"></i> Profaili Yangu</a></li>
        <li><a href="scholar_settings.php" class="nav-link"><i class="fas fa-cog"></i> Mipangilio</a></li>
        <li><a href="../logout.php" class="nav-link text-danger"><i class="fas fa-sign-out-alt"></i> Toka</a></li>
      </ul>
    </nav>
    <div id="content">
      <header id="header">
        <span class="header-avatar" aria-hidden="true"><i class="fas fa-user"></i></span>
        <span>Karibu, <?= htmlspecialchars($scholar['name'] ?? '') ?></span>
      </header>
      <main class="container-fluid" tabindex="0">
        <div class="profile-header" role="region" aria-label="Profile header">
          <div class="avatar" aria-hidden="true"><i class="fas fa-user"></i></div>
          <div>
            <h4><?= htmlspecialchars($scholar['name'] ?? 'Scholar') ?></h4>
            <div class="text-muted"><i class="fas fa-envelope me-1"></i> <?= htmlspecialchars($scholar['email'] ?? '-') ?></div>
            <span class="badge bg-success"><i class="fas fa-user-graduate me-1"></i> Scholar</span>
          </div>
        </div>

        <div class="stats-row" aria-label="Statistics">
          <div class="stats-card" role="region" aria-labelledby="courses-stat">
            <span class="stats-icon"><i class="fas fa-book"></i></span>
            <div>
              <div id="courses-stat" class="fw-bold">Kozi Ulizoweka</div>
              <div class="fs-4"><?= $courses ?></div>
            </div>
          </div>
          <div class="stats-card" role="region" aria-labelledby="questions-stat">
            <span class="stats-icon"><i class="fas fa-question-circle"></i></span>
            <div>
              <div id="questions-stat" class="fw-bold">Maswali ya Kozi</div>
              <div class="fs-4"><?= $questions ?></div>
            </div>
          </div>
          <div class="stats-card" role="region" aria-labelledby="answers-stat">
            <span class="stats-icon"><i class="fas fa-check-circle"></i></span>
            <div>
              <div id="answers-stat" class="fw-bold">Majibu Uliyotoa</div>
              <div class="fs-4"><?= $answers ?></div>
            </div>
          </div>
        </div>

        <div class="profile-card" role="region" aria-label="Taarifa Binafsi">
          <h4>Taarifa Binafsi</h4>
          <ul class="profile-list">
            <li><strong>Jina:</strong> <?= htmlspecialchars($scholar['name'] ?? '-') ?></li>
            <li><strong>Email:</strong> <?= htmlspecialchars($scholar['email'] ?? '-') ?></li>
            <li><strong>Role:</strong> Sheikh</li>
            <li><strong>Joined:</strong> <?= isset($scholar['created_at']) ? date('M d, Y', strtotime($scholar['created_at'])) : '-' ?></li>
          </ul>
        </div>
      </main>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
