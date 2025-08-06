<?php
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "1234", "religion_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$msg = '';
$msg_type = '';

// Handle answer submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'answer') {
        $question_id = intval($_POST['question_id'] ?? 0);
        $answer = trim($_POST['answer'] ?? '');
        $scholar_id = intval($_POST['scholar_id'] ?? 0);
        
        if (empty($answer) || $question_id === 0 || $scholar_id === 0) {
            $msg = "Tafadhali jaza taarifa zote muhimu.";
            $msg_type = "danger";
        } else {
            // Start transaction
            $conn->begin_transaction();
            
            try {
                // Insert answer
                $sql = "INSERT INTO answers (question_id, scholar_id, answer_text, created_at) VALUES (?, ?, ?, NOW())";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iis", $question_id, $scholar_id, $answer);
                $stmt->execute();
                
                // Update question status
                $sql = "UPDATE questions SET status = 'answered' WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $question_id);
                $stmt->execute();
                
                $conn->commit();
                $msg = "Jibu limetumwa kikamilifu.";
                $msg_type = "success";
            } catch (Exception $e) {
                $conn->rollback();
                $msg = "Tatizo limetokea. Tafadhali jaribu tena.";
                $msg_type = "danger";
            }
        }
    }
}

// Get all questions with user info and answers
$sql = "SELECT q.*, u.name as user_name, a.answer_text, a.created_at as answered_at, 
               s.name as scholar_name, s.id as scholar_id
        FROM questions q 
        JOIN users u ON q.user_id = u.id 
        LEFT JOIN answers a ON q.id = a.question_id 
        LEFT JOIN users s ON a.scholar_id = s.id 
        ORDER BY q.created_at DESC";

$result = $conn->query($sql);
$questions = [];
while ($row = $result->fetch_assoc()) {
    $questions[] = $row;
}

// Get all scholars
$scholars = [];
$result = $conn->query("SELECT id, name FROM users WHERE role = 'scholar' ORDER BY name");
while ($row = $result->fetch_assoc()) {
    $scholars[] = $row;
}

// Get question categories
$categories = [
    'aqeedah' => 'Aqeedah',
    'fiqh' => 'Fiqh',
    'hadith' => 'Hadith',
    'tafsir' => 'Tafsir',
    'seerah' => 'Seerah',
    'other' => 'Nyinginezo'
];

$conn->close();
?>

<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Maswali - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            overflow-x: hidden;
        }
        /* Sidebar */
        #sidebar {
            width: 250px;
            background: #343a40;
            color: white;
            min-height: 100vh;
        }
        #sidebar .nav-link {
            color: white;
        }
        #sidebar .nav-link:hover {
            background: #495057;
        }
        #sidebar .nav-link.active {
            background: #007bff;
        }
        /* Content area */
        #content {
            flex-grow: 1;
        }
        /* Header */
        #header {
            height: 60px;
            background: #007bff;
            color: white;
            display: flex;
            align-items: center;
            padding: 0 20px;
            font-weight: 600;
            font-size: 1.25rem;
            box-shadow: 0 2px 5px rgb(0 0 0 / 0.1);
        }
        .question-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .question-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        .question-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        .question-status {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.875rem;
        }
        .status-pending {
            background-color: #ffeeba;
            color: #856404;
        }
        .status-answered {
            background-color: #d4edda;
            color: #155724;
        }
        .question-content {
            font-size: 1.1rem;
            margin-bottom: 15px;
            color: #343a40;
            white-space: pre-line;
        }
        .answer-section {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-top: 15px;
        }
        .scholar-info {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        .scholar-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #007bff;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
            font-size: 1.2rem;
        }
        .category-badge {
            background-color: #e9ecef;
            color: #495057;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.875rem;
            margin-right: 10px;
        }
        .answer-form {
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            margin-top: 15px;
            border: 1px solid #dee2e6;
        }
        .btn-answer {
            background-color: #007bff;
            color: white;
            padding: 8px 20px;
            border-radius: 5px;
            border: none;
            transition: background-color 0.3s ease;
        }
        .btn-answer:hover {
            background-color: #0056b3;
            color: white;
        }
        .user-info {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #6c757d;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
            font-size: 1.2rem;
        }
        .answer-content {
            white-space: pre-line;
            color: #495057;
            font-size: 1rem;
            line-height: 1.6;
        }
        .filters {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>

<div class="wrapper d-flex">
    <!-- Sidebar -->
    <nav id="sidebar" class="d-flex flex-column p-3">
        <a href="#" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none fs-4">
            Admin Panel
        </a>
        <hr>
        <ul class="nav nav-pills flex-column">
            <li class="nav-item">
                <a href="admin.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'admin.php' ? 'active' : '' ?>">
                    <i class="fas fa-home me-2"></i>Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a href="admin_users.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'admin_users.php' ? 'active' : '' ?>">
                    <i class="fas fa-users me-2"></i>Users
                </a>
            </li>
            <li class="nav-item">
                <a href="admin_courses.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'admin_courses.php' ? 'active' : '' ?>">
                    <i class="fas fa-book me-2"></i>Courses
                </a>
            </li>
            <li class="nav-item">
                <a href="admin_questions.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'admin_questions.php' ? 'active' : '' ?>">
                    <i class="fas fa-question-circle me-2"></i>Questions
                </a>
            </li>
            <li class="nav-item">
                <a href="admin_settings.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'admin_settings.php' ? 'active' : '' ?>">
                    <i class="fas fa-cog me-2"></i>Settings
                </a>
            </li>
            <li class="nav-item">
                <a href="../logout.php" class="nav-link">
                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                </a>
            </li>
        </ul>
    </nav>

    <!-- Content Area -->
    <div id="content" class="d-flex flex-column flex-grow-1">
        <!-- Header -->
        <header id="header">
            <span class="text-light">Maswali</span>
        </header>

        <!-- Main Content -->
        <main class="container-fluid py-4">
            <?php if ($msg): ?>
                <div class="alert alert-<?= $msg_type ?> alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($msg) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Filters -->
            <div class="filters">
                <div class="row">
                    <div class="col-md-4">
                        <label for="status-filter" class="form-label">Status</label>
                        <select id="status-filter" class="form-select">
                            <option value="">All</option>
                            <option value="pending">Pending</option>
                            <option value="answered">Answered</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="category-filter" class="form-label">Category</label>
                        <select id="category-filter" class="form-select">
                            <option value="">All</option>
                            <?php foreach ($categories as $value => $label): ?>
                                <option value="<?= htmlspecialchars($value) ?>"><?= htmlspecialchars($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" id="search" class="form-control" placeholder="Search questions...">
                    </div>
                </div>
            </div>

            <!-- Questions List -->
            <div class="questions-list">
                <?php foreach ($questions as $question): ?>
                    <div class="question-card" 
                         data-status="<?= $question['status'] ?>" 
                         data-category="<?= $question['category'] ?>">
                        <div class="question-header">
                            <div class="d-flex align-items-center">
                                <span class="category-badge">
                                    <i class="fas fa-tag me-1"></i>
                                    <?= htmlspecialchars($categories[$question['category']] ?? 'Nyinginezo') ?>
                                </span>
                                <small class="text-muted">
                                    <i class="far fa-clock me-1"></i>
                                    <?= date('d/m/Y H:i', strtotime($question['created_at'])) ?>
                                </small>
                            </div>
                            <span class="question-status <?= $question['status'] == 'answered' ? 'status-answered' : 'status-pending' ?>">
                                <?= $question['status'] == 'answered' ? 'Limejibiwa' : 'Linasubiri' ?>
                            </span>
                        </div>

                        <div class="user-info">
                            <div class="user-avatar">
                                <i class="fas fa-user"></i>
                            </div>
                            <div>
                                <div class="fw-bold"><?= htmlspecialchars($question['user_name']) ?></div>
                                <small class="text-muted">Mwanafunzi</small>
                            </div>
                        </div>
                        
                        <div class="question-content">
                            <?= nl2br(htmlspecialchars($question['question_text'])) ?>
                        </div>

                        <?php if ($question['answer_text']): ?>
                            <div class="answer-section">
                                <div class="scholar-info">
                                    <div class="scholar-avatar">
                                        <i class="fas fa-user-tie"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold">Sheikh <?= htmlspecialchars($question['scholar_name']) ?></div>
                                        <small class="text-muted">
                                            <?= date('d/m/Y H:i', strtotime($question['answered_at'])) ?>
                                        </small>
                                    </div>
                                </div>
                                <div class="answer-content">
                                    <?= nl2br(htmlspecialchars($question['answer_text'])) ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="answer-form">
                                <form method="POST" class="needs-validation" novalidate>
                                    <input type="hidden" name="action" value="answer">
                                    <input type="hidden" name="question_id" value="<?= $question['id'] ?>">
                                    
                                    <div class="mb-3">
                                        <label for="scholar-<?= $question['id'] ?>" class="form-label">Chagua Sheikh</label>
                                        <select name="scholar_id" id="scholar-<?= $question['id'] ?>" class="form-select" required>
                                            <option value="">-- Chagua --</option>
                                            <?php foreach ($scholars as $scholar): ?>
                                                <option value="<?= $scholar['id'] ?>"><?= htmlspecialchars($scholar['name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="invalid-feedback">Tafadhali chagua Sheikh.</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="answer-<?= $question['id'] ?>" class="form-label">Jibu</label>
                                        <textarea name="answer" id="answer-<?= $question['id'] ?>" class="form-control" 
                                                rows="4" required placeholder="Andika jibu hapa..."></textarea>
                                        <div class="invalid-feedback">Tafadhali andika jibu.</div>
                                    </div>
                                    
                                    <div class="text-end">
                                        <button type="submit" class="btn btn-answer">
                                            <i class="fas fa-paper-plane me-2"></i>Tuma Jibu
                                        </button>
                                    </div>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Custom JS -->
<script>
// Form validation
(function () {
    'use strict'
    const forms = document.querySelectorAll('.needs-validation')
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault()
                event.stopPropagation()
            }
            form.classList.add('was-validated')
        }, false)
    })
})()

// Filtering functionality
document.addEventListener('DOMContentLoaded', function() {
    const statusFilter = document.getElementById('status-filter')
    const categoryFilter = document.getElementById('category-filter')
    const searchInput = document.getElementById('search')
    const questionCards = document.querySelectorAll('.question-card')

    function filterQuestions() {
        const status = statusFilter.value.toLowerCase()
        const category = categoryFilter.value.toLowerCase()
        const search = searchInput.value.toLowerCase()

        questionCards.forEach(card => {
            const cardStatus = card.dataset.status.toLowerCase()
            const cardCategory = card.dataset.category.toLowerCase()
            const cardContent = card.textContent.toLowerCase()

            const statusMatch = !status || cardStatus === status
            const categoryMatch = !category || cardCategory === category
            const searchMatch = !search || cardContent.includes(search)

            if (statusMatch && categoryMatch && searchMatch) {
                card.style.display = ''
            } else {
                card.style.display = 'none'
            }
        })
    }

    statusFilter.addEventListener('change', filterQuestions)
    categoryFilter.addEventListener('change', filterQuestions)
    searchInput.addEventListener('input', filterQuestions)
})
</script>
</body>
</html> 