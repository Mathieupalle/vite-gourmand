<!doctype html>
<html lang="fr">
<?php require TEMPLATES_PATH . '/partials/head.php'; ?>

<body class="bg-white">
<?php require TEMPLATES_PATH . '/partials/header.php'; ?>

    <h1>Mes commandes</h1>
    <p><a href="<?= BASE_URL ?>/home">Retour accueil</a></p>
    <hr>

<?php if (!$commandes): ?>
    <p>Aucune commande.</p>
<?php else: ?>
    <?php foreach ($commandes as $c): ?>
        <?php
        $commandeId = (int)$c['commande_id'];
        $statut = (string)$c['statut'];
        $label = $statusLabels[$statut] ?? $statut;

        $prixMenu = (float)$c['prix_menu'];
        $prixLivraison = (float)($c['prix_livraison'] ?? 0);
        $remise = (float)($c['remise'] ?? 0);
        $total = $prixMenu - $remise + $prixLivraison;

        $canEditOrCancel = ($statut === \App\Entity\CommandeStatus::EN_ATTENTE);
        ?>
        <div style="border:1px solid #ccc; padding:12px; margin:12px 0;">
            <h3>Commande <?= htmlspecialchars((string)$c['numero_commande']) ?> — <?= htmlspecialchars($label) ?></h3>

            <p><b>Menu :</b> <?= htmlspecialchars((string)$c['menu_titre']) ?></p>
            <p><b>Date prestation :</b> <?= htmlspecialchars((string)$c['date_prestation']) ?></p>
            <p><b>Date livraison :</b> <?= htmlspecialchars((string)$c['date_livraison']) ?></p>
            <p><b>Heure livraison :</b> <?= htmlspecialchars((string)($c['heure_livraison'] ?? '')) ?></p>
            <p><b>Adresse livraison :</b> <?= htmlspecialchars((string)$c['adresse_livraison']) ?>, <?= htmlspecialchars((string)$c['ville_livraison']) ?></p>

            <p><b>Personnes :</b> <?= (int)$c['nombre_personne'] ?></p>
            <p><b>Prix menu :</b> <?= number_format($prixMenu, 2, ',', ' ') ?> €</p>
            <p><b>Livraison :</b> <?= number_format($prixLivraison, 2, ',', ' ') ?> €</p>
            <p><b>Remise :</b> <?= number_format($remise, 2, ',', ' ') ?> €</p>
            <p><b>Total :</b> <b><?= number_format($total, 2, ',', ' ') ?> €</b></p>

            <?php if ($canEditOrCancel): ?>
                <p>
                    <a href="<?= BASE_URL ?>/commandeEdit?id=<?= $commandeId ?>">Modifier</a>
                    |
                    <a href="<?= BASE_URL ?>/commandeAnnuler?id=<?= $commandeId ?>"
                       onclick="return confirm('Annuler cette commande ?');">
                        Annuler
                    </a>
                </p>
            <?php endif; ?>

            <hr>

            <h4>Suivi</h4>
            <?php $suivis = $suivisByCommande[$commandeId] ?? []; ?>
            <?php if (!$suivis): ?>
                <p>Aucun suivi.</p>
            <?php else: ?>
                <ul>
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
    <?php endforeach; ?>
<?php endif; ?>
<?php require TEMPLATES_PATH . '/partials/footer.php'; ?>
<?php require TEMPLATES_PATH . '/partials/scripts.php'; ?>
</body>
</html>
