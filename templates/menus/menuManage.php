<!doctype html>
<html lang="fr">
<?php require TEMPLATES_PATH . '/partials/head.php'; ?>
<body>
<?php require TEMPLATES_PATH . '/partials/header.php'; ?>

<h1>Gestion des menus</h1>
<p>
    <a href="<?= BASE_URL ?>/home">Accueil</a> |
    <a href="<?= BASE_URL ?>/menuCreate">Créer un menu</a> |
    <a href="<?= BASE_URL ?>/menuEditPlats">Associer plats</a>
    <br>
    <a href="<?= BASE_URL ?>/admin">Retour</a>
</p>

<?php if ($success): ?><p style="color:green"><?= htmlspecialchars($success) ?></p><?php endif; ?>
<?php foreach ($errors as $e): ?><p style="color:red"><?= htmlspecialchars($e) ?></p><?php endforeach; ?>

<?php if (!$menus): ?>
    <p>Aucun menu.</p>
<?php else: ?>
    <table border="1" cellpadding="8" cellspacing="0">
        <thead>
        <tr>
            <th>ID</th>
            <th>Titre</th>
            <th>Thème</th>
            <th>Régime</th>
            <th>Prix/pers</th>
            <th>Min</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($menus as $m): ?>
            <tr>
                <td><?= (int)$m['menu_id'] ?></td>
                <td><?= htmlspecialchars((string)$m['titre']) ?></td>
                <td><?= htmlspecialchars((string)$m['theme']) ?></td>
                <td><?= htmlspecialchars((string)$m['regime']) ?></td>
                <td><?= number_format((float)$m['prix_par_personne'], 2, ',', ' ') ?> €</td>
                <td><?= (int)$m['nombre_personne_minimum'] ?></td>
                <td>
                    <a href="<?= BASE_URL ?>/menu?id=<?= (int)$m['menu_id'] ?>">Voir</a> |
                    <a href="<?= BASE_URL ?>/menuEdit?id=<?= (int)$m['menu_id'] ?>">Modifier</a> |
                    <form method="post" style="display:inline" onsubmit="return confirm('Supprimer ce menu ?');">
                        <input type="hidden" name="delete_id" value="<?= (int)$m['menu_id'] ?>">
                        <button type="submit">Supprimer</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
<?php require TEMPLATES_PATH . '/partials/footer.php'; ?>

<?php require TEMPLATES_PATH . '/partials/scripts.php'; ?>
</body>
</html>