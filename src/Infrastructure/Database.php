<?php
// Connexion PDO (POO)
// Compatible Local (XAMPP) + Heroku (JawsDB)

declare(strict_types=1);

namespace App\Infrastructure;

use PDO;
use Throwable;
use Exception;

final class Database
{
    private static ?PDO $pdo = null;

    public static function getConnection(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $jawsUrl = getenv('JAWSDB_URL');

        try {
            if ($jawsUrl) {
                // PRODUCTION (Heroku + JawsDB)
                $parts = parse_url($jawsUrl);

                if ($parts === false) {
                    throw new Exception('JAWSDB_URL invalide');
                }

                $host   = $parts['host'] ?? '';
                $port   = $parts['port'] ?? 3306;
                $user   = $parts['user'] ?? '';
                $pass   = $parts['pass'] ?? '';
                $dbname = ltrim($parts['path'] ?? '', '/');

                $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

                $opts = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ];

                // Désactivation vérif certificat SSL si disponible (compat Heroku/JawsDB)
                if (defined('Pdo\\Mysql::ATTR_SSL_VERIFY_SERVER_CERT')) {
                    $opts[\Pdo\Mysql::ATTR_SSL_VERIFY_SERVER_CERT] = false;
                } elseif (defined('PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT')) {
                    $opts[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
                }

                self::$pdo = new PDO($dsn, $user, $pass, $opts);
            } else {
                // LOCAL (XAMPP)
                $dsn = "mysql:host=127.0.0.1;port=3306;dbname=vite_gourmand;charset=utf8mb4";

                self::$pdo = new PDO($dsn, "siteweb", "!Cm51]IOX1HfAiix", [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            }

            return self::$pdo;

        } catch (Throwable $e) {
            if ($jawsUrl) {
                // En production → log seulement
                error_log("Database connection error: " . $e->getMessage());
                http_response_code(500);
                exit("Erreur de connexion à la base de données.");
            }
            // En local → debug complet
            die("Erreur DB : " . $e->getMessage());
        }
    }
}