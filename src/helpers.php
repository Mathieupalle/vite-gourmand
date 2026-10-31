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
}

// Enregistre l'utilisateur connecté dans la session
if (!function_exists('setSessionUser')) {
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

// Jeton CSRF : une valeur aléatoire gardée en session pour toute la durée de la connexion
if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    // Champ caché à placer dans chaque formulaire POST
    function csrf_field(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
    }

    // Compare le jeton reçu avec celui de la session (hash_equals évite les attaques par mesure du temps)
    function csrf_valide(string $jeton): bool
    {
        return !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $jeton);
    }
}
