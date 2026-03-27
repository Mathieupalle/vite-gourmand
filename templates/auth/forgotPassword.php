<?php require TEMPLATES_PATH . '/partials/head.php'; ?>

<html lang="fr">
<body class="bg-light">

<?php require TEMPLATES_PATH . '/partials/header.php'; ?>

<main class="container my-5" style="max-width: 500px;">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4 mb-0">Mot de passe oublié</h1>
        <a href="<?= BASE_URL ?>/login" class="btn btn-sm btn-outline-secondary">
            Retour
        </a>
    </div>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($success) ?>
        </div>

        <?php if (!empty($_SESSION['reset_link_demo'])): ?>
            <div class="alert alert-info">
                <strong>Démo :</strong><br>
                Lien de réinitialisation :
                <br>
                <a href="<?= htmlspecialchars((string)$_SESSION['reset_link_demo']) ?>">
                    <?= htmlspecialchars((string)$_SESSION['reset_link_demo']) ?>
                </a>
            </div>
            <?php unset($_SESSION['reset_link_demo']); ?>
        <?php endif; ?>
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
            <label for="email" class="form-label">Adresse email</label>
            <input type="email"
                   id="email"
                   name="email"
                   class="form-control"
                   value="<?= htmlspecialchars((string)($_POST['email'] ?? '')) ?>"
                   required>
        </div>

        <button type="submit" class="btn btn-primary w-100">
            Envoyer le lien de réinitialisation
        </button>

    </form>

</main>

<?php require TEMPLATES_PATH . '/partials/footer.php'; ?>
<?php require TEMPLATES_PATH . '/partials/scripts.php'; ?>

</body>
</html>