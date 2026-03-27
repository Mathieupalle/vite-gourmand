<?php require TEMPLATES_PATH . '/partials/head.php'; ?>

<html lang="fr">
<body>

<?php require TEMPLATES_PATH . '/partials/header.php'; ?>

<div class="container my-5" style="max-width: 700px;">
    <div class="d-flex flex-column flex-md-row align-items-md-end justify-content-between gap-2">
        <h1 class="mb-3">Gestion des horaires</h1>
        <div class="mb-3">
            <a class="btn btn-sm btn-outline-secondary" href="<?= BASE_URL ?>/home">Accueil</a>
            <a class="btn btn-sm btn-outline-secondary" href="<?= BASE_URL ?>/admin">Retour</a>
        </div>
    </div>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>

    <form method="post">

        <table class="table table-bordered text-center">
            <thead>
            <tr>
                <th>Jour</th>
                <th>Ouverture</th>
                <th>Fermeture</th>
            </tr>
            </thead>

            <tbody>
            <?php if (!empty($rows)): ?>
                <?php foreach ($rows as $r): ?>
                    <?php
                    $open = $_POST['horaires'][$r['horaire_id']]['ouverture'] ?? '';
                    $close = $_POST['horaires'][$r['horaire_id']]['fermeture'] ?? '';
                    ?>
                    <tr>
                        <td class="align-middle"><?= htmlspecialchars((string)$r['jour']); ?></td>
                        <td>
                            <label for="horaires" class="form-label"></label>
                            <input type="time"
                                   class="form-control"
                                   id="horaires"
                                   name="horaires[<?= (int)$r['horaire_id']; ?>][ouverture]"
                                   value="<?= htmlspecialchars($open); ?>">
                        </td>
                        <td>
                            <label for="horaires" class="form-label"></label>
                            <input type="time"
                                   class="form-control"
                                   id="horaires"
                                   name="horaires[<?= (int)$r['horaire_id']; ?>][fermeture]"
                                   value="<?= htmlspecialchars($close); ?>">
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>

        <button type="submit" class="btn btn-primary mt-3">Enregistrer</button>

    </form>

</div>

<?php require TEMPLATES_PATH . '/partials/footer.php'; ?>
<?php require TEMPLATES_PATH . '/partials/scripts.php'; ?>

</body>
</html>