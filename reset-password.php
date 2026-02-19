<?php
// reset-password.php : page de réinitialisation
// - Vérifie le token + expiration
// - Demande un nouveau mot de passe
// - Met à jour utilisateur.password + supprime token

session_start();
require_once __DIR__ . '/src/db.php';

$pdo = db();
$errors = [];
$success = null;

// Vérif mot de passe (10 + maj/min/chiffre/spécial)
function isStrongPassword(string $password): bool
{
    if (strlen($password) < 10) return false;
    if (!preg_match('/[A-Z]/', $password)) return false;
    if (!preg_match('/[a-z]/', $password)) return false;
    if (!preg_match('/[0-9]/', $password)) return false;
    if (!preg_match('/[^A-Za-z0-9]/', $password)) return false;
    return true;
}

$token = trim($_GET['token'] ?? '');
if ($token === '') {
    http_response_code(400);
    die("Token manquant.");
}

// 1) Vérifier token + expiration
$stmt = $pdo->prepare("
    SELECT utilisateur_id, reset_expires
    FROM utilisateur
    WHERE reset_token = ?
    LIMIT 1
");
$stmt->execute([$token]);
$row = $stmt->fetch();

if (!$row) {
    die("Lien invalide ou déjà utilisé.");
}

$expires = $row['reset_expires'];
if (!$expires || strtotime($expires) < time()) {
    die("Lien expiré. Merci de refaire une demande.");
}

$userId = (int)$row['utilisateur_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pass1 = $_POST['password'] ?? '';
    $pass2 = $_POST['password_confirm'] ?? '';

    if ($pass1 === '' || $pass2 === '') {
        $errors[] = "Veuillez remplir les deux champs.";
    } elseif ($pass1 !== $pass2) {
        $errors[] = "Les mots de passe ne correspondent pas.";
    } elseif (!isStrongPassword($pass1)) {
        $errors[] = "Mot de passe trop faible : 10 caractères minimum avec majuscule, minuscule, chiffre et caractère spécial.";
    }

    if (!$errors) {
        // 2) Mettre à jour le mot de passe + supprimer le token
        $upd = $pdo->prepare("
            UPDATE utilisateur
            SET password = ?, reset_token = NULL, reset_expires = NULL
            WHERE utilisateur_id = ?
        ");
        $upd->execute([$pass1, $userId]);

        $success = "Mot de passe mis à jour. Vous pouvez vous connecter.";
    }
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Réinitialisation mot de passe</title>
</head>
<body>

<h1>Réinitialiser mon mot de passe</h1>

<?php if ($success): ?>
    <p style="color:green"><?php echo htmlspecialchars($success); ?></p>
    <p><a href="login.php">Aller à la connexion</a></p>
<?php else: ?>

    <?php foreach ($errors as $e): ?>
        <p style="color:red"><?php echo htmlspecialchars($e); ?></p>
    <?php endforeach; ?>

    <form method="post">
        <label>Nouveau mot de passe :</label><br>
        <input type="password" name="password" required><br>
        <small>10 caractères min : 1 majuscule, 1 minuscule, 1 chiffre, 1 caractère spécial.</small>
        <br><br>

        <label>Confirmer :</label><br>
        <input type="password" name="password_confirm" required><br><br>

        <button type="submit">Mettre à jour</button>
    </form>

<?php endif; ?>

</body>
</html>
