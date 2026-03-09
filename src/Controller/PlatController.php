<?php
declare(strict_types=1);

namespace App\Controller;

use App\Infrastructure\Database;
use App\Repository\PlatRepository;
use App\Security\Auth;
use App\Service\PlatService;
use App\View\View;
use Throwable;

class PlatController
{
    // Modifier plats
    public function platEdit(): void
    {
        Auth::requireRole(['employee', 'admin']);

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (empty($_SESSION['csrf'])) {
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
        }

        $pdo = Database::getConnection();
        $repo = new PlatRepository($pdo);
        $service = new PlatService($pdo, $repo);

        $platId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if (!$platId) {
            http_response_code(400);
            exit("ID invalide.");
        }

        $plat = $repo->findById($platId);

        if (!$plat) {
            http_response_code(404);
            exit("Plat introuvable.");
        }

        $allergenes = $repo->listAllergenes();
        $selected = $repo->getAllergeneIdsForPlat($platId);

        $errors = [];
        $success = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
                $errors[] = "Token CSRF invalide.";
            } else {

                try {

                    $service->updateFromPost($platId, $_POST);

                    header('Location: ' . BASE_URL . '/platEdit.php?id=' . $platId . '&success=1');
                    exit;

                } catch (Throwable $e) {
                    $errors[] = $e->getMessage();
                }
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
        Auth::requireRole(['employee', 'admin']);

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (empty($_SESSION['csrf'])) {
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
        }

        $pdo = Database::getConnection();
        $repo = new PlatRepository($pdo);
        $service = new PlatService($pdo, $repo);

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {

            if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
                http_response_code(403);
                exit('Token CSRF invalide');
            }

            $service->delete((int)$_POST['delete_id']);

            redirect('/platManage.php');
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
        Auth::requireRole(['employee', 'admin']);

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