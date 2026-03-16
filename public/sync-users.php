<?php
declare(strict_types=1);

// --- Configuration MySQL Heroku ---
$dsn = 'mysql:host=d1kb8x1fu8rhcnej.cbetxkdyhwsb.us-east-1.rds.amazonaws.com;dbname=kxdo1g9nh3n1jekt;charset=utf8mb4';
$user = 'x9fu5b5l9sd09tzc';
$pass = 'gjfcoqyn4f4ooju0';

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die("Erreur de connexion MySQL : " . $e->getMessage());
}

// --- Comptes à créer ---
$users = [
    [
        'nom' => 'Admin',
        'prenom' => 'Demo',
        'email' => 'admin@demo.fr',
        'password' => 'Admin1234!',
        'role_id' => 1
    ],
    [
        'nom' => 'Employe',
        'prenom' => 'Demo',
        'email' => 'employe@demo.fr',
        'password' => 'Employe1234!',
        'role_id' => 2
    ],
    [
        'nom' => 'User',
        'prenom' => 'Demo',
        'email' => 'user@demo.fr',
        'password' => 'User12345!',
        'role_id' => 1
    ],
];

foreach ($users as $u) {
    $hash = password_hash($u['password'], PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("
        INSERT INTO utilisateur (nom, prenom, email, password, role_id, actif)
        VALUES (:nom, :prenom, :email, :password, :role_id, 1)
        ON DUPLICATE KEY UPDATE
            password = VALUES(password),
            actif = 1
    ");

    $stmt->execute([
        ':nom' => $u['nom'],
        ':prenom' => $u['prenom'],
        ':email' => $u['email'],
        ':password' => $hash,
        ':role_id' => $u['role_id'],
    ]);

    echo "Compte {$u['email']} créé ou mis à jour.\n";
}

echo "Tous les comptes ont été synchronisés avec Heroku.\n";