<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

include '../inc/db.php';

// Delete User
if(isset($_GET['delete'])){
    $id = (int)$_GET['delete'];
    $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: manage_user.php?msg=deleted");
    exit();
}

// Fetch users with courses count
$result = $db->query("
    SELECT u.*, 
    (SELECT COUNT(*) FROM enrollments WHERE user_id = u.id) as courses_count
    FROM users u 
    ORDER BY u.created_at DESC
");

// Get role counts
$stats = [
    'total' => $db->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'],
    'students' => $db->query("SELECT COUNT(*) as count FROM users WHERE role = 'user'")->fetch_assoc()['count'],
    'scholars' => $db->query("SELECT COUNT(*) as count FROM users WHERE role = 'scholar'")->fetch_assoc()['count']
];
?>
<!DOCTYPE html>
<html lang="sw">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Manage Users - Admin Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
  <style>
    body {
      display: flex;
      overflow-x: hidden;
      margin: 0;
      background-color: #e6f0e9; /* Light greenish background */
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    nav#sidebar {
      background: linear-gradient(180deg, #004d00, #006600);
      min-height: 100vh;
      width: 350px;
      color: white;
      padding: 2rem 1.2rem;
      box-shadow: 3px 0 10px rgba(0, 0, 0, 0.15);
    }

    #sidebar .nav-link {
      color: #d1e7dd;
      transition: background-color 0.3s, color 0.3s;
      padding-left: 1.5rem;
      padding-right: 1.5rem;
      font-weight: 600;
      letter-spacing: 0.02em;
    }

    #sidebar .nav-link:hover {
      background-color: #198754; /* Bootstrap green */
      color: white !important;
      text-decoration: none;
    }

    .nav-link.active {
      background-color: #14532d;
      color: #fff !important;
      border-left: 5px solid #198754;
    }

    .profile-icon {
      background: white;
      width: 90px;
      height: 90px;
      border-radius: 50%;
      display: flex;
      justify-content: center;
      align-items: center;
      margin: 0 auto 12px;
      box-shadow: 0 0 10px rgba(25, 135, 84, 0.7);
    }

    .profile-icon i {
      font-size: 2.5rem;
      color: #198754;
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
    }
    .stats-card:hover {
      transform: translateY(-7px);
      box-shadow: 0 8px 25px rgb(25 135 84 / 0.5);
    }

    .stats-icon {
      font-size: 2.8rem;
      opacity: 0.85;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 1.5rem;
      box-shadow: 0 0 10px rgb(0 0 0 / 0.1);
      border-radius: 8px;
      overflow: hidden;
    }

    thead tr {
      background-color: #198754;
      color: white;
      font-weight: 600;
    }

    tbody tr:hover {
      background-color: #d1e7dd;
    }

    th, td {
      padding: 12px 15px;
      text-align: left;
      vertical-align: middle;
    }

    .btn-action {
      padding: 0.25rem 0.5rem;
      font-size: 0.9rem;
    }

    /* Role badges */
    .badge-admin {
      background-color: #dc3545;
    }
    .badge-scholar {
      background-color: #198754;
    }
    .badge-user {
      background-color: #0d6efd;
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
 
<div id="content" class="d-flex flex-column flex-grow-1">
  <header id="header">
    <span>Manage Users</span>
  </header>

  <main>
    <?php if(isset($_GET['msg'])): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php 
          switch($_GET['msg']) {
            case 'deleted': echo "User deleted successfully"; break;
            case 'added': echo "New user added successfully"; break;
            case 'updated': echo "User updated successfully"; break;
          }
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <div class="row g-4 mb-4">
      <div class="col-md-4">
        <div class="card stats-card bg-success shadow-sm">
          <div class="d-flex justify-content-between align-items-center px-3 py-2">
            <div>
              <h5><?= $stats['total'] ?></h5>
              <small>Total Users</small>
            </div>
            <i class="fas fa-users stats-icon"></i>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card stats-card bg-primary shadow-sm">
          <div class="d-flex justify-content-between align-items-center px-3 py-2">
            <div>
              <h5><?= $stats['students'] ?></h5>
              <small>Students</small>
            </div>
            <i class="fas fa-user-graduate stats-icon"></i>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card stats-card bg-info shadow-sm">
          <div class="d-flex justify-content-between align-items-center px-3 py-2">
            <div>
              <h5><?= $stats['scholars'] ?></h5>
              <small>Scholars</small>
            </div>
            <i class="fas fa-user-tie stats-icon"></i>
          </div>
        </div>
      </div>
    </div>

    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0">All Users</h5>
                <a href="add_user.php" class="btn btn-success">
                    <i class="bi bi-person-plus me-1"></i> Add New User
                </a>
            </div>
    <table class="table table-hover rounded shadow-sm" style="background: white;">
      <thead>
        <tr>
          <th>Name</th>
          <th>Role</th>
          <th>Email</th>
          <th>Phone</th>
          <th>Courses</th>
          <th style="text-align: right;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php while($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($row['name']) ?></td>
          <td>
            <?php
            $roleClass = [
              'admin' => 'badge badge-admin',
              'scholar' => 'badge badge-scholar',
              'user' => 'badge badge-user'
            ][$row['role']] ?? 'badge bg-secondary';
            ?>
            <span class="<?= $roleClass ?>"><?= ucfirst($row['role']) ?></span>
          </td>
          <td><?= htmlspecialchars($row['email']) ?></td>
          <td><?= htmlspecialchars($row['phone_number'] ?? 'N/A') ?></td>
          <td><?= $row['courses_count'] ?></td>
          <td style="text-align: right;">
            <a href="edit_user.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-primary btn-action me-1" title="Edit User">
              <i class="fas fa-pencil-alt"></i>
            </a>
            <a href="?delete=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger btn-action" onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.')" title="Delete User">
              <i class="fas fa-trash"></i>
            </a>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>

  </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
