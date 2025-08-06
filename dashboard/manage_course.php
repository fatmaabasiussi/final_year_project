<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

include '../inc/db.php';

// Delete Course
if(isset($_GET['delete'])){
    $id = (int)$_GET['delete'];
    $stmt = $db->prepare("DELETE FROM courses WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: manage_course.php?msg=deleted");
    exit();
}

// Fetch Courses with stats
$result = $db->query("
    SELECT c.*, u.name as scholar_name,
    (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id) as students_count
    FROM courses c 
    JOIN users u ON c.scholar_id = u.id 
    ORDER BY c.created_at DESC
");

// Get stats
$stats = [
    'total' => $db->query("SELECT COUNT(*) as count FROM courses")->fetch_assoc()['count'],
    'active' => $db->query("SELECT COUNT(*) as count FROM courses WHERE status = 'active'")->fetch_assoc()['count'],
    'students' => $db->query("SELECT COUNT(DISTINCT user_id) as count FROM enrollments")->fetch_assoc()['count'],
    'scholars' => $db->query("SELECT COUNT(DISTINCT scholar_id) as count FROM courses")->fetch_assoc()['count']
];
?>

<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Manage Courses - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
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
            width: 380px;
            background: linear-gradient(180deg, #004d00, #006600);
            color: white;
            min-height: 100vh;
            padding: 2rem 1.2rem;
            box-shadow: 3px 0 10px rgba(0, 0, 0, 0.15);
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        #sidebar .profile-icon {
            background: white;
            width: 90px;
            height: 90px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0 auto 12px;
            box-shadow: 0 0 10px rgba(25, 135, 84, 0.7);
            font-size: 2.5rem;
            color: #198754;
        }

        #sidebar h4 {
            margin-bottom: 6px;
        }

        #sidebar .badge {
            font-size: 0.9rem;
            padding: 0.4em 0.8em;
        }

        #sidebar ul.nav {
            width: 100%;
            padding-left: 0;
            margin-top: 1.5rem;
            flex-grow: 1;
        }

        #sidebar .nav-link {
            color: #d1e7dd;
            transition: background-color 0.3s, color 0.3s;
            padding-left: 1.5rem;
            padding-right: 1.5rem;
            font-weight: 600;
            letter-spacing: 0.02em;
            display: flex;
            align-items: center;
            border-radius: 6px;
            margin-bottom: 0.5rem;
        }

        #sidebar .nav-link i {
            margin-right: 0.9rem;
            font-size: 1.1rem;
        }

        #sidebar .nav-link:hover {
            background-color: #198754;
            color: white !important;
            text-decoration: none;
        }

        #sidebar .nav-link.active {
            background-color: #14532d;
            color: #fff !important;
            border-left: 5px solid #198754;
        }

        #sidebar .nav-item.mt-auto {
            margin-top: auto;
            width: 100%;
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

        .stats-card {
            border-radius: 15px;
            padding: 1.3rem 1.8rem;
            color: white;
            box-shadow: 0 4px 15px rgb(25 135 84 / 0.3);
            transition: transform 0.3s ease;
            font-weight: 600;
            height: 100%;
        }
        .stats-card:hover {
            transform: translateY(-7px);
            box-shadow: 0 8px 25px rgb(25 135 84 / 0.5);
        }

        .stats-icon {
            font-size: 2.8rem;
            opacity: 0.85;
        }

        /* Courses Grid */
        .course-card {
            transition: transform 0.2s;
            border-radius: 10px;
            overflow: hidden;
            height: 100%;
            display: flex;
            flex-direction: column;
            box-shadow: 0 3px 10px rgb(0 0 0 / 0.1);
        }
        .course-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 18px rgb(0 0 0 / 0.15);
        }
        .course-image {
            height: 160px;
            object-fit: cover;
            width: 100%;
        }
        .card-body {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .status-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 0.35em 0.65em;
            font-size: 0.85em;
            border-radius: 20px;
            color: white;
        }
        .bg-success.status-badge {
            background-color: #198754;
        }
        .bg-secondary.status-badge {
            background-color: #6c757d;
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
        <span>Manage Courses</span>
    </header>

    <!-- Main Content -->
    <main>
        <?php if(isset($_GET['msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php 
                switch($_GET['msg']) {
                    case 'deleted':
                        echo "Course deleted successfully";
                        break;
                    case 'added':
                        echo "New course added successfully";
                        break;
                    case 'updated':
                        echo "Course updated successfully";
                        break;
                }
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Stats Cards -->
        <div class="row g-4 mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="card stats-card bg-primary text-white h-100">
                    <div class="card-body d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="card-title mb-0">Total Courses</h6>
                            <h2 class="my-3"><?= $stats['total'] ?></h2>
                            <p class="mb-0">Available courses</p>
                        </div>
                        <div class="stats-icon">
                            <i class="bi bi-book"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card stats-card bg-success text-white h-100">
                    <div class="card-body d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="card-title mb-0">Active Courses</h6>
                            <h2 class="my-3"><?= $stats['active'] ?></h2>
                            <p class="mb-0">Currently active</p>
                        </div>
                        <div class="stats-icon">
                            <i class="bi bi-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card stats-card bg-info text-white h-100">
                    <div class="card-body d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="card-title mb-0">Total Students</h6>
                            <h2 class="my-3"><?= $stats['students'] ?></h2>
                            <p class="mb-0">Enrolled students</p>
                        </div>
                        <div class="stats-icon">
                            <i class="bi bi-mortarboard"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card stats-card bg-warning text-white h-100">
                    <div class="card-body d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="card-title mb-0">Total Scholars</h6>
                            <h2 class="my-3"><?= $stats['scholars'] ?></h2>
                            <p class="mb-0">Teaching scholars</p>
                        </div>
                        <div class="stats-icon">
                            <i class="bi bi-person-workspace"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Courses Grid -->
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0">All Courses</h5>
                <a href="add_course.php" class="btn btn-success">
                    <i class="bi bi-plus-lg me-1"></i> Add New Course
                </a>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <?php while($course = $result->fetch_assoc()): ?>
                    <div class="col-md-6 col-lg-4 d-flex">
                        <div class="card course-card position-relative w-100">
                            <img src="<?= htmlspecialchars($course['image'] ?? '../assets/img/course-default.jpg') ?>" 
                                 class="card-img-top course-image" 
                                 alt="<?= htmlspecialchars($course['title']) ?>">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title mb-1"><?= htmlspecialchars($course['title']) ?></h5>
                                <p class="text-muted mb-2">
                                    <i class="bi bi-person me-1"></i>
                                    <?= htmlspecialchars($course['scholar_name']) ?>
                                </p>
                                <p class="card-text small mb-3" style="flex-grow: 1;">
                                    <?php if (!empty($course['document_url'])): ?>
                                        <i class="fas fa-file-alt text-primary me-2"></i>
                                        <a href="../<?= htmlspecialchars($course['document_url']) ?>" target="_blank" class="text-decoration-underline">Pakua Document ya Kozi</a>
                                    <?php else: ?>
                                        <span class="text-muted">Hakuna document</span>
                                    <?php endif; ?>
                                </p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge bg-primary">
                                        <i class="bi bi-people me-1"></i>
                                        <?= $course['students_count'] ?> Students
                                    </span>
                                    <div class="btn-group">
                                        <a href="edit_course.php?id=<?= $course['id'] ?>" 
                                           class="btn btn-sm btn-outline-primary" 
                                           title="Edit Course">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="?delete=<?= $course['id'] ?>" 
                                           class="btn btn-sm btn-outline-danger" 
                                           title="Delete Course"
                                           onclick="return confirm('Are you sure you want to delete this course? This action cannot be undone.')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <span class="badge <?= $course['status'] == 'active' ? 'bg-success' : 'bg-secondary' ?> status-badge">
                                <?= ucfirst($course['status']) ?>
                            </span>
                        </div>
                    </div>
                    <?php endwhile; ?>
                    <?php if ($result->num_rows === 0): ?>
                        <p class="text-muted">No courses found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
