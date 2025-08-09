<?php
session_start();

require_once __DIR__ . '/includes/functions.php';

$error = "";
$success = "";
if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    try {
        if (Auth::login($email, $password)) {
            $role = $_SESSION['role'] ?? 'user';
            if ($role === 'admin') {
                header('Location: dashboard/admin.php');
            } elseif ($role === 'scholar') {
                header('Location: dashboard/scholar.php');
            } else {
                header('Location: dashboard/user.php');
            }
            exit();
        } else {
            $error = 'Nenosiri si sahihi au mtumiaji hajapatikana.';
        }
    } catch (Throwable $e) {
        $error = 'Hitilafu ya mfumo. Tafadhali jaribu tena.';
    }
}
?>

<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ingia - Know Your Religion</title>
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
            min-height: calc(100vh - 200px);
            display: flex;
            align-items: center;
            padding: 40px 0;
        }

        .login-box {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            max-width: 500px;
            width: 100%;
            margin: 0 auto;
        }

        .login-box h2 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
            font-weight: 600;
        }

        .form-control {
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid #ddd;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 10px;
        }

        .form-label {
            color: #333;
            font-weight: 500;
        }

        .btn-primary {
            padding: 12px 30px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            background-color: rgb(16, 71, 249);
            border: none;
            width: 100%;
            margin-bottom: 15px;
            border-radius: 10px;
        }

        .btn-primary:hover {
            background-color: rgb(16, 71, 249);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .btn-link {
            color: rgb(16, 71, 249);
            text-decoration: none;
            font-weight: 500;
        }

        .btn-link:hover {
            color: rgb(16, 71, 249);
            text-decoration: underline;
        }

        footer {
            position: relative;
            z-index: 2;
            background-color: rgba(33, 37, 41, 0.9) !important;
        }

        .alert {
            border-radius: 10px;
            margin-bottom: 25px;
        }

        .input-group-text {
            background-color: transparent;
            border-right: none;
        }

        .form-control {
            border-left: none;
        }

        .form-check-label {
            font-size: 0.9rem;
        }
    </style>
</head>
<body>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">Know Your Religion</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="register.php">Jisajili</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main -->
    <div class="main-container">
        <div class="container">
            <div class="login-box">
                <h2>Ingia kwenye Akaunti Yako</h2>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?= $success ?></div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>

                <form method="post">
                    <div class="mb-3">
                        <label class="form-label">Barua Pepe</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nenosiri</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="remember">
                        <label class="form-check-label" for="remember">Nikumbuke</label>
                    </div>

                    <button type="submit" class="btn btn-primary">Ingia</button>
                    <div class="text-center">
                        <a href="register.php" class="btn-link">Huna akaunti? Jisajili</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Footer -->
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
                <p class="mb-0">&copy; <?= date('Y'); ?> Islamic Education Platform. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

