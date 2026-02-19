<?php
function base_url(): string
{
    // Heroku fournit l'hostname via $_SERVER['HTTP_HOST'] sur les requêtes web
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    $scheme = $https ? 'https' : 'http';

    // Si tu es dans /vite-gourmand en local, garde le path, sinon vide en prod
    $path = '';
    if ($host === 'localhost' || str_contains($host, '127.0.0.1')) {
        $path = '/vite-gourmand';
    }

    return $scheme . '://' . $host . $path;
}
