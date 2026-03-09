<!doctype html>
<html lang="fr">
<?php require TEMPLATES_PATH . '/partials/head.php'; ?>

<body>
<?php require TEMPLATES_PATH . '/partials/header.php'; ?>

<h1>Contact</h1>
<p><a href="<?= BASE_URL ?>/home">← Retour accueil</a></p>

<?php if ($success): ?>
    <p style="color:green"><?= htmlspecialchars($success) ?></p>
<?php endif; ?>

<?php foreach ($errors as $e): ?>
    <p style="color:red"><?= htmlspecialchars($e) ?></p>
<?php endforeach; ?>

<form method="post">
    <label>Email</label><br>
    <input type="email" name="email" value="<?= htmlspecialchars((string)($_POST['email'] ?? '')) ?>" required><br><br>

    <label>Titre</label><br>
    <input type="text" name="titre" value="<?= htmlspecialchars((string)($_POST['titre'] ?? '')) ?>" required><br><br>

    <label>Message</label><br>
    <textarea name="message" rows="6" cols="50" required><?= htmlspecialchars((string)($_POST['message'] ?? '')) ?></textarea><br><br>

    <button type="submit">Envoyer</button>
</form>
<?php require TEMPLATES_PATH . '/partials/footer.php'; ?>

<?php require TEMPLATES_PATH . '/partials/scripts.php'; ?>
</body>
</html>