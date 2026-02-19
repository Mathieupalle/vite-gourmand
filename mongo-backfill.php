<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/db.php';

$pdo = db();

$uri = getenv('MONGODB_URI') ?: 'mongodb+srv://vitegourmand:ViteGourmand12345@cluster0.ptlv7eh.mongodb.net/?appName=Cluster0&authSource=admin';
if (!$uri) {
    die("MONGODB_URI manquant\n");
}

$client = new MongoDB\Client($uri);
$collection = $client->selectCollection('vitegourmand', 'orders_analytics');

echo "Début import...\n";

// Jointure commande + menu
$sql = "
SELECT 
    c.commande_id,
    c.date_commande,
    c.prix_menu,
    c.prix_livraison,
    c.nombre_personne,
    c.statut,
    c.menu_id,
    c.remise,
    m.titre
FROM commande c
JOIN menu m ON m.menu_id = c.menu_id
";

$stmt = $pdo->query($sql);
$count = 0;

while ($row = $stmt->fetch()) {

    $total =
        (float)$row['prix_menu']
        + (float)($row['prix_livraison'] ?? 0)
        - (float)($row['remise'] ?? 0);

    $doc = [
        'sqlCommandeId' => (int)$row['commande_id'],
        'menuId' => (int)$row['menu_id'],
        'menuTitre' => $row['titre'],
        'total' => $total,
        'nombrePersonne' => (int)$row['nombre_personne'],
        'statut' => $row['statut'],
        'dateCommande' => new MongoDB\BSON\UTCDateTime(
            strtotime($row['date_commande']) * 1000
        ),
        'createdAt' => new MongoDB\BSON\UTCDateTime()
    ];

    // Upsert (évite doublons)
    $collection->updateOne(
        ['sqlCommandeId' => (int)$row['commande_id']],
        ['$set' => $doc],
        ['upsert' => true]
    );

    $count++;
}

echo "Import terminé : $count commandes synchronisées.\n";
