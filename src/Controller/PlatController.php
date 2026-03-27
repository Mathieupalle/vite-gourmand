<?php
declare(strict_types=1);

namespace App\Controller;

use App\Infrastructure\Database;
use App\Repository\PlatRepository;
use App\Security\Auth;
use App\Service\PlatService;
use App\Core\View;
use Throwable;

class PlatController
{
    // Modifier plats
    public function platEdit(): void
    {
        Auth::requireRole(['employe', 'admin']);

        $pdo = Database::getConnection();
        $repo = new PlatRepository($pdo);
        $service = new PlatService($pdo, $repo);

        $platId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$platId) exit("ID invalide.");

        $plat = $repo->findById($platId);
        if (!$plat) exit("Plat introuvable.");

        $allergenes = $repo->listAllergenes();
        $selected = $repo->getAllergeneIdsForPlat($platId);

        $errors = [];
        $success = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $service->updateFromPost($platId, $_POST);
                header('Location: ' . BASE_URL . '/platEdit?id=' . $platId . '&success=1');
                exit;
            } catch (Throwable $e) {
                $errors[] = $e->getMessage();
            }
        }

        if (isset($_GET['success'])) {
            $success = "Plat mis à jour.";
        }

        View::render('plats/platEdit', [
            'plat' => $plat,
            'allergenes' => $allergenes,
            'selected' => $selected,
            'errors' => $errors,
            'success' => $success
        ]);
    }

    // Gestion des plats
    public function platManage(): void
    {
        Auth::requireRole(['employe', 'admin']);

        $pdo = Database::getConnection();
        $repo = new PlatRepository($pdo);
        $service = new PlatService($pdo, $repo);

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
            $service->delete((int)$_POST['delete_id']);
            redirect('/platManage');
            exit;
        }

        $plats = $repo->listAll();

        View::render('plats/platManage', [
            'plats' => $plats
        ]);
    }

    // Création de plats
    public function platCreate(): void
    {
        Auth::requireRole(['employe', 'admin']);

        $pdo = Database::getConnection();
        $repo = new PlatRepository($pdo);
        $service = new PlatService($pdo, $repo);

        $allergenes = $repo->listAllergenes();
        $errors = [];
        $success = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $id = $service->createFromPost($_POST);
                $success = "Plat créé (#{$id}).";
            } catch (Throwable $e) {
                $errors[] = $e->getMessage();
            }
        }

        View::render('plats/platCreate', [
            'allergenes' => $allergenes,
            'errors'     => $errors,
            'success'    => $success,
        ]);
    }
}