<?php
$title = $title ?? "Vite & Gourmand - Traiteur en ligne";

$baseUrl = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
if ($baseUrl === '/.') $baseUrl = '';
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Vite & Gourmand est une entreprise de restauration événementielle basée à Bordeaux. Nous proposons des menus adaptés aux mariages, séminaires, anniversaires et événements privés.

Cette application permet de consulter nos menus et de passer commande en ligne de manière simple et sécurisée.">
    <title><?= htmlspecialchars($title) ?></title>

    <!-- Bootstrap 5 (CDN) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- CSS projet -->
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/style.css">
</head>
<body>
