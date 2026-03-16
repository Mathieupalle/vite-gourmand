<?php
declare(strict_types=1);

// ⚡ Ajoute cette ligne pour que PHP connaisse MongoDB\Client
require __DIR__ . '/../vendor/autoload.php';

// Inclure ta config
require __DIR__ . '/../config.php';

echo "<h2>Test base de données</h2>";

// --- Test MySQL ---
try {
    $stmt = $pdo->query("SELECT NOW() AS `current_time`");
    $row = $stmt->fetch();
    echo "MySQL OK: " . $row['current_time'] . "<br>";
} catch (Exception $e) {
    echo "Erreur MySQL: " . $e->getMessage() . "<br>";
}

// --- Test MongoDB ---
try {
    $client = new MongoDB\Client($mongodbUri);
    $dbs = $client->listDatabases();
    echo "MongoDB OK: " . count($dbs) . " databases found.<br>";
} catch (Exception $e) {
    echo "Erreur MongoDB: " . $e->getMessage() . "<br>";
}