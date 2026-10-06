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
// Convertit une date enregistrée en UTC vers l'heure de Paris pour l'affichage
if (!function_exists('dateFr')) {
    function dateFr(?string $value, string $format = 'd/m/Y H:i'): string
    {
        if ($value === null || $value === '') {
            return '';
        }
        try {
            $date = new DateTimeImmutable($value, new DateTimeZone('UTC'));
            return $date->setTimezone(new DateTimeZone('Europe/Paris'))->format($format);
        } catch (Throwable $e) {
            return $value;
        }
    }
}

// Racine des URLs de l'application (vide à la racine du domaine)
if (!function_exists('base_url')) {
    function base_url(): string
    {
        return rtrim(defined('BASE_URL') ? (string)BASE_URL : '', '/');
    }
}
