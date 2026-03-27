<?php
$menus = $menus ?? [];
?>

<?php require TEMPLATES_PATH . '/partials/head.php'; ?>

<html lang="fr">
<body class="bg-white">

<?php require TEMPLATES_PATH . '/partials/header.php'; ?>

<main class="container my-4">
    <div class="d-flex flex-column flex-md-row align-items-md-end justify-content-between gap-2">
        <h1 class="mb-3">Statistiques (MongoDB Atlas)</h1>
        <a href="<?= BASE_URL ?>/admin" class="btn btn-sm btn-outline-secondary mb-3">Retour</a>
    </div>

    <div class="row g-3">

        <div class="col-md-3">
            <div class="card p-3">
                <div class="text-muted mb-2"><strong>Période A</strong></div>

                <div class="mb-2">
                    <label for="start" class="form-label">Début</label>
                    <input type="date" id="start" class="form-control" value="<?= htmlspecialchars(date('Y-m-01')) ?>">
                </div>

                <div>
                    <label for="end" class="form-label">Fin</label>
                    <input type="date" id="end" class="form-control" value="<?= htmlspecialchars(date('Y-m-d')) ?>">
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card p-3">
                <div class="text-muted mb-2"><strong>Période B</strong></div>

                <div class="mb-2">
                    <label for="compare_start" class="form-label">Début</label>
                    <input type="date" id="compare_start" class="form-control">
                </div>

                <div>
                    <label for="compare_end" class="form-label">Fin</label>
                    <input type="date" id="compare_end" class="form-control">
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card p-3">
                <div class="mb-2">
                    <label for="group" class="form-label">Durée</label>
                    <select id="group" class="form-select">
                        <option value="day">Jour</option>
                        <option value="week">Semaine</option>
                        <option value="month">Mois</option>
                        <option value="year">Année</option>
                    </select>
                </div>

                <div>
                    <label for="compare_mode" class="form-label">Mode de comparaison</label>
                    <select id="compare_mode" class="form-select">
                        <option value="relative" selected>Jour par Jour (début = début)</option>
                        <option value="absolute">Par dates (calendrier)</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card p-3">
                <div class="mb-2">
                    <label for="menu_id" class="form-label">Menu A</label>
                    <select id="menu_id" class="form-select">
                        <option value="">Tous les menus</option>
                        <?php foreach ($menus as $m): ?>
                            <option value="<?= (int)$m['menu_id'] ?>">
                                <?= htmlspecialchars($m['titre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-2">
                    <label for="compare_menu_id" class="form-label">Comparer avec Menu B</label>
                    <select id="compare_menu_id" class="form-select">
                        <option value="">— Aucun —</option>
                        <?php foreach ($menus as $m): ?>
                            <option value="<?= (int)$m['menu_id'] ?>">
                                <?= htmlspecialchars($m['titre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="text-muted d-block mt-2">
                        Si Menu B est choisi, comparaison <strong>Menu A vs Menu B</strong> sur la même période A.
                    </small>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card p-3 d-flex gap-2">
                <button id="btnLoad" class="btn btn-primary">Afficher</button>
                <button id="btnClear" class="btn btn-outline-secondary">Reset</button>
            </div>
        </div>

    </div>

    <div class="row mt-4 g-3">
        <div class="col-md-4">
            <div class="card p-3 text-center">
                <div class="text-muted">CA Période A</div>
                <div class="fs-4" id="kpiA">—</div>
                <div class="text-muted">Commandes: <span id="kpiAOrders">—</span></div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card p-3 text-center">
                <div class="text-muted" id="kpiBLabel">CA Période B</div>
                <div class="fs-4" id="kpiB">—</div>
                <div class="text-muted">Commandes: <span id="kpiBOrders">—</span></div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card p-3 text-center">
                <div class="text-muted">Écart (A - B)</div>
                <div class="fs-4" id="kpiDiff">—</div>
                <div class="text-muted">Évolution: <span id="kpiPct">—</span></div>
            </div>
        </div>
    </div>

    <hr>

    <h2>Graphique : comparaison commande par menu / chiffre d'affaires</h2>
    <canvas id="chart" height="90" class="w-100"></canvas>

    <hr>

    <h2>Tableau détaillé</h2>
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
            <tr>
                <th>Période</th>
                <th>CA A (€)</th>
                <th>Commandes A</th>
                <th id="thCaB">CA B (€)</th>
                <th id="thOrdersB">Commandes B</th>
            </tr>
            </thead>
            <tbody id="tbody"></tbody>
        </table>
    </div>

</main>

<?php require TEMPLATES_PATH . '/partials/footer.php'; ?>

<script>
    window.STATS_START = "<?= htmlspecialchars(date('Y-m-01')) ?>";
    window.STATS_END   = "<?= htmlspecialchars(date('Y-m-d')) ?>";
</script>

<?php require TEMPLATES_PATH . '/partials/scripts.php'; ?>

</body>
</html>