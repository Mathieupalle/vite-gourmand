<!doctype html>
<html lang="fr">
<?php require TEMPLATES_PATH . '/partials/head.php'; ?>

<body class="bg-white">
<?php require TEMPLATES_PATH . '/partials/header.php'; ?>

<h1>Gestion des commandes</h1>
<p><a href="<?= BASE_URL ?>/home">Accueil</a></p>
<?php if ($role === 'admin'): ?>
    <p><a href="<?= BASE_URL ?>/admin">← Retour admin</a></p>
<?php endif; ?>

<form method="get" style="margin: 12px 0;">
    <label>Filtrer par statut :</label>
    <select name="statut">
        <option value="">-- Tous --</option>
        <?php foreach ($allowedStatus as $s): ?>
            <option value="<?= htmlspecialchars($s) ?>" <?= ($statutFiltre === $s) ? 'selected' : '' ?>>
                <?= htmlspecialchars($statusLabels[$s] ?? $s) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <button type="submit" name="apply" value="statut">Filtrer</button>

    <label style="margin-left:10px;">Filtrer par menu :</label>
    <select name="menu_id">
        <option value="0">-- Tous --</option>
        <?php foreach ($menuStats as $ms): ?>
            <option value="<?= (int)$ms['menu_id'] ?>" <?= ($menuFiltre === (int)$ms['menu_id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars((string)$ms['titre']) ?> (<?= (int)$ms['nb'] ?>)
            </option>
        <?php endforeach; ?>
    </select>
    <button type="submit" name="apply" value="menu">Filtrer</button>
</form>

<?php if (!$commandes): ?>
    <p>Aucune commande.</p>
<?php else: ?>
    <table border="1" cellpadding="8" cellspacing="0">
        <thead>
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
            $nb = (int)$c['nombre_personne'];
            $prixPers = (float)$c['prix_par_personne'];
            $sousTotal = (float)$c['prix_menu'];
            $liv = ($c['prix_livraison'] !== null) ? (float)$c['prix_livraison'] : 0.0;
            $remise = ($c['remise'] !== null) ? (float)$c['remise'] : 0.0;
            $total = ($sousTotal - $remise) + $liv;
            $pret = ((int)$c['pret_materiel'] === 1) ? 'Oui' : 'Non';
            $rest = ((int)$c['restitution_materiel'] === 1) ? 'Oui' : 'Non';
            $locked = in_array((string)$c['statut'], [CommandeStatus::TERMINEE], true);
            ?>
            <tr>
                <td><?= htmlspecialchars((string)$c['numero_commande']) ?></td>
                <td><?= htmlspecialchars((string)$c['client_email']) ?></td>
                <td><?= htmlspecialchars((string)$c['menu_titre']) ?></td>
                <td><?= (int)$nb ?></td>
                <td><?= htmlspecialchars((string)$c['date_commande']) ?></td>
                <td><?= htmlspecialchars((string)$c['date_prestation']) ?></td>
                <td>
                    <?= htmlspecialchars((string)$c['date_livraison']) ?> <?= htmlspecialchars(' ' . (string)($c['heure_livraison'] ?? '')) ?><br>
                    <small><?= htmlspecialchars((string)$c['adresse_livraison'] . ', ' . (string)$c['ville_livraison']) ?></small>
                </td>
                <td><?= number_format($prixPers, 2, ',', ' ') ?> €</td>
                <td><strong><?= number_format($sousTotal, 2, ',', ' ') ?> €</strong></td>
                <td><?= number_format($remise, 2, ',', ' ') ?> €</td>
                <td><?= number_format($liv, 2, ',', ' ') ?> €</td>
                <td><strong><?= number_format($total, 2, ',', ' ') ?> €</strong></td>
                <td><?= "Prêt: $pret / Restitution: $rest" ?></td>
                <td><?= htmlspecialchars($statusLabels[(string)$c['statut']] ?? (string)$c['statut']) ?></td>
                <td>
                    <?php if (!$locked): ?>
                        <form method="post" action="commandeUpdateStatut">
                            <input type="hidden" name="commande_id" value="<?= (int)$c['commande_id'] ?>">
                            <select name="statut" required>
                                <?php foreach ($allowedStatus as $s): ?>
                                    <option value="<?= htmlspecialchars($s) ?>" <?= ((string)$c['statut'] === $s) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($statusLabels[$s] ?? $s) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <br>
                            <label>Contact :</label><br>
                            <select name="mode_contact">
                                <option value="">--</option>
                                <option value="telephone">Téléphone</option>
                                <option value="mail">Mail</option>
                            </select>
                            <br>
                            <label>Motif :</label><br>
                            <textarea name="motif" rows="2" cols="22"></textarea>
                            <br>
                            <button type="submit">Mettre à jour</button>
                        </form>
                    <?php else: ?>
                        <small>Commande terminée</small>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php require TEMPLATES_PATH . '/partials/footer.php'; ?>
<?php require TEMPLATES_PATH . '/partials/scripts.php'; ?>
</body>
</html>