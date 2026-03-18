<!doctype html>
<html lang="fr">
<?php require TEMPLATES_PATH . '/partials/head.php'; ?>

<body>

<?php require TEMPLATES_PATH . '/partials/header.php'; ?>

<h1>Gestion des horaires</h1>

<p><a href="<?= BASE_URL ?>/home">Accueil</a></p>
<p><a href="<?= BASE_URL ?>/admin">Retour</a></p>

<?php if (!empty($success)): ?>
    <p style="color:green"><?= htmlspecialchars($success) ?></p>
<?php endif; ?>

<form method="post">

    <table border="1" cellpadding="8" cellspacing="0">
        <thead>
        <tr>
            <th>Jour</th>
            <th>Ouverture</th>
            <th>Fermeture</th>
            <th>Note</th>
        </tr>
        </thead>

        <tbody>

        <?php foreach ($rows as $r): ?>

            <?php
            $open = $r['heure_ouverture'] ? substr((string)$r['heure_ouverture'],0,5) : '';
            $close = $r['heure_fermeture'] ? substr((string)$r['heure_fermeture'],0,5) : '';
            ?>

            <tr>

                <td>
                    <?= htmlspecialchars((string)$r['jour']) ?>
                </td>

                <td>
                    <input
                            type="time"
                            name="horaires[<?= (int)$r['horaire_id'] ?>][ouverture]"
                            value="<?= htmlspecialchars($open) ?>"
                    >
                </td>

                <td>
                    <input
                            type="time"
                            name="horaires[<?= (int)$r['horaire_id'] ?>][fermeture]"
                            value="<?= htmlspecialchars($close) ?>"
                    >
                </td>

            </tr>

        <?php endforeach; ?>

        </tbody>
    </table>

    <br>

    <button type="submit">
        Enregistrer
    </button>

</form>

<?php require TEMPLATES_PATH . '/partials/footer.php'; ?>
<?php require TEMPLATES_PATH . '/partials/scripts.php'; ?>

</body>
</html>