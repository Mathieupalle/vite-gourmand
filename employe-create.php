<?php
// employe-create.php : création d’un compte employé (admin uniquement)

session_start();
require_once __DIR__ . '/src/db.php';
require_once __DIR__ . '/src/auth.php';

requireRole(['admin']);

$pdo = db();
$errors = [];
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email invalide.";
    }

    if (strlen($password) < 6) {
        $errors[] = "Mot de passe trop court.";
    }

    if (!$errors) {

        $stmt = $pdo->prepare("SELECT utilisateur_id FROM utilisateur WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $errors[] = "Email déjà utilisé.";
        } else {

            $stmt = $pdo->prepare("
                INSERT INTO utilisateur (email, password, role_id, actif)
                VALUES (?, ?, 2, 1)
            ");
            $stmt->execute([$email, $password]);

            $success = "Employé créé avec succès.";
        }
    }
}
// Envoi d’un mail d’information (sans mot de passe)
$to = $email;
$subject = "Création de votre compte employé - Vite & Gourmand";

$message = "Bonjour,\n\n"
        . "Un compte employé vient d’être créé pour vous.\n\n"
        . "Identifiant (email) : " . $email . "\n\n"
        . "Le mot de passe n’est pas communiqué par mail.\n"
        . "Merci de contacter l’administrateur pour l’obtenir.\n\n"
        . "Cordialement,\n"
        . "Vite & Gourmand";

$headers = "From: contact@vitegourmand.fr";
@mail($to, $subject, $message, $headers);
?>

<h1>Créer un compte employé</h1>
<?php foreach ($errors as $e): ?>
    <p style="color:red"><?php echo htmlspecialchars($e); ?></p>
<?php endforeach; ?>

<?php if ($success): ?>
    <p style="color:green"><?php echo htmlspecialchars($success); ?></p>
<?php endif; ?>

<form method="post">
    <input type="email" name="email" placeholder="Email" required><br><br>
    <input type="password" name="password" placeholder="Mot de passe" required><br><br>
    <button type="submit">Créer</button>
</form>
<p><a href="index.php">Accueil</a></p>
<p><a href="admin.php">← Retour à l’espace Gestion</a></p>
