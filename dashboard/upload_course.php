<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'scholar') {
    header("Location: ../login.php");
    exit;
}
require_once '../includes/functions.php';
require_once '../inc/db.php';

$scholar_id = $_SESSION['user_id'];
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param('i', $scholar_id);
$stmt->execute();
$scholar = $stmt->get_result()->fetch_assoc();

$success = $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = '-';
    $status = $_POST['status'] ?? 'inactive';
    $image = $_FILES['image'] ?? null;
    $document = $_FILES['document'] ?? null;

    if ($title === '') {
        $error = 'Please fill all required fields.';
    } else {
        $image_path = null;
        if ($image && $image['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($image['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            if (in_array($ext, $allowed)) {
                $new_name = time() . '_' . uniqid() . '.' . $ext;
                $upload_dir = '../uploads/courses/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
                $dest = $upload_dir . $new_name;
                if (move_uploaded_file($image['tmp_name'], $dest)) {
                    $image_path = 'uploads/courses/' . $new_name;
                } else {
                    $error = 'Image upload failed.';
                }
            } else {
                $error = 'Invalid image type.';
            }
        }
        $document_path = null;
        if ($document && $document['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($document['name'], PATHINFO_EXTENSION));
            $allowed_docs = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'txt'];
            if (in_array($ext, $allowed_docs)) {
                $new_doc_name = time() . '_' . uniqid() . '.' . $ext;
                $doc_upload_dir = '../uploads/courses/';
                if (!is_dir($doc_upload_dir)) mkdir($doc_upload_dir, 0777, true);
                $doc_dest = $doc_upload_dir . $new_doc_name;
                if (move_uploaded_file($document['tmp_name'], $doc_dest)) {
                    $document_path = 'uploads/courses/' . $new_doc_name;
                } else {
                    $error = 'Document upload failed.';
                }
            } else {
                $error = 'Invalid document type.';
            }
        }

        if (!$error) {
            $stmt = $db->prepare("INSERT INTO courses (title, description, image_url, document_url, status, scholar_id, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param('sssssi', $title, $description, $image_path, $document_path, $status, $scholar_id);
            if ($stmt->execute()) {
                $success = 'Course uploaded successfully!';
                $_POST = [];
            } else {
                $error = 'Database error: ' . $db->error;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="sw">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Upload Course - Sheikh Panel</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
  <style>
    body {
      background: #f0f8f5;
      min-height: 100vh;
      margin: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      color: #155724;
    }
    .wrapper {
      display: flex;
      min-height: 100vh;
      flex-wrap: nowrap;
    }
    /* Sidebar */
    #sidebar {
      width: 260px;
      background: linear-gradient(180deg, #28a745, #1e7e34);
      color: #fff;
      padding: 2rem 1rem;
      display: flex;
      flex-direction: column;
      align-items: center;
      box-shadow: 3px 0 10px rgba(0,0,0,0.1);
      transition: width 0.3s ease;
      position: fixed;
      height: 100vh;
      overflow-y: auto;
    }
    #sidebar .avatar {
      width: 70px;
      height: 70px;
      border-radius: 50%;
      background: #c3e6cb;
      color: #155724;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 2.8rem;
      margin-bottom: 1rem;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      user-select: none;
    }
    #sidebar .scholar-name {
      font-size: 1.5rem;
      font-weight: 700;
      margin-bottom: 0.1rem;
      text-align: center;
      text-shadow: 0 1px 2px rgba(0,0,0,0.2);
      user-select: none;
    }
    #sidebar .role-badge {
      font-size: 0.9rem;
      background: rgba(255 255 255 / 0.3);
      color: #e9f7ef;
      border-radius: 15px;
      padding: 0.4rem 1rem;
      margin-bottom: 2.5rem;
      font-weight: 600;
      letter-spacing: 1px;
      user-select: none;
    }
    #sidebar ul.nav {
      width: 100%;
      padding-left: 0;
      list-style: none;
      margin: 0;
      flex-grow: 1;
      display: flex;
      flex-direction: column;
      gap: 0.7rem;
    }
    #sidebar ul.nav li {
      width: 100%;
    }
    #sidebar ul.nav li a.nav-link {
      color: #d4f1d4;
      font-weight: 600;
      padding: 0.65rem 1rem;
      border-radius: 8px;
      display: flex;
      align-items: center;
      gap: 0.8rem;
      text-decoration: none;
      transition: background-color 0.3s ease, color 0.3s ease;
      user-select: none;
      box-shadow: inset 0 0 0 0 transparent;
    }
    #sidebar ul.nav li a.nav-link.active,
    #sidebar ul.nav li a.nav-link:hover,
    #sidebar ul.nav li a.nav-link:focus {
      background-color: #ffffff30;
      color: #e9f7ef;
      box-shadow: inset 5px 0 0 #fff;
      font-weight: 700;
    }
    #sidebar ul.nav li a.nav-link i {
      font-size: 1.25rem;
      min-width: 25px;
      text-align: center;
    }

    /* Content */
    #content {
      margin-left: 260px;
      flex-grow: 1;
      background: #fff;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      box-shadow: inset 0 0 10px #e0e0e0;
    }
    /* Header */
    #header {
      height: 70px;
      background: #28a745;
      color: #fff;
      display: flex;
      align-items: center;
      padding: 0 2.5rem;
      font-weight: 700;
      font-size: 1.3rem;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
      user-select: none;
      position: sticky;
      top: 0;
      z-index: 10;
    }
    #header .header-avatar {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background: #c3e6cb;
      color: #155724;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 1.3rem;
      margin-right: 1rem;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
      user-select: none;
    }
    main.container {
      padding: 2rem 3rem 3rem 3rem;
      flex-grow: 1;
      max-width: 700px;
    }
    main.container h3 {
      color: #19692c;
      font-weight: 800;
      margin-bottom: 1.2rem;
      user-select: none;
    }
    .alert {
      margin-bottom: 1.5rem;
    }
    form label {
      font-weight: 600;
      user-select: none;
    }
    .btn-success {
      font-weight: 600;
      border-radius: 8px;
      padding: 0.5rem 1.1rem;
      font-size: 1rem;
      transition: all 0.3s ease;
    }
    .btn-success:hover {
      background-color: #1e7e34;
      box-shadow: 0 4px 14px rgba(30,126,52,0.7);
    }

    /* Responsive */
    @media (max-width: 900px) {
      .wrapper {
        flex-direction: column;
      }
      #sidebar {
        width: 100%;
        height: auto;
        padding: 1rem 0.5rem;
        flex-direction: row;
        justify-content: space-around;
        align-items: center;
        position: relative;
      }
      #sidebar .avatar,
      #sidebar .scholar-name,
      #sidebar .role-badge {
        display: none;
      }
      #sidebar ul.nav {
        flex-direction: row;
        gap: 1rem;
        flex-grow: 0;
      }
      #sidebar ul.nav li {
        margin-bottom: 0;
        flex-grow: 1;
        text-align: center;
      }
      #content {
        margin-left: 0;
        min-height: auto;
      }
      #header {
        padding: 0 1rem;
        font-size: 1.1rem;
        justify-content: center;
      }
      main.container {
        padding: 1rem;
        max-width: 100%;
      }
    }
  </style>
</head>
<body>
  <div class="wrapper">
    <nav id="sidebar" aria-label="Main navigation">
      <div class="avatar"><i class="fas fa-user"></i></div>
      <div class="scholar-name"><?= htmlspecialchars($scholar['name'] ?? 'Sheikh') ?></div>
      <span class="role-badge">Sheikh</span>
      <ul class="nav">
        <li><a href="scholar.php" class="nav-link"><i class="fas fa-home"></i> Kwenye Dashboard</a></li>
        <li><a href="upload_course.php" class="nav-link active" aria-current="page"><i class="fas fa-upload"></i> Pakia Kozi</a></li>
        <li><a href="scholar_questions.php" class="nav-link"><i class="fas fa-question-circle"></i> Angalia Maswali</a></li>
        <li><a href="upload_mcq.php" class="nav-link"><i class="fas fa-pen-nib"></i> Tunga Quiz</a></li>
        <li><a href="view_mcq_results.php" class="nav-link"><i class="fas fa-chart-bar"></i> Matokeo ya Quiz</a></li>
        <li><a href="scholar_profile.php" class="nav-link"><i class="fas fa-user"></i> Profaili Yangu</a></li>
        <li><a href="scholar_settings.php" class="nav-link"><i class="fas fa-cog"></i> Mipangilio</a></li>
        <li><a href="../logout.php" class="nav-link text-danger"><i class="fas fa-sign-out-alt"></i> Toka</a></li>
      </ul>
    </nav>

    <div id="content" role="main">
      <header id="header">
        <span class="header-avatar"><i class="fas fa-upload"></i></span>
        <span>Pakia Kozi</span>
      </header>
      <main class="container" tabindex="0">
        <h3>Pakia Kozi Mpya</h3>

        <?php if ($success): ?>
          <div class="alert alert-success" role="alert"><?= $success ?></div>
        <?php elseif ($error): ?>
          <div class="alert alert-danger" role="alert"><?= $error ?></div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data" novalidate>
          <div class="mb-3">
            <label for="title" class="form-label">Kichwa cha Kozi <span class="text-danger">*</span></label>
            <input
              type="text"
              id="title"
              name="title"
              class="form-control"
              required
              value="<?= htmlspecialchars($_POST['title'] ?? '') ?>"
            />
          </div>

          <div class="mb-3">
            <label for="image" class="form-label">Picha ya Kozi</label>
            <input
              type="file"
              id="image"
              name="image"
              class="form-control"
              accept="image/*"
            />
          </div>

          <div class="mb-3">
            <label for="document" class="form-label">Document la Kozi (PDF, DOC, PPT, XLS, TXT)</label>
            <input
              type="file"
              id="document"
              name="document"
              class="form-control"
              accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.txt"
            />
          </div>
          <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select
              id="status"
              name="status"
              class="form-select"
            >
              <option value="active" <?= (($_POST['status'] ?? '') == 'active') ? 'selected' : '' ?>>Active</option>
              <option value="inactive" <?= (($_POST['status'] ?? '') == 'inactive') ? 'selected' : '' ?>>Inactive</option>
            </select>
          </div>

          <button type="submit" class="btn btn-success">Pakia Kozi</button>
        </form>
      </main>
    </div>
  </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
