<?php
declare(strict_types=1);

// Debug
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
ini_set('log_errors', '1');

// Docker
$isDocker = getenv('DOCKER') === '1';
// Mode développement : jamais en production (JAWSDB_URL n'existe que sur Heroku)
if (!getenv('JAWSDB_URL')) {
    if (getenv('APP_ENV') === false) putenv('APP_ENV=dev');
    if (getenv('APP_URL') === false) {
        putenv('APP_URL=' . ($isDocker ? 'http://localhost:8080' : 'http://localhost/vite-gourmand/public'));
    }
}

// MySQL
if (!defined('DB_HOST')) define('DB_HOST', $isDocker ? 'db' : '127.0.0.1');
if (!defined('DB_PORT')) define('DB_PORT', 3306);
if (!defined('DB_NAME')) define('DB_NAME', 'vite_gourmand');
if (!defined('DB_USER')) define('DB_USER', 'siteweb');
if (!defined('DB_PASS')) define('DB_PASS', '!Cm51]IOX1HfAiix');

// Projet
if (!defined('BASE_URL')) define('BASE_URL', $isDocker ? '' : '/vite-gourmand/public');
if (!defined('TEMPLATES_PATH')) define('TEMPLATES_PATH', __DIR__ . '/templates');

// Connexion PDO
$start = time();
while (true) {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ]
        );
        break;
    } catch (PDOException $e) {
        if ((time() - $start) >= 15) {
            die("MySQL indisponible après 15 sec : " . $e->getMessage());
        }
        sleep(1);
    }
}

// MongoDB
return [
    'MONGODB_URI' => ''
];