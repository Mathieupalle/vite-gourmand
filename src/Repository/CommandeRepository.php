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
            SELECT menu_id, titre, prix_par_personne, nombre_personne_minimum
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

    public function updateCommandeStatut(int $commandeId, string $statut): void
    {
        $stmt = $this->pdo->prepare("UPDATE commande SET statut = ? WHERE commande_id = ?");
        $stmt->execute([$statut, $commandeId]);
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

    // Suivis groupés
    public function findSuivisForCommandeIds(array $commandeIds): array
    {
        $commandeIds = array_values(array_filter(array_map('intval', $commandeIds)));
        if (!$commandeIds) return [];

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