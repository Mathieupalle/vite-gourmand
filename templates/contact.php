<?php require TEMPLATES_PATH . '/partials/head.php'; ?>

<html lang="fr">
<body>

<?php require TEMPLATES_PATH . '/partials/header.php'; ?>

<div class="container my-5">
    <div class="d-flex flex-column flex-md-row align-items-md-end justify-content-between gap-2">
        <h1 class="mb-3">Contact</h1>
        <a class="btn btn-sm btn-outline-secondary mb-3" href="<?= BASE_URL ?>/home">Retour Accueil</a>
    </div>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <?php foreach ($errors as $e): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($e); ?></div>
        <?php endforeach; ?>
    <?php endif; ?>

    <form method="post">

        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email"
                   id="email"
                   name="email"
                   class="form-control"
                   value="<?= htmlspecialchars($_POST['email'] ?? ''); ?>"
                   required>
        </div>

        <div class="mb-3">
            <label for="titre" class="form-label">Titre</label>
            <input type="text"
                   id="titre"
                   name="titre"
                   class="form-control"
                   value="<?= htmlspecialchars($_POST['titre'] ?? ''); ?>"
                   required>
        </div>

        <div class="mb-3">
            <label for="message" class="form-label">Message</label>
            <textarea id="message"
                      name="message"
                      class="form-control"
                      rows="6"
                      required><?= htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Envoyer</button>
    </form>

</div>

<?php require TEMPLATES_PATH . '/partials/footer.php'; ?>
<?php require TEMPLATES_PATH . '/partials/scripts.php'; ?>

</body>
</html>