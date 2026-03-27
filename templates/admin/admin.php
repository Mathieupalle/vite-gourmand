<?php
$role = $role ?? '';
?>

<?php require TEMPLATES_PATH . '/partials/head.php'; ?>

<html lang="fr">
<body class="bg-light">

<?php require TEMPLATES_PATH . '/partials/header.php'; ?>

<main class="container my-4">
    <div class="d-flex flex-column flex-md-row align-items-md-end justify-content-between gap-2">
        <h1 class="h3 mb-3">Espace gestion</h1>
        <a href="<?= BASE_URL ?>/home" class="btn btn-sm btn-outline-secondary mb-3">
            Retour accueil
        </a>
    </div>

    <div class="alert alert-light border">
        Connecté :
        <strong><?= htmlspecialchars((string)($user['email'] ?? '')) ?></strong>
        <span class="text-secondary">(<?= htmlspecialchars((string)$role) ?>)</span>
    </div>

    <!-- Gestion -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white">
            <h2 class="h6 mb-0">Gestion</h2>
        </div>
        <div class="card-body">
            <ul class="mb-0">
                <li><a href="<?= BASE_URL ?>/menuManage">Gestion des menus</a></li>
                <li><a href="<?= BASE_URL ?>/platManage">Gestion des plats</a></li>
                <li><a href="<?= BASE_URL ?>/commandeManage">Gestion des commandes</a></li>
                <li><a href="<?= BASE_URL ?>/horaireManage">Gestion des horaires</a></li>
                <li><a href="<?= BASE_URL ?>/avisManage">Gestion des avis</a></li>
            </ul>
        </div>
    </div>

    <!-- Admin -->
    <?php if ($role === 'admin'): ?>
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h2 class="h6 mb-0">Administration</h2>
            </div>
            <div class="card-body">
                <ul class="mb-3">
                    <li><a href="<?= BASE_URL ?>/employeCreate">Créer un compte employé</a></li>
                    <li><a href="<?= BASE_URL ?>/employeManage">Gérer les comptes employés</a></li>
                </ul>

                <hr>

                <ul class="mb-0">
                    <li><a href="<?= BASE_URL ?>/stats">Statistiques (MongoDB)</a></li>
                </ul>
            </div>
        </div>
    <?php endif; ?>

</main>

<?php require TEMPLATES_PATH . '/partials/footer.php'; ?>
<?php require TEMPLATES_PATH . '/partials/scripts.php'; ?>

</body>
</html>