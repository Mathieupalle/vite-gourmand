<?php
declare(strict_types=1);

// Affichage des erreurs pour debug
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

define('DB_HOST', getenv('DB_HOST'));
define('DB_PORT', getenv('DB_PORT'));
define('DB_NAME', getenv('DB_NAME'));
define('DB_USER', getenv('DB_USER'));
define('DB_PASS', getenv('DB_PASS'));

define('BASE_URL', getenv('BASE_URL') ?: 'http://127.0.0.1/vite-gourmand/public/');
define('TEMPLATES_PATH', getenv('HEROKU_TEMPLATES_PATH'));

$pdo = new PDO(
    "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8",
    DB_USER,
    DB_PASS,
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]
);