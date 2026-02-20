<?php
// contact.php : page contact
session_start();

$success = null;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $titre = trim($_POST['titre'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Email invalide.";
    if ($titre === '') $errors[] = "Titre obligatoire.";
    if ($message === '') $errors[] = "Message obligatoire.";

    if (!$errors) {
        // Simulation de l’envoi
        $success = "Votre message a bien été envoyé. Nous vous répondrons au plus vite.";
    }
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Contact - Vite & Gourmand</title>
</head>
<body>

<h1>Contact</h1>
<p><a href="index.php">← Retour accueil</a></p>
<hr>
<?php if ($success): ?>
    <p style="color:green"><?php echo htmlspecialchars($success); ?></p>
<?php endif; ?>

<?php foreach ($errors as $e): ?>
    <p style="color:red"><?php echo htmlspecialchars($e); ?></p>
<?php endforeach; ?>

<form method="post">
    <label>Email :</label><br>
    <input type="email" name="email" required><br><br>

    <label>Titre :</label><br>
    <input type="text" name="titre" required><br><br>

    <label>Description :</label><br>
    <textarea name="message" rows="6" required></textarea><br><br>

    <button type="submit">Envoyer</button>
</form>

</body>
</html>
