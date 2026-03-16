<?php
declare(strict_types=1);

namespace App\Controller;

use App\Infrastructure\Database;
use App\Repository\UserRepository;
use App\Service\UserService;
use App\Security\Auth;
use App\Core\View;

class ProfileController
{
    public function profile(): void
    {
        Auth::requireLogin();

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $userId = (int)($_SESSION['user']['id'] ?? $_SESSION['user']['utilisateur_id'] ?? 0);
        if ($userId <= 0) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $pdo = Database::getConnection();
        $repo = new UserRepository($pdo);
        $service = new UserService($repo);

        $userDb = $repo->findById($userId);
        if (!$userDb) {
            http_response_code(404);
            exit("Utilisateur introuvable.");
        }

        $errors = [];
        $success = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $fresh = $service->updateProfile($userId, $_POST);
                $success = "Profil mis à jour.";

                $_SESSION['user']['nom'] = $fresh['nom'];
                $_SESSION['user']['prenom'] = $fresh['prenom'];
                $_SESSION['user']['telephone'] = $fresh['telephone'];
                $_SESSION['user']['ville'] = $fresh['ville'];
                $_SESSION['user']['adresse_postale'] = $fresh['adresse_postale'];

                $userDb = $fresh;
            } catch (\Throwable $e) {
                $errors[] = $e->getMessage();
            }
        }

        View::render('profile', [
            'userDb' => $userDb,
            'errors' => $errors,
            'success' => $success
        ]);
    }
}