<?php
// commande-create.php

session_start();
require_once __DIR__ . '/src/db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$pdo = db();

// Récupération données formulaire
$menuId = (int)($_POST['menu_id'] ?? 0);
$nb = (int)($_POST['nombre_personne'] ?? 0);

$heureLivraison = $_POST['heure_livraison'] ?? '';
$villeLivraison = trim($_POST['ville_livraison'] ?? '');
$adresseLivraison = trim($_POST['adresse_livraison'] ?? '');

$datePrestation = $_POST['date_prestation'] ?? '';
$dateLivraison = $_POST['date_livraison'] ?? '';
$sameDate = isset($_POST['same_date']);
if ($sameDate) {
    $dateLivraison = $datePrestation;
}

if ($menuId <= 0 || $nb <= 0 || !$datePrestation || !$dateLivraison || $heureLivraison === '') {
    http_response_code(400);
    die("Données invalides.");
}

$today = new DateTimeImmutable('today');
$dp = DateTimeImmutable::createFromFormat('Y-m-d', $datePrestation);
$dl = DateTimeImmutable::createFromFormat('Y-m-d', $dateLivraison);

if (!$dp || !$dl) {
    http_response_code(400);
    die("Dates invalides.");
}

if ($dp < $today) {
    http_response_code(400);
    die("La date de prestation ne peut pas être avant aujourd’hui.");
}

if ($dl < $today) {
    http_response_code(400);
    die("La date de livraison ne peut pas être avant aujourd’hui.");
}

$userId = (int)$_SESSION['user']['id'];

if (
    $menuId <= 0 ||
    $nb <= 0 ||
    $datePrestation === '' ||
    $dateLivraison === '' ||
    $villeLivraison === '' ||
    $adresseLivraison === ''
) {
    die("Données invalides.");
}

// Récupérer infos menu (j'ajoute titre pour l'analytics Mongo)
$stmt = $pdo->prepare("
    SELECT titre, prix_par_personne, nombre_personne_minimum
    FROM menu
    WHERE menu_id = ?
");
$stmt->execute([$menuId]);
$menu = $stmt->fetch();

if (!$menu) {
    die("Menu introuvable.");
}

if ($nb < (int)$menu['nombre_personne_minimum']) {
    die("Nombre minimum non respecté.");
}

$menuTitre = (string)($menu['titre'] ?? '');
$prixPers = (float)$menu['prix_par_personne'];
$sousTotal = $nb * $prixPers;

// Remise -10% si +5 personnes
$remise = 0;
if ($nb >= ((int)$menu['nombre_personne_minimum'] + 5)) {
    $remise = $sousTotal * 0.10;
}

// Livraison
$distanceKm = 0;
$prixLivraison = 0;

if (strtolower($villeLivraison) !== 'bordeaux') {
    $distanceKm = 10; // valeur fixe pour projet étudiant
    $prixLivraison = 5 + ($distanceKm * 0.59);
}

$totalFinal = $sousTotal - $remise + $prixLivraison;

$numeroCommande = 'CMD' . time();

$stmt = $pdo->prepare("
    INSERT INTO commande (
        numero_commande,
        utilisateur_id,
        menu_id,
        nombre_personne,
        date_prestation,
        date_livraison,
        heure_livraison,
        adresse_prestation,
        ville_prestation,
        adresse_livraison,
        ville_livraison,
        prix_menu,
        prix_livraison,
        distance_km,
        remise,
        pret_materiel,
        restitution_materiel,
        statut
    )
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, 0, 'en_attente')
");

$stmt->execute([
    $numeroCommande,
    $userId,
    $menuId,
    $nb,
    $datePrestation,
    $dateLivraison,
    $heureLivraison,
    $adresseLivraison,
    $villeLivraison,
    $adresseLivraison,
    $villeLivraison,
    $prixPers,
    $prixLivraison,
    $distanceKm,
    $remise
]);

$commandeId = (int)$pdo->lastInsertId();

$stmt = $pdo->prepare("INSERT INTO commande_suivi (commande_id, statut) VALUES (?, ?)");
$stmt->execute([$commandeId, 'en_attente']);

/**
 * AJOUT MONGODB (NoSQL) : on enregistre une version “analytics” de la commande
 * Ne bloque jamais le parcours utilisateur : en cas d’échec Mongo, on ignore.
 */
$mongoUri = getenv('MONGODB_URI');
if ($mongoUri) {
    try {
        require_once __DIR__ . '/vendor/autoload.php';

        $mongo = new MongoDB\Client($mongoUri);
        $col = $mongo->selectCollection('vitegourmand', 'orders_analytics');

        $nowMs = (int) round(microtime(true) * 1000);

        $datePrestationMs = strtotime($datePrestation . ' 00:00:00') * 1000;
        $dateLivraisonMs  = strtotime($dateLivraison  . ' 00:00:00') * 1000;

        $col->insertOne([
            'sqlCommandeId' => $commandeId,
            'numeroCommande' => $numeroCommande,

            'userId' => $userId,

            'menuId' => $menuId,
            'menuTitre' => $menuTitre,

            'nombrePersonne' => $nb,
            'prixParPersonne' => $prixPers,

            'sousTotal' => $sousTotal,
            'remise' => $remise,
            'prixLivraison' => $prixLivraison,
            'distanceKm' => $distanceKm,
            'total' => $totalFinal,

            'statut' => 'en_attente',

            // Dates pour filtres + graphiques
            'dateCommande' => new MongoDB\BSON\UTCDateTime($nowMs),
            'datePrestation' => new MongoDB\BSON\UTCDateTime((int)$datePrestationMs),
            'dateLivraison' => new MongoDB\BSON\UTCDateTime((int)$dateLivraisonMs),

            // Livraison (stats par ville)
            'villeLivraison' => $villeLivraison,
        ]);
    } catch (Throwable $e) {
        // Ne rien faire pour ne pas casser la création SQL.
    }
}

header("Location: mes-commandes.php");
exit;
