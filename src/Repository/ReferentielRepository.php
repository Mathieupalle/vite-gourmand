<?php
declare(strict_types=1);

namespace App\Repository;

use PDO;

// Accès aux tables de référence "thème" et "allergène" (un id + un libellé)
final class ReferentielRepository
{
    // Tables autorisées : un nom de table ne peut pas être passé avec "?" dans une requête préparée,
    // il est donc choisi uniquement dans cette liste fixe (jamais depuis le formulaire)
    private const TABLES = [
        'theme'     => 'theme_id',
        'allergene' => 'allergene_id',
    ];

    public function __construct(private PDO $pdo) {}

    public function listAll(string $type): array
    {
        $colonneId = self::TABLES[$type];
        return $this->pdo->query("SELECT $colonneId AS id, libelle FROM $type ORDER BY libelle")->fetchAll();
    }

    public function create(string $type, string $libelle): void
    {
        $stmt = $this->pdo->prepare("INSERT INTO $type (libelle) VALUES (?)");
        $stmt->execute([$libelle]);
    }

    public function update(string $type, int $id, string $libelle): void
    {
        $colonneId = self::TABLES[$type];
        $stmt = $this->pdo->prepare("UPDATE $type SET libelle = ? WHERE $colonneId = ?");
        $stmt->execute([$libelle, $id]);
    }

    public function delete(string $type, int $id): void
    {
        $colonneId = self::TABLES[$type];
        $stmt = $this->pdo->prepare("DELETE FROM $type WHERE $colonneId = ?");
        $stmt->execute([$id]);
    }

    public static function typeValide(string $type): bool
    {
        return isset(self::TABLES[$type]);
    }
}
