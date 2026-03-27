<?php require TEMPLATES_PATH . '/partials/head.php'; ?>

<html lang="fr">
<body>

<?php require TEMPLATES_PATH . '/partials/header.php'; ?>

<div class="container my-5">
    <div class="d-flex flex-column flex-md-row align-items-md-end justify-content-between gap-2">
        <h1 class="mb-3">Conditions Générales de Vente</h1>
        <a class="btn btn-sm btn-outline-secondary mb-3" href="<?= BASE_URL ?>/home">Retour Accueil</a>
    </div>

    <h2 class="h3 mt-4">Commandes</h2>
    <p>
        Toute commande passée via l’application doit respecter le nombre minimum de personnes indiqué sur le menu.
    </p>

    <h2 class="h3 mt-4">Annulation</h2>
    <p>
        Une commande peut être annulée tant qu’elle n’a pas été acceptée par l’équipe.
    </p>

    <h2 class="h3 mt-4">Livraison</h2>
    <p>
        Les frais de livraison sont affichés avant validation de la commande.
    </p>

    <h2 class="h3 mt-4">Matériel</h2>
    <p>
        En cas de prêt de matériel, le client s’engage à le restituer sous 10 jours ouvrés, sinon il devra s'acquitter de 600 euros de frais pour non-restitution de matériel.
        Le client doit prendre contact avec la société pour rendre le matériel.
    </p>

</div>

<?php require TEMPLATES_PATH . '/partials/footer.php'; ?>
<?php require TEMPLATES_PATH . '/partials/scripts.php'; ?>

</body>
</html>