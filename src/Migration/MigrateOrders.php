<?php
declare(strict_types=1);

namespace App\Migration;

use PDO;
use MongoDB\Client;

class MigrateOrders
{
    private PDO $pdo;
    private Client $mongo;

    public function __construct(PDO $pdo, Client $mongo)
    {
        $this->pdo = $pdo;
        $this->mongo = $mongo;
    }

    public function migrateNewOrders(): int
    {
        $col = $this->mongo->selectCollection('vitegourmand', 'orders_analytics');

        // Récupère uniquement les commandes qui ne sont pas déjà dans MongoDB
        $stmt = $this->pdo->query("
            SELECT 
                c.commande_id,
                c.numero_commande,
                c.date_commande,
                c.date_prestation,
                c.date_livraison,
                c.heure_livraison,
                c.adresse_livraison,
                c.ville_livraison,
                c.nombre_personne,
                c.prix_menu,
                c.prix_livraison,
                c.remise,
                c.pret_materiel,
                c.restitution_materiel,
                c.statut,
                u.email AS client_email,
                m.titre AS menu_titre,
                m.prix_par_personne
            FROM commande c
            JOIN utilisateur u ON u.utilisateur_id = c.utilisateur_id
            JOIN menu m ON m.menu_id = c.menu_id
            ORDER BY c.date_commande ASC
        ");

        $inserted = 0;

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            // Vérifie si la commande existe déjà dans MongoDB
            $exists = $col->countDocuments(['commande_id' => (int)$row['commande_id']]);
            if ($exists > 0) continue;

            $doc = [
                'commande_id' => (int)$row['commande_id'],
                'numero_commande' => $row['numero_commande'],
                'dateCommande' => new \MongoDB\BSON\UTCDateTime(strtotime($row['date_commande']) * 1000),
                'datePrestation' => new \MongoDB\BSON\UTCDateTime(strtotime($row['date_prestation']) * 1000),
                'dateLivraison' => new \MongoDB\BSON\UTCDateTime(strtotime($row['date_livraison']) * 1000),
                'heureLivraison' => $row['heure_livraison'],
                'adresseLivraison' => $row['adresse_livraison'],
                'villeLivraison' => $row['ville_livraison'],
                'qtyPeople' => (int)$row['nombre_personne'],
                'total' => (float)$row['prix_menu'] + (float)$row['prix_livraison'] - (float)$row['remise'],
                'pretMateriel' => (bool)$row['pret_materiel'],
                'restitutionMateriel' => (bool)$row['restitution_materiel'],
                'statut' => $row['statut'],
                'clientEmail' => $row['client_email'],
                'menuId' => (int)$row['commande_id'],
                'menuTitre' => $row['menu_titre'],
                'prixParPersonne' => (float)$row['prix_par_personne'],
            ];

            $col->insertOne($doc);
            $inserted++;
        }

        return $inserted;
    }
}