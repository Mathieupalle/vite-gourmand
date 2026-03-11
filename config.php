<?php
declare(strict_types=1);

// Affichage des erreurs pour debug
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);


define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'vite_gourmand');
define('DB_USER', getenv('DB_USER') ?: 'siteweb');
define('DB_PASS', getenv('DB_PASS') ?: '!Cm51]IOX1HfAiix');


// URL & TEMPLATES
define('BASE_URL', getenv('BASE_URL') ?: '/');
define('TEMPLATES_PATH', getenv('HEROKU_TEMPLATES_PATH') ?: __DIR__ . '/templates');


// CONNEXION PDO
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}