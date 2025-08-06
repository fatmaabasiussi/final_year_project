<?php
include '../inc/db.php'; // DB connection

// Initialize error and success messages
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize input
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $role = $_POST['role'] ?? 'user'; // default to user
    $password = $_POST['password'] ?? '';

    // Basic validation
    if (!$name) {
        $errors[] = "Jina linahitajika.";
    }
    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Barua pepe halali inahitajika.";
    }
    if (!$password || strlen($password) < 6) {
        $errors[] = "Nenosiri linahitajika na liwe la angalau herufi 6.";
    }
    if (!in_array($role, ['admin', 'scholar', 'user'])) {
        $errors[] = "Chaguo la role halikubaliki.";
    }

    // Check if email already exists
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $errors[] = "Barua pepe tayari imetumika.";
    }
    $stmt->close();

    if (empty($errors)) {
        // Hash password
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        // Insert user
        $stmt = $db->prepare("INSERT INTO users (name, email, phone, role, password) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $name, $email, $phone, $role, $passwordHash);

        if ($stmt->execute()) {
            $success = true;
            // Clear POST to avoid resubmission
            $_POST = [];
        } else {
            $errors[] = "Hitilafu ilitokea wakati wa kuongeza mtumiaji.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Ongeza Mtumiaji Mpya - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<div class="container py-4">
    <h1 class="mb-4">Ongeza Mtumiaji Mpya</h1>

    <?php if ($success): ?>
        <div class="alert alert-success">
            Mtumiaji mpya ameongezwa kwa mafanikio. <a href="manage_user.php">Rudi kwenye orodha ya watumiaji</a>.
        </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="add_user.php" novalidate>
        <div class="mb-3">
            <label for="name" class="form-label">Jina Kamili</label>
            <input type="text" class="form-control" id="name" name="name" 
                   value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Barua Pepe</label>
            <input type="email" class="form-control" id="email" name="email" 
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
        </div>

        <div class="mb-3">
            <label for="phone" class="form-label">Nambari ya Simu</label>
            <input type="text" class="form-control" id="phone" name="phone" 
                   value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
        </div>

        <div class="mb-3">
            <label for="role" class="form-label">Jukumu (Role)</label>
            <select class="form-select" id="role" name="role" required>
                <option value="user" <?= (($_POST['role'] ?? '') === 'user') ? 'selected' : '' ?>>Mwanafunzi (User)</option>
                <option value="scholar" <?= (($_POST['role'] ?? '') === 'scholar') ? 'selected' : '' ?>>Sheikh (Scholar)</option>
                <option value="admin" <?= (($_POST['role'] ?? '') === 'admin') ? 'selected' : '' ?>>Msimamizi (Admin)</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Nenosiri</label>
            <input type="password" class="form-control" id="password" name="password" required minlength="6">
            <div class="form-text">Tafadhali tumia angalau herufi 6.</div>
        </div>

        <button type="submit" class="btn btn-success">Ongeza Mtumiaji</button>
        <a href="manage_user.php" class="btn btn-secondary ms-2">Rudi</a>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
