<?php
// menu-edit-plats.php : page protégée (employee / admin).
// Permet d’associer des plats à un menu (table de liaison menu_plat).

session_start();
require_once __DIR__ . '/src/db.php';
require_once __DIR__ . '/src/auth.php';

requireRole(['employee', 'admin']);

$pdo = db();

$menuId = isset($_GET['menu_id']) ? (int)$_GET['menu_id'] : 0;

// 1) Liste des menus
$menus = $pdo->query("SELECT menu_id, titre FROM menu ORDER BY titre")->fetchAll();

// 2) Liste des plats
$plats = $pdo->query("SELECT plat_id, titre_plat FROM plat ORDER BY titre_plat")->fetchAll();

// 3) Si un menu est sélectionné, récupérer les plats déjà liés
$selectedPlatIds = [];
if ($menuId > 0) {
    $stmt = $pdo->prepare("SELECT plat_id FROM menu_plat WHERE menu_id = ?");
    $stmt->execute([$menuId]);
    $selectedPlatIds = array_map('intval', array_column($stmt->fetchAll(), 'plat_id'));
}

// 4) Sauvegarde
$success = null;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $menuId = (int)($_POST['menu_id'] ?? 0);
    $newPlatIds = $_POST['plats'] ?? [];

    $newPlatIds = array_values(array_filter(array_map('intval', (array)$newPlatIds), fn($v) => $v > 0));

    if ($menuId <= 0) {
        $errors[] = "Veuillez sélectionner un menu.";
    }

    if (!$errors) {
        try {
            $pdo->beginTransaction();

            // On repart sur une liste propre : on supprime les liaisons existantes
            $pdo->prepare("DELETE FROM menu_plat WHERE menu_id = ?")->execute([$menuId]);

            if (!empty($newPlatIds)) {
                $ins = $pdo->prepare("INSERT INTO menu_plat (menu_id, plat_id) VALUES (?, ?)");
                foreach ($newPlatIds as $pid) {
                    $ins->execute([$menuId, $pid]);
                }
            }

            $pdo->commit();
            $success = "Plats associés au menu avec succès.";

            // Recharger la sélection
            $selectedPlatIds = $newPlatIds;

        } catch (Throwable $e) {
            $pdo->rollBack();
            $errors[] = "Erreur lors de l'enregistrement des plats.";
        }
    }
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Associer plats à un menu</title>
</head>
<body>
<h1>Associer des plats à un menu</h1>

<p>
    <a href="menu-manage.php">← Gestion des menus</a> |
    <a href="plat-manage.php">Gestion des plats</a> |
    <a href="menus.php">Voir côté public</a>
</p>

<?php if ($success): ?>
    <p style="color:green"><?php echo htmlspecialchars($success); ?></p>
<?php endif; ?>

<?php foreach ($errors as $e): ?>
    <p style="color:red"><?php echo htmlspecialchars($e); ?></p>
<?php endforeach; ?>

<form method="get" style="margin-bottom: 20px;">
    <label>Choisir un menu :</label>
    <select name="menu_id" onchange="this.form.submit()">
        <option value="">-- sélectionner --</option>
        <?php foreach ($menus as $m): ?>
            <option value="<?php echo (int)$m['menu_id']; ?>" <?php echo ($menuId === (int)$m['menu_id']) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($m['titre']); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <noscript><button type="submit">Charger</button></noscript>
</form>

<?php if ($menuId <= 0): ?>
    <p>Sélectionne un menu pour voir et modifier ses plats.</p>
<?php else: ?>
    <form method="post">
        <input type="hidden" name="menu_id" value="<?php echo (int)$menuId; ?>">

        <fieldset>
            <legend>Plats disponibles</legend>

            <?php if (!$plats): ?>
                <p>Aucun plat en base. Ajoute des plats d'abord.</p>
            <?php else: ?>
                <?php foreach ($plats as $p): ?>
                    <?php $pid = (int)$p['plat_id']; ?>
                    <label>
                        <input type="checkbox" name="plats[]" value="<?php echo $pid; ?>"
                                <?php echo in_array($pid, $selectedPlatIds, true) ? 'checked' : ''; ?>>
                        <?php echo htmlspecialchars($p['titre_plat']); ?>
                    </label><br>
                <?php endforeach; ?>
            <?php endif; ?>
        </fieldset>

        <br>
        <button type="submit">Enregistrer</button>
    </form>
<?php endif; ?>
</body>
</html>
