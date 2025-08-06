<?php 
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'user') {
    header("Location: ../login.php");
    exit();
}
if (!isset($_GET['id'])) {
    header("Location: my_courses.php");
    exit();
}

$course_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

$conn = new mysqli("localhost", "root", "1234", "religion_db");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Fetch course info
$sql = "SELECT c.*, u.name as scholar_name, e.progress, e.last_accessed,
        (SELECT COUNT(*) FROM course_modules WHERE course_id = c.id) as total_modules,
        (SELECT COUNT(*) FROM module_completion WHERE user_id = ? AND module_id IN 
            (SELECT id FROM course_modules WHERE course_id = c.id)
        ) as completed_modules
        FROM courses c
        JOIN users u ON c.scholar_id = u.id
        JOIN enrollments e ON c.id = e.course_id
        WHERE c.id = ? AND e.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $user_id, $course_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    header("Location: my_courses.php");
    exit();
}
$course = $result->fetch_assoc();

// Get modules
$modules_sql = "SELECT m.*, 
                (SELECT completed_at FROM module_completion 
                 WHERE user_id = ? AND module_id = m.id) as completed_at
                FROM course_modules m 
                WHERE m.course_id = ?
                ORDER BY m.order_number";
$modules_stmt = $conn->prepare($modules_sql);
$modules_stmt->bind_param("ii", $user_id, $course_id);
$modules_stmt->execute();
$modules_result = $modules_stmt->get_result();
$modules = [];
while ($module = $modules_result->fetch_assoc()) $modules[] = $module;

$conn->close();

$progress_percentage = $course['total_modules'] > 0
    ? round(($course['completed_modules'] / $course['total_modules']) * 100)
    : 0;
?>
<!DOCTYPE html>
<html lang="sw">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= htmlspecialchars($course['title']); ?> - View Course</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
  <style>
    body { display: flex; overflow-x: hidden; margin: 0; }
    nav#sidebar {
      background: linear-gradient(to bottom, #0f2027, #203a43, #2c5364);
      min-height: 100vh;
      width: 260px;
      color: white;
    }
    #sidebar .nav-link { color: white; transition: 0.3s; }
    #sidebar .nav-link:hover { background-color: #198754; color: #fff !important; }
    .nav-link.active { background-color: #198754 !important; }
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
    .course-header, .course-description, .module-item {
      background: white;
      border-radius: 10px;
      padding: 20px;
      margin-bottom: 20px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .course-title {
      color: #198754;
      font-size: 1.75rem;
      font-weight: 600;
      margin-bottom: 15px;
    }
    .progress-bar {
      background-color: #198754;
    }
    .sheikh-info {
      display: flex;
      align-items: center;
      margin: 20px 0;
      padding: 15px;
      background: rgba(25, 135, 84, 0.1);
      border-radius: 10px;
    }
    .sheikh-avatar {
      width: 60px;
      height: 60px;
      border-radius: 50%;
      background-color: #198754;
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-right: 15px;
      font-size: 1.5rem;
    }
    .module-list { list-style: none; padding: 0; }
    .module-item {
      display: flex;
      align-items: center;
      transition: 0.3s;
    }
    .module-item:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }
    .module-number {
      width: 35px;
      height: 35px;
      background: #198754;
      color: white;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: bold;
      margin-right: 15px;
    }
    .module-status {
      margin-left: 15px;
      font-size: 1.2rem;
    }
    .completed { color: #28a745; }
    .pending { color: #ffc107; }
    .btn-start {
      background-color: #198754;
      color: white;
      padding: 8px 20px;
      border-radius: 5px;
      text-decoration: none;
      transition: 0.3s;
    }
    .btn-start:hover {
      background-color: #157347;
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
    <header id="header">
      <a href="my_courses.php" class="text-white text-decoration-none me-3"><i class="fas fa-arrow-left"></i></a>
      <?= htmlspecialchars($course['title']); ?>
    </header>
    <main class="container-fluid py-4">
      <div class="course-header">
        <div class="course-title"><?= htmlspecialchars($course['title']); ?></div>
        <div class="sheikh-info">
          <div class="sheikh-avatar"><i class="fas fa-user"></i></div>
          <div>
            <strong>Sheikh <?= htmlspecialchars($course['scholar_name']); ?></strong><br>
            <small class="text-muted">Mwalimu</small>
          </div>
        </div>
      </div>
      <?php 
      if (!empty($course['document_url'])): 
          $allowed_folder = realpath(__DIR__ . '/../uploads/courses/');
          $real_path = realpath(__DIR__ . '/../' . $course['document_url']);
          if ($real_path !== false && strpos($real_path, $allowed_folder) === 0):
              $ext = strtolower(pathinfo($course['document_url'], PATHINFO_EXTENSION));
      ?>
        <div class="bg-white p-4 rounded-3 shadow-sm">
          <h5 class="mb-3"><i class="fas fa-file-alt me-2"></i> Document ya Kozi</h5>
          <?php if ($ext === 'pdf'): ?>
            <embed src="../<?= htmlspecialchars($course['document_url']) ?>" type="application/pdf" width="100%" height="600px" />
            <div class="mt-2">
              <a href="../<?= htmlspecialchars($course['document_url']) ?>" target="_blank" class="btn btn-primary">Pakua PDF</a>
            </div>
          <?php else: ?>
            <a href="../<?= htmlspecialchars($course['document_url']) ?>" target="_blank" class="btn btn-primary">Fungua/Download Document</a>
          <?php endif; ?>
        </div>
      <?php else: ?>
        <div class="alert alert-danger">Document haipatikani au haikutolewa kwa usahihi.</div>
      <?php endif; ?>
      <?php else: ?>
        <div class="alert alert-info">Hakuna document ya kozi hii.</div>
      <?php endif; ?>
    </main>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
