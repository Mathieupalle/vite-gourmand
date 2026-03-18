<?php
declare(strict_types=1);

namespace App\Infrastructure;

use MongoDB\Client;

final class MongoDB
{
    public static function createFromEnvOrLocalConfig(): Client
    {
        $root = dirname(__DIR__, 2); // racine projet
        $mongoUri = getenv('MONGODB_URI');

        if (!$mongoUri && file_exists($root . '/config.local.php')) {
            $cfg = require $root . '/config.local.php';
            $mongoUri = $cfg['MONGODB_URI'] ?? null;
        }

        if (!$mongoUri) {
            throw new \RuntimeException('MONGODB_URI manquant');
        }

        $driverOpts = [];

        // Hack TLS local
        if (PHP_SAPI !== 'cli' && (($_SERVER['HTTP_HOST'] ?? '') === '127.0.0.1')) {
            $driverOpts['tlsCAFile'] = '/Applications/XAMPP/xamppfiles/etc/ssl/cacert.pem';
        }

        return new Client($mongoUri, [], $driverOpts);
    }
}