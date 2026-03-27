<?php require TEMPLATES_PATH . '/partials/head.php'; ?>

<html lang="fr">
<body class="bg-light">

<?php require TEMPLATES_PATH . '/partials/header.php'; ?>

<main class="container my-4">
    <div class="d-flex flex-column flex-md-row align-items-md-end justify-content-between gap-2">
        <h1 class="h3 mb-3">Laisser un avis</h1>

        <div class="mb-3">
            <a href="<?= BASE_URL ?>/home" class="btn btn-sm btn-outline-secondary">Accueil</a>
            <a href="<?= BASE_URL ?>/mesCommandes" class="btn btn-sm btn-outline-secondary">Retour</a>
        </div>
    </div>

    <div class="mb-3">
        <strong>Commande :</strong>
        <?= htmlspecialchars((string)($commande['menu_titre'] ?? '')) ?>
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

    <?php if (empty($success)): ?>
        <form method="post" class="card shadow-sm">
            <div class="card-body">

                <div class="mb-3">
                    <label for="note" class="form-label">Note (1 à 5)</label>
                    <select name="note" id="note" class="form-select" required>
                        <option value="">-- choisir --</option>

                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <option value="<?= $i ?>">
                                <?= $i ?>
                            </option>
                        <?php endfor; ?>

                    </select>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Commentaire</label>
                    <textarea name="description"
                              id="description"
                              class="form-control"
                              rows="4"
                              required></textarea>
                </div>

            </div>

            <div class="card-footer text-end">
                <button type="submit" class="btn btn-primary">
                    Envoyer l'avis
                </button>
            </div>
        </form>
    <?php endif; ?>

</main>

<?php require TEMPLATES_PATH . '/partials/footer.php'; ?>
<?php require TEMPLATES_PATH . '/partials/scripts.php'; ?>

</body>
</html>