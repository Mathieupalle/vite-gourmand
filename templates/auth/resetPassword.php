<?php require TEMPLATES_PATH . '/partials/head.php'; ?>

<html lang="fr">
<body class="bg-light">

<?php require TEMPLATES_PATH . '/partials/header.php'; ?>

<main class="container my-5" style="max-width: 500px;">

    <h1 class="h4 mb-4">Réinitialiser mon mot de passe</h1>

    <?php if (!empty($success)): ?>

        <div class="alert alert-success">
            <?= htmlspecialchars($success) ?>
        </div>

        <a href="<?= BASE_URL ?>/login" class="btn btn-primary w-100">
            Aller à la connexion
        </a>

    <?php else: ?>

        <?php if (!empty($errors)): ?>
            <?php foreach ($errors as $e): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($e) ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <form method="post">

            <div class="mb-3">
                <label for="password" class="form-label">Nouveau mot de passe</label>
                <input type="password"
                       id="password"
                       name="password"
                       class="form-control"
                       required>

                <div class="form-text">
                    10 caractères min : 1 majuscule, 1 minuscule, 1 chiffre, 1 caractère spécial.
                </div>
            </div>

            <div class="mb-4">
                <label for="password_confirm" class="form-label">Confirmer le mot de passe</label>
                <input type="password"
                       id="password_confirm"
                       name="password_confirm"
                       class="form-control"
                       required>
            </div>

            <button type="submit" class="btn btn-primary w-100">
                Mettre à jour le mot de passe
            </button>

        </form>

    <?php endif; ?>

</main>

<?php require TEMPLATES_PATH . '/partials/footer.php'; ?>
<?php require TEMPLATES_PATH . '/partials/scripts.php'; ?>

</body>
</html>