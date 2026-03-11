<!doctype html>
<html lang="fr">

<?php require TEMPLATES_PATH . '/partials/head.php'; ?>

<body class="bg-white">

<?php require TEMPLATES_PATH . '/partials/header.php'; ?>

<h1>Créer un compte employé</h1>

<?php if (!empty($errors)): ?>
    <?php foreach ($errors as $e): ?>
        <p style="color:red"><?= htmlspecialchars($e) ?></p>
    <?php endforeach; ?>
<?php endif; ?>

<?php if (!empty($success)): ?>
    <p style="color:green"><?= htmlspecialchars($success) ?></p>
<?php endif; ?>

<form method="post">

    <input
            type="email"
            name="email"
            placeholder="Email"
            required
    >

    <br><br>

    <input
            type="password"
            name="password"
            placeholder="Mot de passe"
            required
    >

    <br><br>

    <button type="submit">
        Créer
    </button>

</form>

<p><a href="<?= BASE_URL ?>/home">Accueil</a></p>
<p><a href="<?= BASE_URL ?>/admin">Retour</a></p>

<?php require TEMPLATES_PATH . '/partials/footer.php'; ?>
<?php require TEMPLATES_PATH . '/partials/scripts.php'; ?>

</body>
</html>