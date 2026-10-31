<?php
$themes = $themes ?? [];
$allergenes = $allergenes ?? [];

// Les deux listes de la page : [type, titre, lignes]
$sections = [
    ['theme', 'Thèmes', $themes],
    ['allergene', 'Allergènes', $allergenes],
];
?>

<?php require TEMPLATES_PATH . '/partials/head.php'; ?>

<body class="bg-light">

<?php require TEMPLATES_PATH . '/partials/header.php'; ?>

<main class="container my-4">
    <div class="d-flex flex-column flex-md-row align-items-md-end justify-content-between gap-2">
        <h1 class="h3 mb-3">Thèmes et allergènes</h1>
        <div class="mb-3">
            <a href="<?= BASE_URL ?>/home" class="btn btn-sm btn-outline-secondary">Accueil</a>
            <a href="<?= BASE_URL ?>/admin" class="btn btn-sm btn-outline-secondary">Retour</a>
        </div>
    </div>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <?php foreach ($errors as $e): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($e) ?></div>
    <?php endforeach; ?>

    <div class="row g-4">
        <?php foreach ($sections as [$type, $titre, $lignes]): ?>
            <div class="col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h2 class="h6 mb-0"><?= $titre ?></h2>
                    </div>
                    <div class="card-body">

                        <!-- Ajout -->
                        <form method="post" class="d-flex gap-2 mb-3">
                            <?= csrf_field() ?>
                            <input type="hidden" name="type" value="<?= $type ?>">
                            <input type="hidden" name="action" value="ajouter">
                            <label for="ajout_<?= $type ?>" class="visually-hidden">Nouveau libellé</label>
                            <input type="text" name="libelle" id="ajout_<?= $type ?>" class="form-control form-control-sm"
                                   placeholder="Nouveau libellé" required>
                            <button type="submit" class="btn btn-sm btn-primary">Ajouter</button>
                        </form>

                        <!-- Liste : renommer ou supprimer -->
                        <?php foreach ($lignes as $l): ?>
                            <?php $id = (int)$l['id']; ?>
                            <div class="d-flex gap-2 mb-2">
                                <form method="post" class="d-flex gap-2 flex-grow-1">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="type" value="<?= $type ?>">
                                    <input type="hidden" name="action" value="renommer">
                                    <input type="hidden" name="id" value="<?= $id ?>">
                                    <label for="<?= $type ?>_<?= $id ?>" class="visually-hidden">Libellé</label>
                                    <input type="text" name="libelle" id="<?= $type ?>_<?= $id ?>" class="form-control form-control-sm"
                                           value="<?= htmlspecialchars($l['libelle']) ?>" required>
                                    <button type="submit" class="btn btn-sm btn-outline-secondary">Renommer</button>
                                </form>
                                <form method="post" onsubmit="return confirm('Supprimer cet élément ?');">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="type" value="<?= $type ?>">
                                    <input type="hidden" name="action" value="supprimer">
                                    <input type="hidden" name="id" value="<?= $id ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Supprimer</button>
                                </form>
                            </div>
                        <?php endforeach; ?>

                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</main>

<?php require TEMPLATES_PATH . '/partials/footer.php'; ?>
<?php require TEMPLATES_PATH . '/partials/scripts.php'; ?>

</body>
</html>
