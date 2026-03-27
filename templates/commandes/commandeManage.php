<?php
$allowedStatus = $allowedStatus ?? [];
$menuStats = $menuStats ?? [];
$statutFiltre = $statutFiltre ?? '';
$menuFiltre = $menuFiltre ?? 0;
?>

<?php require TEMPLATES_PATH . '/partials/head.php'; ?>

<html lang="fr">
<body class="bg-light">

<?php require TEMPLATES_PATH . '/partials/header.php'; ?>

<main class="container my-4">
    <div class="d-flex flex-column flex-md-row align-items-md-end justify-content-between gap-2">
        <h1 class="h3 mb-3">Gestion des commandes</h1>

        <p class="mb-3">
            <a href="<?= BASE_URL ?>/home" class="btn btn-sm btn-outline-secondary">Accueil</a>
            <a href="<?= BASE_URL ?>/admin" class="btn btn-sm btn-outline-secondary">Retour</a>
        </p>
    </div>

    <form method="get" class="mb-3 row g-2 align-items-end">
        <div class="col-auto">
            <label for="statut" class="form-label">Filtrer par statut :</label>
            <select name="statut" id="statut" class="form-select">
                <option value="">-- Tous --</option>
                <?php foreach ($allowedStatus as $s): ?>
                    <option value="<?= htmlspecialchars($s) ?>" <?= ($statutFiltre === $s) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($statusLabels[$s] ?? $s) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-auto">
            <button type="submit" name="apply" value="statut" class="btn btn-primary">Filtrer</button>
            <button type="submit" name="statut" value="" class="btn btn-outline-secondary">Réinitialiser</button>
        </div>

        <div class="col-auto">
            <label for="menu_id" class="form-label">Filtrer par menu :</label>
            <select name="menu_id" id="menu_id" class="form-select">
                <option value="0">-- Tous --</option>
                <?php foreach ($menuStats as $ms): ?>
                    <option value="<?= (int)$ms['menu_id'] ?>" <?= ($menuFiltre === (int)$ms['menu_id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars((string)$ms['titre']) ?> (<?= (int)$ms['nb'] ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-auto">
            <button type="submit" name="apply" value="menu" class="btn btn-primary">Filtrer</button>
            <button type="submit" name="menu_id" value="0" class="btn btn-outline-secondary">Réinitialiser</button>
        </div>
    </form>

    <?php if (empty($commandes)): ?>
        <div class="alert alert-info">Aucune commande.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped table-bordered align-middle">
                <thead class="table-light">
                <tr>
                    <th>N°</th>
                    <th>Client</th>
                    <th>Menu</th>
                    <th>Nb pers.</th>
                    <th>Date commande</th>
                    <th>Prestation</th>
                    <th>Livraison</th>
                    <th>Prix/pers</th>
                    <th>Sous-total</th>
                    <th>Remise</th>
                    <th>Livraison €</th>
                    <th>Total</th>
                    <th>Matériel</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($commandes as $c):
                    $nb = (int)($c['nombre_personne'] ?? 0);
                    $prixPers = (float)($c['prix_par_personne'] ?? 0.0);
                    $sousTotal = (float)($c['prix_menu'] ?? 0.0);
                    $liv = ($c['prix_livraison'] ?? 0.0);
                    $remise = ($c['remise'] ?? 0.0);
                    $total = ($sousTotal - $remise) + $liv;

                    $pret = ((int)($c['pret_materiel'] ?? 0) === 1) ? 'Oui' : 'Non';
                    $rest = ((int)($c['restitution_materiel'] ?? 0) === 1) ? 'Oui' : 'Non';

                    $locked = ($c['statut'] ?? '') === 'terminee';
                    ?>
                    <tr>
                        <td><?= htmlspecialchars((string)($c['numero_commande'] ?? '')) ?></td>
                        <td><?= htmlspecialchars((string)($c['client_email'] ?? '')) ?></td>
                        <td><?= htmlspecialchars((string)($c['menu_titre'] ?? '')) ?></td>
                        <td><?= $nb ?></td>
                        <td><?= htmlspecialchars((string)($c['date_commande'] ?? '')) ?></td>
                        <td><?= htmlspecialchars((string)($c['date_prestation'] ?? '')) ?></td>
                        <td>
                            <?= htmlspecialchars(($c['date_livraison'] ?? '') . ' ' . ($c['heure_livraison'] ?? '')) ?>
                            <small><?= htmlspecialchars(($c['adresse_livraison'] ?? '') . ', ' . ($c['ville_livraison'] ?? '')) ?></small>
                        </td>
                        <td><?= number_format($prixPers, 2, ',', ' ') ?> €</td>
                        <td><strong><?= number_format($sousTotal, 2, ',', ' ') ?> €</strong></td>
                        <td><?= number_format($remise, 2, ',', ' ') ?> €</td>
                        <td><?= number_format($liv, 2, ',', ' ') ?> €</td>
                        <td><strong><?= number_format($total, 2, ',', ' ') ?> €</strong></td>
                        <td><?= "Prêt: $pret / Restitution: $rest" ?></td>
                        <td><?= htmlspecialchars($statusLabels[(string)($c['statut'] ?? '')] ?? (string)($c['statut'] ?? '')) ?></td>
                        <td>
                            <?php if (!$locked): ?>
                                <form method="post" action="<?= BASE_URL ?>/commandeUpdateStatut" class="mb-2">
                                    <input type="hidden" name="commande_id" value="<?= (int)($c['commande_id'] ?? 0) ?>">
                                    <select name="statut" id="statut" class="form-select form-select-sm mb-1" required>
                                        <?php foreach ($allowedStatus as $s): ?>
                                            <option value="<?= htmlspecialchars($s) ?>" <?= (($c['statut'] ?? '') === $s) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($statusLabels[$s] ?? $s) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>

                                    <label for="mode_contact" class="form-label small mb-0">Contact :</label>
                                    <select name="mode_contact" id="mode_contact" class="form-select form-select-sm mb-1">
                                        <option value="">--</option>
                                        <option value="telephone">Téléphone</option>
                                        <option value="mail">Mail</option>
                                    </select>

                                    <label for="motif" class="form-label small mb-0">Motif :</label>
                                    <textarea name="motif" id="motif" rows="2" class="form-control form-control-sm mb-1"></textarea>

                                    <button type="submit" class="btn btn-sm btn-primary w-100">Mettre à jour</button>
                                </form>
                            <?php else: ?>
                                <small>Commande terminée</small>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

</main>

<?php require TEMPLATES_PATH . '/partials/footer.php'; ?>
<?php require TEMPLATES_PATH . '/partials/scripts.php'; ?>

</body>
</html>