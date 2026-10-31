<?php
declare(strict_types=1);

// Configuration de production (Heroku)

// Pas d'affichage des erreurs en production, elles sont envoyées dans les logs Heroku
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
error_reporting(E_ALL);
ini_set('log_errors', '1');

// La connexion MySQL utilise la variable JAWSDB_URL (voir src/Infrastructure/Database.php)

// URL & Templates
define('BASE_URL', getenv('BASE_URL') ?: '/');
define('TEMPLATES_PATH', __DIR__ . '/templates');

// Configuration MongoDB (statistiques)
return [
    'MONGODB_URI' => getenv('MONGODB_URI') ?: ''
];
