<?php
session_start();
// Scholar only access
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'scholar') {
    header("Location: ../login.php");
    exit;
}
require_once '../includes/functions.php';
require_once '../inc/db.php';

$scholar_id = $_SESSION['user_id'];
$success = $error = '';

// Handle answer submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['question_id'], $_POST['answer'])) {
    $question_id = intval($_POST['question_id']);
    $answer = trim($_POST['answer']);
    if ($answer === '') {
        $error = 'Jibu haliwezi kuwa tupu.';
    } else {
        $stmt = $db->prepare("UPDATE questions SET answer = ?, status = 'answered' WHERE id = ?");
        $stmt->bind_param('si', $answer, $question_id);
        if ($stmt->execute()) {
            $success = 'Jibu limehifadhiwa!';
        } else {
            $error = 'Tatizo la database: ' . $db->error;
        }
    }
}

// Fetch all questions (pending and answered) for this scholar
$sql = "SELECT q.*, u.name as user_name FROM questions q 
        JOIN users u ON q.user_id = u.id 
        WHERE q.scholar_id = ? 
        ORDER BY q.status ASC, q.created_at DESC";
$stmt = $db->prepare($sql);
$stmt->bind_param('i', $scholar_id);
$stmt->execute();
$questions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>View Questions - Sheikh Panel</title>
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
    }
    #sidebar .scholar-name {
      font-size: 1.5rem;
      font-weight: 700;
      margin-bottom: 0.1rem;
      text-align: center;
      text-shadow: 0 1px 2px rgba(0,0,0,0.2);
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

    #content {
      flex-grow: 1;
      background: #ffffff;
      display: flex;
      flex-direction: column;
      min-height: 100vh;
      box-shadow: inset 0 0 10px #e0e0e0;
    }
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
    }

    main.container-fluid {
      padding: 2rem 3rem 3rem 3rem;
      flex-grow: 1;
    }
    main.container-fluid h3 {
      color: #19692c;
      font-weight: 800;
      margin-bottom: 0.6rem;
      user-select: none;
    }
    main.container-fluid p.lead {
      color: #3a6e3f;
      font-size: 1.15rem;
      margin-bottom: 2rem;
      font-weight: 600;
      user-select: none;
    }

    .stats-row {
      display: flex;
      gap: 1.8rem;
      margin-bottom: 3rem;
      flex-wrap: wrap;
    }
    .stats-card {
      flex: 1 1 210px;
      background: #d4edda;
      border-radius: 14px;
      box-shadow: 0 3px 12px rgba(40,167,69,0.3);
      padding: 1.6rem 1.4rem;
      display: flex;
      align-items: center;
      gap: 1.2rem;
      border: 1.5px solid #c3e6cb;
      transition: box-shadow 0.3s, transform 0.3s;
      color: #155724;
      cursor: default;
      user-select: none;
    }
    .stats-card:hover {
      box-shadow: 0 10px 28px rgba(40,167,69,0.5);
      transform: translateY(-5px) scale(1.06);
    }
    .stats-icon {
      font-size: 2.3rem;
      color: #155724;
      background: #c3e6cb;
      border-radius: 50%;
      padding: 0.8rem;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }
    .stats-card div > div.fw-bold {
      font-size: 1.05rem;
      margin-bottom: 0.15rem;
    }
    .stats-card div > div.fs-4 {
      font-weight: 700;
      font-size: 1.9rem;
    }

    /* Table */
    table.table {
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 3px 15px rgba(0,0,0,0.07);
      user-select: none;
    }
    thead.table-success {
      background: #28a745;
      color: white;
      font-weight: 700;
      user-select: none;
    }
    tbody tr:hover {
      background-color: #e6f4ea !important;
      cursor: pointer;
    }
    tbody td {
      vertical-align: middle;
    }
    tbody td a {
      text-decoration: none;
      color: #28a745;
      font-weight: 600;
      transition: color 0.2s;
    }
    tbody td a:hover {
      color: #1e7e34;
    }
    .btn-outline-primary {
      font-weight: 600;
      border-radius: 8px;
      padding: 0.3rem 0.7rem;
      font-size: 0.9rem;
      transition: all 0.2s ease;
    }
    .btn-outline-primary:hover {
      background-color: #28a745;
      color: white;
      box-shadow: 0 4px 12px rgba(40,167,69,0.7);
    }

    /* Responsive */
    @media (max-width: 900px) {
      .wrapper {
        flex-direction: column;
      }
      #sidebar {
        width: 100%;
        padding: 1rem 0.5rem;
        flex-direction: row;
        justify-content: space-around;
        align-items: center;
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
        min-height: auto;
      }
      #header {
        padding: 0 1rem;
        font-size: 1.1rem;
        justify-content: center;
      }
      main.container-fluid {
        padding: 1rem 1rem 2rem 1rem;
      }
      .stats-row {
        gap: 1rem;
        margin-bottom: 2rem;
      }
    }
    /* Main content */
    #content {
      margin-left: 0px;
      flex-grow: 1;
      display: flex;
      flex-direction: column;
      min-height: 100vh;
      background: #fff;
    }
    /* Header */
    #header {
      height: 70px;
      background: #2bb14f;
      color: #fff;
      display: flex;
      align-items: center;
      padding: 0 2rem;
      font-weight: 600;
      font-size: 1.25rem;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      position: sticky;
      top: 0;
      z-index: 10;
    }
    #header span {
      user-select: none;
    }
    /* Main area */
    main {
      flex-grow: 1;
      overflow-y: auto;
      padding: 2rem;
      max-width: 800px;
      margin: 0 auto 2rem auto;
    }
    .question-card {
      margin-bottom: 1.5rem;
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
        <li><a href="scholar.php" class="nav-link" ><i class="fas fa-home"></i> Kwenye Dashboard</a></li>
        <li><a href="upload_course.php" class="nav-link"><i class="fas fa-upload"></i> Pakia Kozi</a></li>
        <li><a href="scholar_questions.php" class="nav-link active" aria-current="page"><i class="fas fa-question-circle"></i> Angalia Maswali</a></li>
        <li><a href="upload_mcq.php" class="nav-link"><i class="fas fa-pen-nib"></i> Tunga Quiz</a></li>
        <li><a href="view_mcq_results.php" class="nav-link"><i class="fas fa-chart-bar"></i> Matokeo ya Quiz</a></li>
        <li><a href="scholar_profile.php" class="nav-link"><i class="fas fa-user"></i> Profaili Yangu</a></li>
        <li><a href="scholar_settings.php" class="nav-link"><i class="fas fa-cog"></i> Mipangilio</a></li>
        <li><a href="../logout.php" class="nav-link text-danger"><i class="fas fa-sign-out-alt"></i> Toka</a></li>
      </ul>
    </nav>
    <div id="content">
      <header id="header">
       <span class="header-avatar"><i class="fas fa-question-circle"></i></span>
         <span>Angalia Maswali</span>
      </header>
      <main>
        <?php if ($success): ?>
          <div class="alert alert-success"> <?= $success ?> </div>
        <?php elseif ($error): ?>
          <div class="alert alert-danger"> <?= $error ?> </div>
        <?php endif; ?>
        <?php
        $pending = array_filter($questions, fn($q) => $q['status'] === 'pending');
        $answered = array_filter($questions, fn($q) => $q['status'] === 'answered');
        ?>
        <h3 class="mb-4">Maswali Yako (Yanayosubiri Jibu)</h3>
        <?php if (empty($pending)): ?>
          <div class="alert alert-info">Hakuna maswali yanayosubiri jibu.</div>
        <?php else: ?>
          <?php foreach ($pending as $q): ?>
            <div class="card question-card shadow-sm">
              <div class="card-body">
                <h5 class="card-title mb-1"><?= htmlspecialchars($q['question']) ?></h5>
                <p class="mb-1">
                  <small>
                    <i class="fas fa-user"></i> <?= htmlspecialchars($q['user_name']) ?> |
                    <i class="fas fa-tag"></i> <?= htmlspecialchars($q['category']) ?> |
                    <i class="fas fa-clock"></i> <?= date('M d, Y', strtotime($q['created_at'])) ?>
                  </small>
                </p>
                <hr>
                <div class="mb-2"><span class="badge bg-warning text-dark">Pending</span></div>
                <form method="post" class="mt-2">
                  <input type="hidden" name="question_id" value="<?= $q['id'] ?>">
                  <div class="mb-2">
                    <textarea name="answer" class="form-control" rows="2" placeholder="Andika jibu hapa..." required></textarea>
                  </div>
                  <button type="submit" class="btn btn-success btn-sm">Jibu</button>
                </form>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>

        <h3 class="mb-4 mt-5">Maswali Uliyokwisha Jibu</h3>
        <?php if (empty($answered)): ?>
          <div class="alert alert-info">Hakuna maswali yaliyokwisha jibiwa.</div>
        <?php else: ?>
          <?php foreach ($answered as $q): ?>
            <div class="card question-card shadow-sm">
              <div class="card-body">
                <h5 class="card-title mb-1"><?= htmlspecialchars($q['question']) ?></h5>
                <p class="mb-1">
                  <small>
                    <i class="fas fa-user"></i> <?= htmlspecialchars($q['user_name']) ?> |
                    <i class="fas fa-tag"></i> <?= htmlspecialchars($q['category']) ?> |
                    <i class="fas fa-clock"></i> <?= date('M d, Y', strtotime($q['created_at'])) ?>
                  </small>
                </p>
                <hr>
                <div class="mb-2"><span class="badge bg-success">Answered</span></div>
                <div class="mb-2"><strong>Jibu:</strong> <?= nl2br(htmlspecialchars($q['answer'])) ?></div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </main>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
