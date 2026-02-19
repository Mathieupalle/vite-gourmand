<?php
// test-db.php : page de test technique.
// Vérifie que la connexion PDO à la base de données fonctionne.

require_once __DIR__ . '/src/db.php';

try {
    $pdo = db();
    $row = $pdo->query("SELECT NOW() AS now")->fetch();
    echo "✅ DB OK : " . $row['now'];
} catch (Throwable $e) {
    http_response_code(500);
    echo "❌ DB ERROR: " . $e->getMessage();
}
