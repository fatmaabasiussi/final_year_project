<?php
session_start();

// Ruhusu admin na scholar tu
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'scholar'])) {
    header("Location: ../login.php");
    exit;
}

$conn = new mysqli("localhost", "root", "1234", "religion_db");
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $course_id = (int)($_POST['course_id'] ?? 0);
    $title = $conn->real_escape_string($_POST['title'] ?? '');
    $description = '-'; // placeholder
    $scholar_id = (int)($_POST['scholar_id'] ?? 0);
    $status = $conn->real_escape_string($_POST['status'] ?? '');

    // Handle document upload
    $document_path = $course['document_url'] ?? null;
    if (isset($_FILES['document']) && $_FILES['document']['error'] == 0) {
        $allowed_docs = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'txt'];
        $ext = strtolower(pathinfo($_FILES['document']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed_docs)) {
            $upload_dir = "../uploads/courses/";
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $new_doc_name = uniqid() . '.' . $ext;
            $upload_path = $upload_dir . $new_doc_name;
            if (move_uploaded_file($_FILES['document']['tmp_name'], $upload_path)) {
                // Delete old document if exists
                if (!empty($course['document_url']) && file_exists("../" . $course['document_url'])) {
                    @unlink("../" . $course['document_url']);
                }
                $document_path = 'uploads/courses/' . $new_doc_name;
            } else {
                $error = "Failed to upload document.";
            }
        } else {
            $error = "Invalid document format. Allowed: pdf, doc, docx, ppt, pptx, xls, xlsx, txt.";
        }
    }

    if ($course_id <= 0) {
        $error = "Invalid course ID";
    } else if (!isset($error)) {
        // Handle image upload (existing logic)
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['image']['name'];
            $filetype = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

            if (in_array($filetype, $allowed)) {
                $upload_dir = "../uploads/courses/";
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                $new_filename = uniqid() . '.' . $filetype;
                $upload_path = $upload_dir . $new_filename;

                if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                    // Delete old image if exists
                    $old_image_result = $conn->query("SELECT image_url FROM courses WHERE id = $course_id");
                    if ($old_image_result && $old_image = $old_image_result->fetch_assoc()) {
                        if (!empty($old_image['image_url']) && file_exists("../" . $old_image['image_url'])) {
                            @unlink("../" . $old_image['image_url']);
                        }
                    }

                    // Update with new image and document
                    $image_url = 'uploads/courses/' . $new_filename;
                    $sql = "UPDATE courses SET title = ?, description = ?, scholar_id = ?, status = ?, image_url = ?, document_url = ? WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    if ($stmt === false) {
                        $error = "Prepare failed: " . $conn->error;
                    } else {
                        $stmt->bind_param("ssisssi", $title, $description, $scholar_id, $status, $image_url, $document_path, $course_id);
                    }
                } else {
                    $error = "Failed to upload image.";
                }
            } else {
                $error = "Invalid image format. Allowed: jpg, jpeg, png, gif.";
            }
        } else {
            // Update without changing image, but update document
            $sql = "UPDATE courses SET title = ?, description = ?, scholar_id = ?, status = ?, document_url = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            if ($stmt === false) {
                $error = "Prepare failed: " . $conn->error;
            } else {
                $stmt->bind_param("ssissi", $title, $description, $scholar_id, $status, $document_path, $course_id);
            }
        }

        if (!isset($error)) {
            if ($stmt->execute()) {
                // Log activity
                $admin_id = $_SESSION['user_id'] ?? 0;
                if ($admin_id > 0) {
                    $log_sql = "INSERT INTO activity_logs (user_id, action, details) VALUES (?, 'edit_course', ?)";
                    $log_stmt = $conn->prepare($log_sql);
                    if ($log_stmt) {
                        $details = "Updated course: $title";
                        $log_stmt->bind_param("is", $admin_id, $details);
                        $log_stmt->execute();
                    }
                }
                header("Location: manage_course.php?success=2");
                exit;
            } else {
                $error = "Failed to update course: " . $stmt->error;
            }
        }
    }
}

// Get course data
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: manage_course.php");
    exit;
}

$course_id = (int)$_GET['id'];
$course_result = $conn->query("SELECT * FROM courses WHERE id = $course_id");
if (!$course_result || $course_result->num_rows == 0) {
    header("Location: manage_course.php");
    exit;
}

$course = $course_result->fetch_assoc();

// Scholar aweze ku-edit tu kozi zake
if ($_SESSION['role'] == 'scholar' && $course['scholar_id'] != $_SESSION['user_id']) {
    header("Location: scholar.php");
    exit;
}

// Get scholars for dropdown
$scholars = $conn->query("SELECT id, name FROM users WHERE role = 'scholar'");
?>

<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Edit Course - Admin Dashboard</title>
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
        .current-image {
            max-width: 200px;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>

    <!-- Main content -->
    <div id="content">
        <header id="header">
            <span>Edit Course</span>
        </header>

        <main class="p-4">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-body">
                                <h2 class="card-title mb-4">Edit Course</h2>

                                <?php if (!empty($error)): ?>
                                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                                <?php endif; ?>

                                <form method="POST" enctype="multipart/form-data">
                                    <input type="hidden" name="course_id" value="<?= $course_id ?>" />

                                    <div class="mb-3">
                                        <label class="form-label">Course Title</label>
                                        <input type="text" class="form-control" name="title"
                                            value="<?= htmlspecialchars($course['title'] ?? '') ?>" required />
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Assign Scholar</label>
                                        <select class="form-select" name="scholar_id" required>
                                            <?php if ($scholars): ?>
                                                <?php while ($scholar = $scholars->fetch_assoc()): ?>
                                                    <option value="<?= $scholar['id'] ?>"
                                                        <?= ($scholar['id'] == ($course['scholar_id'] ?? 0)) ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($scholar['name']) ?>
                                                    </option>
                                                <?php endwhile; ?>
                                            <?php endif; ?>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Status</label>
                                        <select class="form-select" name="status" required>
                                            <option value="active" <?= ($course['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                                            <option value="pending" <?= ($course['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                                            <option value="inactive" <?= ($course['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Course Image</label>
                                        <?php if (!empty($course['image_url']) && file_exists("../" . $course['image_url'])): ?>
                                            <div class="current-image">
                                                <img src="../<?= htmlspecialchars($course['image_url']) ?>"
                                                    class="img-thumbnail" alt="Current course image" />
                                            </div>
                                        <?php endif; ?>
                                        <input type="file" class="form-control" name="image" accept="image/*" />
                                        <small class="text-muted">Leave empty to keep current image</small>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Course Document (PDF, DOC, PPT, XLS, TXT)</label>
                                        <?php if (!empty($course['document_url'])): ?>
                                            <div class="mb-2">
                                                <a href="../<?= htmlspecialchars($course['document_url']) ?>" target="_blank">View Current Document</a>
                                            </div>
                                        <?php endif; ?>
                                        <input type="file" class="form-control" name="document" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.txt">
                                    </div>

                                    <div class="d-flex justify-content-between">
                                        <a href="<?= $_SESSION['role'] == 'scholar' ? 'scholar.php' : 'manage_course.php' ?>" class="btn btn-secondary">Cancel</a>
                                        <button type="submit" class="btn btn-success">Update Course</button>
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
