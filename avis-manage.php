<?php
// avis-manage.php : employé/admin valident ou refusent les avis.
// Seuls les avis "valide" seront affichés sur la page d'accueil.

session_start();
require_once __DIR__ . '/src/db.php';
require_once __DIR__ . '/src/auth.php';

requireRole(['employee', 'admin']);

$pdo = db();

// Mise à jour statut (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $avisId = (int)($_POST['avis_id'] ?? 0);
    $newStatut = trim($_POST['statut'] ?? '');

    $allowed = ['valide', 'refuse', 'en_attente'];

    if ($avisId > 0 && in_array($newStatut, $allowed, true)) {
        $stmt = $pdo->prepare("UPDATE avis SET statut = ? WHERE avis_id = ?");
        $stmt->execute([$newStatut, $avisId]);
    }

    header("Location: avis-manage.php");
    exit;
}

// Liste des avis
$avis = $pdo->query("
    SELECT a.avis_id, a.note, a.description, a.statut, a.date_avis,
           u.email AS client_email
    FROM avis a
    LEFT JOIN utilisateur u ON u.utilisateur_id = a.utilisateur_id
    ORDER BY a.date_avis DESC
")->fetchAll();
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Gestion des avis</title>
</head>
<body>

<h1>Gestion des avis</h1>
<p><a href="index.php">Accueil</a></p>
<p><a href="admin.php">← Retour à l’espace Gestion</a></p>

<?php if (!$avis): ?>
    <p>Aucun avis.</p>
<?php else: ?>

    <table border="1" cellpadding="8" cellspacing="0">
        <thead>
        <tr>
            <th>Client</th>
            <th>Note</th>
            <th>Commentaire</th>
            <th>Statut</th>
            <th>Date</th>
            <th>Action</th>
        </tr>
        </thead>
        <tbody>

        <?php foreach ($avis as $a): ?>
            <tr>
                <td><?php echo htmlspecialchars($a['client_email'] ?? '-'); ?></td>
                <td><?php echo (int)$a['note']; ?>/5</td>
                <td><?php echo nl2br(htmlspecialchars($a['description'])); ?></td>
                <td><?php echo htmlspecialchars($a['statut']); ?></td>
                <td><?php echo htmlspecialchars($a['date_avis']); ?></td>
                <td>
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="avis_id" value="<?php echo (int)$a['avis_id']; ?>">
                        <select name="statut">
                            <?php foreach (['en_attente','valide','refuse'] as $s): ?>
                                <option value="<?php echo $s; ?>" <?php echo ($a['statut'] === $s) ? 'selected' : ''; ?>>
                                    <?php echo $s; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit">Mettre à jour</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>

        </tbody>
    </table>

<?php endif; ?>

</body>
</html>
