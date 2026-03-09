<!doctype html>
<html lang="fr">
<?php require TEMPLATES_PATH . '/partials/head.php'; ?>

<body>
<?php require TEMPLATES_PATH . '/partials/header.php'; ?>

<h1>Mentions légales</h1>
<p><a href="<?= BASE_URL ?>/home">← Retour accueil</a></p>

<p>
    Site réalisé dans le cadre d’un projet pédagogique (ECF STUDI).
</p>

<h2>Éditeur du site</h2>
<p>
    Vite & Gourmand — Bordeaux<br>
    Contact : via la page <a href="<?= BASE_URL ?>/contact">Contact</a>
</p>

<h2>Données personnelles</h2>
<p>
    Les informations collectées (compte, commandes) sont utilisées uniquement pour le fonctionnement de l’application.
    Conformément au RGPD, l’utilisateur peut demander la modification/suppression de ses données.
</p>
<?php require TEMPLATES_PATH . '/partials/footer.php'; ?>

<?php require TEMPLATES_PATH . '/partials/scripts.php'; ?>
</body>
</html>