<?php
// plat-create.php : création d'un plat + sélection allergènes

session_start();
require_once __DIR__ . '/src/db.php';
require_once __DIR__ . '/src/auth.php';

requireRole(['employee', 'admin']);

$pdo = db();
$errors = [];
$success = null;

// Récupérer tous les allergènes
$allergenes = $pdo->query("SELECT allergene_id, libelle FROM allergene ORDER BY libelle")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $titre = trim($_POST['titre_plat'] ?? '');
    $categorie = trim($_POST['categorie'] ?? '');
    $allergenesSelectionnes = $_POST['allergenes'] ?? [];

    if ($titre === '') {
        $errors[] = "Titre obligatoire.";
    }

    if (!$errors) {
        try {
            $pdo->beginTransaction();

            // 1) Insérer le plat
            $stmt = $pdo->prepare("INSERT INTO plat (titre_plat, categorie) VALUES (?, ?)");
            $stmt->execute([$titre, $categorie]);
            $platId = (int)$pdo->lastInsertId();

            // 2) Insérer les allergènes (table de liaison plat_allergene)
            if (!empty($allergenesSelectionnes)) {
                $stmtAll = $pdo->prepare("INSERT INTO plat_allergene (plat_id, allergene_id) VALUES (?, ?)");
                foreach ($allergenesSelectionnes as $aid) {
                    $stmtAll->execute([$platId, (int)$aid]);
                }
            }

            $pdo->commit();
            $success = "Plat créé avec succès.";

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
    <title>Créer un plat</title>
</head>
<body>
<h1>Créer un plat</h1>
<p><a href="plat-manage.php">← Gestion des plats</a></p>

<?php if ($success): ?>
    <p style="color:green"><?php echo htmlspecialchars($success); ?></p>
<?php endif; ?>

<?php foreach ($errors as $e): ?>
    <p style="color:red"><?php echo htmlspecialchars($e); ?></p>
<?php endforeach; ?>

<form method="post">
    <label>Titre du plat :</label><br>
    <input name="titre_plat" required><br><br>

    <fieldset>
        <legend>Allergènes (optionnel)</legend>
        <?php if (!$allergenes): ?>
            <p>Aucun allergène en base.</p>
        <?php else: ?>
            <?php foreach ($allergenes as $a): ?>
                <label>
                    <input type="checkbox" name="allergenes[]" value="<?php echo (int)$a['allergene_id']; ?>">
                    <?php echo htmlspecialchars($a['libelle']); ?>
                </label><br>
            <?php endforeach; ?>
        <?php endif; ?>
    </fieldset>

    <br>
    <button type="submit">Créer</button>
</form>
</body>
</html>
