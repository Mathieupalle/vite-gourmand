<?php
// horaire-manage.php : gestion des horaires (employee / admin)

session_start();
require_once __DIR__ . '/src/db.php';
require_once __DIR__ . '/src/auth.php';

requireRole(['employee', 'admin']);

$pdo = db();
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Tableau horaires [jour] [ouverture/fermeture]
    $horaires = $_POST['horaires'] ?? [];

    foreach ($horaires as $horaireId => $values) {
        $ouverture = trim($values['ouverture'] ?? '');
        $fermeture = trim($values['fermeture'] ?? '');

        // Si vide -> fermé (NULL/NULL)
        if ($ouverture === '' || $fermeture === '') {
            $stmt = $pdo->prepare("UPDATE horaire SET heure_ouverture = NULL, heure_fermeture = NULL WHERE horaire_id = ?");
            $stmt->execute([(int)$horaireId]);
        } else {
            $stmt = $pdo->prepare("UPDATE horaire SET heure_ouverture = ?, heure_fermeture = ? WHERE horaire_id = ?");
            $stmt->execute([$ouverture, $fermeture, (int)$horaireId]);
        }
    }

    $success = "Horaires mis à jour.";
}

$rows = $pdo->query("SELECT horaire_id, jour, heure_ouverture, heure_fermeture FROM horaire ORDER BY horaire_id ASC")->fetchAll();
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Gestion des horaires</title>
</head>
<body>

<h1>Gestion des horaires</h1>
<p><a href="index.php">Accueil</a></p>
<p><a href="admin.php">← Retour à l’espace Gestion</a></p>

<?php if ($success): ?>
    <p style="color:green"><?php echo htmlspecialchars($success); ?></p>
<?php endif; ?>

<form method="post">
    <table border="1" cellpadding="8" cellspacing="0">
        <thead>
        <tr>
            <th>Jour</th>
            <th>Ouverture</th>
            <th>Fermeture</th>
            <th>Note</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
            <?php
            $open = $r['heure_ouverture'] ? substr($r['heure_ouverture'],0,5) : '';
            $close = $r['heure_fermeture'] ? substr($r['heure_fermeture'],0,5) : '';
            ?>
            <tr>
                <td><?php echo htmlspecialchars($r['jour']); ?></td>
                <td>
                    <input type="time" name="horaires[<?php echo (int)$r['horaire_id']; ?>][ouverture]" value="<?php echo htmlspecialchars($open); ?>">
                </td>
                <td>
                    <input type="time" name="horaires[<?php echo (int)$r['horaire_id']; ?>][fermeture]" value="<?php echo htmlspecialchars($close); ?>">
                </td>
                <td>Si vide : fermé</td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <br>
    <button type="submit">Enregistrer</button>
</form>

</body>
</html>
