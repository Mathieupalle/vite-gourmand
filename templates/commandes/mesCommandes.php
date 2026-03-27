<?php require TEMPLATES_PATH . '/partials/head.php'; ?>

<html lang="fr">
<body class="bg-light">

<?php require TEMPLATES_PATH . '/partials/header.php'; ?>

<main class="container my-4">
    <div class="d-flex flex-column flex-md-row align-items-md-end justify-content-between gap-2">
        <h1 class="h3 mb-3">Mes commandes</h1>
        <a href="<?= BASE_URL ?>/home" class="btn btn-sm btn-outline-secondary mb-3">Retour accueil</a>
    </div>

    <?php if (empty($commandes)): ?>
        <div class="alert alert-info">
            Aucune commande.
        </div>
    <?php else: ?>

        <?php foreach ($commandes as $c): ?>

            <?php
            // Préparation des données
            $commandeId = (int)$c['commande_id'];
            $statut = (string)$c['statut'];
            $label = $statusLabels[$statut] ?? $statut;

            $prixMenu = (float)$c['prix_menu'];
            $prixLivraison = (float)($c['prix_livraison'] ?? 0);
            $remise = (float)($c['remise'] ?? 0);
            $total = $prixMenu - $remise + $prixLivraison;

            $canEditOrCancel = ($statut === 'en_attente');
            ?>

            <div class="card shadow-sm mb-4">
                <div class="card-body">

                    <h5 class="card-title">
                        Commande <?= htmlspecialchars((string)$c['numero_commande']) ?>
                        <span class="badge text-bg-light border">
                            <?= htmlspecialchars($label) ?>
                        </span>
                    </h5>

                    <hr>

                    <p class="mb-1"><strong>Menu :</strong> <?= htmlspecialchars((string)$c['menu_titre']) ?></p>
                    <p class="mb-1"><strong>Date prestation :</strong> <?= htmlspecialchars((string)$c['date_prestation']) ?></p>
                    <p class="mb-1"><strong>Date livraison :</strong> <?= htmlspecialchars((string)$c['date_livraison']) ?></p>
                    <p class="mb-1"><strong>Heure livraison :</strong> <?= htmlspecialchars((string)($c['heure_livraison'] ?? '')) ?></p>
                    <p class="mb-1">
                        <strong>Adresse :</strong>
                        <?= htmlspecialchars((string)$c['adresse_livraison']) ?>,
                        <?= htmlspecialchars((string)$c['ville_livraison']) ?>
                    </p>

                    <p class="mb-1"><strong>Personnes :</strong> <?= (int)$c['nombre_personne'] ?></p>
                    <p class="mb-1"><strong>Prix menu :</strong> <?= number_format($prixMenu, 2, ',', ' ') ?> €</p>
                    <p class="mb-1"><strong>Livraison :</strong> <?= number_format($prixLivraison, 2, ',', ' ') ?> €</p>
                    <p class="mb-1"><strong>Remise :</strong> <?= number_format($remise, 2, ',', ' ') ?> €</p>
                    <p class="fw-bold">Total : <?= number_format($total, 2, ',', ' ') ?> €</p>

                    <?php if ($canEditOrCancel): ?>
                        <div class="mt-3 d-flex gap-2">
                            <a href="<?= BASE_URL ?>/commandeEdit?id=<?= $commandeId ?>"
                               class="btn btn-sm btn-primary">
                                Modifier
                            </a>

                            <a href="<?= BASE_URL ?>/commandeAnnuler?id=<?= $commandeId ?>"
                               class="btn btn-sm btn-outline-danger"
                               onclick="return confirm('Annuler cette commande ?');">
                                Annuler
                            </a>
                        </div>
                    <?php endif; ?>

                    <hr>

                    <h6>Suivi</h6>

                    <?php $suivis = $suivisByCommande[$commandeId] ?? []; ?>

                    <?php if (empty($suivis)): ?>
                        <p class="text-muted small">Aucun suivi.</p>
                    <?php else: ?>
                        <ul class="small">
                            <?php foreach ($suivis as $s): ?>
                                <?php
                                $sStatut = (string)$s['statut'];
                                $sLabel = $statusLabels[$sStatut] ?? $sStatut;
                                ?>
                                <li>
                                    <?= htmlspecialchars($sLabel) ?>
                                    — <?= htmlspecialchars((string)$s['date_modif']) ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>

                </div>
            </div>

        <?php endforeach; ?>

    <?php endif; ?>

</main>

<?php require TEMPLATES_PATH . '/partials/footer.php'; ?>
<?php require TEMPLATES_PATH . '/partials/scripts.php'; ?>

</body>
</html>