<!doctype html>
<html lang="fr">
<?php require TEMPLATES_PATH . '/partials/head.php'; ?>

<body>
<?php require TEMPLATES_PATH . '/partials/header.php'; ?>

<h1>Mon profil</h1>
<p><a href="<?= BASE_URL ?>/home">← Retour accueil</a></p>
<hr>

<?php if (!empty($success)): ?>
    <p style="color:green"><?= htmlspecialchars($success) ?></p>
<?php endif; ?>

<?php foreach ($errors as $e): ?>
    <p style="color:red"><?= htmlspecialchars($e) ?></p>
<?php endforeach; ?>

<form method="post">
    <label>Email :</label><br>
    <input type="email" value="<?= htmlspecialchars((string)$userDb['email']) ?>" disabled>
    <br><br>

    <label>Nom :</label><br>
    <input name="nom" value="<?= htmlspecialchars((string)$userDb['nom']) ?>" required>
    <br><br>

    <label>Prénom :</label><br>
    <input name="prenom" value="<?= htmlspecialchars((string)($userDb['prenom'] ?? '')) ?>">
    <br><br>

    <label>Téléphone :</label><br>
    <input name="telephone" value="<?= htmlspecialchars((string)($userDb['telephone'] ?? '')) ?>">
    <br><br>

    <label>Ville :</label><br>
    <input name="ville" value="<?= htmlspecialchars((string)($userDb['ville'] ?? '')) ?>">
    <br><br>

    <label>Adresse postale :</label><br>
    <input name="adresse_postale" value="<?= htmlspecialchars((string)($userDb['adresse_postale'] ?? '')) ?>">
    <br><br>

    <button type="submit">Enregistrer</button>
</form>
<?php require TEMPLATES_PATH . '/partials/footer.php'; ?>

<?php require TEMPLATES_PATH . '/partials/scripts.php'; ?>
</body>
</html>