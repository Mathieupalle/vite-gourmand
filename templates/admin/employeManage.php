<?php require TEMPLATES_PATH . '/partials/head.php'; ?>

<html lang="fr">
<body class="bg-white">

<?php require TEMPLATES_PATH . '/partials/header.php'; ?>

<main class="container my-4">
    <div class="d-flex flex-column flex-md-row align-items-md-end justify-content-between gap-2">
        <h1 class="mb-3">Gestion des comptes employés</h1>

        <div class="mb-3">
            <a href="<?= BASE_URL ?>/home" class="btn btn-sm btn-outline-secondary">Accueil</a>
            <a href="<?= BASE_URL ?>/admin" class="btn btn-sm btn-outline-secondary">Retour</a>
        </div>
    </div>

    <div class="table-responsive mt-4">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>Email</th>
                <th>Actif</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            <?php if (!empty($employes)): ?>
                <?php foreach ($employes as $e): ?>
                    <tr>
                        <td><?= (int)$e['utilisateur_id'] ?></td>
                        <td><?= htmlspecialchars((string)$e['email']) ?></td>
                        <td><?= ((int)$e['actif'] === 1) ? 'Oui' : 'Non' ?></td>
                        <td>
                            <?php if ((int)$e['actif'] === 1): ?>
                                <form method="post" class="d-inline">
                                    <input type="hidden" name="utilisateur_id" value="<?= (int)$e['utilisateur_id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">Désactiver</button>
                                </form>
                            <?php else: ?>
                                <span class="text-muted">Compte désactivé</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

</main>

<?php require TEMPLATES_PATH . '/partials/footer.php'; ?>
<?php require TEMPLATES_PATH . '/partials/scripts.php'; ?>

</body>
</html>