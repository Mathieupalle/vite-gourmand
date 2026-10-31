<?php
declare(strict_types=1);

// Script pour migrer les commandes MySQL vers MongoDB

require_once __DIR__ . '/../vendor/autoload.php';

use App\Migration\MigrateOrders;
use App\Infrastructure\Database;
use MongoDB\Client;

// Charger la configuration
$configFile = __DIR__ . '/../config.local.php';
if (!file_exists($configFile)) {
    $configFile = __DIR__ . '/../config.php';
}
$config = require $configFile;

// Connexion MySQL
$pdo = Database::getConnection();

// Connexion MongoDB
if (!isset($config['MONGODB_URI'])) {
    die("Erreur : MongoDB URI manquant dans la config.\n");
}
$mongo = new Client($config['MONGODB_URI']);

// Instanciation du migrateur
$migrator = new MigrateOrders($pdo, $mongo);

// Lancer la migration
$nbMigrated = $migrator->migrateNewOrders();

// Afficher le résultat
echo "Nombre de commandes migrées : $nbMigrated\n";