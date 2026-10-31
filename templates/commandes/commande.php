<?php
$prixParPersonne = $prixParPersonne ?? 0;
$minPers = $minPers ?? 0;
$today = $today ?? date('Y-m-d');
$client = $client ?? [];
?>

<?php require TEMPLATES_PATH . '/partials/head.php'; ?>

<body class="bg-light">

<?php require TEMPLATES_PATH . '/partials/header.php'; ?>

<main class="container my-4">
    <div class="d-flex flex-column flex-md-row align-items-md-end justify-content-between gap-2">
        <h1 class="h3 mb-1">
            Commander : <?= htmlspecialchars($menu['titre'] ?? '') ?>
        </h1>
        <a href="<?= BASE_URL ?>/menus" class="btn btn-sm btn-outline-secondary">
            Retour aux menus
        </a>
    </div>

    <div class="mb-4">
        <p class="mb-1">
            Prix par personne :
            <strong><?= number_format((float)$prixParPersonne, 2, ',', ' ') ?> €</strong>
        </p>
        <p>
            Minimum :
            <strong><?= (int)$minPers ?> personnes</strong>
        </p>
    </div>

    <form method="post" action="<?= BASE_URL ?>/commandeCreate" id="commandeForm">
        <?= csrf_field() ?>

        <input type="hidden" name="menu_id" value="<?= (int)($menu['menu_id'] ?? 0) ?>">

        <h2 class="h5 mt-4">Client</h2>
        <!-- Informations reprises du compte (modifiables depuis "Mon profil") -->
        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label for="client_nom" class="form-label">Nom</label>
                <input type="text" id="client_nom" class="form-control" value="<?= htmlspecialchars((string)($client['nom'] ?? '')) ?>" readonly>
            </div>
            <div class="col-md-6">
                <label for="client_prenom" class="form-label">Prénom</label>
                <input type="text" id="client_prenom" class="form-control" value="<?= htmlspecialchars((string)($client['prenom'] ?? '')) ?>" readonly>
            </div>
            <div class="col-md-6">
                <label for="client_email" class="form-label">Email</label>
                <input type="email" id="client_email" class="form-control" value="<?= htmlspecialchars((string)($client['email'] ?? '')) ?>" readonly>
            </div>
            <div class="col-md-6">
                <label for="client_telephone" class="form-label">GSM</label>
                <input type="text" id="client_telephone" class="form-control" value="<?= htmlspecialchars((string)($client['telephone'] ?? '')) ?>" readonly>
            </div>
        </div>

        <h2 class="h5 mt-4">Prestation</h2>

        <div class="mb-3">
            <label for="date_prestation" class="form-label">Date de prestation</label>
            <input type="date" name="date_prestation" id="date_prestation" class="form-control"
                   min="<?= $today ?>" required>
        </div>

        <div class="mb-3">
            <label for="adresse_prestation" class="form-label">Adresse</label>
            <input type="text" name="adresse_prestation" id="adresse_prestation" class="form-control"
                   value="<?= htmlspecialchars((string)($client['adresse_postale'] ?? '')) ?>" required>
        </div>

        <div class="mb-3">
            <label for="ville_prestation" class="form-label">Ville</label>
            <input type="text" name="ville_prestation" id="ville_prestation" class="form-control"
                   value="<?= htmlspecialchars((string)($client['ville'] ?? '')) ?>" required>
        </div>

        <h2 class="h5 mt-4">Livraison</h2>

        <div class="form-check mb-2">
            <label class="form-check-label" for="sameAddress">
                Livraison à la même adresse que la prestation
            </label>
            <input class="form-check-input" type="checkbox" id="sameAddress" checked>
        </div>

        <div class="form-check mb-3">
            <label class="form-check-label" for="same_date">
                Livraison à la même date
            </label>
            <input class="form-check-input" type="checkbox" id="same_date" name="same_date">
        </div>

        <div class="mb-3">
            <label for="date_livraison" class="form-label">Date de livraison</label>
            <input type="date" name="date_livraison" id="date_livraison" class="form-control"
                   min="<?= $today ?>" required>
        </div>

        <div class="mb-3">
            <label for="heure_livraison" class="form-label">Heure de livraison</label>
            <input type="time" name="heure_livraison" id="heure_livraison" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="adresseLivraison" class="form-label">Adresse de livraison</label>
            <input type="text" name="adresse_livraison" id="adresseLivraison" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="villeLivraison" class="form-label">Ville de livraison</label>
            <input type="text" name="ville_livraison" id="villeLivraison" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="distanceKm" class="form-label">Distance (km)</label>
            <input type="number" step="0.1" min="0" name="distance_km" id="distanceKm"
                   class="form-control" value="0">
        </div>

        <h2 class="h5 mt-4">Menu</h2>

        <div class="mb-3">
            <label for="nbPersonnes" class="form-label">Nombre de personnes</label>
            <input type="number"
                   name="nombre_personne"
                   id="nbPersonnes"
                   class="form-control"
                   min="<?= (int)$minPers ?>"
                   value="<?= (int)$minPers ?>"
                   required>
        </div>

        <h2 class="h5 mt-4">Détail du prix</h2>

        <div class="mb-3">
            <p class="mb-1">Menu : <span id="prixMenu">0.00</span> €</p>
            <p class="mb-1">Remise : <span id="prixRemise">-0.00</span> €</p>
            <p class="mb-1">Livraison : <span id="prixLivraison">0.00</span> €</p>
            <p><strong>Total : <span id="prixTotal">0.00</span> €</strong></p>
        </div>

        <?php if (!empty($menu['conditions'])): ?>
            <div class="alert alert-warning">
                <div class="fw-semibold mb-1">Conditions du menu</div>
                <div><?= nl2br(htmlspecialchars($menu['conditions'])) ?></div>
                <div class="form-check mt-2">
                    <input class="form-check-input" type="checkbox" name="accepte_conditions" id="accepte_conditions" required>
                    <label class="form-check-label" for="accepte_conditions">
                        J'ai lu et j'accepte les conditions de ce menu
                    </label>
                </div>
            </div>
        <?php endif; ?>

        <button type="submit" class="btn btn-primary mb-4">
            Valider la commande
        </button>

    </form>

</main>

<?php require TEMPLATES_PATH . '/partials/footer.php'; ?>

<script>
    window.PRIX_PAR_PERSONNE = <?= json_encode($prixParPersonne) ?>;
    window.MIN_PERSONNES = <?= json_encode($minPers) ?>;
</script>

<?php require TEMPLATES_PATH . '/partials/scripts.php'; ?>

</body>
</html>