<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config.php';

use App\Infrastructure\Database;

// --- Connexion PDO ---
$pdo = Database::getConnection();

// --- Comptes à mettre à jour ---
$accounts = [
    'user@demo.fr' => 'User12345!',
    'employe@demo.fr' => 'Employe1234!',
    'admin@demo.fr' => 'Admin1234!',
];

foreach ($accounts as $email => $plainPassword) {
    $hash = password_hash($plainPassword, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE utilisateur SET password = ? WHERE email = ?");
    $stmt->execute([$hash, $email]);
    echo "Mot de passe pour $email mis à jour.\n";
}

echo "✅ Tous les mots de passe ont été corrigés et synchronisés.\n";