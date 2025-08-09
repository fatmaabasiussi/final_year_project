<?php

class Auth
{
   public static function login(string $email, string $password): bool
{
    $user = User::findByEmail($email);
    if (!$user) {
        return false;
    }
    if (!password_verify($password, $user->passwordHash)) {
        return false;
    }
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    // Save session info
    $_SESSION['user_id'] = $user->id;
    $_SESSION['role'] = $user->role;
    $_SESSION['name'] = $user->name;

    // Update last_login in DB
    User::updateLastLogin($user->id);

    return true;
}


    public static function register(string $name, string $email, string $password): int
    {
        if (User::emailExists($email)) {
            throw new InvalidArgumentException('Email already exists');
        }
        return User::create($name, $email, $password);
    }
}

?>

