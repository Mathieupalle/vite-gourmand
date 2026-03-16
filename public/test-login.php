<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
use App\Infrastructure\Database;

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

$testEmail = 'user@demo.fr';       // <-- change ici pour tester un autre compte
$testPassword = 'User12345!';      // <-- mot de passe que tu veux tester

try {
    $pdo = Database::getConnection();

    $stmt = $pdo->prepare("SELECT email, password FROM utilisateur WHERE email = ? LIMIT 1");
    $stmt->execute([$testEmail]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo "Utilisateur introuvable pour l'email : {$testEmail}";
        exit;
    }

    echo "Email trouvé : " . $user['email'] . "<br>";

    $storedHash = $user['password'];
    echo "Hash stocké : " . $storedHash . "<br>";

    if (password_verify($testPassword, $storedHash)) {
        echo "<strong>Mot de passe correct !</strong>";
    } else {
        echo "<strong>Mot de passe incorrect !</strong>";
    }

} catch (Throwable $e) {
    echo "Erreur : " . $e->getMessage();
}