<?php
// employe-manage.php : liste et désactivation des employés (admin)

session_start();
require_once __DIR__ . '/src/db.php';
require_once __DIR__ . '/src/auth.php';

requireRole(['admin']);

$pdo = db();

// Désactivation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['utilisateur_id'] ?? 0);

    if ($id > 0) {
        $stmt = $pdo->prepare("UPDATE utilisateur SET actif = 0 WHERE utilisateur_id = ?");
        $stmt->execute([$id]);
    }
}

// Liste des employés
$stmt = $pdo->query("
    SELECT utilisateur_id, email, actif
    FROM utilisateur
    WHERE role_id = 2
    ORDER BY utilisateur_id DESC
");
$employes = $stmt->fetchAll();
?>

<h1>Gestion des comptes employés</h1>
<p><a href="index.php">Accueil</a></p>
<p><a href="admin.php">← Retour à l’espace Gestion</a></p>

<table border="1" cellpadding="8">
    <tr>
        <th>ID</th>
        <th>Email</th>
        <th>Actif</th>
        <th>Action</th>
    </tr>

    <?php foreach ($employes as $e): ?>
        <tr>
            <td><?php echo $e['utilisateur_id']; ?></td>
            <td><?php echo htmlspecialchars($e['email']); ?></td>
            <td><?php echo $e['actif'] ? 'Oui' : 'Non'; ?></td>
            <td>
                <?php if ($e['actif']): ?>
                    <form method="post">
                        <input type="hidden" name="utilisateur_id" value="<?php echo $e['utilisateur_id']; ?>">
                        <button type="submit">Désactiver</button>
                    </form>
                <?php else: ?>
                    Compte désactivé
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
</table>
