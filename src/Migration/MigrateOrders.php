<?php
declare(strict_types=1);

namespace App\Migration;

use PDO;
use MongoDB\Client;
use MongoDB\BSON\UTCDateTime;

class MigrateOrders
{
    private PDO $pdo;
    private Client $mongo;

    // Requête commune : une commande avec son client et son menu
    private const SQL_COMMANDES = "
        SELECT
            c.commande_id,
            c.menu_id,
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
    ";

    public function __construct(PDO $pdo, Client $mongo)
    {
        $this->pdo = $pdo;
        $this->mongo = $mongo;
    }

    // Copie dans MongoDB les commandes qui n'y sont pas encore
    public function migrateNewOrders(): int
    {
        $col = $this->mongo->selectCollection('vitegourmand', 'orders_analytics');

        $stmt = $this->pdo->query(self::SQL_COMMANDES . " ORDER BY c.date_commande ASC");

        $inserted = 0;

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            // Commande déjà présente dans MongoDB : on passe à la suivante
            $exists = $col->countDocuments(['commande_id' => (int)$row['commande_id']]);
            if ($exists > 0) continue;

            $col->insertOne($this->creerDocument($row));
            $inserted++;
        }

        return $inserted;
    }

    // Met à jour une commande dans MongoDB après une modification (statut, prix…)
    // Le document est remplacé, ou créé s'il n'existe pas encore (upsert)
    public function syncCommande(int $commandeId): void
    {
        $stmt = $this->pdo->prepare(self::SQL_COMMANDES . " WHERE c.commande_id = ?");
        $stmt->execute([$commandeId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return;
        }

        $col = $this->mongo->selectCollection('vitegourmand', 'orders_analytics');
        $col->replaceOne(
            ['commande_id' => $commandeId],
            $this->creerDocument($row),
            ['upsert' => true]
        );
    }

    // Recopie toutes les commandes (utile après des annulations ou des modifications)
    public function syncToutes(): int
    {
        $ids = $this->pdo->query("SELECT commande_id FROM commande")->fetchAll(PDO::FETCH_COLUMN);

        foreach ($ids as $id) {
            $this->syncCommande((int)$id);
        }

        return count($ids);
    }

    // Transforme une ligne MySQL en document MongoDB
    private function creerDocument(array $row): array
    {
        return [
            'commande_id' => (int)$row['commande_id'],
            'numero_commande' => $row['numero_commande'],
            'dateCommande' => new UTCDateTime(strtotime($row['date_commande']) * 1000),
            'datePrestation' => new UTCDateTime(strtotime($row['date_prestation']) * 1000),
            'dateLivraison' => new UTCDateTime(strtotime($row['date_livraison']) * 1000),
            'heureLivraison' => $row['heure_livraison'],
            'adresseLivraison' => $row['adresse_livraison'],
            'villeLivraison' => $row['ville_livraison'],
            'qtyPeople' => (int)$row['nombre_personne'],
            'total' => (float)$row['prix_menu'] + (float)$row['prix_livraison'] - (float)$row['remise'],
            'pretMateriel' => (bool)$row['pret_materiel'],
            'restitutionMateriel' => (bool)$row['restitution_materiel'],
            'statut' => $row['statut'],
            'clientEmail' => $row['client_email'],
            'menuId' => (int)$row['menu_id'],
            'menuTitre' => $row['menu_titre'],
            'prixParPersonne' => (float)$row['prix_par_personne'],
        ];
    }
}
