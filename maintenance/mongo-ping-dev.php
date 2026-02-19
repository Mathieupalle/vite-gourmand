<?php
ini_set('display_errors','1');
error_reporting(E_ALL);

require_once __DIR__ . '/vendor/autoload.php';

// 1) Heroku / env
$uri = getenv('MONGODB_URI');

// 2) Local XAMPP / config.local.php
if (!$uri && file_exists(__DIR__ . '/config.local.php')) {
    $cfg = require __DIR__ . '/config.local.php';
    $uri = $cfg['MONGODB_URI'] ?? null;
}

if (!$uri) {
    http_response_code(500);
    exit('MONGODB_URI manquant (env ou config.local.php)');
}

try {
    // DEV ONLY: contournement TLS local
    $client = new MongoDB\Client($uri, [], [
        'tls' => true,
        'tlsAllowInvalidCertificates' => true
    ]);

    $res = $client->selectDatabase('admin')->command(['ping' => 1])->toArray();
    echo "Ping OK (DEV)\n";
    var_dump($res);
} catch (Throwable $e) {
    http_response_code(500);
    echo "Erreur Mongo: " . $e->getMessage();
}
