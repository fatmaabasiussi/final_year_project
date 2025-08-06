<?php
session_start();

// Check if user is logged in and is a user
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'user') {
    header("Location: ../login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "1234", "religion_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle enroll action
if (isset($_GET['enroll'])) {
    $course_id = intval($_GET['enroll']);
    $user_id = $_SESSION['user_id'];
    $check = $conn->query("SELECT * FROM enrollments WHERE user_id=$user_id AND course_id=$course_id");
    if ($check->num_rows == 0) {
        $conn->query("INSERT INTO enrollments (user_id, course_id) VALUES ($user_id, $course_id)");
        header("Location: my_courses.php?enrolled=1");
        exit();
    } else {
        header("Location: my_courses.php?enrolled=0");
        exit();
    }
}

// Get all courses with course and instructor details
$sql = "SELECT c.*, u.name as sheikh_name FROM courses c JOIN users u ON c.scholar_id = u.id ORDER BY c.created_at DESC";
$result = $conn->query($sql);

$courses = [];
while ($row = $result->fetch_assoc()) {
    $courses[] = $row;
}

// Fetch all enrollments for this user
$user_id = $_SESSION['user_id'];
$enrolled_courses = [];
$enroll_result = $conn->query("SELECT course_id FROM enrollments WHERE user_id = $user_id");
while ($row = $enroll_result->fetch_assoc()) {
    $enrolled_courses[] = $row['course_id'];
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kozi Zangu - Know Your Religion</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            display: flex;
            margin: 0;
            overflow-x: hidden;
        }
        nav#sidebar {
            background: linear-gradient(to bottom, #0f2027, #203a43, #2c5364);
            min-height: 100vh;
            width: 410px; 
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
            padding-bottom: 50px;
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
        .courses-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            padding: 20px;
        }
        .course-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            border: 1px solid rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .course-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        .course-title {
            color: #198754;
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 15px;
        }
        .sheikh-info {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        .sheikh-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #198754;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
            font-size: 1.2rem;
        }
        .progress {
            height: 10px;
            border-radius: 5px;
            margin-top: 15px;
        }
        .progress-bar {
            background-color: #198754;
        }
        .btn-continue {
            background-color: #198754;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }
        .btn-continue:hover {
            background-color:  #198754;
            color: white;
        }
        .no-courses {
            text-align: center;
            padding: 40px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .wrapper {
    display: flex;
    width: 100vw;
}
    </style>
</head>
<body>
<div class="wrapper d-flex">
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
    <div id="content" class="d-flex flex-column flex-grow-1">
        <header id="header">
            <span>Kozi Zangu</span>
        </header>
        <main class="py-4" style="padding-left: 30px; padding-right: 30px;">
            <?php if (empty($courses)): ?>
                <div class="no-courses">
                    <i class="fas fa-book-open fa-3x mb-3 text-primary"></i>
                    <h3>Hakuna Kozi Bado</h3>
                    <p class="text-muted">Haujajisajili kwenye kozi yoyote. Tafadhali angalia kozi zetu na ujisajili.</p>
                    <a href="courses.php" class="btn btn-continue">
                        <i class="fas fa-plus me-2"></i> Angalia Kozi
                    </a>
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($courses as $course): ?>
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="course-card h-100">
                                <div class="course-title">
                                    <?= htmlspecialchars($course['title']) ?>
                                </div>
                                <div class="sheikh-info">
                                    <div class="sheikh-avatar">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold">Sheikh <?= htmlspecialchars($course['sheikh_name']) ?></div>
                                        <small class="text-muted">Mwalimu</small>
                                    </div>
                                </div>
                                <p class="mb-2">
                                    <i class="fas fa-clock text-success me-2"></i>
                                    Ulijiunga: <?= date('d/m/Y', strtotime($course['created_at'])) ?>
                                </p>
                                <?php if (!empty($course['document_url'])): ?>
                                    <p class="mb-2">
                                        <i class="fas fa-file-alt text-primary me-2"></i>
                                        
                                        <a href="../<?= htmlspecialchars($course['document_url']) ?>" target="_blank" class="text-decoration-underline">Pakua Document ya Kozi</a>
                                    </p>
                                <?php endif; ?>
                                <div class="text-end mt-3">
                                    <?php if (in_array($course['id'], $enrolled_courses)): ?>
                                        <a href="take_mcq.php?course_id=<?= $course['id'] ?>" class="btn btn-sm btn-primary">Jibu MCQ</a>
                                        <a href="view_course.php?id=<?= $course['id'] ?>" class="btn btn-continue">
                                            Endelea na Kozi <i class="fas fa-arrow-right ms-2"></i>
                                        </a>
                                    <?php else: ?>
                                        <a href="take_mcq.php?course_id=<?= $course['id'] ?>" class="btn btn-sm btn-primary">
  Jibu MCQ
</a>

                                        <a href="my_courses.php?enroll=<?= $course['id'] ?>" class="btn btn-success">
                                            Jiunge na Kozi <i class="fas fa-plus ms-2"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
