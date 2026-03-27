<?php require TEMPLATES_PATH . '/partials/head.php'; ?>

<html lang="fr">
<body class="bg-light">

<?php require TEMPLATES_PATH . '/partials/header.php'; ?>

<main class="container my-5" style="max-width: 500px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Créer un compte</h1>
        <a class="btn btn-sm btn-outline-secondary" href="<?= BASE_URL ?>/home">Retour accueil</a>
    </div>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <?php foreach ($errors as $e): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($e) ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <form method="post">

        <div class="mb-3">
            <label for="nom" class="form-label">Nom</label>
            <input type="text"
                   id="nom"
                   name="nom"
                   class="form-control"
                   value="<?= htmlspecialchars((string)($_POST['nom'] ?? '')) ?>"
                   required>
        </div>

        <div class="mb-3">
            <label for="prenom" class="form-label">Prénom</label>
            <input type="text"
                   id="prenom"
                   name="prenom"
                   class="form-control"
                   value="<?= htmlspecialchars((string)($_POST['prenom'] ?? '')) ?>"
                   required>
        </div>

        <div class="mb-3">
            <label for="telephone" class="form-label">Téléphone</label>
            <input type="text"
                   id="telephone"
                   name="telephone"
                   class="form-control"
                   value="<?= htmlspecialchars((string)($_POST['telephone'] ?? '')) ?>"
                   required>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email"
                   id="email"
                   name="email"
                   class="form-control"
                   value="<?= htmlspecialchars((string)($_POST['email'] ?? '')) ?>"
                   required>
        </div>

        <div class="mb-3">
            <label for="adresse_postale" class="form-label">Adresse postale</label>
            <input type="text"
                   id="adresse_postale"
                   name="adresse_postale"
                   class="form-control"
                   value="<?= htmlspecialchars((string)($_POST['adresse_postale'] ?? '')) ?>"
                   required>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Mot de passe sécurisé</label>
            <input type="password"
                   id="password"
                   name="password"
                   class="form-control"
                   required>
            <div class="form-text">
                10 caractères minimum : 1 majuscule, 1 minuscule, 1 chiffre, 1 caractère spécial.
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100">
            Créer un compte
        </button>

    </form>

</main>

<?php require TEMPLATES_PATH . '/partials/footer.php'; ?>
<?php require TEMPLATES_PATH . '/partials/scripts.php'; ?>

</body>
</html>