<?php require TEMPLATES_PATH . '/partials/head.php'; ?>

<html lang="fr">
<body class="bg-light">

<?php require TEMPLATES_PATH . '/partials/header.php'; ?>

<main class="container my-4">
    <div class="d-flex flex-column flex-md-row align-items-md-end justify-content-between gap-2">
        <h1 class="h3 mb-3">Modifier le menu</h1>
        <a href="<?= BASE_URL ?>/menuManage" class="btn btn-sm btn-outline-secondary mb-3">Retour</a>
    </div>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <?php foreach ($errors as $e): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($e) ?></div>
        <?php endforeach; ?>
    <?php endif; ?>

    <form method="post">

        <div class="mb-3">
            <label for="titre" class="form-label">Titre</label>
            <input type="text" name="titre" id="titre" class="form-control"
                   value="<?= htmlspecialchars((string)($menu['titre'] ?? '')) ?>" required>
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <input type="text" name="description" id="description" class="form-control"
                   value="<?= htmlspecialchars((string)($menu['description'] ?? '')) ?>">
        </div>

        <div class="mb-3">
            <label for="prix_par_personne" class="form-label">Prix par personne (€)</label>
            <input type="number" step="0.01" name="prix_par_personne" id="prix_par_personne" class="form-control"
                   value="<?= htmlspecialchars((string)($menu['prix_par_personne'] ?? '')) ?>" required>
        </div>

        <div class="mb-3">
            <label for="nombre_personne_minimum" class="form-label">Nombre minimum de personnes</label>
            <input type="number" name="nombre_personne_minimum" id="nombre_personne_minimum" class="form-control"
                   value="<?= (int)($menu['nombre_personne_minimum'] ?? 0) ?>" required>
        </div>

        <div class="mb-3">
            <label for="quantite_restante" class="form-label">Quantité restante</label>
            <input type="number" name="quantite_restante" id="quantite_restante" class="form-control"
                   value="<?= htmlspecialchars((string)($menu['quantite_restante'] ?? '')) ?>">
        </div>

        <div class="mb-3">
            <label for="theme_id" class="form-label">Thème</label>
            <select name="theme_id" id="theme_id" class="form-select" required>
                <?php if (!empty($themes)): ?>
                    <?php foreach ($themes as $t): ?>
                        <option value="<?= (int)$t['theme_id'] ?>"
                                <?= ((int)($menu['theme_id'] ?? 0) === (int)$t['theme_id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars((string)$t['libelle']) ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="regime_id" class="form-label">Régime</label>
            <select name="regime_id" id="regime_id" class="form-select" required>
                <?php if (!empty($regimes)): ?>
                    <?php foreach ($regimes as $r): ?>
                        <option value="<?= (int)$r['regime_id'] ?>"
                                <?= ((int)($menu['regime_id'] ?? 0) === (int)$r['regime_id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars((string)$r['libelle']) ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>

        <button type="submit" class="btn btn-primary mb-3">Enregistrer</button>

    </form>

</main>

<?php require TEMPLATES_PATH . '/partials/footer.php'; ?>
<?php require TEMPLATES_PATH . '/partials/scripts.php'; ?>

</body>
</html>