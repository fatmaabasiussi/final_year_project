<?php 
require_once '../includes/Database.php';  // badilisha path kwa database connection file yako

$Database = Database::getInstance()->getConnection();


// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = '-'; // placeholder
    $image = trim($_POST['image']);
    $status = $_POST['status'];
    $scholar_id = (int)$_POST['scholar_id'];

    // Handle document upload
    $document_path = null;
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
                $document_path = 'uploads/courses/' . $new_doc_name;
            } else {
                $error = "Failed to upload document.";
            }
        } else {
            $error = "Invalid document format. Allowed: pdf, doc, docx, ppt, pptx, xls, xlsx, txt.";
        }
    }

    // Simple validation
    if ($title === '' || !$scholar_id) {
        $error = "Tafadhali jaza masuala yote muhimu.";
    } else {
        // Insert course into database
        $stmt = $Database->prepare("INSERT INTO courses (title, description, image_url, document_url, status, scholar_id, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("sssssi", $title, $description, $image, $document_path, $status, $scholar_id);
        if ($stmt->execute()) {
            header("Location: manage_course.php?msg=added");
            exit();
        } else {
            $error = "Kosa lilitokea wakati wa kuongeza kozi: " . $db->error;
        }
    }
}

// Fetch scholars for dropdown
$scholars_result = $Database->query("SELECT id, name FROM users WHERE role = 'scholar' ORDER BY name ASC");
?>

<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Ongeza Kozi Mpya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<div class="container mt-5">
    <h2>Ongeza Kozi Mpya</h2>
    <a href="manage_course.php" class="btn btn-secondary mb-3">Rudi nyuma</a>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="title" class="form-label">Kichwa cha Kozi</label>
            <input type="text" class="form-control" id="title" name="title" required value="<?= isset($_POST['title']) ? htmlspecialchars($_POST['title']) : '' ?>">
        </div>
        <!-- Ondoa kabisa sehemu ya maelezo kwenye fomu -->
        <!-- <div class="mb-3"> ... description ... </div> imeondolewa -->
        <div class="mb-3">
            <label for="image" class="form-label">URL ya Picha (hiari)</label>
            <input type="text" class="form-control" id="image" name="image" placeholder="Mfano: ../assets/img/course1.jpg" value="<?= isset($_POST['image']) ? htmlspecialchars($_POST['image']) : '' ?>">
        </div>
        <div class="mb-3">
            <label for="status" class="form-label">Hali ya Kozi</label>
            <select class="form-select" id="status" name="status" required>
                <option value="active" <?= (isset($_POST['status']) && $_POST['status'] === 'active') ? 'selected' : '' ?>>Active</option>
                <option value="inactive" <?= (isset($_POST['status']) && $_POST['status'] === 'inactive') ? 'selected' : '' ?>>Inactive</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="scholar_id" class="form-label">Mwalimu / Sheikh wa Kozi</label>
            <select class="form-select" id="scholar_id" name="scholar_id" required>
                <option value="">-- Chagua Mwalimu --</option>
                <?php while ($scholar = $scholars_result->fetch_assoc()): ?>
                    <option value="<?= $scholar['id'] ?>" <?= (isset($_POST['scholar_id']) && $_POST['scholar_id'] == $scholar['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($scholar['name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="document" class="form-label">Course Document (PDF, DOC, PPT, XLS, TXT)</label>
            <input type="file" class="form-control" id="document" name="document" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.txt">
        </div>
        <button type="submit" class="btn btn-success">Ongeza Kozi</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
