<?php
session_start();

if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    require_once __DIR__ . '/includes/functions.php';

    $conn = Database::getInstance()->getConnection();
    $stmt = $conn->prepare("UPDATE users SET last_logout = NOW() WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->close();
}
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    User::updateLastLogout($userId);
}
// Sasa fanya logout
session_unset();
session_destroy();

header("Location: index.php");
exit;
?>
