<!doctype html>
<html lang="fr">

<?php require TEMPLATES_PATH . '/partials/head.php'; ?>

<body class="bg-white">

<?php require TEMPLATES_PATH . '/partials/header.php'; ?>

<h1>Gestion des avis</h1>

<p><a href="<?= BASE_URL ?>/home">Accueil</a></p>
<p><a href="<?= BASE_URL ?>/admin">← Retour à l’espace Gestion</a></p>

<?php if (!$avis): ?>

    <p>Aucun avis.</p>

<?php else: ?>

    <table border="1" cellpadding="8" cellspacing="0">

        <thead>
        <tr>
            <th>Client</th>
            <th>Note</th>
            <th>Commentaire</th>
            <th>Statut</th>
            <th>Date</th>
            <th>Action</th>
        </tr>
        </thead>

        <tbody>

        <?php foreach ($avis as $a): ?>

            <tr>

                <td>
                    <?= htmlspecialchars((string)($a['client_email'] ?? '-')) ?>
                </td>

                <td>
                    <?= (int)$a['note'] ?>/5
                </td>

                <td>
                    <?= nl2br(htmlspecialchars((string)$a['description'])) ?>
                </td>

                <td>
                    <?= htmlspecialchars((string)$a['statut']) ?>
                </td>

                <td>
                    <?= htmlspecialchars((string)$a['date_avis']) ?>
                </td>

                <td>

                    <form method="post" style="display:inline;">

                        <input type="hidden" name="avis_id" value="<?= (int)$a['avis_id'] ?>">

                        <select name="statut">

                            <?php foreach (['en_attente','valide','refuse'] as $s): ?>

                                <option
                                        value="<?= $s ?>"
                                        <?= ((string)$a['statut'] === $s) ? 'selected' : '' ?>
                                >
                                    <?= $s ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                        <button type="submit">
                            Mettre à jour
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