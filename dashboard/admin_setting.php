<?php
session_start();

// Ruhusu admin tu
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

$conn = new mysqli("localhost", "root", "1234", "religion_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$admin_id = $_SESSION['user_id'];
$role = $_SESSION['role']; // 'admin'
$success_msg = "";
$error_msg = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone_number = $conn->real_escape_string($_POST['phone_number'] ?? '');
    $password = $_POST['password'];

    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $update = $conn->query("UPDATE users SET name='$name', email='$email', password='$hashed_password', phone_number='$phone_number' WHERE id=$admin_id");
    } else {
        $update = $conn->query("UPDATE users SET name='$name', email='$email', phone_number='$phone_number' WHERE id=$admin_id");
    }

    if ($update) {
        $success_msg = "Taarifa zimehifadhiwa kikamilifu!";
        $_SESSION['name'] = $name; // Update session name
    } else {
        $error_msg = "Hitilafu imetokea. Tafadhali jaribu tena.";
    }
}

// Get admin data
$result = $conn->query("SELECT * FROM users WHERE id=$admin_id");
$user = $result->fetch_assoc();

?>
<!DOCTYPE html>
<html lang="sw">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin Settings - Know Your Religion</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
  <style>
    body {
      display: flex;
      overflow-x: hidden;
      margin: 0;
      background-color: #e6f0e9;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      min-height: 100vh;
    }

    nav#sidebar {
      background: linear-gradient(180deg, #004d00, #006600);
      min-height: 100vh;
      width: 350px;
      color: white;
      padding: 2rem 1.2rem;
      box-shadow: 3px 0 10px rgba(0, 0, 0, 0.15);
      display: flex;
      flex-direction: column;
      align-items: center;
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
      font-size: 3rem;
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

    .form-control:focus {
      border-color: #198754;
      box-shadow: 0 0 0 0.2rem rgba(25, 135, 84, 0.25);
    }

    button.btn-success {
      background-color: #198754;
      border-color: #198754;
    }

    button.btn-success:hover {
      background-color: #14532d;
      border-color: #14532d;
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

  <!-- Content Area -->
  <div id="content" class="d-flex flex-column flex-grow-1">
    <!-- Header -->
    <header id="header">
      Karibu, <?= htmlspecialchars($_SESSION['name']) ?>
    </header>

    <!-- Main Content -->
    <main>
      <?php if ($success_msg): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          <?= htmlspecialchars($success_msg) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php elseif ($error_msg): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <?= htmlspecialchars($error_msg) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>

      <h2>Badilisha Taarifa zako</h2>
      <form method="POST" autocomplete="off" novalidate>
        <div class="mb-3">
          <label for="name" class="form-label">Jina</label>
          <input type="text" id="name" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
        </div>
        <div class="mb-3">
          <label for="email" class="form-label">Barua Pepe</label>
          <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
        </div>
        <div class="mb-3">
          <label for="phone_number" class="form-label">Namba ya Simu</label>
          <input type="text" id="phone_number" name="phone_number" class="form-control" value="<?= htmlspecialchars($user['phone_number'] ?? '') ?>" />
        </div>
        <div class="mb-3">
          <label for="password" class="form-label">Nywila Mpya (acha kama hutaki kubadilisha)</label>
          <input type="password" id="password" name="password" class="form-control" autocomplete="new-password">
        </div>
        <button type="submit" class="btn btn-success">
          <i class="fas fa-save me-2"></i> Hifadhi Mabadiliko
        </button>
      </form>
    </main>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
