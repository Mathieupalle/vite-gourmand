<?php
// plat-manage.php : page protégée (employee / admin).
// Liste les plats et permet d’en créer/mettre à jour selon le besoin.

session_start();
require_once __DIR__ . '/src/db.php';
require_once __DIR__ . '/src/auth.php';

requireRole(['employee', 'admin']);

$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $deleteId = (int)$_POST['delete_id'];

    $pdo->prepare("DELETE FROM plat_allergene WHERE plat_id = ?")->execute([$deleteId]);
    $pdo->prepare("DELETE FROM menu_plat WHERE plat_id = ?")->execute([$deleteId]); // au cas où plus tard
    $pdo->prepare("DELETE FROM plat WHERE plat_id = ?")->execute([$deleteId]);

    header("Location: plat-manage.php");
    exit;
}

$plats = $pdo->query("SELECT plat_id, titre_plat FROM plat ORDER BY plat_id DESC")->fetchAll();
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Gestion des plats</title>
</head>
<body>
<h1>Gestion des plats</h1>
<p><a href="index.php">Accueil</a></p>
<p><a href="admin.php">← Retour à l’espace Gestion</a></p>
<p><a href="plat-create.php">+ Ajouter un plat</a></p>

<?php if (!$plats): ?>
    <p>Aucun plat pour le moment.</p>
<?php else: ?>
    <ul>
        <?php foreach ($plats as $p): ?>
            <li>
                #<?php echo (int)$p['plat_id']; ?> —
                <strong><?php echo htmlspecialchars($p['titre_plat']); ?></strong>
                <td>
                    <a href="plat-edit.php?id=<?php echo (int)$p['plat_id']; ?>">Modifier plat</a>
                </td>
                <form method="post" style="display:inline" onsubmit="return confirm('Supprimer ce plat ?');">
                    <input type="hidden" name="delete_id" value="<?php echo (int)$p['plat_id']; ?>">
                    <button type="submit">Supprimer</button>
                </form>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
</body>
</html>
