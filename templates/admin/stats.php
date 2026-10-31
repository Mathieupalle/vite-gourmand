<?php
$menus = $menus ?? [];
$stats = $stats ?? [];

// Données envoyées au graphique (app.js)
$labels = [];
$nbCommandes = [];
foreach ($stats as $ligne) {
    $labels[] = $ligne['titre'];
    $nbCommandes[] = $ligne['nbCommandes'];
}
?>

<?php require TEMPLATES_PATH . '/partials/head.php'; ?>

<body class="bg-white">

<?php require TEMPLATES_PATH . '/partials/header.php'; ?>

<main class="container my-4">
    <div class="d-flex flex-column flex-md-row align-items-md-end justify-content-between gap-2">
        <h1 class="mb-3">Statistiques (MongoDB Atlas)</h1>
        <a href="<?= BASE_URL ?>/admin" class="btn btn-sm btn-outline-secondary mb-3">Retour</a>
    </div>

    <!-- Filtres -->
    <form method="get" action="<?= BASE_URL ?>/stats" class="card p-3 mb-4">
        <div class="row g-3 align-items-end">
            <div class="col-md-3">
                <label for="date_debut" class="form-label">Date de début</label>
                <input type="date" id="date_debut" name="date_debut" class="form-control"
                       value="<?= htmlspecialchars($dateDebut) ?>">
            </div>

            <div class="col-md-3">
                <label for="date_fin" class="form-label">Date de fin</label>
                <input type="date" id="date_fin" name="date_fin" class="form-control"
                       value="<?= htmlspecialchars($dateFin) ?>">
            </div>

            <div class="col-md-3">
                <label for="menu_id" class="form-label">Menu</label>
                <select id="menu_id" name="menu_id" class="form-select">
                    <option value="0">Tous les menus</option>
                    <?php foreach ($menus as $m): ?>
                        <option value="<?= (int)$m['menu_id'] ?>" <?= (int)$m['menu_id'] === $menuId ? 'selected' : '' ?>>
                            <?= htmlspecialchars($m['titre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Filtrer</button>
                <a href="<?= BASE_URL ?>/stats" class="btn btn-outline-secondary">Réinitialiser</a>
            </div>
        </div>
    </form>

    <?php if (empty($stats)): ?>
        <div class="alert alert-info">Aucune commande sur cette période.</div>
    <?php else: ?>

        <h2 class="h4">Nombre de commandes par menu</h2>
        <canvas id="chart" height="90" class="w-100 mb-4"></canvas>

        <h2 class="h4">Chiffre d'affaires par menu</h2>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th>Menu</th>
                    <th>Commandes</th>
                    <th>Chiffre d'affaires (€)</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($stats as $ligne): ?>
                    <tr>
                        <td><?= htmlspecialchars($ligne['titre']) ?></td>
                        <td><?= (int)$ligne['nbCommandes'] ?></td>
                        <td><?= number_format($ligne['chiffreAffaires'], 2, ',', ' ') ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot>
                <tr class="fw-bold">
                    <td>Total</td>
                    <td><?= (int)$totalCommandes ?></td>
                    <td><?= number_format($totalCA, 2, ',', ' ') ?></td>
                </tr>
                </tfoot>
            </table>
        </div>

    <?php endif; ?>

</main>

<?php require TEMPLATES_PATH . '/partials/footer.php'; ?>

<script>
    window.STATS_LABELS = <?= json_encode($labels) ?>;
    window.STATS_COMMANDES = <?= json_encode($nbCommandes) ?>;
</script>

<?php require TEMPLATES_PATH . '/partials/scripts.php'; ?>

</body>
</html>
