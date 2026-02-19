<?php
// forgot-password.php : demande de réinitialisation de mot de passe
// L'utilisateur saisit son email
// Si email existe : génération d'un token + expiration et envoi d'un lien de reset

session_start();
require_once __DIR__ . '/src/db.php';

$pdo = db();
$success = null;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email invalide.";
    }

    if (!$errors) {
        // Vérifier que l'utilisateur existe
        $stmt = $pdo->prepare("SELECT utilisateur_id FROM utilisateur WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // Pour éviter de révéler si l'email existe ou non :
        // Affichage d'un message de confirmation d'envoi
        $success = "Si un compte existe avec cet email, un lien de réinitialisation a été envoyé.";

        if ($user) {
            $userId = (int)$user['utilisateur_id'];

            // Génération d'un token sécurisé
            $token = bin2hex(random_bytes(32)); // 64 caractères hex
            $expires = date('Y-m-d H:i:s', time() + 3600); // 1h

            $upd = $pdo->prepare("UPDATE utilisateur SET reset_token = ?, reset_expires = ? WHERE utilisateur_id = ?");
            $upd->execute([$token, $expires, $userId]);

            // Lien de reset (local)
            require_once __DIR__ . '/src/helpers.php';
            $resetLink = base_url() . "/reset-password.php?token=" . urlencode($token);

            // Option 1 : mail
            // @mail($email, "Réinitialisation de votre mot de passe", "Cliquez sur ce lien : $resetLink");

            // Option 2 : simulation
            $_SESSION['reset_link_demo'] = $resetLink;
        }
    }
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Mot de passe oublié</title>
</head>
<body>

<h1>Mot de passe oublié</h1>
<p><a href="login.php">← Retour connexion</a></p>

<?php if ($success): ?>
    <p style="color:green"><?php echo htmlspecialchars($success); ?></p>

    <?php if (!empty($_SESSION['reset_link_demo'])): ?>
        <p><strong>Démo :</strong> lien de réinitialisation (simulation) :</p>
        <p><a href="<?php echo htmlspecialchars($_SESSION['reset_link_demo']); ?>">
                <?php echo htmlspecialchars($_SESSION['reset_link_demo']); ?>
            </a></p>
        <?php unset($_SESSION['reset_link_demo']); ?>
    <?php endif; ?>

<?php endif; ?>

<?php foreach ($errors as $e): ?>
    <p style="color:red"><?php echo htmlspecialchars($e); ?></p>
<?php endforeach; ?>

<form method="post">
    <label>Email :</label><br>
    <input type="email" name="email" required><br><br>
    <button type="submit">Envoyer le lien</button>
</form>

</body>
</html>
