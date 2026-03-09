<?php
declare(strict_types=1);

namespace App\Security;

final class Auth
{
    public static function requireLogin(string $redirectTo = 'login'): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (empty($_SESSION['user'])) {
            header("Location: {$redirectTo}");
            exit;
        }
    }

    public static function requireRole(array $roles): void
    {
        self::requireLogin();

        $role = (string)($_SESSION['user']['role'] ?? '');
        if (!in_array($role, $roles, true)) {
            http_response_code(403);
            echo "Accès interdit.";
            exit;
        }
    }

    public static function isLoggedIn(): bool
    {
        return session_status() === PHP_SESSION_ACTIVE
            && isset($_SESSION['user'])
            && !empty($_SESSION['user']);
    }
}