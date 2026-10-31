<?php
declare(strict_types=1);

namespace App\Repository;

use PDO;

final class CommandeRepository
{
    public function __construct(private PDO $pdo) {}

    public function findMenuById(int $menuId): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT menu_id, titre, prix_par_personne, nombre_personne_minimum, quantite_restante, conditions
            FROM menu
            WHERE menu_id = ?
            LIMIT 1
        ");
        $stmt->execute([$menuId]);
        return $stmt->fetch() ?: null;
    }

    public function findCommandeStatutById(int $commandeId): ?string
    {
        $stmt = $this->pdo->prepare("SELECT statut FROM commande WHERE commande_id = ? LIMIT 1");
        $stmt->execute([$commandeId]);
        $row = $stmt->fetch();
        return $row ? (string)$row['statut'] : null;
    }

    public function findClientEmailByCommandeId(int $commandeId): ?string
    {
        $stmt = $this->pdo->prepare("
            SELECT u.email
            FROM commande c
            JOIN utilisateur u ON u.utilisateur_id = c.utilisateur_id
            WHERE c.commande_id = ?
            LIMIT 1
        ");
        $stmt->execute([$commandeId]);
        $row = $stmt->fetch();
        return $row ? (string)$row['email'] : null;
    }

    public function createCommande(array $data): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO commande (
                numero_commande,
                date_prestation,
                heure_livraison,
                prix_menu,
                nombre_personne,
                prix_livraison,
                statut,
                pret_materiel,
                restitution_materiel,
                utilisateur_id,
                menu_id,
                adresse_prestation,
                ville_prestation,
                distance_km,
                remise,
                date_livraison,
                ville_livraison,
                adresse_livraison
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $data['numero_commande'],
            $data['date_prestation'],
            $data['heure_livraison'],
            $data['prix_menu'],
            $data['nombre_personne'],
            $data['prix_livraison'],
            $data['statut'],
            $data['pret_materiel'],
            $data['restitution_materiel'],
            $data['utilisateur_id'],
            $data['menu_id'],
            $data['adresse_prestation'],
            $data['ville_prestation'],
            $data['distance_km'],
            $data['remise'],
            $data['date_livraison'],
            $data['ville_livraison'],
            $data['adresse_livraison'],
        ]);

        return (int)$this->pdo->lastInsertId();
    }

    // Retire une commande du stock du menu (si un stock est défini)
    public function retirerStock(int $menuId): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE menu
            SET quantite_restante = quantite_restante - 1
            WHERE menu_id = ? AND quantite_restante IS NOT NULL AND quantite_restante > 0
        ");
        $stmt->execute([$menuId]);
    }

    // Rend une place dans le stock du menu de la commande annulée
    public function rendreStock(int $commandeId): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE menu m
            JOIN commande c ON c.menu_id = m.menu_id
            SET m.quantite_restante = m.quantite_restante + 1
            WHERE c.commande_id = ? AND m.quantite_restante IS NOT NULL
        ");
        $stmt->execute([$commandeId]);
    }

    public function updateCommandeStatut(int $commandeId, string $statut): void
    {
        $stmt = $this->pdo->prepare("UPDATE commande SET statut = ? WHERE commande_id = ?");
        $stmt->execute([$statut, $commandeId]);
    }

    // Matériel prêté au client (pret) et matériel rendu (restitution)
    public function updateMateriel(int $commandeId, bool $pret, bool $restitution): void
    {
        $stmt = $this->pdo->prepare("UPDATE commande SET pret_materiel = ?, restitution_materiel = ? WHERE commande_id = ?");
        $stmt->execute([(int)$pret, (int)$restitution, $commandeId]);
    }

    // Nombre de commandes par menu (pour le filtre de la gestion des commandes)
    public function countCommandesParMenu(): array
    {
        return $this->pdo->query("
            SELECT m.menu_id, m.titre, COUNT(c.commande_id) AS nb
            FROM menu m
            LEFT JOIN commande c ON c.menu_id = m.menu_id
            GROUP BY m.menu_id, m.titre
            ORDER BY m.titre
        ")->fetchAll();
    }

    // Toutes les commandes pour l'employé, avec un filtre facultatif
    // (statut, menu ou partie de l'email du client)
    public function findCommandesForManage(string $statut, int $menuId, string $client): array
    {
        $sql = "
            SELECT
                c.commande_id, c.numero_commande, c.date_commande, c.date_prestation,
                c.date_livraison, c.heure_livraison, c.adresse_livraison, c.ville_livraison,
                c.nombre_personne, c.prix_menu, c.prix_livraison, c.remise,
                c.pret_materiel, c.restitution_materiel, c.statut,
                u.email AS client_email,
                m.titre AS menu_titre,
                m.prix_par_personne
            FROM commande c
            JOIN utilisateur u ON u.utilisateur_id = c.utilisateur_id
            JOIN menu m ON m.menu_id = c.menu_id
            WHERE 1 = 1
        ";
        $params = [];

        if ($statut !== '') {
            $sql .= " AND c.statut = ?";
            $params[] = $statut;
        }
        if ($menuId > 0) {
            $sql .= " AND c.menu_id = ?";
            $params[] = $menuId;
        }
        if ($client !== '') {
            $sql .= " AND u.email LIKE ?";
            $params[] = '%' . $client . '%';
        }

        $sql .= " ORDER BY c.date_commande DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // Une commande du client connecté, avec les infos du menu
    // (le filtre sur utilisateur_id empêche d'accéder à la commande d'un autre)
    public function findCommandeOfUser(int $commandeId, int $userId): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT c.*,
                   m.prix_par_personne,
                   m.nombre_personne_minimum,
                   m.titre AS menu_titre
            FROM commande c
            JOIN menu m ON m.menu_id = c.menu_id
            WHERE c.commande_id = ? AND c.utilisateur_id = ?
            LIMIT 1
        ");
        $stmt->execute([$commandeId, $userId]);
        return $stmt->fetch() ?: null;
    }

    // Modification d'une commande par son client (le menu ne change pas)
    public function updateCommandeClient(int $commandeId, int $userId, array $data): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE commande
            SET date_prestation = ?, adresse_prestation = ?, ville_prestation = ?,
                date_livraison = ?, heure_livraison = ?, adresse_livraison = ?, ville_livraison = ?,
                distance_km = ?, nombre_personne = ?,
                prix_menu = ?, remise = ?, prix_livraison = ?
            WHERE commande_id = ? AND utilisateur_id = ?
        ");
        $stmt->execute([
            $data['date_prestation'], $data['adresse_prestation'], $data['ville_prestation'],
            $data['date_livraison'], $data['heure_livraison'], $data['adresse_livraison'], $data['ville_livraison'],
            $data['distance_km'], $data['nombre_personne'],
            $data['prix_menu'], $data['remise'], $data['prix_livraison'],
            $commandeId, $userId,
        ]);
    }

    public function insertSuivi(int $commandeId, string $statut, ?string $modeContact, ?string $motif): void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO commande_suivi (commande_id, statut, mode_contact, motif)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$commandeId, $statut, $modeContact, $motif]);
    }

    // Commandes user
    public function findCommandesByUser(int $userId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT
                c.*,
                m.titre AS menu_titre,
                m.prix_par_personne,
                m.nombre_personne_minimum
            FROM commande c
            JOIN menu m ON m.menu_id = c.menu_id
            WHERE c.utilisateur_id = ?
            ORDER BY c.date_commande DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll() ?: [];
    }

    // Historique des statuts pour plusieurs commandes
    public function findSuivisForCommandeIds(array $commandeIds): array
    {
        if (empty($commandeIds)) {
            return [];
        }

        // Un "?" par commande : IN (?, ?, ?)
        $placeholders = implode(',', array_fill(0, count($commandeIds), '?'));

        $stmt = $this->pdo->prepare("
            SELECT commande_id, statut, date_modif, mode_contact, motif
            FROM commande_suivi
            WHERE commande_id IN ($placeholders)
            ORDER BY date_modif ASC
        ");
        $stmt->execute($commandeIds);
        return $stmt->fetchAll() ?: [];
    }
}