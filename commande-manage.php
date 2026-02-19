<?php
// commande-manage.php : espace employé / admin
// Liste commandes + filtre + changement de statut.
// L'employé doit saisir un mode de contact + motif avant modification/annulation.

session_start();
require_once __DIR__ . '/src/db.php';
require_once __DIR__ . '/src/auth.php';

requireRole(['employee', 'admin']);

$user = $_SESSION['user'] ?? null;
$role = $user['role'] ?? 'employee';

$pdo = db();

// Statuts autorisés
$allowedStatus = [
        'en_attente',
        'accepte',
        'en_preparation',
        'en_cours_livraison',
        'livre',
        'attente_retour_materiel',
        'terminee',
        'annulee',
];

$statusLabels = [
        'en_attente' => 'en attente',
        'accepte' => 'accepté',
        'en_preparation' => 'en préparation',
        'en_cours_livraison' => 'en cours de livraison',
        'livre' => 'livré',
        'attente_retour_materiel' => 'en attente du retour de matériel',
        'terminee' => 'terminée',
        'annulee' => 'annulée',
];
$menuStats = $pdo->query("
  SELECT m.menu_id, m.titre, COUNT(c.commande_id) AS nb
  FROM menu m
  LEFT JOIN commande c ON c.menu_id = m.menu_id
  GROUP BY m.menu_id, m.titre
  ORDER BY m.titre
")->fetchAll();

$whereParts = [];
$params = [];

$apply = $_GET['apply'] ?? ''; // "statut" ou "menu"

// Valeurs récupérées (pour garder selected)
$statutFiltre = trim($_GET['statut'] ?? '');
$menuFiltre   = (int)($_GET['menu_id'] ?? 0);

// Appliquer un seul filtre selon le bouton
if ($apply === 'statut') {
    if ($statutFiltre !== '' && in_array($statutFiltre, $allowedStatus, true)) {
        $whereParts[] = "c.statut = ?";
        $params[] = $statutFiltre;
    }
    // important : ignorer menu même si présent dans l'URL
    $menuFiltre = 0;
}

if ($apply === 'menu') {
    if ($menuFiltre > 0) {
        $whereParts[] = "c.menu_id = ?";
        $params[] = $menuFiltre;
    }
    // important : ignorer statut même si présent dans l'URL
    $statutFiltre = '';
}

$whereSql = $whereParts ? ('WHERE ' . implode(' AND ', $whereParts)) : '';

// Récupérer commandes
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
        c.pret_materiel,
        c.restitution_materiel,
        c.statut,
        u.email AS client_email,
        m.titre AS menu_titre
    FROM commande c
    JOIN utilisateur u ON u.utilisateur_id = c.utilisateur_id
    JOIN menu m ON m.menu_id = c.menu_id
    $whereSql
    ORDER BY c.date_commande DESC
");
$stmt->execute($params);
$commandes = $stmt->fetchAll();
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Gestion des commandes</title>
</head>
<body>

<h1>Gestion des commandes</h1>
<p><a href="index.php">Accueil</a></p>

<?php if ($role === 'admin'): ?>
    <p><a href="admin.php">← Retour à l'espace Gestion</a></p>
<?php endif; ?>

<form method="get" style="margin: 12px 0;">
    <label>Filtrer par statut :</label>
    <select name="statut">
        <option value="">-- Tous --</option>
        <?php foreach ($allowedStatus as $s): ?>
            <option value="<?php echo htmlspecialchars($s); ?>" <?php echo ($statutFiltre === $s) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($statusLabels[$s] ?? $s); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <button type="submit" name="apply" value="statut">Filtrer</button>

    <label style="margin-left:10px;">Filtrer par menu :</label>
    <select name="menu_id">
        <option value="0">-- Tous --</option>
        <?php foreach ($menuStats as $ms): ?>
            <option value="<?php echo (int)$ms['menu_id']; ?>" <?php echo ($menuFiltre === (int)$ms['menu_id']) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($ms['titre']); ?> (<?php echo (int)$ms['nb']; ?>)
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

        <?php foreach ($commandes as $c): ?>
            <?php
            $nb = (int)$c['nombre_personne'];
            $prixPers = (float)$c['prix_menu'];
            $liv = ($c['prix_livraison'] !== null) ? (float)$c['prix_livraison'] : 0.0;
            $remise = ($c['remise'] !== null) ? (float)$c['remise'] : 0.0;

            $sousTotal = $nb * $prixPers;
            $total = ($sousTotal - $remise) + $liv;

            $pret = ((int)$c['pret_materiel'] === 1) ? 'Oui' : 'Non';
            $rest = ((int)$c['restitution_materiel'] === 1) ? 'Oui' : 'Non';

            $livTxt = trim(($c['ville_livraison'] ?? '') . ' - ' . ($c['adresse_livraison'] ?? ''));
            if ($livTxt === '-' || $livTxt === '') $livTxt = '-';
            ?>
            <tr>
                <td><?php echo htmlspecialchars($c['numero_commande']); ?></td>
                <td><?php echo htmlspecialchars($c['client_email']); ?></td>
                <td><?php echo htmlspecialchars($c['menu_titre']); ?></td>
                <td><?php echo $nb; ?></td>
                <td><?php echo htmlspecialchars($c['date_commande']); ?></td>

                <td>
                    <?php echo htmlspecialchars((string)$c['date_prestation']); ?>
                </td>

                <td>
                    <?php echo htmlspecialchars((string)$c['date_livraison']); ?>
                    <?php echo htmlspecialchars(' ' . ($c['heure_livraison'] ?? '')); ?>
                    <br>
                    <small><?php echo htmlspecialchars($livTxt); ?></small>
                </td>

                <td><?php echo number_format($prixPers, 2, ',', ' '); ?> €</td>
                <td><strong><?php echo number_format($sousTotal, 2, ',', ' '); ?> €</strong></td>
                <td><?php echo number_format($remise, 2, ',', ' '); ?> €</td>
                <td><?php echo number_format($liv, 2, ',', ' '); ?> €</td>
                <td><strong><?php echo number_format($total, 2, ',', ' '); ?> €</strong></td>

                <td><?php echo "Prêt: $pret / Restitution: $rest"; ?></td>
                <td><?php echo htmlspecialchars($statusLabels[$c['statut']] ?? $c['statut']); ?></td>

                <td>
                    <?php
                    // Statuts modifiables
                    $opts = $allowedStatus;

                    // Si déjà "terminee" : on ne change plus
                    $locked = in_array($c['statut'], ['terminee'], true);
                    ?>

                    <?php if (!$locked): ?>
                        <form method="post" action="commande-update-statut.php" style="margin-bottom:6px;">
                            <input type="hidden" name="commande_id" value="<?php echo (int)$c['commande_id']; ?>">

                            <label>Statut :</label><br>
                            <select name="statut" required>
                                <?php foreach ($opts as $s): ?>
                                    <option value="<?php echo $s; ?>" <?php echo ($c['statut'] === $s) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($statusLabels[$s] ?? $s); ?>
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
                            <textarea name="motif" rows="2" cols="22" placeholder="Ex: client contacté, confirmation..." ></textarea>
                            <br>

                            <button type="submit">Mettre à jour</button>
                        </form>

                    <?php else: ?>
                        <small>Commande terminée </small>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>

        </tbody>
    </table>

<?php endif; ?>

<p style="margin-top:10px;color:#555">
    Note Employé : si vous souhaitez annuler une commande client, veuillez contacter le client puis saisir le mode de contact utilisé ainsi que le motif d'annulation.
</p>

</body>
</html>
