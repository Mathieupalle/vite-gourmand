<?php
require __DIR__ . '/vendor/autoload.php';

$uri = getenv('MONGODB_URI');
if (!$uri) {
    http_response_code(500);
    echo "MONGODB_URI manquant\n";
    exit;
}

try {
    $client = new MongoDB\Client($uri);
    $client->selectDatabase('admin')->command(['ping' => 1]);
    echo "Ping OK\n";
} catch (Throwable $e) {
    http_response_code(500);
    echo "Erreur: " . $e->getMessage() . "\n";
}
