<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php'; // si vous utilisez Composer
require __DIR__ . '/../src/Auth.php';       // chemin vers votre Auth.php

use App\Security\Auth;

$email = 'user@example.com';        // Email à réinitialiser
$newPassword = 'Test1234';          // Nouveau mot de passe

// Connexion PDO à votre DB Heroku
$pdo = new PDO(getenv('DATABASE_URL'));

// Générer le hash
$hash = Auth::hashPassword($newPassword);

// Mettre à jour la base
$stmt = $pdo->prepare('UPDATE users SET password = :hash WHERE email = :email');
$stmt->execute([
    ':hash' => $hash,
    ':email' => $email
]);

echo "Mot de passe réinitialisé pour $email\n";

// Vérification
$stmt = $pdo->prepare('SELECT password FROM users WHERE email = :email');
$stmt->execute([':email' => $email]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (Auth::verifyPassword($newPassword, $row['password'])) {
    echo "Login test réussi ✅\n";
} else {
    echo "Problème de hash ❌\n";
}