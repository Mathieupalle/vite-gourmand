<?php require TEMPLATES_PATH . '/partials/head.php'; ?>

<html lang="fr">
<body>

<?php require TEMPLATES_PATH . '/partials/header.php'; ?>

<div class="container my-5" style="max-width: 600px;">
    <div class="d-flex flex-column flex-md-row align-items-md-end justify-content-between gap-2">
        <h1 class="mb-3">Mon profil</h1>
        <a class="btn btn-sm btn-outline-secondary mb-3" href="<?= BASE_URL ?>/home">Retour Accueil</a>
    </div>
    <hr>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <?php foreach ($errors as $e): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($e); ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <form method="post">

        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input id="email" name="email" type="email" class="form-control" value="<?= htmlspecialchars($userDb['email'] ?? ''); ?>" disabled>
        </div>

        <div class="mb-3">
            <label for="nom" class="form-label">Nom</label>
            <input id="nom" name="nom" class="form-control" value="<?= htmlspecialchars($userDb['nom'] ?? ''); ?>" required>
        </div>

        <div class="mb-3">
            <label for="prenom" class="form-label">Prénom</label>
            <input id="prenom" name="prenom" class="form-control" value="<?= htmlspecialchars($userDb['prenom'] ?? ''); ?>">
        </div>

        <div class="mb-3">
            <label for="telephone" class="form-label">Téléphone</label>
            <input id="telephone" name="telephone" class="form-control" value="<?= htmlspecialchars($userDb['telephone'] ?? ''); ?>">
        </div>

        <div class="mb-3">
            <label for="ville" class="form-label">Ville</label>
            <input id="ville" name="ville" class="form-control" value="<?= htmlspecialchars($userDb['ville'] ?? ''); ?>">
        </div>

        <div class="mb-3">
            <label for="adresse_postale" class="form-label">Adresse postale</label>
            <input id="adresse_postale" name="adresse_postale" class="form-control" value="<?= htmlspecialchars($userDb['adresse_postale'] ?? ''); ?>">
        </div>

        <button type="submit" class="btn btn-primary w-100">Enregistrer</button>
    </form>

</div>

<?php require TEMPLATES_PATH . '/partials/footer.php'; ?>
<?php require TEMPLATES_PATH . '/partials/scripts.php'; ?>

</body>
</html>