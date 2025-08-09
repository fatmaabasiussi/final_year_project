<?php 
session_start();

// Ruhusu admin tu
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit;
}

require_once __DIR__ . '/../includes/functions.php';
$conn = Database::getInstance()->getConnection();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = (int)$_POST['user_id'];
    $name = $conn->real_escape_string($_POST['name']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $role = $conn->real_escape_string($_POST['role']);
    $status = $conn->real_escape_string($_POST['status']);
    $phone_number = $conn->real_escape_string($_POST['phone_number'] ?? '');
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: edit_user.php?id=$user_id&error=invalid_email");
        exit;
    }
    
    // Check if email exists for other users
    $check = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $check->bind_param("si", $email, $user_id);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        header("Location: edit_user.php?id=$user_id&error=email_exists");
        exit;
    }
    
    // Handle avatar upload
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['avatar']['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);
        
        if (in_array(strtolower($filetype), $allowed)) {
            $upload_dir = "../uploads/avatars/";
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $new_filename = uniqid() . '.' . $filetype;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $upload_path)) {
                // Delete old avatar if exists
                $old_avatar = $conn->query("SELECT avatar_url FROM users WHERE id = $user_id")->fetch_assoc();
                if ($old_avatar && $old_avatar['avatar_url']) {
                    @unlink("../" . $old_avatar['avatar_url']);
                }
                
                // Update with new avatar
                $avatar_url = 'uploads/avatars/' . $new_filename;
                $sql = "UPDATE users SET name = ?, email = ?, role = ?, status = ?, phone_number = ?, avatar_url = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssssssi", $name, $email, $role, $status, $phone_number, $avatar_url, $user_id);
            }
        }
    } else {
        // Update without changing avatar
        $sql = "UPDATE users SET name = ?, email = ?, role = ?, status = ?, phone_number = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssi", $name, $email, $role, $status, $phone_number, $user_id);
    }
    
    // Update password if provided
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $pass_sql = "UPDATE users SET password = ? WHERE id = ?";
        $pass_stmt = $conn->prepare($pass_sql);
        $pass_stmt->bind_param("si", $password, $user_id);
        $pass_stmt->execute();
    }
    
    if ($stmt->execute()) {
        // Log activity
        $admin_id = $_SESSION['user_id'];
        $log_sql = "INSERT INTO activity_logs (user_id, action, details) VALUES (?, 'edit_user', ?)";
        $log_stmt = $conn->prepare($log_sql);
        $details = "Updated user: $name ($role)";
        $log_stmt->bind_param("is", $admin_id, $details);
        $log_stmt->execute();
        
        header("Location: manage_user.php?msg=updated");
        exit;
    } else {
        header("Location: edit_user.php?id=$user_id&error=1");
        exit;
    }
}

// Get user data
if (!isset($_GET['id'])) {
    header("Location: manage_user.php");
    exit;
}

$user_id = (int)$_GET['id'];
$user = $conn->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();

if (!$user) {
    header("Location: manage_user.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Edit User - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <style>
        body {
            min-height: 100vh;
            display: flex;
            overflow-x: hidden;
        }
        #content {
            flex-grow: 1;
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
        }
        .current-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>

    <!-- Main content -->
    <div id="content">
        <header id="header">
            <span>Edit User</span>
        </header>

        <main class="p-4">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-body">
                                <h2 class="card-title mb-4">Edit User</h2>

                                <?php if (isset($_GET['error'])): ?>
                                    <div class="alert alert-danger">
                                        <?php
                                        switch ($_GET['error']) {
                                            case 'invalid_email':
                                                echo "Invalid email format";
                                                break;
                                            case 'email_exists':
                                                echo "Email already exists";
                                                break;
                                            default:
                                                echo "An error occurred";
                                        }
                                        ?>
                                    </div>
                                <?php endif; ?>

                                <form method="POST" enctype="multipart/form-data">
                                    <input type="hidden" name="user_id" value="<?= $user_id ?>" />

                                    <div class="text-center mb-4">
                                        <?php if (!empty($user['avatar_url'])): ?>
                                            <img src="../<?= htmlspecialchars($user['avatar_url']) ?>" 
                                                 class="current-avatar" alt="Current avatar" />
                                        <?php else: ?>
                                            <img src="../assets/images/default-avatar.png" 
                                                 class="current-avatar" alt="Default avatar" />
                                        <?php endif; ?>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Full Name</label>
                                        <input
                                            type="text"
                                            class="form-control"
                                            name="name"
                                            value="<?= htmlspecialchars($user['name']) ?>"
                                            required
                                        />
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Email</label>
                                        <input
                                            type="email"
                                            class="form-control"
                                            name="email"
                                            value="<?= htmlspecialchars($user['email']) ?>"
                                            required
                                        />
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Phone Number</label>
                                        <input type="text" class="form-control" name="phone_number" value="<?= htmlspecialchars($user['phone_number'] ?? '') ?>" />
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">New Password</label>
                                        <input type="password" class="form-control" name="password" />
                                        <small class="text-muted">Leave empty to keep current password</small>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Role</label>
                                        <select class="form-select" name="role" required>
                                            <option value="user" <?= $user['role'] == 'user' ? 'selected' : '' ?>>Student</option>
                                            <option value="scholar" <?= $user['role'] == 'scholar' ? 'selected' : '' ?>>Scholar</option>
                                            <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Status</label>
                                        <select class="form-select" name="status" required>
                                            <option value="active" <?= ($user['status'] ?? 'inactive') == 'active' ? 'selected' : '' ?>>Active</option>
                                            <option value="inactive" <?= ($user['status'] ?? 'inactive') == 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                            <option value="suspended" <?= ($user['status'] ?? 'inactive') == 'suspended' ? 'selected' : '' ?>>Suspended</option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Profile Picture</label>
                                        <input type="file" class="form-control" name="avatar" accept="image/*" />
                                        <small class="text-muted">Leave empty to keep current picture</small>
                                    </div>

                                    <div class="d-flex justify-content-between">
                                        <a href="manage_user.php" class="btn btn-secondary">Cancel</a>
                                        <button type="submit" class="btn btn-success">Update User</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
