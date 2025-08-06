<?php 
session_start();

// Ruhusu user pekee (role = 'user')
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'user') {
    header("Location: ../login.php");
    exit;
}

$conn = new mysqli("localhost", "root", "1234", "religion_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];  // Hapa ni 'user' tu
$success_msg = "";
$error_msg = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $update = $conn->query("UPDATE users SET name='$name', email='$email', password='$hashed_password' WHERE id=$user_id");
    } else {
        $update = $conn->query("UPDATE users SET name='$name', email='$email' WHERE id=$user_id");
    }

    if ($update) {
        $success_msg = "Taarifa zimehifadhiwa kikamilifu!";
        $_SESSION['name'] = $name; // Update session name
    } else {
        $error_msg = "Hitilafu imetokea. Tafadhali jaribu tena.";
    }
}

// Get user data
$result = $conn->query("SELECT * FROM users WHERE id=$user_id");
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="sw">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Settings - Know Your Religion</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
  <style>
    body {
      display: flex;
      overflow-x: hidden;
      margin: 0;
      min-height: 100vh;
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
      margin: 0 auto 10px auto;
      font-size: 2rem;
      color: #198754;
    }

    #content {
      flex-grow: 1;
      background-color: #f8f9fa;
      padding: 20px 30px;
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
      margin-bottom: 20px;
    }

    .form-control:focus {
      border-color: #198754;
      box-shadow: 0 0 0 0.2rem rgba(25, 135, 84, 0.25);
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

  <!-- Content Area -->
  <div id="content">
    <header id="header">Taarifa za Mtumiaji - Settings</header>

    <main class="container py-4">
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
