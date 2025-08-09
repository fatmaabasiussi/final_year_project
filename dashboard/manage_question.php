<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

require_once __DIR__ . '/../includes/functions.php';
$db = Database::getInstance()->getConnection();

// Delete Question
if(isset($_GET['delete'])){
    $id = (int)$_GET['delete'];
    $stmt = $db->prepare("DELETE FROM questions WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: manage_question.php?msg=deleted");
    exit();
}

// Fetch Questions with related data
$result = $db->query("
    SELECT q.*, u.name as user_name, c.title as course_title
    FROM questions q 
    JOIN users u ON q.user_id = u.id 
    LEFT JOIN courses c ON q.course_id = c.id
    ORDER BY q.created_at DESC
");

// Get stats
$stats = [
    'total' => $db->query("SELECT COUNT(*) as count FROM questions")->fetch_assoc()['count'],
    'answered' => $db->query("SELECT COUNT(*) as count FROM questions WHERE answer IS NOT NULL AND answer != ''")->fetch_assoc()['count'],
    'unanswered' => $db->query("SELECT COUNT(*) as count FROM questions WHERE answer IS NULL OR answer = ''")->fetch_assoc()['count'],
    'users' => $db->query("SELECT COUNT(DISTINCT user_id) as count FROM questions")->fetch_assoc()['count']
];
?>

<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Manage Questions - Admin Dashboard</title>
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
            width: 500px;
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

        /* Questions Cards */
        .question-card {
            transition: transform 0.2s;
            margin-bottom: 15px;
            border-radius: 10px;
            box-shadow: 0 3px 8px rgb(0 0 0 / 0.1);
        }
        .question-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 18px rgb(0 0 0 / 0.15);
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
        <span>Manage Questions</span>
    </header>

    <!-- Main Content -->
    <main>
        <?php if(isset($_GET['msg'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= $_GET['msg'] === 'deleted' ? 'Question deleted successfully' : '' ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Stats Cards -->
        <div class="row g-4 mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="card stats-card bg-primary text-white h-100">
                    <div class="card-body d-flex justify-content-between align-items-start">
                        <div>
                            <h6>Total Questions</h6>
                            <h2><?= $stats['total'] ?></h2>
                            <p>All questions asked</p>
                        </div>
                        <div class="stats-icon">
                            <i class="bi bi-question-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card stats-card bg-success text-white h-100">
                    <div class="card-body d-flex justify-content-between align-items-start">
                        <div>
                            <h6>Answered</h6>
                            <h2><?= $stats['answered'] ?></h2>
                            <p>Questions with answers</p>
                        </div>
                        <div class="stats-icon">
                            <i class="bi bi-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card stats-card bg-warning text-white h-100">
                    <div class="card-body d-flex justify-content-between align-items-start">
                        <div>
                            <h6>Pending</h6>
                            <h2><?= $stats['unanswered'] ?></h2>
                            <p>Awaiting answers</p>
                        </div>
                        <div class="stats-icon">
                            <i class="bi bi-clock"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card stats-card bg-info text-white h-100">
                    <div class="card-body d-flex justify-content-between align-items-start">
                        <div>
                            <h6>Active Users</h6>
                            <h2><?= $stats['users'] ?></h2>
                            <p>Users asking questions</p>
                        </div>
                        <div class="stats-icon">
                            <i class="bi bi-people"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Questions List -->
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3"><h5>All Questions</h5></div>
            <div class="card-body">
                <div class="row g-4">
                    <?php while($question = $result->fetch_assoc()): ?>
                        <div class="col-12">
                            <div class="card question-card">
                                <div class="card-body">
                                    <h5><?= htmlspecialchars(substr($question['question'] ?? '', 0, 100)) ?>...</h5>
                                    <p class="text-muted">
                                        <small>
                                            <i class="bi bi-person"></i> <?= htmlspecialchars($question['user_name']) ?> |
                                            <i class="bi bi-book"></i> <?= htmlspecialchars($question['course_title'] ?? 'N/A') ?> |
                                            <i class="bi bi-clock"></i> <?= date('M d, Y', strtotime($question['created_at'])) ?>
                                        </small>
                                    </p>
                                    <p><?= htmlspecialchars(substr($question['question'] ?? '', 0, 200)) ?>...</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge bg-<?= ($question['answer'] && trim($question['answer']) != '') ? 'success' : 'warning' ?>">
                                            <?= ($question['answer'] && trim($question['answer']) != '') ? 'Answered' : 'Pending' ?>
                                        </span>
                                        <span class="badge bg-secondary"><?= ucfirst($question['status'] ?? 'pending') ?></span>
                                        <div class="btn-group">
                                            <a href="view_question.php?id=<?= $question['id'] ?>" class="btn btn-sm btn-outline-primary" title="View Question">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="?delete=<?= $question['id'] ?>" onclick="return confirm('Delete this question?')" class="btn btn-sm btn-outline-danger" title="Delete Question">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                    <?php if ($result->num_rows === 0): ?>
                        <p class="text-muted text-center">No questions found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
