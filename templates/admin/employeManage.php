<!doctype html>
<html lang="fr">

<?php require TEMPLATES_PATH . '/partials/head.php'; ?>

<body class="bg-white">

<?php require TEMPLATES_PATH . '/partials/header.php'; ?>

<h1>Gestion des comptes employés</h1>

<p><a href="<?= BASE_URL ?>/home">Accueil</a></p>
<p><a href="<?= BASE_URL ?>/admin">← Retour à l’espace Gestion</a></p>

<table border="1" cellpadding="8">

    <tr>
        <th>ID</th>
        <th>Email</th>
        <th>Actif</th>
        <th>Action</th>
    </tr>

    <?php foreach ($employes as $e): ?>

        <tr>

            <td>
                <?= (int)$e['utilisateur_id'] ?>
            </td>

            <td>
                <?= htmlspecialchars((string)$e['email']) ?>
            </td>

            <td>
                <?= ((int)$e['actif'] === 1) ? 'Oui' : 'Non' ?>
            </td>

            <td>

                <?php if ((int)$e['actif'] === 1): ?>

                    <form method="post">
                        <input type="hidden" name="utilisateur_id" value="<?= (int)$e['utilisateur_id'] ?>">
                        <button type="submit">Désactiver</button>
                    </form>

                <?php else: ?>

                    Compte désactivé

                <?php endif; ?>

            </td>

        </tr>

    <?php endforeach; ?>

</table>

<?php require TEMPLATES_PATH . '/partials/footer.php'; ?>
<?php require TEMPLATES_PATH . '/partials/scripts.php'; ?>

</body>
</html>