<?php
declare(strict_types=1);

if (!function_exists('e')) {
    function e(?string $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('redirect')) {
    function redirect(string $path): void
    {
        if ($path !== '' && $path[0] !== '/') $path = '/' . $path;
        header('Location: ' . base_url() . $path);
        exit;
    }

    function setSessionUser(array $user): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $_SESSION['user'] = [
            'id' => $user['id'],
            'email' => $user['email'],
            'role' => strtolower($user['role']), // 'admin', 'employe', 'user'
            'nom' => $user['nom'],
            'prenom' => $user['prenom'],
            'telephone' => $user['telephone'] ?? '',
            'ville' => $user['ville'] ?? null,
            'adresse_postale' => $user['adresse_postale'] ?? '',
        ];
    }
}