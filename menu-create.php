<?php
// menu-create.php : page protégée (employee / admin).
// Formulaire de création d’un menu et insertion en base de données via PDO.

session_start();
require_once __DIR__ . '/src/db.php';
require_once __DIR__ . '/src/auth.php';

requireRole(['employee', 'admin']);

$pdo = db();
$errors = [];
$success = null;

// Listes pour selects
$themes = $pdo->query("SELECT theme_id, libelle FROM theme ORDER BY libelle")->fetchAll();
$regimes = $pdo->query("SELECT regime_id, libelle FROM regime ORDER BY libelle")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Récupération
    $titre = trim($_POST['titre'] ?? '');
    $prix = (float)($_POST['prix_par_personne'] ?? 0);
    $min  = (int)($_POST['nombre_personne_minimum'] ?? 0);
    $desc = trim($_POST['description'] ?? '');
    $qte  = (($_POST['quantite_restante'] ?? '') === '' ? null : (int)$_POST['quantite_restante']);

    $theme_id  = (int)($_POST['theme_id'] ?? 0);
    $regime_id = (int)($_POST['regime_id'] ?? 0);

    // Vérifications
    if ($titre === '') $errors[] = "Titre obligatoire.";
    if ($prix <= 0) $errors[] = "Prix par personne invalide.";
    if ($min <= 0) $errors[] = "Nombre minimum invalide.";
    if ($theme_id <= 0) $errors[] = "Thème obligatoire.";
    if ($regime_id <= 0) $errors[] = "Régime obligatoire.";
    if ($qte !== null && $qte < 0) $errors[] = "Quantité restante invalide.";

    // Insertion
    if (!$errors) {
        try {
            $stmt = $pdo->prepare("
    INSERT INTO menu 
    (titre, nombre_personne_minimum, prix_par_personne, description, quantite_restante, regime_id, theme_id)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
");

            $stmt->execute([
                    $titre,
                    $min,
                    $prix,
                    $desc ?: null,
                    $qte,
                    $regime_id,
                    $theme_id
            ]);

            $success = "Menu ajouté.";
        } catch (Throwable $e) {
            $errors[] = "Erreur lors de la création du menu.";
        }
    }
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Créer un menu</title>
</head>
<body>
<h1>Créer un menu</h1>
<p><a href="menu-manage.php">← Gestion des menus</a></p>

<?php if ($success): ?>
    <p style="color:green"><?php echo htmlspecialchars($success); ?></p>
<?php endif; ?>

<?php foreach ($errors as $e): ?>
    <p style="color:red"><?php echo htmlspecialchars($e); ?></p>
<?php endforeach; ?>

<form method="post">
    <input name="titre" placeholder="Titre" required><br><br>

    <input type="number" step="0.01" name="prix_par_personne" placeholder="Prix par personne" required><br><br>

    <input type="number" name="nombre_personne_minimum" placeholder="Nombre minimum de personnes" required><br><br>

    <input type="number" name="quantite_restante" placeholder="Quantité restante (optionnel)"><br><br>

    <input name="description" placeholder="Description (optionnel)"><br><br>

    <label>Thème :</label>
    <select name="theme_id" required>
        <option value="">-- choisir --</option>
        <?php foreach ($themes as $t): ?>
            <option value="<?php echo (int)$t['theme_id']; ?>">
                <?php echo htmlspecialchars($t['libelle']); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <br><br>

    <label>Régime :</label>
    <select name="regime_id" required>
        <option value="">-- choisir --</option>
        <?php foreach ($regimes as $r): ?>
            <option value="<?php echo (int)$r['regime_id']; ?>">
                <?php echo htmlspecialchars($r['libelle']); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <br><br>

    <button type="submit">Créer</button>
</form>
</body>
</html>
