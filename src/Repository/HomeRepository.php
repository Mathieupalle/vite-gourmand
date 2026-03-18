<?php
declare(strict_types=1);

namespace App\Repository;

use PDO;

final class HomeRepository
{
    public function __construct(private PDO $pdo) {}

    public function getHoraires(): array
    {
        return $this->pdo->query("
            SELECT jour, heure_ouverture, heure_fermeture
            FROM horaire
            ORDER BY horaire_id ASC
        ")->fetchAll() ?: [];
    }

    public function getAvisValides(int $limit = 5): array
    {
        $limit = max(1, min($limit, 20));
        $stmt = $this->pdo->prepare("
            SELECT note, description
            FROM avis
            WHERE statut = 'valide'
            ORDER BY avis_id DESC
            LIMIT {$limit}
        ");
        $stmt->execute();
        return $stmt->fetchAll() ?: [];
    }
}