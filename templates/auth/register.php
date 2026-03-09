<!doctype html>
<html lang="fr">
<?php require TEMPLATES_PATH . '/partials/head.php'; ?>
<body>
<?php require TEMPLATES_PATH . '/partials/header.php'; ?>

<h1>Créer un compte</h1>

<?php if (!empty($success)): ?>
    <p style="color:green"><?= htmlspecialchars($success) ?></p>
<?php endif; ?>

<?php foreach ($errors as $e): ?>
    <p style="color:red"><?= htmlspecialchars($e) ?></p>
<?php endforeach; ?>

<form method="post">
    <label>Nom :</label><br>
    <input type="text" name="nom" value="<?= htmlspecialchars((string)($_POST['nom'] ?? '')) ?>" required><br><br>

    <label>Prénom :</label><br>
    <input type="text" name="prenom" value="<?= htmlspecialchars((string)($_POST['prenom'] ?? '')) ?>" required><br><br>

    <label>Téléphone :</label><br>
    <input type="text" name="telephone" value="<?= htmlspecialchars((string)($_POST['telephone'] ?? '')) ?>" required><br><br>

    <label>Email :</label><br>
    <input type="email" name="email" value="<?= htmlspecialchars((string)($_POST['email'] ?? '')) ?>" required><br><br>

    <label>Adresse postale :</label><br>
    <input type="text" name="adresse_postale" value="<?= htmlspecialchars((string)($_POST['adresse_postale'] ?? '')) ?>" required><br><br>

    <label>Mot de passe sécurisé :</label><br>
    <input type="password" name="password" required><br>
    <small>(10 caractères minimum : 1 majuscule, 1 minuscule, 1 chiffre, 1 caractère spécial).</small>
    <br><br>

    <button type="submit">Créer un compte</button>
</form>

<p><a href="<?= BASE_URL ?>/home">← Retour accueil</a></p>

<?php require TEMPLATES_PATH . '/partials/footer.php'; ?>
<?php require TEMPLATES_PATH . '/partials/scripts.php'; ?>
</body>
</html>