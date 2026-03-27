<?php require TEMPLATES_PATH . '/partials/head.php'; ?>

<html lang="fr">
<body class="bg-light">

<?php require TEMPLATES_PATH . '/partials/header.php'; ?>

<main class="container my-4">
    <div class="d-flex flex-column flex-md-row align-items-md-end justify-content-between gap-2">
        <h1 class="mb-3">Nos menus</h1>
        <a class="btn btn-sm btn-outline-secondary mb-3" href="<?= BASE_URL ?>/home">Retour Accueil</a>
    </div>

    <div class="row g-4">

        <aside class="col-12 col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h2 class="h6 mb-0">Filtres</h2>
                </div>
                <div class="card-body">
                    <form id="filtersForm">

                        <div class="mb-3">
                            <label for="prix_max" class="form-label">Prix maximum</label>
                            <input type="number" step="0.01" name="prix_max" id="prix_max" class="form-control" placeholder="Ex: 30">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Fourchette de prix</label>
                            <div class="row g-2">
                                <div class="col">
                                    <label for="prix_min" class="visually-hidden">Prix min</label>
                                    <input type="number" step="0.01" name="prix_min" id="prix_min" class="form-control" placeholder="Min">
                                </div>
                                <div class="col">
                                    <label for="prix_max_range" class="visually-hidden">Prix max</label>
                                    <input type="number" step="0.01" name="prix_max_range" id="prix_max_range" class="form-control" placeholder="Max">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="theme_id" class="form-label">Thème</label>
                            <select name="theme_id" id="theme_id" class="form-select">
                                <option value="">-- Tous --</option>
                                <?php if (!empty($themes)): ?>
                                    <?php foreach ($themes as $t): ?>
                                        <option value="<?= (int)$t['theme_id']; ?>"><?= htmlspecialchars($t['libelle']); ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="regime_id" class="form-label">Régime</label>
                            <select name="regime_id" id="regime_id" class="form-select">
                                <option value="">-- Tous --</option>
                                <?php if (!empty($regimes)): ?>
                                    <?php foreach ($regimes as $r): ?>
                                        <option value="<?= (int)$r['regime_id']; ?>"><?= htmlspecialchars($r['libelle']); ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="min_personnes" class="form-label">Nombre minimum de personnes</label>
                            <input type="number" name="min_personnes" id="min_personnes" class="form-control" placeholder="Ex: 10">
                        </div>

                        <div class="d-flex gap-2">
                            <button type="button" id="btnFiltrer" class="btn btn-primary w-100">Filtrer</button>
                            <button type="button" id="btnReset" class="btn btn-outline-secondary w-100">Réinitialiser</button>
                        </div>

                    </form>
                </div>
            </div>
        </aside>

        <section class="col-12 col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h2 class="h6 mb-0">Résultats</h2>
                    <div class="d-flex align-items-center gap-2">
                        <div id="resultsCount" class="text-secondary small"></div>
                        <div id="loadingBadge" class="badge text-bg-light border d-none">Chargement…</div>
                    </div>
                </div>
                <div class="card-body">
                    <div id="menusContainer">
                        <div class="d-flex align-items-center gap-2 text-secondary">
                            <div class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></div>
                            <span>Chargement...</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </div>
</main>

<?php
require TEMPLATES_PATH . '/partials/footer.php';
require TEMPLATES_PATH . '/partials/scripts.php';
?>

</body>
</html>