<?php  
session_start();

// Check if user is logged in and is a user (mwanafunzi) only
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: ../login.php");
    exit();
}

// Connect to the database
$conn = new mysqli("localhost", "root", "1234", "religion_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch user info with additional details
$user_id = $_SESSION['user_id'];
$sql = "SELECT u.*, 
        (SELECT COUNT(*) FROM enrollments WHERE user_id = u.id) as total_courses,
        (SELECT COUNT(*) FROM questions WHERE user_id = u.id) as total_questions,
        (SELECT created_at FROM enrollments WHERE user_id = u.id ORDER BY created_at ASC LIMIT 1) as member_since
        FROM users u WHERE u.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Get profile picture URL
$profilePic = !empty($user['profile_pic']) 
    ? 'uploads/' . htmlspecialchars($user['profile_pic']) 
    : 'https://ui-avatars.com/api/?name=' . urlencode($user['name']) . '&background=007bff&color=fff';

// Role display name for user only
$roleDisplayNames = [
    'user' => 'Mwanafunzi'
];
?>
<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Wasifu Wangu - Know Your Religion</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
    <style>
        body {
          display: flex;
          overflow-x: hidden;
          margin: 0;
          background-color: #f8f9fa;
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
          padding-left: 1.2rem;
          padding-right: 1.2rem;
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
          margin: 0 auto 10px;
        }

        #content {
          flex-grow: 1;
          background-color: #f8f9fa;
          padding-bottom: 50px;
          min-height: 100vh;
          display: flex;
          flex-direction: column;
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

        .profile-header {
          text-align: center;
          margin: 30px auto 40px;
          background: white;
          border-radius: 10px;
          padding: 20px;
          box-shadow: 0 2px 10px rgba(0,0,0,0.1);
          max-width: 600px;
        }

        .profile-pic {
          width: 150px;
          height: 150px;
          border-radius: 50%;
          object-fit: cover;
          margin: 0 auto 20px;
          border: 5px solid #198754;
        }

        .stats-card {
          background: white;
          border-radius: 10px;
          padding: 20px;
          margin-bottom: 20px;
          box-shadow: 0 2px 10px rgba(0,0,0,0.1);
          transition: transform 0.3s ease;
          text-align: center;
          max-width: 200px;
          margin: 0 auto;
        }

        .stats-card:hover {
          transform: translateY(-5px);
        }

        .stats-icon {
          width: 50px;
          height: 30px;
          background: rgba(25, 135, 84, 0.1);
          border-radius: 10px;
          display: flex;
          align-items: center;
          justify-content: center;
          font-size: 1.5rem;
          color: #198754;
          margin-bottom: 15px;
          margin-left: auto;
          margin-right: auto;
        }

        .info-card {
          background: white;
          border-radius: 10px;
          padding: 20px;
          margin: 30px auto;
          box-shadow: 0 2px 10px rgba(0,0,0,0.1);
          max-width: 600px;
        }

        .info-item {
          margin-bottom: 15px;
          padding-bottom: 10px;
          border-bottom: 1px solid #eee;
        }

        .info-item:last-child {
          margin-bottom: 0;
          padding-bottom: 0;
          border-bottom: none;
        }

        .btn-edit {
          background-color: #198754;
          color: white;
          padding: 10px 25px;
          border-radius: 5px;
          text-decoration: none;
          transition: background-color 0.3s ease;
          display: inline-flex;
          align-items: center;
          gap: 0.5rem;
          margin: 0 auto 50px;
          max-width: 250px;
          justify-content: center;
        }

        .btn-edit:hover {
          background-color: #145c32;
          color: white;
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

  <!-- Content Area -->
  <div id="content" class="d-flex flex-column flex-grow-1">
    <!-- Header -->
    <header id="header">
      <span>Wasifu Wangu</span>
    </header>

    <!-- Main Content -->
    <main class="container my-4 flex-grow-1">
      <div class="profile-header">
        <img src="<?= $profilePic ?>" alt="Picha ya mtumiaji" class="profile-pic" />
        <h2><?= htmlspecialchars($user['name']) ?></h2>
        <h6 class="text-muted"><?= $roleDisplayNames[$_SESSION['role']] ?></h6>
      </div>

      <div class="d-flex justify-content-center gap-4 flex-wrap mb-5">
        <div class="stats-card">
          <div class="stats-icon"><i class="fas fa-book"></i></div>
          <h3><?= (int)$user['total_courses'] ?></h3>
          <p>Kozi Zilizosajiliwa</p>
        </div>
        <div class="stats-card">
          <div class="stats-icon"><i class="fas fa-question-circle"></i></div>
          <h3><?= (int)$user['total_questions'] ?></h3>
          <p>Maswali Uliyouliza</p>
        </div>
        <div class="stats-card">
          <div class="stats-icon"><i class="fas fa-calendar-check"></i></div>
          <h3><?= $user['member_since'] ? date('d M Y', strtotime($user['member_since'])) : 'N/A' ?></h3>
          <p>Umejiunga Tarehe</p>
        </div>
      </div>

      <div class="info-card">
        <h4 class="mb-4"><i class="fas fa-id-card me-2"></i> Maelezo ya Mtumiaji</h4>
        <div class="info-item">
          <strong><i class="fas fa-user me-2"></i>Jina:</strong> 
          <?= htmlspecialchars($user['name']) ?>
        </div>
        <div class="info-item">
          <strong><i class="fas fa-envelope me-2"></i>Barua Pepe:</strong> 
          <?= htmlspecialchars($user['email']) ?>
        </div>
        <div class="info-item">
          <strong><i class="fas fa-user-tag me-2"></i>Role:</strong> 
          <?= $roleDisplayNames[$_SESSION['role']] ?>
        </div>
      </div>

      <a href="settings.php" class="btn-edit">
        <i class="fas fa-pencil-alt"></i>
        Hariri Taarifa Zangu
      </a>
    </main>
  </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
