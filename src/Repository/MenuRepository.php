<?php
declare(strict_types=1);

namespace App\Repository;

use PDO;

final class MenuRepository
{
    public function __construct(private PDO $pdo) {}

    public function getThemes(): array
    {
        return $this->pdo->query("SELECT theme_id, libelle FROM theme ORDER BY libelle")->fetchAll() ?: [];
    }

    public function getRegimes(): array
    {
        return $this->pdo->query("SELECT regime_id, libelle FROM regime ORDER BY libelle")->fetchAll() ?: [];
    }

    public function findAllForManage(): array
    {
        $stmt = $this->pdo->query("
            SELECT m.*, t.libelle AS theme, r.libelle AS regime
            FROM menu m
            JOIN theme t ON t.theme_id = m.theme_id
            JOIN regime r ON r.regime_id = m.regime_id
            ORDER BY m.menu_id DESC
        ");
        return $stmt->fetchAll() ?: [];
    }

    // Liste des menus avec les filtres de la page "Nos menus"
    public function findForCardsFiltered(array $filters): array
    {
        $sql = "
            SELECT m.menu_id, m.titre, m.description, m.prix_par_personne, m.nombre_personne_minimum,
                   t.libelle AS theme, r.libelle AS regime
            FROM menu m
            JOIN theme t ON t.theme_id = m.theme_id
            JOIN regime r ON r.regime_id = m.regime_id
            WHERE 1 = 1
        ";
        $params = [];

        // Prix maximum
        if (!empty($filters['prix_max'])) {
            $sql .= " AND m.prix_par_personne <= ?";
            $params[] = (float)$filters['prix_max'];
        }

        // Fourchette de prix (min et max)
        if (!empty($filters['prix_min'])) {
            $sql .= " AND m.prix_par_personne >= ?";
            $params[] = (float)$filters['prix_min'];
        }
        if (!empty($filters['prix_max_range'])) {
            $sql .= " AND m.prix_par_personne <= ?";
            $params[] = (float)$filters['prix_max_range'];
        }

        // Thème et régime
        if (!empty($filters['theme_id'])) {
            $sql .= " AND m.theme_id = ?";
            $params[] = (int)$filters['theme_id'];
        }
        if (!empty($filters['regime_id'])) {
            $sql .= " AND m.regime_id = ?";
            $params[] = (int)$filters['regime_id'];
        }

        // Le menu doit être possible pour ce nombre de personnes
        if (!empty($filters['min_personnes'])) {
            $sql .= " AND m.nombre_personne_minimum <= ?";
            $params[] = (int)$filters['min_personnes'];
        }

        $sql .= " ORDER BY m.menu_id DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findById(int $menuId): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM menu WHERE menu_id = ? LIMIT 1");
        $stmt->execute([$menuId]);
        return $stmt->fetch() ?: null;
    }

    public function findByIdWithThemeRegime(int $menuId): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT m.*, t.libelle AS theme, r.libelle AS regime
            FROM menu m
            JOIN theme t ON t.theme_id = m.theme_id
            JOIN regime r ON r.regime_id = m.regime_id
            WHERE m.menu_id = ?
            LIMIT 1
        ");
        $stmt->execute([$menuId]);
        return $stmt->fetch() ?: null;
    }

    public function listMenusForSelect(): array
    {
        return $this->pdo->query("SELECT menu_id, titre FROM menu ORDER BY titre")->fetchAll() ?: [];
    }

    public function getPlatIdsForMenu(int $menuId): array
    {
        $stmt = $this->pdo->prepare("SELECT plat_id FROM menu_plat WHERE menu_id = ?");
        $stmt->execute([$menuId]);

        $ids = [];
        foreach ($stmt->fetchAll() as $row) {
            $ids[] = (int)$row['plat_id'];
        }
        return $ids;
    }

    public function replaceMenuPlats(int $menuId, array $platIds): void
    {
        $this->pdo->prepare("DELETE FROM menu_plat WHERE menu_id = ?")->execute([$menuId]);

        if (!$platIds) return;

        $ins = $this->pdo->prepare("INSERT INTO menu_plat (menu_id, plat_id) VALUES (?, ?)");
        foreach ($platIds as $pid) {
            $ins->execute([$menuId, (int)$pid]);
        }
    }

    public function getMenuPlatsWithAllergenes(int $menuId): array
    {
        // Plat + allergènes (0,n).
        $stmt = $this->pdo->prepare("
            SELECT
                p.plat_id,
                p.titre_plat,
                p.categorie,
                a.libelle AS allergene
            FROM menu_plat mp
            JOIN plat p ON p.plat_id = mp.plat_id
            LEFT JOIN plat_allergene pa ON pa.plat_id = p.plat_id
            LEFT JOIN allergene a ON a.allergene_id = pa.allergene_id
            WHERE mp.menu_id = ?
            ORDER BY p.categorie, p.titre_plat, a.libelle
        ");
        $stmt->execute([$menuId]);
        return $stmt->fetchAll() ?: [];
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO menu (titre, nombre_personne_minimum, prix_par_personne, description, conditions, quantite_restante, regime_id, theme_id)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['titre'],
            $data['nombre_personne_minimum'],
            $data['prix_par_personne'],
            $data['description'],
            $data['conditions'],
            $data['quantite_restante'],
            $data['regime_id'],
            $data['theme_id'],
        ]);

        return (int)$this->pdo->lastInsertId();
    }

    public function update(int $menuId, array $data): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE menu
            SET titre = ?, nombre_personne_minimum = ?, prix_par_personne = ?, description = ?, conditions = ?, quantite_restante = ?, regime_id = ?, theme_id = ?
            WHERE menu_id = ?
        ");
        $stmt->execute([
            $data['titre'],
            $data['nombre_personne_minimum'],
            $data['prix_par_personne'],
            $data['description'],
            $data['conditions'],
            $data['quantite_restante'],
            $data['regime_id'],
            $data['theme_id'],
            $menuId,
        ]);
    }

    public function delete(int $menuId): void
    {
        // Supprimer liaisons d'abord
        $this->pdo->prepare("DELETE FROM menu_plat WHERE menu_id = ?")->execute([$menuId]);
        $this->pdo->prepare("DELETE FROM menu WHERE menu_id = ?")->execute([$menuId]);
    }

    // Images de la galerie d'un menu
    public function getImages(int $menuId): array
    {
        $stmt = $this->pdo->prepare("SELECT chemin FROM menu_image WHERE menu_id = ? ORDER BY image_id");
        $stmt->execute([$menuId]);

        $chemins = [];
        foreach ($stmt->fetchAll() as $row) {
            $chemins[] = $row['chemin'];
        }
        return $chemins;
    }

    // Remplace toutes les images d'un menu
    public function replaceImages(int $menuId, array $chemins): void
    {
        $this->pdo->prepare("DELETE FROM menu_image WHERE menu_id = ?")->execute([$menuId]);

        $ins = $this->pdo->prepare("INSERT INTO menu_image (menu_id, chemin) VALUES (?, ?)");
        foreach ($chemins as $chemin) {
            $ins->execute([$menuId, $chemin]);
        }
    }

    public function getAllPlats(): array
    {
        return $this->pdo->query("SELECT * FROM plat ORDER BY titre_plat")->fetchAll() ?: [];
    }
}