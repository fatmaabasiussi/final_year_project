<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'scholar') {
    header("Location: ../login.php");
    exit;
}

require_once '../inc/db.php';

$scholar_id = $_SESSION['user_id'];
$success_msg = $error_msg = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $db->real_escape_string($_POST['name']);
    $email = $db->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    if (!empty($password)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $update = $db->query("UPDATE users SET name='$name', email='$email', password='$hashed' WHERE id=$scholar_id");
    } else {
        $update = $db->query("UPDATE users SET name='$name', email='$email' WHERE id=$scholar_id");
    }

    if ($update) {
        $success_msg = "Taarifa zimehifadhiwa kikamilifu!";
        $_SESSION['name'] = $name;
    } else {
        $error_msg = "Hitilafu imetokea. Tafadhali jaribu tena.";
    }
}

// Fetch scholar info
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param('i', $scholar_id);
$stmt->execute();
$scholar = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="sw">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Sheikh Settings</title>
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
    #content {
      margin-left: 0px;
      flex-grow: 1;
      display: flex;
      flex-direction: column;
      min-height: 100vh;
      background: #fff;
    }
    #header {
      background: #2bb14f; color: white;
      font-weight: 600; padding: 1rem 1.5rem;
      margin-bottom: 1.5rem;
      display: flex; align-items: center;
    }
    .profile-header {
      display: flex; align-items: center; gap: 1rem;
      margin-bottom: 2rem;
    }
    .profile-header .avatar {
      width: 60px; height: 60px;
      border-radius: 50%;
      background: #d4f1d4;
      color: #09722e;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 2rem;
    }
    .form-control:focus {
      border-color: #198754;
      box-shadow: 0 0 0 0.2rem rgba(25, 135, 84, 0.25);
    }
  </style>
</head>
<body>

  <!-- Sidebar -->
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
        <li><a href="scholar_profile.php" class="nav-link"><i class="fas fa-user"></i> Profaili Yangu</a></li>
        <li><a href="scholar_settings.php" class="nav-link active" aria-current="page""><i class="fas fa-cog"></i> Mipangilio</a></li>
        <li><a href="../logout.php" class="nav-link text-danger"><i class="fas fa-sign-out-alt"></i> Toka</a></li>
      </ul>
    </nav>

  <!-- Content -->
  <div id="content">
    <div id="header">
        <span class="header-avatar"><i class="fas fa-cog me-2"></i></span>
        <span>Taarifa za Sheikh</span>
    </div>

    <div class="container">
      <div class="profile-header">
        <div class="avatar"><i class="fas fa-user"></i></div>
        <div>
          <h4 class="mb-1"><?= htmlspecialchars($scholar['name']) ?></h4>
          <div class="text-muted"><i class="fas fa-envelope"></i> <?= htmlspecialchars($scholar['email']) ?></div>
          <span class="badge bg-success"><i class="fas fa-user-graduate me-1"></i> Scholar</span>
        </div>
      </div>

      <?php if ($success_msg): ?>
        <div class="alert alert-success"><?= $success_msg ?></div>
      <?php elseif ($error_msg): ?>
        <div class="alert alert-danger"><?= $error_msg ?></div>
      <?php endif; ?>

      <form method="POST" class="mt-4">
        <div class="mb-3">
          <label for="name" class="form-label">Jina</label>
          <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($scholar['name']) ?>" required>
        </div>
        <div class="mb-3">
          <label for="email" class="form-label">Barua Pepe</label>
          <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($scholar['email']) ?>" required>
        </div>
        <div class="mb-3">
          <label for="password" class="form-label">Nywila Mpya (Acha kama hutaki kubadilisha)</label>
          <input type="password" name="password" class="form-control" autocomplete="new-password">
        </div>
        <button type="submit" class="btn btn-success"><i class="fas fa-save me-1"></i> Hifadhi</button>
      </form>
    </div>
  </div>
</div>
</body>
</html>
