<?php
// plat-edit.php : modifier un plat (employee/admin)
// Modifie le titre du plat + ses allergènes (table plat_allergene)

session_start();
require_once __DIR__ . '/src/db.php';
require_once __DIR__ . '/src/auth.php';

requireRole(['employee','admin']);

$pdo = db();

$platId = (int)($_GET['id'] ?? 0);
if ($platId <= 0) {
    http_response_code(400);
    die("ID invalide.");
}

// 1) Récupérer le plat
$stmt = $pdo->prepare("SELECT plat_id, titre_plat FROM plat WHERE plat_id = ?");
$stmt->execute([$platId]);
$plat = $stmt->fetch();

if (!$plat) {
    http_response_code(404);
    die("Plat introuvable.");
}

// 2) Récupérer les allergènes (pour afficher les cases)
$allergenes = $pdo->query("SELECT allergene_id, libelle FROM allergene ORDER BY libelle")->fetchAll();

// 3) Récupérer les allergènes déjà cochés pour ce plat
$stmt = $pdo->prepare("SELECT allergene_id FROM plat_allergene WHERE plat_id = ?");
$stmt->execute([$platId]);
$selectedIds = array_map('intval', array_column($stmt->fetchAll(), 'allergene_id'));

$errors = [];
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $titre = trim($_POST['titre_plat'] ?? '');
    $selected = $_POST['allergenes'] ?? [];

    if ($titre === '') {
        $errors[] = "Titre obligatoire.";
    }

    if (!$errors) {
        try {
            $pdo->beginTransaction();

            // A) Modifier le titre
            $upd = $pdo->prepare("UPDATE plat SET titre_plat = ? WHERE plat_id = ?");
            $upd->execute([$titre, $platId]);

            // B) Mettre à jour les allergènes (on supprime puis on réinsère)
            $pdo->prepare("DELETE FROM plat_allergene WHERE plat_id = ?")->execute([$platId]);

            if (!empty($selected)) {
                $ins = $pdo->prepare("INSERT INTO plat_allergene (plat_id, allergene_id) VALUES (?, ?)");
                foreach ($selected as $aid) {
                    $ins->execute([$platId, (int)$aid]);
                }
            }

            $pdo->commit();

            $success = "Plat modifié.";

            // Recharger
            $plat['titre_plat'] = $titre;
            $selectedIds = array_map('intval', $selected);

        } catch (Throwable $e) {
            $pdo->rollBack();
            $errors[] = "Erreur : " . $e->getMessage();
        }
    }
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Modifier un plat</title>
</head>
<body>

<h1>Modifier un plat</h1>
<p><a href="plat-manage.php">← Gestion des plats</a></p>

<?php if ($success): ?>
    <p style="color:green"><?php echo htmlspecialchars($success); ?></p>
<?php endif; ?>

<?php foreach ($errors as $e): ?>
    <p style="color:red"><?php echo htmlspecialchars($e); ?></p>
<?php endforeach; ?>

<form method="post">
    <label>Titre du plat :</label><br>
    <input name="titre_plat" value="<?php echo htmlspecialchars($plat['titre_plat']); ?>" required><br><br>

    <fieldset>
        <legend>Allergènes (optionnel)</legend>
        <?php if (!$allergenes): ?>
            <p>Aucun allergène en base.</p>
        <?php else: ?>
            <?php foreach ($allergenes as $a): ?>
                <?php $aid = (int)$a['allergene_id']; ?>
                <label>
                    <input type="checkbox" name="allergenes[]" value="<?php echo $aid; ?>"
                            <?php echo in_array($aid, $selectedIds, true) ? 'checked' : ''; ?>>
                    <?php echo htmlspecialchars($a['libelle']); ?>
                </label><br>
            <?php endforeach; ?>
        <?php endif; ?>
    </fieldset>

    <br>
    <button type="submit">Enregistrer</button>
</form>

</body>
</html>
