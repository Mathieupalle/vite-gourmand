<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Infrastructure\Database;

// Connexion PDO
$pdo = Database::getConnection();

// Liste des utilisateurs et leurs hashes
$users = [
    'user@demo.fr' => '$2y$10$73NwSFxbvRM2bdlLf8qzZuD1LKVS8CImAUSlTv6qxsb/mPH7mj4fW',
    'employe@demo.fr' => '$2y$10$FLZz89lEgdUtjfotBqwfT.GA7qlr9RwqXCm3hxRiTCYqS7XNewyvG',
    'admin@demo.fr' => '$2y$10$B4IE5i8LkJGxa6BDJ5nLu.cbwcMjnx0lfvMul5kbjxwHUxazSf6OK',
];

foreach ($users as $email => $hash) {
    $stmt = $pdo->prepare("UPDATE utilisateur SET password = ? WHERE email = ?");
    $stmt->execute([$hash, $email]);
    echo "Mot de passe pour {$email} mis à jour.\n";
}

echo "Tous les mots de passe ont été synchronisés.\n";