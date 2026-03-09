<!doctype html>
<html lang="fr">
<?php require TEMPLATES_PATH . '/partials/head.php'; ?>

<body>
<?php require TEMPLATES_PATH . '/partials/header.php'; ?>

<h1>Espace gestion</h1>
<p><a href="<?= BASE_URL ?>/home">← Retour accueil</a></p>

<p>
    Connecté :
    <strong><?= htmlspecialchars((string)$user['email']) ?></strong>
    (<?= htmlspecialchars($role) ?>)
</p>

<hr>

<h2>Gestion</h2>
<ul>
    <li><a href="<?= BASE_URL ?>/menuManage">Gestion des menus</a></li>
    <li><a href="<?= BASE_URL ?>/platManage">Gestion des plats</a></li>
    <li><a href="<?= BASE_URL ?>/commandeManage">Gestion des commandes</a></li>
    <li><a href="<?= BASE_URL ?>/horaireManage">Gestion des horaires</a></li>
    <li><a href="<?= BASE_URL ?>/avisManage">Gestion des avis</a></li>
</ul>

<?php if ($role === 'admin'): ?>

    <hr>

    <h2>Administration</h2>

    <ul>
        <li><a href="<?= BASE_URL ?>/employeCreate">Créer un compte employé</a></li>
        <li><a href="<?= BASE_URL ?>/employeManage">Gérer les comptes employés</a></li>
        <br><br>
        <li><a href="<?= BASE_URL ?>/stats">Statistiques (MongoDB)</a></li>
    </ul>

<?php endif; ?>
<?php require TEMPLATES_PATH . '/partials/footer.php'; ?>

<?php require TEMPLATES_PATH . '/partials/scripts.php'; ?>
</body>
</html>