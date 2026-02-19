<?php
require __DIR__ . '/vendor/autoload.php';

$uri = getenv('MONGODB_URI');
$client = new MongoDB\Client($uri);

$cursor = $client->listDatabases();
foreach ($cursor as $db) {
    echo $db->getName() . "\n";
}
