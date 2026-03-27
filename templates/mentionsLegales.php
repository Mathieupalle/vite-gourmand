<?php require TEMPLATES_PATH . '/partials/head.php'; ?>

<html lang="fr">
<body>

<?php require TEMPLATES_PATH . '/partials/header.php'; ?>

<div class="container my-5">
    <div class="d-flex flex-column flex-md-row align-items-md-end justify-content-between gap-2">
        <h1 class="mb-3">Mentions légales</h1>
        <a class="btn btn-sm btn-outline-secondary mb-3" href="<?= BASE_URL ?>/home">Retour Accueil</a>
    </div>

    <p class="mt-3">
        Site réalisé dans le cadre d’un projet pédagogique (ECF STUDI).
    </p>

    <h2 class="h3 mt-4">Éditeur du site</h2>
    <p>
        Vite & Gourmand — Bordeaux<br>
        Contact : via la page <a href="<?= BASE_URL ?>/contact">Contact</a>
    </p>

    <h2 class="h3 mt-4">Données personnelles</h2>
    <p>
        Les informations collectées (compte, commandes) sont utilisées uniquement pour le fonctionnement de l’application.
        Conformément au RGPD, l’utilisateur peut demander la modification ou la suppression de ses données.
    </p>

</div>

<?php require TEMPLATES_PATH . '/partials/footer.php'; ?>
<?php require TEMPLATES_PATH . '/partials/scripts.php'; ?>

</body>
</html>