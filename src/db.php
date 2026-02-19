<?php
// src/db.php : connexion PDO
// Compatible Local (XAMPP) + Heroku (JawsDB)

declare(strict_types=1);

function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $jawsUrl = getenv('JAWSDB_URL');

    try {

        if ($jawsUrl) {
            // =========================
            // PRODUCTION (Heroku + JawsDB)
            // =========================

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

            $pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false
            ]);

        } else {
            // =========================
            // LOCAL (XAMPP)
            // =========================

            $dsn = "mysql:host=127.0.0.1;port=3306;dbname=vite_gourmand;charset=utf8mb4";

            $pdo = new PDO($dsn, "root", "", [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        }

        return $pdo;

    } catch (Throwable $e) {

        if ($jawsUrl) {
            // En production → on log seulement
            error_log("Database connection error: " . $e->getMessage());
            http_response_code(500);
            exit("Erreur de connexion à la base de données.");
        }

        // En local → debug complet
        die("Erreur DB : " . $e->getMessage());
    }
}
