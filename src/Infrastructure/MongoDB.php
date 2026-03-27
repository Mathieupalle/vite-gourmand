<?php
declare(strict_types=1);

namespace App\Infrastructure;

use MongoDB\Client;

final class MongoDB
{
    public static function createFromEnvOrLocalConfig(): Client
    {
        $root = dirname(__DIR__, 2);

        $mongoUri = getenv('MONGODB_URI');

        if (!$mongoUri && file_exists($root . '/config.local.php')) {
            $cfg = require $root . '/config.local.php';
            $mongoUri = $cfg['MONGODB_URI'] ?? null;
        }

        if (!$mongoUri) {
            throw new \RuntimeException('MONGODB_URI manquant');
        }

        return new Client($mongoUri);
    }
}