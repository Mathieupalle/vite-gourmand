<?php
// src/db.php : connexion PDO.
// Centralise la connexion à la base de données (local XAMPP ou variables Heroku/JawsDB).

declare(strict_types=1);

function db(): PDO
{
    $jawsUrl = getenv('JAWSDB_URL');

    if ($jawsUrl) {
        // PRODUCTION (Heroku + JawsDB)
        $parts = parse_url($jawsUrl);

        $host = $parts['host'] ?? '';
        $port = $parts['port'] ?? 3306;
        $user = $parts['user'] ?? '';
        $pass = $parts['pass'] ?? '';
        $dbname = ltrim($parts['path'] ?? '', '/');

        $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
    } else {
        // LOCAL (XAMPP)
        $host = "127.0.0.1";
        $port = 3306;
        $dbname = "vite_gourmand";
        $user = "root";
        $pass = "";

        $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
    }

    try {
        return new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    } catch (PDOException $e) {
        // En production, ne pas afficher les détails
        if ($jawsUrl) {
            error_log("Database connection error");
            http_response_code(500);
            exit("Erreur de connexion à la base de données.");
        }

        // En local on peut afficher l’erreur
        die("Erreur DB : " . $e->getMessage());
    }
}
