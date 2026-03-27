<?php
declare(strict_types=1);

namespace App\Repository;

use PDO;

final class PlatRepository
{
    public function __construct(private PDO $pdo) {}

    public function listAll(): array
    {
        return $this->pdo->query("SELECT plat_id, titre_plat, categorie FROM plat ORDER BY titre_plat")->fetchAll() ?: [];
    }

    public function findById(int $platId): ?array
    {
        $stmt = $this->pdo->prepare("SELECT plat_id, titre_plat, categorie FROM plat WHERE plat_id = ? LIMIT 1");
        $stmt->execute([$platId]);
        return $stmt->fetch() ?: null;
    }

    public function listAllergenes(): array
    {
        return $this->pdo->query("SELECT allergene_id, libelle FROM allergene ORDER BY libelle")->fetchAll() ?: [];
    }

    public function getAllergeneIdsForPlat(int $platId): array
    {
        $stmt = $this->pdo->prepare("SELECT allergene_id FROM plat_allergene WHERE plat_id = ?");
        $stmt->execute([$platId]);
        return array_map('intval', array_column($stmt->fetchAll() ?: [], 'allergene_id'));
    }

    public function create(string $titre, string $categorie): int
    {
        $stmt = $this->pdo->prepare("INSERT INTO plat (titre_plat, categorie) VALUES (?, ?)");
        $stmt->execute([$titre, $categorie]);
        return (int)$this->pdo->lastInsertId();
    }

    public function update(int $platId, string $titre, string $categorie): void
    {
        $stmt = $this->pdo->prepare("UPDATE plat SET titre_plat = ?, categorie = ? WHERE plat_id = ?");
        $stmt->execute([$titre, $categorie, $platId]);
    }

    public function replacePlatAllergenes(int $platId, array $allergeneIds): void
    {
        $this->pdo->prepare("DELETE FROM plat_allergene WHERE plat_id = ?")->execute([$platId]);

        if (!$allergeneIds) return;

        $ins = $this->pdo->prepare("INSERT INTO plat_allergene (plat_id, allergene_id) VALUES (?, ?)");
        foreach ($allergeneIds as $aid) {
            $ins->execute([$platId, (int)$aid]);
        }
    }

    public function delete(int $platId): void
    {
        // Supprimer liaisons
        $this->pdo->prepare("DELETE FROM plat_allergene WHERE plat_id = ?")->execute([$platId]);
        $this->pdo->prepare("DELETE FROM menu_plat WHERE plat_id = ?")->execute([$platId]);
        $this->pdo->prepare("DELETE FROM plat WHERE plat_id = ?")->execute([$platId]);
    }
}