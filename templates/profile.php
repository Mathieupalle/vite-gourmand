<!doctype html>
<html lang="fr">
<?php require TEMPLATES_PATH . '/partials/head.php'; ?>

<body>
<?php require TEMPLATES_PATH . '/partials/header.php'; ?>

<h1>Mon profil</h1>
<p><a href="<?= BASE_URL ?>/home">Retour accueil</a></p>
<hr>

<!-- Messages -->
<?php if (!empty($success)): ?>
    <p style="color:green"><?= htmlspecialchars($success) ?></p>
<?php endif; ?>

<?php if (!empty($errors)): ?>
    <?php foreach ($errors as $e): ?>
        <p style="color:red"><?= htmlspecialchars($e) ?></p>
    <?php endforeach; ?>
<?php endif; ?>

<form method="post">
    <label>Email :</label><br>
    <input type="email" value="<?= htmlspecialchars($userDb['email'] ?? '') ?>" disabled>
    <br><br>

    <label>Nom :</label><br>
    <input name="nom" value="<?= htmlspecialchars($userDb['nom'] ?? '') ?>" required>
    <br><br>

    <label>Prénom :</label><br>
    <input name="prenom" value="<?= htmlspecialchars($userDb['prenom'] ?? '') ?>">
    <br><br>

    <label>Téléphone :</label><br>
    <input name="telephone" value="<?= htmlspecialchars($userDb['telephone'] ?? '') ?>">
    <br><br>

    <label>Ville :</label><br>
    <input name="ville" value="<?= htmlspecialchars($userDb['ville'] ?? '') ?>">
    <br><br>

    <label>Adresse postale :</label><br>
    <input name="adresse_postale" value="<?= htmlspecialchars($userDb['adresse_postale'] ?? '') ?>">
    <br><br>

    <button type="submit">Enregistrer</button>
</form>

<?php require TEMPLATES_PATH . '/partials/footer.php'; ?>
<?php require TEMPLATES_PATH . '/partials/scripts.php'; ?>
</body>
</html>