<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config.php';

echo "<h2>Test MongoDB Bases</h2>";

try {
    $client = new MongoDB\Client($mongodbUri);
    $dbs = iterator_to_array($client->listDatabases());

    echo "Toutes les bases MongoDB :<br>";
    foreach ($dbs as $db) {
        echo "- " . $db['name'] . "<br>";
    }

    // Filtrer uniquement les bases “utilisateur”
    $userDbs = array_filter($dbs, fn($db) => !in_array($db['name'], ['admin', 'local', 'config']));
    echo "<br>Bases utilisateur : " . count($userDbs) . "<br>";
    foreach ($userDbs as $db) {
        echo "- " . $db['name'] . "<br>";
    }

} catch (Exception $e) {
    echo "Erreur MongoDB: " . $e->getMessage() . "<br>";
}