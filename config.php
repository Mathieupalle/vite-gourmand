<?php
declare(strict_types=1);

// Pas d’affichage des erreurs en prod
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
error_reporting(E_ALL);

// Log activé pour envoyer dans les logs Heroku
ini_set('log_errors', '1');

// Configuration base de données MySQL
$databaseUrl = getenv('JAWSDB_URL'); // Variable Heroku JawsDB

if ($databaseUrl) {
    // Si JAWSDB_URL existe, récupérer host, user, pass, db
    $dbparts = parse_url($databaseUrl);

    define('DB_HOST', $dbparts['host']);
    define('DB_PORT', $dbparts['port'] ?? '3306');
    define('DB_NAME', ltrim($dbparts['path'], '/'));
    define('DB_USER', $dbparts['user']);
    define('DB_PASS', $dbparts['pass']);
} else {
    // Sinon, configuration locale ou via variables d'environnement
    define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
    define('DB_PORT', getenv('DB_PORT') ?: '3306');
    define('DB_NAME', getenv('DB_NAME') ?: 'vite_gourmand');
    define('DB_USER', getenv('DB_USER') ?: 'siteweb');
    define('DB_PASS', getenv('DB_PASS') ?: '!Cm51]IOX1HfAiix');
}

// URL & Templates
define('BASE_URL', getenv('BASE_URL') ?: '/');
define('TEMPLATES_PATH', getenv('HEROKU_TEMPLATES_PATH') ?: __DIR__ . '/templates');

// Connexion PDO MySQL
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    die("Erreur de connexion MySQL : " . $e->getMessage());
}

// Configuration MongoDB
$mongodbUri = getenv('MONGODB_URI') ?: 'mongodb+srv://vitegourmand:ViteGourmand12345@cluster0.ptlv7eh.mongodb.net/?appName=Cluster0&tlsAllowInvalidCertificates=true';

return [
    'MONGODB_URI' => $mongodbUri
];