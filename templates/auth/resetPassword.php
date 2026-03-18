<!doctype html>
<html lang="fr">
<?php require TEMPLATES_PATH . '/partials/head.php'; ?>
<body>
<?php require TEMPLATES_PATH . '/partials/header.php'; ?>

<h1>Réinitialiser mon mot de passe</h1>

<?php if (!empty($success)): ?>
    <p style="color:green"><?= htmlspecialchars($success) ?></p>
    <p><a href="<?= BASE_URL ?>/login">Aller à la connexion</a></p>
<?php else: ?>

    <?php if (!empty($errors)): ?>
        <?php foreach ($errors as $e): ?>
            <p style="color:red"><?= htmlspecialchars($e) ?></p>
        <?php endforeach; ?>
    <?php endif; ?>

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

<?php require TEMPLATES_PATH . '/partials/footer.php'; ?>
<?php require TEMPLATES_PATH . '/partials/scripts.php'; ?>
</body>
</html>