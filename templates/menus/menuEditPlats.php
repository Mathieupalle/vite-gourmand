<!doctype html>
<html lang="fr">
<?php require TEMPLATES_PATH . '/partials/head.php'; ?>
<body>
<?php require TEMPLATES_PATH . '/partials/header.php'; ?>

<h1>Associer des plats à un menu</h1>
<p><a href="<?= BASE_URL ?>/menuManage">Retour</a></p>

<?php if ($success): ?><p style="color:green"><?= htmlspecialchars($success) ?></p><?php endif; ?>
<?php foreach ($errors as $e): ?><p style="color:red"><?= htmlspecialchars($e) ?></p><?php endforeach; ?>

<form method="get">
    <label>Choisir un menu :</label>
    <select name="menu_id" onchange="this.form.submit()">
        <option value="0">--</option>
        <?php foreach ($menus as $m): ?>
            <option value="<?= (int)$m['menu_id'] ?>" <?= ($menuId === (int)$m['menu_id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars((string)$m['titre']) ?>
            </option>
        <?php endforeach; ?>
    </select>
</form>

<hr>

<?php if ($menuId > 0): ?>
    <form method="post">
        <input type="hidden" name="menu_id" value="<?= (int)$menuId ?>">

        <p>Sélectionne les plats à associer :</p>
        <?php foreach ($plats as $p): ?>
            <?php $pid = (int)$p['plat_id']; ?>
            <label>
                <input type="checkbox" name="plats[]" value="<?= $pid ?>" <?= in_array($pid, $selectedPlatIds, true) ? 'checked' : '' ?>>
                <?= htmlspecialchars((string)$p['titre_plat']) ?> <small>(<?= htmlspecialchars((string)$p['categorie']) ?>)</small>
            </label><br>
        <?php endforeach; ?>

        <br>
        <button type="submit">Enregistrer</button>
    </form>
<?php else: ?>
    <p><em>Choisis un menu pour gérer ses plats.</em></p>
<?php endif; ?>
<?php require TEMPLATES_PATH . '/partials/footer.php'; ?>

<?php require TEMPLATES_PATH . '/partials/scripts.php'; ?>
</body>
</html>