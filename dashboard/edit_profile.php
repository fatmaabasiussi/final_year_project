<?php
session_start();

// Check if user is logged in and is a user
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'user') {
    header("Location: ../index.php");
    exit();
}

require_once __DIR__ . '/../includes/functions.php';
$conn = Database::getInstance()->getConnection();

$user_id = $_SESSION['user_id'];
$msg = "";
$msg_type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $profile_pic = '';

    // Validate inputs
    if (empty($name) || empty($email)) {
        $msg = "Tafadhali jaza taarifa zote muhimu.";
        $msg_type = "danger";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $msg = "Tafadhali weka barua pepe sahihi.";
        $msg_type = "danger";
    } else {
        // Handle profile picture upload
        if (!empty($_FILES["profile_pic"]["name"])) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $max_size = 5 * 1024 * 1024; // 5MB

            if (!in_array($_FILES["profile_pic"]["type"], $allowed_types)) {
                $msg = "Aina ya picha isiyoruhusiwa. Tafadhali tumia JPEG, PNG au GIF.";
                $msg_type = "danger";
            } elseif ($_FILES["profile_pic"]["size"] > $max_size) {
                $msg = "Ukubwa wa picha usizidi 5MB.";
                $msg_type = "danger";
            } else {
                $target_dir = "uploads/";
                if (!file_exists($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }
                
                $file_extension = strtolower(pathinfo($_FILES["profile_pic"]["name"], PATHINFO_EXTENSION));
                $file_name = time() . '_' . $user_id . '.' . $file_extension;
                $target_file = $target_dir . $file_name;

                if (move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $target_file)) {
                    $profile_pic = $file_name;
                } else {
                    $msg = "Tatizo limetokea wakati wa kupakia picha.";
                    $msg_type = "danger";
                }
            }
        }

        if (empty($msg)) {
            if ($profile_pic) {
                $sql = "UPDATE users SET name=?, email=?, phone=?, profile_pic=? WHERE id=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssssi", $name, $email, $phone, $profile_pic, $user_id);
            } else {
                $sql = "UPDATE users SET name=?, email=?, phone=? WHERE id=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssi", $name, $email, $phone, $user_id);
            }

            if ($stmt->execute()) {
                $_SESSION['name'] = $name;
                header("Location: profile.php");
                exit();
            } else {
                $msg = "Tatizo limetokea wakati wa kuhifadhi taarifa.";
                $msg_type = "danger";
            }
        }
    }
}

// Fetch user info
$sql = "SELECT name, email, phone, profile_pic FROM users WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Get profile picture URL
$profilePic = !empty($user['profile_pic']) 
    ? 'uploads/' . htmlspecialchars($user['profile_pic']) 
    : 'https://ui-avatars.com/api/?name=' . urlencode($user['name']) . '&background=007bff&color=fff';
?>

<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Hariri Wasifu - Know Your Religion</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            /*min-height: 100vh;
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
            background: #198754;
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
        .edit-form {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .profile-pic-preview {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto 20px;
            border: 5px solid #007bff;
            display: block;
        }
        .form-label {
            font-weight: 500;
            color: #495057;
        }
        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
        .btn-save {
            background-color: #007bff;
            color: white;
            padding: 8px 20px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }
        .btn-save:hover {
            background-color: #0056b3;
            color: white;
        }
        .btn-cancel {
            background-color: #6c757d;
            color: white;
            padding: 8px 20px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }
        .btn-cancel:hover {
            background-color: #5a6268;
            color: white;
        }
    </style>
</head>
<body>

<div class="wrapper d-flex">
    <!-- Sidebar -->
    <nav id="sidebar" class="d-flex flex-column p-3">
        <a href="#" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none fs-4">
            User Panel
        </a>
        <hr>
        <ul class="nav nav-pills flex-column">
            <li class="nav-item">
                <a href="user.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'user.php' ? 'active' : '' ?>">Home</a>
            </li>
            <li class="nav-item">
                <a href="my_courses.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'my_courses.php' ? 'active' : '' ?>">My Courses</a>
            </li>
            <li class="nav-item">
                <a href="my_questions.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'my_questions.php' ? 'active' : '' ?>">My Questions</a>
            </li>
            <li class="nav-item">
                <a href="profile.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'profile.php' || basename($_SERVER['PHP_SELF']) == 'edit_profile.php' ? 'active' : '' ?>">Profile</a>
            </li>
            <li class="nav-item">
                <a href="settings.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : '' ?>">Settings</a>
            </li>
            <li class="nav-item">
                <a href="../logout.php" class="nav-link">Logout</a>
            </li>
        </ul>
    </nav>

    <!-- Content Area -->
    <div id="content" class="d-flex flex-column flex-grow-1">
        <!-- Header -->
        <header id="header">
            <span class="text-light">
                <a href="profile.php" class="text-light text-decoration-none me-2">
                    <i class="fas fa-arrow-left"></i>
                </a>
                Hariri Wasifu
            </span>
        </header>

        <!-- Main Content -->
        <main class="container-fluid py-4">
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <?php if ($msg): ?>
                        <div class="alert alert-<?= $msg_type ?> alert-dismissible fade show" role="alert">
                            <?= htmlspecialchars($msg) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <div class="edit-form">
                        <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                            <div class="text-center mb-4">
                                <img src="<?= $profilePic ?>" alt="Profile Picture" class="profile-pic-preview" id="profile-preview">
                                <div class="mt-3">
                                    <label for="profile_pic" class="btn btn-outline-primary">
                                        <i class="fas fa-camera me-2"></i>Badilisha Picha
                                    </label>
                                    <input type="file" name="profile_pic" id="profile_pic" class="d-none" accept="image/*">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="name" class="form-label">Jina Kamili</label>
                                <input type="text" name="name" id="name" class="form-control" 
                                       value="<?= htmlspecialchars($user['name']) ?>" required>
                                <div class="invalid-feedback">Tafadhali jaza jina lako.</div>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Barua Pepe</label>
                                <input type="email" name="email" id="email" class="form-control" 
                                       value="<?= htmlspecialchars($user['email']) ?>" required>
                                <div class="invalid-feedback">Tafadhali jaza barua pepe sahihi.</div>
                            </div>

                            <div class="mb-4">
                                <label for="phone" class="form-label">Namba ya Simu</label>
                                <input type="tel" name="phone" id="phone" class="form-control" 
                                       value="<?= htmlspecialchars($user['phone']) ?>" 
                                       pattern="[0-9]{10,12}">
                                <div class="form-text">Mfano: 0712345678</div>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="profile.php" class="btn btn-cancel">
                                    <i class="fas fa-times me-2"></i>Ghairi
                                </a>
                                <button type="submit" class="btn btn-save">
                                    <i class="fas fa-save me-2"></i>Hifadhi Mabadiliko
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Custom JS -->
<script>
// Preview profile picture before upload
document.getElementById('profile_pic').addEventListener('change', function(e) {
    if (e.target.files && e.target.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('profile-preview').src = e.target.result;
        }
        reader.readAsDataURL(e.target.files[0]);
    }
});

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
</script>
</body>
</html>
