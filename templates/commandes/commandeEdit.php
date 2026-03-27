<?php require TEMPLATES_PATH . '/partials/head.php'; ?>

<html lang="fr">
<body class="bg-light">

<?php require TEMPLATES_PATH . '/partials/header.php'; ?>

<main class="container my-4">
    <div class="d-flex flex-column flex-md-row align-items-md-end justify-content-between gap-2">
        <h1 class="h3 mb-3">Modifier ma commande</h1>
        <a href="<?= BASE_URL ?>/mesCommandes" class="btn btn-sm btn-outline-secondary mb-3">Retour</a>
    </div>

    <?php if (!empty($errors)): ?>
        <?php foreach ($errors as $e): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($e) ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <form method="post" class="card shadow-sm">
        <div class="card-body">

            <div class="mb-3">
                <strong>Menu :</strong>
                <?= htmlspecialchars((string)($c['menu_titre'] ?? '')) ?>
            </div>

            <h5 class="mb-3">Prestation</h5>

            <div class="mb-3">
                <label for="date_prestation" class="form-label">Date prestation</label>
                <input type="date"
                       name="date_prestation"
                       id="date_prestation"
                       class="form-control"
                       value="<?= htmlspecialchars((string)($c['date_prestation'] ?? '')) ?>"
                       required>
            </div>

            <div class="mb-3">
                <label for="adresse_prestation" class="form-label">Adresse prestation</label>
                <input type="text"
                       name="adresse_prestation"
                       id="adresse_prestation"
                       class="form-control"
                       value="<?= htmlspecialchars((string)($c['adresse_prestation'] ?? '')) ?>"
                       required>
            </div>

            <div class="mb-3">
                <label for="ville_prestation" class="form-label">Ville prestation</label>
                <input type="text"
                       name="ville_prestation"
                       id="ville_prestation"
                       class="form-control"
                       value="<?= htmlspecialchars((string)($c['ville_prestation'] ?? '')) ?>"
                       required>
            </div>

            <hr>

            <h5 class="mb-3">Livraison</h5>

            <div class="mb-3">
                <label for="date_livraison" class="form-label">Date livraison</label>
                <input type="date"
                       name="date_livraison"
                       id="date_livraison"
                       class="form-control"
                       value="<?= htmlspecialchars((string)($c['date_livraison'] ?? '')) ?>"
                       required>
            </div>

            <div class="mb-3">
                <label for="heure_livraison" class="form-label">Heure livraison</label>
                <input type="time"
                       name="heure_livraison"
                       id="heure_livraison"
                       class="form-control"
                       value="<?= htmlspecialchars((string)($c['heure_livraison'] ?? '')) ?>"
                       required>
            </div>

            <div class="mb-3">
                <label for="adresse_livraison" class="form-label">Adresse livraison</label>
                <input type="text"
                       name="adresse_livraison"
                       id="adresse_livraison"
                       class="form-control"
                       value="<?= htmlspecialchars((string)($c['adresse_livraison'] ?? '')) ?>"
                       required>
            </div>

            <div class="mb-3">
                <label for="ville_livraison" class="form-label">Ville livraison</label>
                <input type="text"
                       name="ville_livraison"
                       id="ville_livraison"
                       class="form-control"
                       value="<?= htmlspecialchars((string)($c['ville_livraison'] ?? '')) ?>"
                       required>
            </div>

            <div class="mb-3">
                <label for="distance_km" class="form-label">Distance (km)</label>
                <input type="number"
                       step="0.1"
                       min="0"
                       name="distance_km"
                       id="distance_km"
                       class="form-control"
                       value="<?= htmlspecialchars((string)($c['distance_km'] ?? 0)) ?>">
            </div>

            <hr>

            <h5 class="mb-3">Participants</h5>

            <div class="mb-3">
                <label for="nombre_personne" class="form-label">Nombre de personnes</label>
                <input type="number"
                       name="nombre_personne"
                       id="nombre_personne"
                       class="form-control"
                       value="<?= (int)($c['nombre_personne'] ?? 0) ?>"
                       min="<?= (int)($c['nombre_personne_minimum'] ?? 1) ?>"
                       required>
            </div>

        </div>

        <div class="card-footer text-end">
            <button type="submit" class="btn btn-primary">
                Enregistrer
            </button>
        </div>
    </form>

</main>

<?php require TEMPLATES_PATH . '/partials/footer.php'; ?>
<?php require TEMPLATES_PATH . '/partials/scripts.php'; ?>

</body>
</html>