<?php
declare(strict_types=1);

require 'path/to/Auth.php'; // ajustez selon votre structure

// Paramètres à modifier
$email = 'user@example.com';
$newPassword = 'Test1234'; // mot de passe temporaire pour test

// Connexion à la DB Heroku (PDO)
$dsn = getenv('DATABASE_URL'); // ou votre DSN PDO
$pdo = new PDO($dsn);

// Hash du mot de passe
$hashed = App\Security\Auth::hashPassword($newPassword);

// Mise à jour dans la base
$stmt = $pdo->prepare('UPDATE users SET password = :hash WHERE email = :email');
$stmt->execute([
    ':hash' => $hashed,
    ':email' => $email,
]);

echo "Mot de passe réinitialisé pour $email\n";

// Vérification rapide
$stmt = $pdo->prepare('SELECT password FROM users WHERE email = :email');
$stmt->execute([':email' => $email]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (App\Security\Auth::verifyPassword($newPassword, $row['password'])) {
    echo "Login test réussi ✅\n";
} else {
    echo "Problème de hash ❌\n";
}