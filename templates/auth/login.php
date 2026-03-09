<!doctype html>
<html lang="fr">
<?php require TEMPLATES_PATH . '/partials/head.php'; ?>
<body>
<?php require TEMPLATES_PATH . '/partials/header.php'; ?>

<h1>Connexion</h1>

<?php if (!empty($errors)): ?>
    <?php foreach ($errors as $error): ?>
        <p style="color:red"><?= htmlspecialchars($error) ?></p>
    <?php endforeach; ?>
<?php endif; ?>

<form method="post">
    <input type="email" name="email" placeholder="Email" required><br><br>
    <input type="password" name="password" placeholder="Mot de passe" required><br><br>
    <button type="submit">Se connecter</button>
</form>

<p><a href="<?= BASE_URL ?>/forgotPassword">Mot de passe oublié ?</a></p>
<p><a href="<?= BASE_URL ?>/home">← Retour accueil</a></p>

<?php require TEMPLATES_PATH . '/partials/footer.php'; ?>
<?php require TEMPLATES_PATH . '/partials/scripts.php'; ?>
</body>
</html>