<?php require TEMPLATES_PATH . '/partials/head.php'; ?>

<html lang="fr">
<body class="bg-light">

<?php require TEMPLATES_PATH . '/partials/header.php'; ?>

<main class="container my-4">
    <div class="d-flex flex-column flex-md-row align-items-md-end justify-content-between gap-2">
        <h1 class="h3 mb-3">Gestion des menus</h1>

        <p class="mb-3">
            <a href="<?= BASE_URL ?>/home" class="btn btn-sm btn-outline-secondary">Accueil</a>
            <a href="<?= BASE_URL ?>/menuCreate" class="btn btn-sm btn-primary">Créer un menu</a>
            <a href="<?= BASE_URL ?>/menuEditPlats" class="btn btn-sm btn-secondary">Associer plats</a>
            <a href="<?= BASE_URL ?>/admin" class="btn btn-sm btn-outline-secondary">Retour</a>
        </p>
    </div>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <?php foreach ($errors as $e): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($e) ?></div>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php if (empty($menus)): ?>
        <p>Aucun menu.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped table-bordered align-middle">
                <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Titre</th>
                    <th>Thème</th>
                    <th>Régime</th>
                    <th>Prix / pers</th>
                    <th>Min</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($menus as $m): ?>
                    <tr>
                        <td><?= (int)($m['menu_id'] ?? 0) ?></td>
                        <td><?= htmlspecialchars((string)($m['titre'] ?? '')) ?></td>
                        <td><?= htmlspecialchars((string)($m['theme'] ?? '')) ?></td>
                        <td><?= htmlspecialchars((string)($m['regime'] ?? '')) ?></td>
                        <td>
                            <?= isset($m['prix_par_personne'])
                                    ? number_format((float)$m['prix_par_personne'], 2, ',', ' ') . ' €'
                                    : '—' ?>
                        </td>
                        <td><?= (int)($m['nombre_personne_minimum'] ?? 0) ?></td>
                        <td>
                            <a href="<?= BASE_URL ?>/menu?id=<?= (int)($m['menu_id'] ?? 0) ?>" class="btn btn-sm btn-outline-primary">Voir</a>
                            <a href="<?= BASE_URL ?>/menuEdit?id=<?= (int)($m['menu_id'] ?? 0) ?>" class="btn btn-sm btn-outline-secondary">Modifier</a>
                            <form method="post" class="d-inline" onsubmit="return confirm('Supprimer ce menu ?');">
                                <input type="hidden" name="delete_id" value="<?= (int)($m['menu_id'] ?? 0) ?>">
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