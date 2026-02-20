<?php
// menu-manage.php : page protégée (employee / admin).
// Liste les menus et permet de créer / modifier / associer les plats.

session_start();
require_once __DIR__ . '/src/db.php';
require_once __DIR__ . '/src/auth.php';

requireRole(['employee', 'admin']);

$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $deleteId = (int)$_POST['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM menu WHERE menu_id = ?");
    $stmt->execute([$deleteId]);
    header("Location: menu-manage.php");
    exit;
}

$menus = $pdo->query("
  SELECT m.menu_id, m.titre, m.prix_par_personne, t.libelle AS theme, r.libelle AS regime
  FROM menu m
  JOIN theme t ON t.theme_id = m.theme_id
  JOIN regime r ON r.regime_id = m.regime_id
  ORDER BY m.menu_id DESC
")->fetchAll();
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Gestion des menus</title>
</head>
<body>
<h1>Gestion des menus</h1>
<p><a href="index.php">Accueil</a></p>
<p><a href="admin.php">← Retour à l’espace Gestion</a></p>
<p><a href="menu-create.php">+ Ajouter un menu</a></p>

<?php if (!$menus): ?>
    <p>Aucun menu.</p>
<?php else: ?>
    <ul>
        <?php foreach ($menus as $m): ?>
            <li>
                #<?php echo (int)$m['menu_id']; ?> —
                <strong><?php echo htmlspecialchars($m['titre']); ?></strong>
                (<?php echo htmlspecialchars($m['theme']); ?> / <?php echo htmlspecialchars($m['regime']); ?>)
                — <?php echo htmlspecialchars((string)$m['prix_par_personne']); ?> € / pers

                <td>
                    <a href="menu-edit.php?id=<?php echo (int)$m['menu_id']; ?>">Modifier menu</a>
                    |
                    <a href="menu-edit-plats.php?id=<?php echo (int)$m['menu_id']; ?>">Modifier plats</a>
                </td>

                <form method="post" style="display:inline" onsubmit="return confirm('Supprimer ce menu ?');">
                    <input type="hidden" name="delete_id" value="<?php echo (int)$m['menu_id']; ?>">
                    <button type="submit">Supprimer</button>
                </form>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
</body>
</html>
