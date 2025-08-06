<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Check if course ID is provided
if (!isset($_GET['id'])) {
    header("Location: ../my_courses.php");
    exit();
}

$course_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

$conn = new mysqli("localhost", "root", "1234", "religion_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get course details with instructor info and user progress
$sql = "SELECT c.*, u.name as sheikh_name, u.bio as sheikh_bio, e.progress, e.last_accessed,
        (SELECT COUNT(*) FROM course_modules WHERE course_id = c.id) as total_modules,
        (SELECT COUNT(*) FROM module_completion WHERE user_id = ? AND module_id IN 
            (SELECT id FROM course_modules WHERE course_id = c.id)
        ) as completed_modules
        FROM courses c
        JOIN users u ON c.instructor_id = u.id
        JOIN enrollments e ON c.id = e.course_id
        WHERE c.id = ? AND e.user_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $user_id, $course_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: ../my_courses.php");
    exit();
}

$course = $result->fetch_assoc();

// Get course modules
$modules_sql = "SELECT m.*, 
                (SELECT completed_at FROM module_completion 
                 WHERE user_id = ? AND module_id = m.id) as completed_at
                FROM course_modules m 
                WHERE m.course_id = ?
                ORDER BY m.sequence";

$modules_stmt = $conn->prepare($modules_sql);
$modules_stmt->bind_param("ii", $user_id, $course_id);
$modules_stmt->execute();
$modules_result = $modules_stmt->get_result();

$modules = [];
while ($module = $modules_result->fetch_assoc()) {
    $modules[] = $module;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($course['title']); ?> - Know Your Religion</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-image: url('https://images.unsplash.com/photo-1542816417-0983c9c9ad53?ixlib=rb-4.0.3');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            min-height: 100vh;
            position: relative;
        }

        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            z-index: 0;
        }

        .navbar {
            position: relative;
            z-index: 2;
            background-color: rgba(33, 37, 41, 0.9) !important;
        }

        .main-container {
            position: relative;
            z-index: 1;
            padding: 40px 0;
            min-height: calc(100vh - 200px);
        }

        .course-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
        }

        .course-header {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }

        .course-title {
            color: rgb(16, 71, 249);
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .progress {
            height: 10px;
            border-radius: 5px;
            margin: 15px 0;
        }

        .progress-bar {
            background-color: rgb(16, 71, 249);
        }

        .sheikh-info {
            display: flex;
            align-items: center;
            margin: 20px 0;
            padding: 15px;
            background: rgba(16, 71, 249, 0.1);
            border-radius: 10px;
        }

        .sheikh-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background-color: rgb(16, 71, 249);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 1.5rem;
        }

        .module-list {
            list-style: none;
            padding: 0;
        }

        .module-item {
            background: white;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            border: 1px solid rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .module-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .module-title {
            font-weight: 500;
            color: #333;
            flex-grow: 1;
        }

        .module-status {
            margin-left: 15px;
        }

        .module-status i {
            font-size: 1.2rem;
        }

        .completed {
            color: #28a745;
        }

        .pending {
            color: #ffc107;
        }

        .btn-start {
            background-color: rgb(16, 71, 249);
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }

        .btn-start:hover {
            background-color: rgb(13, 60, 214);
            color: white;
        }

        .course-description {
            background: rgba(255, 255, 255, 0.8);
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
        }

        footer {
            position: relative;
            z-index: 2;
            background-color: rgba(33, 37, 41, 0.9) !important;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="../index.php">Know Your Religion</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../dashboard/user.php">
                            <i class="fas fa-home"></i> Dashibodi
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../my_courses.php">
                            <i class="fas fa-book"></i> Kozi Zangu
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../dashboard/profile.php">
                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['name']); ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../auth/logout.php">
                            <i class="fas fa-sign-out-alt"></i> Toka
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="main-container">
        <div class="container">
            <div class="course-container">
                <div class="course-header">
                    <h1 class="course-title"><?php echo htmlspecialchars($course['title']); ?></h1>
                    
                    <div class="progress">
                        <div class="progress-bar" role="progressbar" 
                             style="width: <?php echo $course['progress']; ?>%"
                             aria-valuenow="<?php echo $course['progress']; ?>" 
                             aria-valuemin="0" aria-valuemax="100">
                            <?php echo $course['progress']; ?>%
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-0">
                                <i class="fas fa-book-reader text-primary"></i>
                                Modules Completed: <?php echo $course['completed_modules']; ?>/<?php echo $course['total_modules']; ?>
                            </p>
                        </div>
                        <div>
                            <p class="mb-0">
                                <i class="fas fa-clock text-primary"></i>
                                Last Accessed: <?php echo date('d/m/Y', strtotime($course['last_accessed'])); ?>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="sheikh-info">
                    <div class="sheikh-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div>
                        <h5 class="mb-1">Sheikh <?php echo htmlspecialchars($course['sheikh_name']); ?></h5>
                        <p class="mb-0"><?php echo htmlspecialchars($course['sheikh_bio'] ?? 'Mwalimu wa Kozi'); ?></p>
                    </div>
                </div>

                <div class="course-description">
                    <h4>Maelezo ya Kozi</h4>
                    <p><?php echo nl2br(htmlspecialchars($course['description'])); ?></p>
                </div>

                <h4 class="mb-4">Modules za Kozi</h4>
                <div class="module-list">
                    <?php foreach ($modules as $module): ?>
                        <div class="module-item">
                            <div class="module-title">
                                <h5 class="mb-0"><?php echo htmlspecialchars($module['title']); ?></h5>
                                <?php if ($module['description']): ?>
                                    <small class="text-muted"><?php echo htmlspecialchars($module['description']); ?></small>
                                <?php endif; ?>
                            </div>
                            <div class="module-status">
                                <?php if ($module['completed_at']): ?>
                                    <i class="fas fa-check-circle completed" title="Completed on <?php echo date('d/m/Y', strtotime($module['completed_at'])); ?>"></i>
                                <?php else: ?>
                                    <a href="module.php?id=<?php echo $module['id']; ?>" class="btn-start">
                                        <?php echo $module['completed_at'] ? 'Review' : 'Start'; ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <footer class="text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>Kuhusu Sisi</h5>
                    <p>Islamic Education Platform inatoa elimu bora ya Kiislamu kutoka kwa walimu wenye ujuzi.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <h5>Mawasiliano</h5>
                    <p><i class="fas fa-envelope me-2"></i> info@islamicedu.com</p>
                    <p><i class="fas fa-phone me-2"></i> +255 123 456 789</p>
                </div>
            </div>
            <hr>
            <div class="text-center">
                <p class="mb-0">&copy; <?php echo date('Y'); ?> Islamic Education Platform. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 