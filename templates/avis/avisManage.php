<?php require TEMPLATES_PATH . '/partials/head.php'; ?>

<html lang="fr">
<body class="bg-light">

<?php require TEMPLATES_PATH . '/partials/header.php'; ?>

<main class="container my-4">

    <div class="d-flex flex-column flex-md-row align-items-md-end justify-content-between gap-2">
        <h1 class="h3 mb-3">Gestion des avis</h1>

        <div class="mb-3">
            <a href="<?= BASE_URL ?>/home" class="btn btn-sm btn-outline-secondary">Accueil</a>
            <a href="<?= BASE_URL ?>/admin" class="btn btn-sm btn-outline-secondary">Retour</a>
        </div>
    </div>

    <?php if (empty($avis)): ?>
        <div class="alert alert-info">
            Aucun avis.
        </div>
    <?php else: ?>

        <div class="card shadow-sm">
            <div class="card-body">

                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">

                        <thead class="table-light">
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
                                    <form method="post" class="d-flex gap-2 align-items-center">

                                        <input type="hidden"
                                               name="avis_id"
                                               value="<?= (int)$a['avis_id'] ?>">

                                        <label for="statut_<?= (int)$a['avis_id'] ?>" class="visually-hidden">
                                            Statut de l'avis
                                        </label>

                                        <select name="statut"
                                                id="statut_<?= (int)$a['avis_id'] ?>"
                                                class="form-select form-select-sm">

                                            <?php foreach (['en_attente','valide','refuse'] as $s): ?>
                                                <option value="<?= $s ?>"
                                                        <?= ((string)$a['statut'] === $s) ? 'selected' : '' ?>>
                                                    <?= $s ?>
                                                </option>
                                            <?php endforeach; ?>

                                        </select>

                                        <button type="submit" class="btn btn-sm btn-primary">
                                            OK
                                        </button>

                                    </form>
                                </td>

                            </tr>
                        <?php endforeach; ?>
                        </tbody>

                    </table>
                </div>

            </div>
        </div>

    <?php endif; ?>

</main>

<?php require TEMPLATES_PATH . '/partials/footer.php'; ?>
<?php require TEMPLATES_PATH . '/partials/scripts.php'; ?>

</body>
</html>