<!doctype html>
<html lang="fr">
<?php require TEMPLATES_PATH . '/partials/head.php'; ?>

<body class="bg-light">
<?php require TEMPLATES_PATH . '/partials/header.php'; ?>

<header class="py-4 bg-white border-bottom">
    <div class="container">
        <div class="d-flex flex-column flex-md-row align-items-md-end justify-content-between gap-2">
            <div>
                <h1 class="h3 mb-1">Nos menus</h1>
            </div>
            <a class="btn btn-sm btn-outline-secondary" href="<?= BASE_URL ?>/home">Retour Accueil</a>
        </div>
    </div>
</header>

<main class="container my-4">
    <div class="row g-4">

        <aside class="col-12 col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <div class="d-flex align-items-center justify-content-between">
                        <h2 class="h6 mb-0">Filtres</h2>
                    </div>
                </div>
                <div class="card-body">
                    <form id="filtersForm">
                        <div class="mb-3">
                            <label class="form-label">Prix maximum</label>
                            <input type="number" step="0.01" name="prix_max" class="form-control" placeholder="Ex: 30">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Fourchette de prix</label>
                            <div class="row g-2">
                                <div class="col">
                                    <input type="number" step="0.01" name="prix_min" class="form-control" placeholder="Min">
                                </div>
                                <div class="col">
                                    <input type="number" step="0.01" name="prix_max_range" class="form-control" placeholder="Max">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Thème</label>
                            <select name="theme_id" class="form-select">
                                <option value="">-- Tous --</option>
                                <?php foreach ($themes as $t): ?>
                                    <option value="<?php echo (int)$t['theme_id']; ?>">
                                        <?php echo htmlspecialchars($t['libelle']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Régime</label>
                            <select name="regime_id" class="form-select">
                                <option value="">-- Tous --</option>
                                <?php foreach ($regimes as $r): ?>
                                    <option value="<?php echo (int)$r['regime_id']; ?>">
                                        <?php echo htmlspecialchars($r['libelle']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nombre minimum de personnes</label>
                            <input type="number" name="min_personnes" class="form-control" placeholder="Ex: 10">
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
                    <div class="d-flex align-items-center justify-content-between">
                        <h2 class="h6 mb-0">Résultats</h2>
                        <div class="d-flex align-items-center gap-2">
                            <div id="resultsCount" class="text-secondary small"></div>
                            <div id="loadingBadge" class="badge text-bg-light border d-none">Chargement…</div>
                        </div>
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
<?php require TEMPLATES_PATH . '/partials/footer.php'; ?>

<?php require TEMPLATES_PATH . '/partials/scripts.php'; ?>
<script>
    const menusContainer = document.getElementById('menusContainer');
    const loadingBadge = document.getElementById('loadingBadge');
    const resultsCount = document.getElementById('resultsCount');

    function setLoading(isLoading) {
        loadingBadge.classList.toggle('d-none', !isLoading);
    }

    async function loadMenus() {
        const form = document.getElementById('filtersForm');
        const params = new URLSearchParams(new FormData(form));

        setLoading(true);
        menusContainer.innerHTML = `
            <div class="d-flex align-items-center gap-2 text-secondary">
                <div class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></div>
                <span>Chargement...</span>
            </div>
        `;

        try {
            const res = await fetch('menusAjax?' + params.toString());
            const html = await res.text();

            menusContainer.innerHTML = html;

            // Petit compteur approximatif : compte les liens vers menu?id=
            const count = (menusContainer.innerHTML.match(/menu\?id=/g) || []).length;
            resultsCount.textContent = count ? `${count} menu(s)` : '';
        } catch (e) {
            menusContainer.innerHTML = `
                <div class="alert alert-danger mb-0">
                    Erreur lors du chargement des menus. Vérifie <code>menusAjax</code>.
                </div>
            `;
        } finally {
            setLoading(false);
        }
    }

    document.getElementById('btnFiltrer').addEventListener('click', loadMenus);

    document.getElementById('btnReset').addEventListener('click', () => {
        document.getElementById('filtersForm').reset();
        loadMenus();
    });

    // Chargement initial
    loadMenus();
</script>
</body>
</html>