<?php
declare(strict_types=1);

namespace App\Repository;

use PDO;

final class HoraireRepository
{
    public function __construct(private PDO $pdo) {}

    public function listAll(): array
    {
        return $this->pdo->query("
            SELECT horaire_id, jour, heure_ouverture, heure_fermeture
            FROM horaire
            ORDER BY horaire_id ASC
        ")->fetchAll() ?: [];
    }

    // Un jour fermé est enregistré avec des heures vides
    public function updateOne(int $horaireId, string $ouverture, string $fermeture): void
    {
        if ($ouverture === '' || $fermeture === '') {
            $ouverture = '';
            $fermeture = '';
        }

        $stmt = $this->pdo->prepare("UPDATE horaire SET heure_ouverture = ?, heure_fermeture = ? WHERE horaire_id = ?");
        $stmt->execute([$ouverture, $fermeture, $horaireId]);
    }
}