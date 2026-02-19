<?php
// src/auth.php : Authentification utilisateurs.
// Fonctions pour vérifier la connexion et les rôles (user/employee/admin).

declare(strict_types=1);

function requireLogin(): void
{
    if (empty($_SESSION['user'])) {
        header('Location: login.php');
        exit;
    }
}

function requireRole(array $roles): void
{
    requireLogin();
    $role = $_SESSION['user']['role'] ?? '';
    if (!in_array($role, $roles, true)) {
        http_response_code(403);
        echo "Accès interdit.";
        exit;
    }
}
