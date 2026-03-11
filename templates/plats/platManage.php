<!doctype html>
<html lang="fr">

<?php require TEMPLATES_PATH . '/partials/head.php'; ?>

<body class="bg-white">

<?php require TEMPLATES_PATH . '/partials/header.php'; ?>

<h1>Gestion des plats</h1>

<p>
    <a href="<?= BASE_URL ?>/home">Accueil</a> |
    <a href="<?= BASE_URL ?>/platCreate">Créer un plat</a>
    <br>
    <a href="<?= BASE_URL ?>/admin">Retour</a>
</p>

<?php if (!$plats): ?>

    <p>Aucun plat.</p>

<?php else: ?>

    <table border="1" cellpadding="8" cellspacing="0">

        <thead>
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

                <td><?= (int)$p['plat_id'] ?></td>

                <td><?= htmlspecialchars((string)$p['titre_plat']) ?></td>

                <td><?= htmlspecialchars((string)$p['categorie']) ?></td>

                <td>

                    <a href="<?= BASE_URL ?>/platEdit?id=<?= (int)$p['plat_id'] ?>">
                        Modifier
                    </a>

                    |

                    <form method="post" style="display:inline" onsubmit="return confirm('Supprimer ce plat ?');">

                        <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">

                        <input
                                type="hidden"
                                name="delete_id"
                                value="<?= (int)$p['plat_id'] ?>"
                        >

                        <button type="submit">
                            Supprimer
                        </button>

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