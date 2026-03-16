<?php
require_once __DIR__ . '/../src/Infrastructure/Database.php';

try {
    $pdo = \App\Infrastructure\Database::getConnection();
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables dans la base :<br>";
    echo implode('<br>', $tables);
} catch (Throwable $e) {
    echo "Erreur : " . $e->getMessage();
}