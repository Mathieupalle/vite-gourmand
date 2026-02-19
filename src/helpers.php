<?php
function base_url(): string
{
    // Heroku fournit l'hostname via $_SERVER['HTTP_HOST'] sur les requêtes web
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    $scheme = $https ? 'https' : 'http';

    // Si on est dans /vite-gourmand en local, garder le path, sinon vider en prod
    $path = '';
    if ($host === 'localhost' || str_contains($host, '127.0.0.1')) {
        $path = '/vite-gourmand';
    }

    return $scheme . '://' . $host . $path;
}
