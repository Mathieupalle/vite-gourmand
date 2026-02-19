<?php
// mes-commandes.php : espace utilisateur -> liste des commandes + actions (modifier/annuler) + suivi

session_start();
require_once __DIR__ . '/src/db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$pdo = db();
$userId = (int)($_SESSION['user']['id'] ?? 0);

if ($userId <= 0) {
    die("Utilisateur invalide.");
}

// Libellés de statuts (affichage plus propre)
$statusLabels = [
        'en_attente' => 'En attente',
        'accepte' => 'Acceptée',
        'en_preparation' => 'En préparation',
        'en_cours_livraison' => 'En cours de livraison',
        'livre' => 'Livrée',
        'attente_retour_materiel' => 'Attente retour matériel',
        'terminee' => 'Terminée',
        'annulee' => 'Annulée',
        'refusee' => 'Refusée',
        'modifiee_par_client' => 'Modifiée par le client'
];

// 1) Récupérer les commandes de l'utilisateur
$stmt = $pdo->prepare("
    SELECT
        c.commande_id,
        c.numero_commande,
        c.date_commande,
        c.date_prestation,
        c.date_livraison,
        c.heure_livraison,
        c.adresse_livraison,
        c.ville_livraison,
        c.nombre_personne,
        c.prix_menu,
        c.prix_livraison,
        c.remise,
        c.distance_km,
        c.pret_materiel,
        c.restitution_materiel,
        c.statut,
        m.titre AS menu_titre,
        m.nombre_personne_minimum
    FROM commande c
    JOIN menu m ON m.menu_id = c.menu_id
    WHERE c.utilisateur_id = ?
    ORDER BY c.date_commande DESC
");
$stmt->execute([$userId]);
$commandes = $stmt->fetchAll();

// 2) Récupérer tout le suivi des commandes de l'utilisateur
$suiviByCommande = [];

if ($commandes) {
    $ids = array_map(fn($c) => (int)$c['commande_id'], $commandes);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    $stmtSuivi = $pdo->prepare("
        SELECT commande_id, statut, date_modif
        FROM commande_suivi
        WHERE commande_id IN ($placeholders)
        ORDER BY date_modif ASC
    ");
    $stmtSuivi->execute($ids);
    $rows = $stmtSuivi->fetchAll();

    foreach ($rows as $r) {
        $cid = (int)$r['commande_id'];
        if (!isset($suiviByCommande[$cid])) {
            $suiviByCommande[$cid] = [];
        }
        $suiviByCommande[$cid][] = $r;
    }
}

// Helpers simples
function euro($n): string {
    return number_format((float)$n, 2, ',', ' ') . ' €';
}
function yn($v): string {
    if ($v === null) return '-';
    return ((int)$v === 1) ? 'Oui' : 'Non';
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Mes commandes</title>
</head>
<body>

<h1>Mes commandes</h1>

<p><a href="index.php">← Retour accueil</a></p>
<hr>

<?php if (!$commandes): ?>
    <p>Vous n'avez pas encore passé de commande.</p>
<?php else: ?>

    <?php foreach ($commandes as $c): ?>
        <?php
        $commandeId = (int)$c['commande_id'];
        $nb = (int)$c['nombre_personne'];
        $prixPers = (float)$c['prix_menu'];
        $liv = ($c['prix_livraison'] !== null) ? (float)$c['prix_livraison'] : 0.0;
        $remise = ($c['remise'] !== null) ? (float)$c['remise'] : 0.0;

        $sousTotal = $nb * $prixPers;
        $total = $sousTotal - $remise + $liv;

        $statut = (string)$c['statut'];
        $statutLabel = $statusLabels[$statut] ?? $statut;

        $isEditable = ($statut === 'en_attente'); // simple : tant que pas acceptée
        ?>
        <div style="border:1px solid #ccc; padding:12px; margin:12px 0;">
            <h3>
                Commande <?php echo htmlspecialchars($c['numero_commande']); ?>
                — <span><?php echo htmlspecialchars($statutLabel); ?></span>
            </h3>

            <p><strong>Menu :</strong> <?php echo htmlspecialchars($c['menu_titre']); ?></p>

            <p>
                <strong>Date commande :</strong> <?php echo htmlspecialchars($c['date_commande']); ?><br>
                <strong>Date prestation :</strong> <?php echo htmlspecialchars($c['date_prestation']); ?><br>
                <strong>Date livraison :</strong> <?php echo htmlspecialchars($c['date_livraison']); ?><br>
                <strong>Heure livraison :</strong> <?php echo htmlspecialchars($c['heure_livraison'] ?? '-'); ?><br>
                <strong>Adresse livraison :</strong> <?php echo htmlspecialchars($c['adresse_livraison']); ?><br>
                <strong>Ville livraison :</strong> <?php echo htmlspecialchars($c['ville_livraison']); ?><br>
                <strong>Distance :</strong> <?php echo htmlspecialchars((string)($c['distance_km'] ?? 0)); ?> km
            </p>

            <p>
                <strong>Nombre de personnes :</strong> <?php echo $nb; ?>
                (minimum : <?php echo (int)$c['nombre_personne_minimum']; ?>)
            </p>

            <p>
                <strong>Prix / pers :</strong> <?php echo euro($prixPers); ?><br>
                <strong>Sous-total :</strong> <?php echo euro($sousTotal); ?><br>
                <strong>Remise :</strong> -<?php echo euro($remise); ?><br>
                <strong>Livraison :</strong> <?php echo euro($liv); ?><br>
                <strong>Total :</strong> <?php echo euro($total); ?>
            </p>

            <p>
                <strong>Matériel :</strong>
                Prêt : <?php echo yn($c['pret_materiel']); ?> /
                Restitution : <?php echo yn($c['restitution_materiel']); ?>
            </p>

            <!-- Actions utilisateur -->
            <p>
                <?php if ($isEditable): ?>
                <a href="commande-edit.php?id=<?php echo $commandeId; ?>">Modifier</a>

                |
            <form method="post" action="commande-annuler.php" style="display:inline;">
                <input type="hidden" name="commande_id" value="<?php echo $commandeId; ?>">
                <button type="submit" onclick="return confirm('Annuler cette commande ?');">Annuler</button>
            </form>
            <?php else: ?>
                <em>Modification / annulation indisponibles (commande déjà traitée).</em>
            <?php endif; ?>
            </p>

            <!-- Avis : seulement si terminée -->
            <?php if ($statut === 'terminee'): ?>
                <p>
                    <a href="avis-create.php?commande_id=<?php echo $commandeId; ?>">
                        Donner un avis
                    </a>
                </p>
            <?php endif; ?>

            <!-- Suivi de la commande -->
            <div style="margin-top:10px; padding-top:10px; border-top:1px dashed #aaa;">
                <strong>Suivi de la commande :</strong>

                <?php
                $suivi = $suiviByCommande[$commandeId] ?? [];
                ?>

                <?php if (!$suivi): ?>
                    <p>Aucun suivi enregistré.</p>
                <?php else: ?>
                    <ul>
                        <?php foreach ($suivi as $s): ?>
                            <?php
                            $st = (string)$s['statut'];
                            $label = $statusLabels[$st] ?? $st;
                            ?>
                            <li>
                                <?php echo htmlspecialchars($label); ?>
                                — <?php echo htmlspecialchars($s['date_modif']); ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>

        </div>
    <?php endforeach; ?>

<?php endif; ?>

</body>
</html>
