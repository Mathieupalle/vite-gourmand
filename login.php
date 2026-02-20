<?php
// login.php : connexion utilisateur

session_start();
require_once __DIR__ . '/src/db.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1) Champs
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // 2) Vérifications
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email invalide.";
    }

    if ($password === '') {
        $errors[] = "Mot de passe obligatoire.";
    }

    // 3) Vérifications en base de données
    if (empty($errors)) {
        $pdo = db();

        // Compte actif uniquement
        $stmt = $pdo->prepare("
            SELECT u.utilisateur_id, u.email, u.password, u.role_id, u.actif, r.libelle AS role
            FROM utilisateur u
            JOIN role r ON r.role_id = u.role_id
            WHERE u.email = ? AND u.actif = 1
            LIMIT 1
        ");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            $errors[] = "Email ou mot de passe incorrect.";
        } else {

            // Comparaison
            if ($password !== $user['password']) {
                $errors[] = "Email ou mot de passe incorrect.";
            } else {

                $_SESSION['user'] = [
                        'id' => $user['utilisateur_id'],
                        'email' => $user['email'],
                        'role' => $user['role']
                ];

                header("Location: index.php");
                exit;
            }
        }
    }
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Connexion</title>
</head>
<body>

<h1>Connexion</h1>
<?php foreach ($errors as $error): ?>
    <p style="color:red"><?php echo htmlspecialchars($error); ?></p>
<?php endforeach; ?>

<form method="post">
    <input type="email" name="email" placeholder="Email" required><br><br>
    <input type="password" name="password" placeholder="Mot de passe" required><br><br>
    <button type="submit">Se connecter</button>
</form>

<p>
    <a href="forgot-password.php">Mot de passe oublié ?</a>
</p>

<p>
    <a href="index.php">← Retour accueil</a>
</p>

</body>
</html>
