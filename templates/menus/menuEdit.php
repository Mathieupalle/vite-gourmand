<!doctype html>
<html lang="fr">
<?php require TEMPLATES_PATH . '/partials/head.php'; ?>
<body>
<?php require TEMPLATES_PATH . '/partials/header.php'; ?>

<h1>Modifier le menu</h1>
<p><a href="<?= BASE_URL ?>/menuManage">Retour</a></p>

<?php if ($success): ?><p style="color:green"><?= htmlspecialchars($success) ?></p><?php endif; ?>
<?php foreach ($errors as $e): ?><p style="color:red"><?= htmlspecialchars($e) ?></p><?php endforeach; ?>

<form method="post">
    <label>Titre</label><br>
    <input type="text" name="titre" value="<?= htmlspecialchars((string)$menu['titre']) ?>" required><br><br>

    <label>Description</label><br>
    <input type="text" name="description" value="<?= htmlspecialchars((string)($menu['description'] ?? '')) ?>"><br><br>

    <label>Prix par personne (€)</label><br>
    <input type="number" step="0.01" name="prix_par_personne" value="<?= htmlspecialchars((string)$menu['prix_par_personne']) ?>" required><br><br>

    <label>Nombre minimum de personnes</label><br>
    <input type="number" name="nombre_personne_minimum" value="<?= (int)$menu['nombre_personne_minimum'] ?>" required><br><br>

    <label>Quantité restante</label><br>
    <input type="number" name="quantite_restante" value="<?= htmlspecialchars((string)($menu['quantite_restante'] ?? '')) ?>"><br><br>

    <label>Thème</label><br>
    <select name="theme_id" required>
        <?php foreach ($themes as $t): ?>
            <option value="<?= (int)$t['theme_id'] ?>" <?= ((int)$menu['theme_id'] === (int)$t['theme_id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars((string)$t['libelle']) ?>
            </option>
        <?php endforeach; ?>
    </select><br><br>

    <label>Régime</label><br>
    <select name="regime_id" required>
        <?php foreach ($regimes as $r): ?>
            <option value="<?= (int)$r['regime_id'] ?>" <?= ((int)$menu['regime_id'] === (int)$r['regime_id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars((string)$r['libelle']) ?>
            </option>
        <?php endforeach; ?>
    </select><br><br>

    <button type="submit">Enregistrer</button>
</form>
<?php require TEMPLATES_PATH . '/partials/footer.php'; ?>

<?php require TEMPLATES_PATH . '/partials/scripts.php'; ?>
</body>
</html>