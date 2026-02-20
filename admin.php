<?php
// admin.php : espace de gestion (employee / admin)
// Regroupe les liens de gestion : menus, plats, horaires, commandes, avis (et employés si admin)

session_start();
require_once __DIR__ . '/src/auth.php';

requireRole(['employee', 'admin']);

$user = $_SESSION['user'] ?? null;
$role = $user['role'] ?? 'employee';
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Espace gestion</title>
</head>
<body>

<h1>Espace gestion</h1>
<p><a href="index.php">← Retour accueil</a></p>

<p>
    Connecté : <strong><?php echo htmlspecialchars($user['email']); ?></strong>
    (<?php echo htmlspecialchars($role); ?>)
</p>

<hr>

<h2>Gestion</h2>
<ul>
    <li><a href="menu-manage.php">Gestion des menus</a></li>
    <li><a href="plat-manage.php">Gestion des plats</a></li>
    <li><a href="commande-manage.php">Gestion des commandes</a></li>
    <li><a href="horaire-manage.php">Gestion des horaires</a></li>
    <li><a href="avis-manage.php">Gestion des avis</a></li>
</ul>

<?php if ($role === 'admin'): ?>
    <hr>
    <h2>Administration</h2>
    <ul>
        <li><a href="employe-create.php">Créer un compte employé</a></li>
        <li><a href="employe-manage.php">Gérer les comptes employés</a></li>
        <br><br>
        <li><a href="stats.php">Statistiques (MongoDB)</a></li>
    </ul>
<?php endif; ?>

</body>
</html>
