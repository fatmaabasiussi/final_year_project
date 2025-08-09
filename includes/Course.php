<?php

class Course
{
    public static function listWithScholar(): array
    {
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT c.*, u.name AS scholar_name FROM courses c JOIN users u ON c.scholar_id = u.id ORDER BY c.created_at DESC";
        $result = $db->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public static function listByScholar(int $scholarId): array
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('SELECT * FROM courses WHERE scholar_id = ? ORDER BY created_at DESC');
        $stmt->bind_param('i', $scholarId);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    public static function stats(): array
    {
        $db = Database::getInstance()->getConnection();
        $total = (int)($db->query("SELECT COUNT(*) AS c FROM courses")->fetch_assoc()['c'] ?? 0);
        $active = (int)($db->query("SELECT COUNT(*) AS c FROM courses WHERE status = 'active'")->fetch_assoc()['c'] ?? 0);
        $students = (int)($db->query("SELECT COUNT(DISTINCT user_id) AS c FROM enrollments")->fetch_assoc()['c'] ?? 0);
        $scholars = (int)($db->query("SELECT COUNT(DISTINCT scholar_id) AS c FROM courses")->fetch_assoc()['c'] ?? 0);
        return [
            'total' => $total,
            'active' => $active,
            'students' => $students,
            'scholars' => $scholars,
        ];
    }

    public static function findForUserWithProgress(int $courseId, int $userId): ?array
    {
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT c.*, u.name AS scholar_name, e.progress, e.last_accessed,
                       (SELECT COUNT(*) FROM course_modules WHERE course_id = c.id) AS total_modules,
                       (SELECT COUNT(*) FROM module_completion WHERE user_id = ? AND module_id IN (SELECT id FROM course_modules WHERE course_id = c.id)) AS completed_modules
                FROM courses c
                JOIN users u ON c.scholar_id = u.id
                JOIN enrollments e ON c.id = e.course_id
                WHERE c.id = ? AND e.user_id = ?";
        $stmt = $db->prepare($sql);
        $stmt->bind_param('iii', $userId, $courseId, $userId);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        return $row ?: null;
    }

    public static function getModulesWithCompletion(int $courseId, int $userId): array
    {
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT m.*, (SELECT completed_at FROM module_completion WHERE user_id = ? AND module_id = m.id) AS completed_at
                FROM course_modules m WHERE m.course_id = ? ORDER BY m.order_number";
        $stmt = $db->prepare($sql);
        $stmt->bind_param('ii', $userId, $courseId);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }
}

?>

