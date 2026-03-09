<?php
declare(strict_types=1);

namespace App\Controller;

use App\Core\View;
use App\Service\MenuService;

final class MenuController
{
    public function __construct(private MenuService $service) {}

    public function menus(): void
    {
        $data = $this->service->getFiltersData();
        View::render('menus/menus', $data);
    }

    public function menu(int $id): void
    {
        $menu = $this->service->getMenuDetail($id);

        if (!$menu) {
            http_response_code(404);
            die('Menu introuvable');
        }

        $imagesWeb = $menu['images_web'] ?? [];
        $prix      = $menu['prix_par_personne'] ?? '';
        $minP      = $menu['nombre_personne_minimum'] ?? 0;
        $stock     = $menu['quantite_restante'] ?? null;
        $user      = $_SESSION['user'] ?? null;

        View::render('menus/menu', compact('menu', 'imagesWeb', 'prix', 'minP', 'stock', 'user'));
    }

    public function menusAjax(): void
    {
        header('Content-Type: text/html; charset=utf-8');

        $filters = [
            'prix_max' => $_GET['prix_max'] ?? null,
            'prix_min' => $_GET['prix_min'] ?? null,
            'prix_max_range' => $_GET['prix_max_range'] ?? null,
            'theme_id' => $_GET['theme_id'] ?? null,
            'regime_id' => $_GET['regime_id'] ?? null,
            'min_personnes' => $_GET['min_personnes'] ?? null,
        ];

        try {
            $menus = $this->service->getMenusForCards($filters);

            if (!$menus) {
                echo "<div class='alert alert-warning mb-0'><em>Aucun menu ne correspond aux filtres.</em></div>";
                return;
            }

            echo \App\Core\View::render('menus/menusAjax', [
                'menus' => $menus,
            ]);

        } catch (\Throwable $e) {
            http_response_code(500);
            echo "<div class='alert alert-danger mb-0'>Erreur serveur lors de la récupération des menus.</div>";
        }
    }

    public function menuCreate(): void
    {
        $user = $_SESSION['user'] ?? null;
        if (!$user || !in_array($user['role'], ['employee', 'admin'], true)) {
            http_response_code(403);
            die('Accès refusé');
        }

        $themes = $this->service->getThemes();
        $regimes = $this->service->getRegimes();
        $success = null;
        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $id = $this->service->createFromPost($_POST);
                $success = "Menu créé (#{$id}).";
            } catch (\Throwable $e) {
                $errors[] = $e->getMessage();
            }
        }

        \App\Core\View::render('menus/menuCreate', compact('themes', 'regimes', 'success', 'errors'));
    }

    public function menuEditPlats(): void
    {
        $user = $_SESSION['user'] ?? null;
        if (!$user || !in_array($user['role'], ['employee', 'admin'], true)) {
            http_response_code(403);
            die('Accès refusé');
        }

        $menus = $this->service->getAllMenus();
        $menuId = isset($_GET['menu_id']) ? (int)$_GET['menu_id'] : 0;

        $plats = [];
        $selectedPlatIds = [];
        $success = null;
        $errors = [];

        if ($menuId > 0) {
            $plats = $this->service->getAllPlats();
            $selectedPlatIds = $this->service->getPlatIdsForMenu($menuId);

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                try {
                    $platsToAssociate = $_POST['plats'] ?? [];
                    $this->service->updateMenuPlats($menuId, $platsToAssociate);
                    $selectedPlatIds = $platsToAssociate;
                    $success = "Plats mis à jour avec succès pour le menu #{$menuId}.";
                } catch (\Throwable $e) {
                    $errors[] = $e->getMessage();
                }
            }
        }

        \App\Core\View::render('menus/menuEditPlats', compact(
            'menus',
            'menuId',
            'plats',
            'selectedPlatIds',
            'success',
            'errors'
        ));
    }

    public function menuEdit(int $id): void
    {
        $menu = $this->service->getMenuDetail($id);
        if (!$menu) {
            http_response_code(404);
            die('Menu introuvable.');
        }

        $themes = $this->service->getThemes();
        $regimes = $this->service->getRegimes();
        $success = null;
        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $this->service->updateFromPost($id, $_POST);
                $success = "Menu mis à jour.";
                $menu = $this->service->getMenuDetail($id); // reload
            } catch (\Throwable $e) {
                $errors[] = $e->getMessage();
            }
        }

        View::render('menus/menuEdit', compact('menu', 'themes', 'regimes', 'success', 'errors'));
    }

    public function menuManage(): void
    {
        $success = null;
        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
            try {
                $this->service->delete((int)$_POST['delete_id']);
                $success = "Menu supprimé.";
            } catch (\Throwable $e) {
                $errors[] = $e->getMessage();
            }
        }

        $menus = $this->service->getAllMenusForManage();

        View::render('menus/menuManage', compact('menus', 'success', 'errors'));
    }
}