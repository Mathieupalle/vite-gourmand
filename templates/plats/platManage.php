<?php require TEMPLATES_PATH . '/partials/head.php'; ?>

<html lang="fr">
<body class="bg-light">

<?php require TEMPLATES_PATH . '/partials/header.php'; ?>

<main class="container my-4">
    <div class="d-flex flex-column flex-md-row align-items-md-end justify-content-between gap-2">
        <h1 class="h3 mb-3">Gestion des plats</h1>

        <p class="mb-3">
            <a href="<?= BASE_URL ?>/home" class="btn btn-sm btn-outline-secondary">Accueil</a>
            <a href="<?= BASE_URL ?>/platCreate" class="btn btn-sm btn-primary">Créer un plat</a>
            <a href="<?= BASE_URL ?>/admin" class="btn btn-sm btn-outline-secondary">Retour</a>
        </p>
    </div>

    <?php if (empty($plats)): ?>
        <div class="alert alert-info">Aucun plat.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped table-bordered align-middle">
                <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Titre</th>
                    <th>Catégorie</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($plats as $p): ?>
                    <tr>
                        <td><?= (int)($p['plat_id'] ?? 0) ?></td>
                        <td><?= htmlspecialchars((string)($p['titre_plat'] ?? '')) ?></td>
                        <td><?= htmlspecialchars((string)($p['categorie'] ?? '')) ?></td>
                        <td>
                            <a href="<?= BASE_URL ?>/platEdit?id=<?= (int)($p['plat_id'] ?? 0) ?>" class="btn btn-sm btn-outline-primary">Modifier</a>

                            <form method="post" style="display:inline" onsubmit="return confirm('Supprimer ce plat ?');" class="d-inline">
                                <input type="hidden" name="delete_id" value="<?= (int)($p['plat_id'] ?? 0) ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

</main>

<?php require TEMPLATES_PATH . '/partials/footer.php'; ?>
<?php require TEMPLATES_PATH . '/partials/scripts.php'; ?>

</body>
</html>