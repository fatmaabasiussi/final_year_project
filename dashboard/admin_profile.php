<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

require_once __DIR__ . '/../includes/functions.php';
$conn = Database::getInstance()->getConnection();

$admin_id = $_SESSION['user_id'];
$query = $conn->prepare("SELECT * FROM users WHERE id = ?");
$query->bind_param("i", $admin_id);
$query->execute();
$result = $query->get_result();
$admin = $result->fetch_assoc();

$profilePic = !empty($admin['profile_pic']) 
    ? 'uploads/' . htmlspecialchars($admin['profile_pic']) 
    : 'https://ui-avatars.com/api/?name=' . urlencode($admin['name']) . '&background=198754&color=fff';
?>
<!DOCTYPE html>
<html lang="sw">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Profile</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
  <style>
    body {
      display: flex;
      margin: 0;
      overflow-x: hidden;
      background-color: #e6f0e9;
    }

    nav#sidebar {
      background: linear-gradient(180deg, #004d00, #006600);
      min-height: 100vh;
      width: 350px;
      color: white;
      padding: 2rem 1rem;
    }

    #sidebar .nav-link {
      color: #d1e7dd;
      padding-left: 1.5rem;
      font-weight: 600;
    }

    .nav-link.active {
      background-color: #14532d;
      color: #fff !important;
      border-left: 5px solid #198754;
    }

    .nav-link:hover {
      background-color: #198754;
      color: #fff;
    }

    .profile-icon {
      width: 90px;
      height: 90px;
      border-radius: 50%;
      overflow: hidden;
      margin: 0 auto 1rem;
      box-shadow: 0 0 10px rgba(25, 135, 84, 0.7);
    }

    #content {
      flex-grow: 1;
      background: #fff;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    #header {
      height: 65px;
      background-color: #198754;
      color: white;
      display: flex;
      align-items: center;
      padding: 0 25px;
      font-weight: bold;
      font-size: 1.4rem;
    }

    main {
      padding: 30px;
      flex-grow: 1;
    }

    .profile-section {
      background: #fff;
      max-width: 600px;
      margin: auto;
      padding: 30px;
      border-radius: 15px;
      box-shadow: 0 0 15px rgba(0,0,0,0.1);
      text-align: center;
    }

    .profile-section img {
      width: 150px;
      height: 150px;
      border-radius: 50%;
      border: 5px solid #198754;
      object-fit: cover;
      margin-bottom: 20px;
    }

    .info-item {
      text-align: left;
      padding: 10px 0;
      border-bottom: 1px solid #ccc;
    }

    .btn-edit {
      margin-top: 30px;
      background-color: #198754;
      color: white;
      border: none;
      padding: 10px 25px;
      border-radius: 5px;
      text-decoration: none;
      transition: 0.3s;
    }

    .btn-edit:hover {
      background-color: #145c32;
    }
  </style>
</head>
<body>

<!-- Upau wa upande (Sidebar) -->
<nav id="sidebar" class="d-flex flex-column align-items-center text-white">
  <div class="text-center mb-5">
  <div class="profile-icon">
      <img src="<?= $profilePic ?>" alt="Admin" style="width: 100%; height: 100%; object-fit: cover;">
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
  <a href="report.php" class="nav-link d-flex align-items-center rounded <?= basename($_SERVER['PHP_SELF']) == 'report.php' ? 'active' : '' ?>">
    <i class="fas fa-chart-pie me-3"></i> Ripoti ya Mfumo
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
<!-- Content -->
<div id="content">
  <header id="header">
    Wasifu wa Admin
  </header>

  <main>
    <div class="profile-section">
      <img src="<?= $profilePic ?>" alt="Profile Picture">
      <h3><?= htmlspecialchars($admin['name']) ?></h3>
      <p class="text-muted">Admin</p>

      <div class="info-item">
        <strong><i class="fas fa-envelope me-2"></i>Email:</strong> <?= htmlspecialchars($admin['email']) ?>
      </div>
      <div class="info-item">
        <strong><i class="fas fa-user-tag me-2"></i>Role:</strong> Admin
      </div>
<br>
      <a href="admin_setting.php" class="btn-edit">
        <i class="fas fa-edit me-2"></i> Hariri Taarifa
      </a>
    </div>
  </main>
</div>

</body>
</html>
