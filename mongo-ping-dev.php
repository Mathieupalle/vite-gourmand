<?php
require __DIR__ . '/vendor/autoload.php';

$uri = getenv('MONGODB_URI');
if (!$uri) { die("MONGODB_URI manquant\n"); }

try {
    $client = new MongoDB\Client($uri, [
            'tls' => true,
            'tlsAllowInvalidCertificates' => true, // DEV ONLY
    ]);
    $client->selectDatabase('admin')->command(['ping' => 1]);
    echo "Ping OK (DEV)\n";
} catch (Throwable $e) {
    echo "Erreur: " . $e->getMessage() . "\n";
}