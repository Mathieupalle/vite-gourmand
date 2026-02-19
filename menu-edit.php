<?php
// menu-edit.php : modifier les informations d'un menu (employee/admin)

session_start();
require_once __DIR__ . '/src/db.php';
require_once __DIR__ . '/src/auth.php';

requireRole(['employee','admin']);

$pdo = db();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    http_response_code(400);
    die("ID invalide.");
}

// Récupération menu
$stmt = $pdo->prepare("SELECT * FROM menu WHERE menu_id = ?");
$stmt->execute([$id]);
$menu = $stmt->fetch();
if (!$menu) {
    http_response_code(404);
    die("Menu introuvable.");
}

// Liste pour SELECTS
$themes = $pdo->query("SELECT theme_id, libelle FROM theme ORDER BY libelle")->fetchAll();
$regimes = $pdo->query("SELECT regime_id, libelle FROM regime ORDER BY libelle")->fetchAll();

$errors = [];
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $titre = trim($_POST['titre'] ?? '');
    $prix = (float)($_POST['prix_par_personne'] ?? 0);
    $min  = (int)($_POST['nombre_personne_minimum'] ?? 0);
    $desc = trim($_POST['description'] ?? '');
    $qte  = ($_POST['quantite_restante'] === '' ? null : (int)$_POST['quantite_restante']);

    $theme_id  = (int)($_POST['theme_id'] ?? 0);
    $regime_id = (int)($_POST['regime_id'] ?? 0);

    if ($titre === '') $errors[] = "Titre obligatoire.";
    if ($prix <= 0) $errors[] = "Prix invalide.";
    if ($min <= 0) $errors[] = "Nombre minimum invalide.";
    if ($theme_id <= 0) $errors[] = "Thème obligatoire.";
    if ($regime_id <= 0) $errors[] = "Régime obligatoire.";

    if (!$errors) {
        $upd = $pdo->prepare("
            UPDATE menu
            SET titre = ?, prix_par_personne = ?, nombre_personne_minimum = ?,
                description = ?, quantite_restante = ?, theme_id = ?, regime_id = ?
            WHERE menu_id = ?
        ");
        $upd->execute([
                $titre,
                $prix,
                $min,
                ($desc === '' ? null : $desc),
                $qte,
                $theme_id,
                $regime_id,
                $id
        ]);

        $success = "Menu modifié.";
        // Recharger les données
        $stmt = $pdo->prepare("SELECT * FROM menu WHERE menu_id = ?");
        $stmt->execute([$id]);
        $menu = $stmt->fetch();
    }
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Modifier menu</title>
</head>
<body>

<h1>Modifier le menu</h1>
<p><a href="menu-manage.php">← Gestion des menus</a> | <a href="menu-edit-plats.php?id=<?php echo (int)$id; ?>">Modifier plats</a></p>

<?php if ($success): ?>
    <p style="color:green"><?php echo htmlspecialchars($success); ?></p>
<?php endif; ?>

<?php foreach ($errors as $e): ?>
    <p style="color:red"><?php echo htmlspecialchars($e); ?></p>
<?php endforeach; ?>

<form method="post">
    <label>Titre :</label><br>
    <input name="titre" value="<?php echo htmlspecialchars($menu['titre']); ?>" required><br><br>

    <label>Prix par personne :</label><br>
    <input type="number" step="0.01" name="prix_par_personne"
           value="<?php echo htmlspecialchars((string)$menu['prix_par_personne']); ?>" required><br><br>

    <label>Nombre minimum de personnes :</label><br>
    <input type="number" name="nombre_personne_minimum"
           value="<?php echo (int)$menu['nombre_personne_minimum']; ?>" required><br><br>

    <label>Quantité restante (optionnel) :</label><br>
    <input type="number" name="quantite_restante"
           value="<?php echo htmlspecialchars((string)($menu['quantite_restante'] ?? '')); ?>"><br><br>

    <label>Description (optionnel) :</label><br>
    <input name="description" value="<?php echo htmlspecialchars((string)($menu['description'] ?? '')); ?>"><br><br>

    <label>Thème :</label><br>
    <select name="theme_id" required>
        <option value="">-- choisir --</option>
        <?php foreach ($themes as $t): ?>
            <option value="<?php echo (int)$t['theme_id']; ?>"
                    <?php echo ((int)$menu['theme_id'] === (int)$t['theme_id']) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($t['libelle']); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <br><br>

    <label>Régime :</label><br>
    <select name="regime_id" required>
        <option value="">-- choisir --</option>
        <?php foreach ($regimes as $r): ?>
            <option value="<?php echo (int)$r['regime_id']; ?>"
                    <?php echo ((int)$menu['regime_id'] === (int)$r['regime_id']) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($r['libelle']); ?>
            </option>
        <?php endforeach; ?>
    </select>

    <br><br>
    <button type="submit">Enregistrer</button>
</form>

</body>
</html>
