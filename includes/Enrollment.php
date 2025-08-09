<?php

class Enrollment
{
    public static function isEnrolled(int $userId, int $courseId): bool
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('SELECT 1 FROM enrollments WHERE user_id = ? AND course_id = ? LIMIT 1');
        $stmt->bind_param('ii', $userId, $courseId);
        $stmt->execute();
        $res = $stmt->get_result();
        return (bool)$res->fetch_row();
    }

    public static function enroll(int $userId, int $courseId): bool
    {
        if (self::isEnrolled($userId, $courseId)) {
            return true;
        }
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('INSERT INTO enrollments (user_id, course_id) VALUES (?, ?)');
        $stmt->bind_param('ii', $userId, $courseId);
        return $stmt->execute();
    }

    public static function listByUser(int $userId): array
    {
        $db = Database::getInstance()->getConnection();
        $sql = 'SELECT c.* FROM enrollments e JOIN courses c ON e.course_id = c.id WHERE e.user_id = ?';
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }
}

?>

