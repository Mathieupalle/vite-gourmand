<?php require TEMPLATES_PATH . '/partials/head.php'; ?>

<html lang="fr">
<body class="bg-white">

<?php require TEMPLATES_PATH . '/partials/header.php'; ?>

<main class="container my-5" style="max-width: 400px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Connexion</h1>
        <a class="btn btn-sm btn-outline-secondary" href="<?= BASE_URL ?>/home">Retour Accueil</a>
    </div>

    <?php if (!empty($errors)): ?>
        <?php foreach ($errors as $error): ?>
            <div class="alert alert-danger" role="alert">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <form method="post" action="<?= BASE_URL ?>/login">
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email"
                   id="email"
                   name="email"
                   class="form-control"
                   placeholder="Email"
                   required>
        </div>

        <div class="mb-4">
            <label for="password" class="form-label">Mot de passe</label>
            <input type="password"
                   id="password"
                   name="password"
                   class="form-control"
                   placeholder="Mot de passe"
                   required>
        </div>

        <button type="submit" class="btn btn-primary mb-3 w-100">Se connecter</button>
    </form>

    <div>
        <a class="btn btn-sm btn-outline-secondary mb-4" href="<?= BASE_URL ?>/forgotPassword">Mot de passe oublié ?</a>
    </div>

</main>

<?php require TEMPLATES_PATH . '/partials/footer.php'; ?>
<?php require TEMPLATES_PATH . '/partials/scripts.php'; ?>

</body>
</html>