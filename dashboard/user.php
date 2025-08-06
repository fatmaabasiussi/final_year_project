<?php
session_start();

// Ruhusu user tu
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'user') {
    header("Location: ../login.php");
    exit;
}

$conn = new mysqli("localhost", "root", "1234", "religion_db");

$enrolled_msg = "";

// Kujiunga na kozi
if (isset($_GET['enroll'])) {
    $course_id = $_GET['enroll'];
    $user_id = $_SESSION['user_id'];

    $check = $conn->query("SELECT * FROM enrollments WHERE user_id=$user_id AND course_id=$course_id");
    if ($check->num_rows == 0) {
        $conn->query("INSERT INTO enrollments (user_id, course_id) VALUES ($user_id, $course_id)");
        $enrolled_msg = "Umejiunga na kozi kikamilifu!";
    } else {
        $enrolled_msg = "Tayari umejiunga na kozi hii.";
    }
}

// Kozi zote
$courses = $conn->query("SELECT c.*, u.name AS scholar FROM courses c JOIN users u ON c.scholar_id = u.id");

// Kozi alizojiunga
$user_id = $_SESSION['user_id'];
$my_courses = $conn->query("SELECT c.* FROM enrollments e JOIN courses c ON e.course_id = c.id WHERE e.user_id = $user_id");
?>

<!DOCTYPE html>
<html lang="sw">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Dashibodi ya Mtumiaji</title>
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
      width: 450px;
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
        <a href="take_mcq.php" class="nav-link d-flex align-items-center px-3 py-2 rounded <?= basename($_SERVER['PHP_SELF']) == 'take_mcq.php' ? 'active' : '' ?>">
          <i class="fas fa-pencil-alt me-2"></i> Jibu Maswali ya MCQ
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

  <!-- Eneo la Maudhui -->
  <div id="content" class="d-flex flex-column flex-grow-1">
    <!-- Kichwa cha ukurasa -->
    <header id="header">
      <span>Karibu, <?= $_SESSION['name'] ?></span>
    </header>

    <!-- Maudhui makuu -->
    <main class="container-fluid py-4">
      <div class="text-center mb-5">
        <h1>Karibu kwenye Tovuti ya Kiislamu</h1>
        <p class="lead">Jifunze kuhusu dini ya Kiislamu kutoka kwa Masheikh na walimu wa Kiislamu</p>
      </div>

      <div class="row">
        <div class="col-md-4 mb-4">
          <div class="card h-100">
            <div class="card-body">
              <h5 class="card-title"><i class="fas fa-book-open text-primary"></i> Kozi</h5>
              <p class="card-text">Jiunge na kozi zetu za kinafsi za Kiislamu zinazohusu mada mbalimbali.</p>
            </div>
          </div>
        </div>
        <div class="col-md-4 mb-4">
          <div class="card h-100">
            <div class="card-body">
              <h5 class="card-title"><i class="fas fa-question-circle text-success"></i> Uliza Maswali</h5>
              <p class="card-text">Pata majibu ya maswali yako kuhusu dini ya Kiislamu kutoka kwa masheikh waliobobea.</p>
            </div>
          </div>
        </div>
        <div class="col-md-4 mb-4">
          <div class="card h-100">
            <div class="card-body">
              <h5 class="card-title"><i class="fas fa-user-graduate text-info"></i> Masheikh</h5>
              <p class="card-text">Jifunze kutoka kwa timu yetu ya masheikh waliothibitishwa wa dini ya Kiislamu.</p>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="js/script.js"></script>
</body>
</html>
