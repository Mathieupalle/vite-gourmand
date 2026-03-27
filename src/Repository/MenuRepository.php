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

    public function findForCardsFiltered(array $filters): array
    {
        // Définition des variables
        $prixMax = $filters['prix_max'] ?? null;
        $prixMin = $filters['prix_min'] ?? null;
        $prixMaxRange = $filters['prix_max_range'] ?? null;
        $themeId = $filters['theme_id'] ?? null;
        $regimeId = $filters['regime_id'] ?? null;
        $minPersonnes = $filters['min_personnes'] ?? null;

        // Helper pour construire WHERE + params
        $buildWhere = function(array $fieldsMap) use ($prixMax, $prixMin, $prixMaxRange, $themeId, $regimeId, $minPersonnes) {
            $where = [];
            $params = [];

            if (!empty($fieldsMap['prix'])) {
                if (is_numeric($prixMax) && (float)$prixMax > 0) { $where[] = "{$fieldsMap['prix']} <= ?"; $params[] = (float)$prixMax; }
                if (is_numeric($prixMin) && (float)$prixMin >= 0) { $where[] = "{$fieldsMap['prix']} >= ?"; $params[] = (float)$prixMin; }
                if (is_numeric($prixMaxRange) && (float)$prixMaxRange > 0) { $where[] = "{$fieldsMap['prix']} <= ?"; $params[] = (float)$prixMaxRange; }
            }

            if (!empty($fieldsMap['theme']) && is_numeric($themeId) && (int)$themeId > 0) { $where[] = "{$fieldsMap['theme']} = ?"; $params[] = (int)$themeId; }
            if (!empty($fieldsMap['regime']) && is_numeric($regimeId) && (int)$regimeId > 0) { $where[] = "{$fieldsMap['regime']} = ?"; $params[] = (int)$regimeId; }
            if (!empty($fieldsMap['min_personnes']) && is_numeric($minPersonnes) && (int)$minPersonnes > 0) { $where[] = "{$fieldsMap['min_personnes']} <= ?"; $params[] = (int)$minPersonnes; }

            return [$where, $params];
        };

        // Requête avec theme / regime
        $sqlRich = "
        SELECT m.menu_id, m.titre, m.description, m.prix_par_personne, m.nombre_personne_minimum,
               t.libelle AS theme, r.libelle AS regime
        FROM menu m
        JOIN theme t ON t.theme_id = m.theme_id
        JOIN regime r ON r.regime_id = m.regime_id
    ";

        [$where, $params] = $buildWhere([
            'prix' => 'm.prix_par_personne',
            'theme' => 'm.theme_id',
            'regime' => 'm.regime_id',
            'min_personnes' => 'm.nombre_personne_minimum'
        ]);

        if ($where) $sqlRich .= " WHERE " . implode(" AND ", $where);
        $sqlRich .= " ORDER BY m.menu_id DESC";

        try {
            $stmt = $this->pdo->prepare($sqlRich);
            $stmt->execute($params);
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            // Fallback si requête riche échoue
            $sqlSimple = "SELECT menu_id, titre, description, prix FROM menu m";

            [$whereSimple, $paramsSimple] = $buildWhere([
                'prix' => 'm.prix',
                'theme' => 'm.theme_id',
                'regime' => 'm.regime_id'
                // pas de min_personnes si la colonne n’existe pas
            ]);

            if ($whereSimple) $sqlSimple .= " WHERE " . implode(" AND ", $whereSimple);
            $sqlSimple .= " ORDER BY m.menu_id DESC";

            $stmt = $this->pdo->prepare($sqlSimple);
            $stmt->execute($paramsSimple);
            return $stmt->fetchAll() ?: [];
        }
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
        return array_map('intval', array_column($stmt->fetchAll() ?: [], 'plat_id'));
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
            INSERT INTO menu (titre, nombre_personne_minimum, prix_par_personne, description, quantite_restante, regime_id, theme_id)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['titre'],
            $data['nombre_personne_minimum'],
            $data['prix_par_personne'],
            $data['description'],
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
            SET titre = ?, nombre_personne_minimum = ?, prix_par_personne = ?, description = ?, quantite_restante = ?, regime_id = ?, theme_id = ?
            WHERE menu_id = ?
        ");
        $stmt->execute([
            $data['titre'],
            $data['nombre_personne_minimum'],
            $data['prix_par_personne'],
            $data['description'],
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

    public function getAllPlats(): array
    {
        return $this->pdo->query("SELECT * FROM plat ORDER BY titre_plat")->fetchAll() ?: [];
    }
}