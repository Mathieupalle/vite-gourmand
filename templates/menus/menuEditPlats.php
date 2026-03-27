<?php
$selectedPlatIds = $selectedPlatIds ?? [];
$menuId = $menuId ?? 0;
?>

<?php require TEMPLATES_PATH . '/partials/head.php'; ?>

<html lang="fr">
<body class="bg-light">

<?php require TEMPLATES_PATH . '/partials/header.php'; ?>

<main class="container my-4">
    <div class="d-flex flex-column flex-md-row align-items-md-end justify-content-between gap-2">
        <h1 class="h3 mb-3">Associer des plats à un menu</h1>
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

    <form method="get" class="mb-4">
        <div class="mb-3">
            <label for="menu_id" class="form-label">Choisir un menu :</label>
            <select name="menu_id" id="menu_id" class="form-select" onchange="this.form.submit()">
                <option value="0">--</option>
                <?php if (!empty($menus)): ?>
                    <?php foreach ($menus as $m): ?>
                        <option value="<?= (int)$m['menu_id'] ?>" <?= ($menuId === (int)$m['menu_id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars((string)$m['titre']) ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
    </form>

    <hr>

    <?php if ($menuId > 0): ?>
        <form method="post">
            <input type="hidden" name="menu_id" value="<?= (int)$menuId ?>">

            <p class="mb-2">Sélectionne les plats à associer :</p>

            <div class="mb-3">
                <?php if (!empty($plats)): ?>
                    <?php foreach ($plats as $p): ?>
                        <?php $pid = (int)$p['plat_id']; ?>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox"
                                   name="plats[]" id="plat_<?= $pid ?>" value="<?= $pid ?>"
                                    <?= in_array($pid, $selectedPlatIds, true) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="plat_<?= $pid ?>">
                                <?= htmlspecialchars((string)$p['titre_plat']) ?>
                                <small class="text-muted">(<?= htmlspecialchars((string)$p['categorie']) ?>)</small>
                            </label>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn btn-primary">Enregistrer</button>
        </form>
    <?php else: ?>
        <p class="text-muted"><em>Choisis un menu pour gérer ses plats.</em></p>
    <?php endif; ?>

</main>

<?php require TEMPLATES_PATH . '/partials/footer.php'; ?>
<?php require TEMPLATES_PATH . '/partials/scripts.php'; ?>

</body>
</html>