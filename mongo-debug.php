<?php
require __DIR__ . '/vendor/autoload.php';

try {
    $uri = getenv('MONGODB_URI');
    if (!$uri) {
        throw new RuntimeException("MONGODB_URI manquant");
    }

    $mongo = new MongoDB\Client($uri);
    $col = $mongo->selectCollection('vitegourmand', 'orders_analytics');

    $res = $col->insertOne([
        'type' => 'debug',
        'createdAt' => new MongoDB\BSON\UTCDateTime((int) round(microtime(true) * 1000)),
        'where' => 'heroku-run'
    ]);

    echo "Inserted ID: " . (string)$res->getInsertedId() . "\n";
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
