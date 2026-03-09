<!doctype html>
<html lang="fr">
<?php require TEMPLATES_PATH . '/partials/head.php'; ?>
<body>
<?php require TEMPLATES_PATH . '/partials/header.php'; ?>

<h1>Mot de passe oublié</h1>
<p><a href="<?= BASE_URL ?>/login">← Retour connexion</a></p>

<?php if (!empty($success)): ?>
    <p style="color:green"><?= htmlspecialchars($success) ?></p>

    <?php if (!empty($_SESSION['reset_link_demo'])): ?>
        <p><strong>Démo :</strong> lien de réinitialisation (simulation) :</p>
        <p><a href="<?= htmlspecialchars((string)$_SESSION['reset_link_demo']) ?>">
                <?= htmlspecialchars((string)$_SESSION['reset_link_demo']) ?>
            </a></p>
        <?php unset($_SESSION['reset_link_demo']); ?>
    <?php endif; ?>
<?php endif; ?>

<?php if (!empty($errors)): ?>
    <?php foreach ($errors as $e): ?>
        <p style="color:red"><?= htmlspecialchars($e) ?></p>
    <?php endforeach; ?>
<?php endif; ?>

<form method="post">
    <label>Email :</label><br>
    <input type="email" name="email" value="<?= htmlspecialchars((string)($_POST['email'] ?? '')) ?>" required><br><br>
    <button type="submit">Envoyer le lien</button>
</form>

<?php require TEMPLATES_PATH . '/partials/footer.php'; ?>
<?php require TEMPLATES_PATH . '/partials/scripts.php'; ?>
</body>
</html>