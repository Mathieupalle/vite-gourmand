<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Infrastructure\Database;

try {
    $pdo = Database::getConnection();
    echo "Connexion MySQL OK !<br>";

    $stmt = $pdo->query("SELECT email, role_id FROM utilisateur LIMIT 5");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<pre>";
    print_r($users);
    echo "</pre>";

} catch (Throwable $e) {
    echo "Erreur connexion : " . $e->getMessage();
}