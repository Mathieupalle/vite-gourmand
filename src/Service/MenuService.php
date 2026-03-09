<?php
declare(strict_types=1);

namespace App\Service;

use App\Repository\MenuRepository;
use PDO;
use Throwable;
use Exception;

final class MenuService
{
    public function __construct(
        private PDO $pdo,
        private MenuRepository $repo
    ) {
        // Sécurité : on s'assure que le repo utilise le même PDO
    }

    public function createFromPost(array $post): int
    {
        $data = $this->validate($post);

        $this->pdo->beginTransaction();
        try {
            $id = $this->repo->create($data);
            $this->pdo->commit();
            return $id;
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function updateFromPost(int $menuId, array $post): void
    {
        if ($menuId <= 0) throw new Exception("Menu invalide.");
        $data = $this->validate($post);

        $this->pdo->beginTransaction();
        try {
            $this->repo->update($menuId, $data);
            $this->pdo->commit();
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function delete(int $menuId): void
    {
        if ($menuId <= 0) return;

        $this->pdo->beginTransaction();
        try {
            $this->repo->delete($menuId);
            $this->pdo->commit();
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function syncPlats(int $menuId, array $platIds): void
    {
        if ($menuId <= 0) throw new Exception("Menu invalide.");

        $platIds = array_values(array_filter(array_map('intval', $platIds), fn($v) => $v > 0));

        $this->pdo->beginTransaction();
        try {
            $this->repo->replaceMenuPlats($menuId, $platIds);
            $this->pdo->commit();
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    private function validate(array $post): array
    {
        $titre = trim((string)($post['titre'] ?? ''));
        $desc  = trim((string)($post['description'] ?? ''));
        $min   = (int)($post['nombre_personne_minimum'] ?? 0);
        $prix  = (float)($post['prix_par_personne'] ?? 0);
        $qte   = ($post['quantite_restante'] ?? '') === '' ? null : (int)$post['quantite_restante'];
        $regimeId = (int)($post['regime_id'] ?? 0);
        $themeId  = (int)($post['theme_id'] ?? 0);

        if ($titre === '' || mb_strlen($titre) < 2) throw new Exception("Titre invalide.");
        if ($min <= 0) throw new Exception("Nombre minimum invalide.");
        if ($prix <= 0) throw new Exception("Prix invalide.");
        if ($regimeId <= 0) throw new Exception("Régime invalide.");
        if ($themeId <= 0) throw new Exception("Thème invalide.");

        return [
            'titre' => $titre,
            'nombre_personne_minimum' => $min,
            'prix_par_personne' => $prix,
            'description' => ($desc === '' ? null : $desc),
            'quantite_restante' => $qte,
            'regime_id' => $regimeId,
            'theme_id' => $themeId,
        ];
    }

    public function getFiltersData(): array
    {
        return [
            'themes'  => $this->repo->getThemes(),
            'regimes' => $this->repo->getRegimes(),
        ];
    }

    public function getMenuDetail(int $menuId): ?array
    {
        if ($menuId <= 0) {
            return null;
        }

        $menu = $this->repo->findByIdWithThemeRegime($menuId);
        if (!$menu) {
            return null;
        }

        $rows = $this->repo->getMenuPlatsWithAllergenes($menuId);

        $group = [
            'entree'  => [],
            'plat'    => [],
            'dessert' => [],
        ];

        foreach ($rows as $row) {
            $cat = $row['categorie'] ?? 'plat';
            if (!isset($group[$cat])) {
                $cat = 'plat';
            }

            $pid = (int)$row['plat_id'];

            if (!isset($group[$cat][$pid])) {
                $group[$cat][$pid] = [
                    'titre' => $row['titre_plat'],
                    'allergenes' => [],
                ];
            }

            if (!empty($row['allergene'])) {
                $group[$cat][$pid]['allergenes'][] = $row['allergene'];
            }
        }

        foreach ($group as $k => $v) {
            $group[$k] = array_values($v);
        }

        $menu['plats_grouped'] = $group;

        return $menu;
    }

    public function getMenusForCards(array $filters): array
    {
        return $this->repo->findForCardsFiltered($filters);
    }

    public function getThemes(): array
    {
        return $this->repo->getThemes();
    }

    public function getRegimes(): array
    {
        return $this->repo->getRegimes();
    }

    public function getAllMenusForManage(): array
    {
        return $this->repo->findAllForManage();
    }
}