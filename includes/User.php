<?php

class User
{
    public int $id;
    public string $name;
    public string $email;
    public string $passwordHash;
    public string $role;

    public static function findByEmail(string $email): ?User
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('SELECT id, name, email, password, role FROM users WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $user = new self();
            $user->id = (int)$row['id'];
            $user->name = $row['name'];
            $user->email = $row['email'];
            $user->passwordHash = $row['password'];
            $user->role = $row['role'];
            return $user;
        }
        return null;
    }
    public static function updateLastLogin(int $userId): void
{
    $conn = Database::getInstance()->getConnection();
    $stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->close();
}
public static function updateLastLogout(int $userId): void
{
    $conn = Database::getInstance()->getConnection();
    $stmt = $conn->prepare("UPDATE users SET last_logout = NOW() WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->close();
}

    public static function emailExists(string $email): bool
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('SELECT 1 FROM users WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        return (bool)$result->fetch_row();
    }

    public static function create(string $name, string $email, string $password, string $role = 'user'): int
    {
        $db = Database::getInstance()->getConnection();
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $db->prepare('INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('ssss', $name, $email, $hash, $role);
        if (!$stmt->execute()) {
            throw new RuntimeException('Failed to create user');
        }
        return $db->insert_id;
    }
}

?>

