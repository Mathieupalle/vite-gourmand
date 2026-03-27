<?php
declare(strict_types=1);

namespace App\Repository;

use PDO;

final class AvisRepository
{
    public function __construct(private PDO $pdo) {}

    public function findCommandeForAvis(int $commandeId, int $userId): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT c.commande_id, c.statut, m.titre AS menu_titre
            FROM commande c
            JOIN menu m ON m.menu_id = c.menu_id
            WHERE c.commande_id = ? AND c.utilisateur_id = ?
            LIMIT 1
        ");
        $stmt->execute([$commandeId, $userId]);
        return $stmt->fetch() ?: null;
    }

    public function existsForCommande(int $commandeId): bool
    {
        $stmt = $this->pdo->prepare("SELECT avis_id FROM avis WHERE commande_id = ? LIMIT 1");
        $stmt->execute([$commandeId]);
        return (bool)$stmt->fetch();
    }

    public function create(int $note, string $description, int $userId, int $commandeId): void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO avis (note, description, statut, utilisateur_id, commande_id)
            VALUES (?, ?, 'en_attente', ?, ?)
        ");
        $stmt->execute([$note, $description, $userId, $commandeId]);
    }

    public function listAllForManage(): array
    {
        return $this->pdo->query("
            SELECT a.avis_id, a.note, a.description, a.statut, a.date_avis,
                   u.email AS client_email
            FROM avis a
            LEFT JOIN utilisateur u ON u.utilisateur_id = a.utilisateur_id
            ORDER BY a.date_avis DESC
        ")->fetchAll() ?: [];
    }

    public function updateStatut(int $avisId, string $statut): void
    {
        $stmt = $this->pdo->prepare("UPDATE avis SET statut = ? WHERE avis_id = ?");
        $stmt->execute([$statut, $avisId]);
    }
}