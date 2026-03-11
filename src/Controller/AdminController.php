<?php
declare(strict_types=1);

namespace App\Controller;

use App\Core\View;
use App\Security\Auth;
use App\Infrastructure\Database;
use App\Infrastructure\MongoDB;
use App\Repository\AdminRepository;
use App\Repository\UserRepository;
use App\Repository\StatsRepository;
use App\Service\AdminService;
use App\Service\UserService;
use App\Service\StatsService;

class AdminController
{
    public function admin(): void
    {
        Auth::requireRole(['employe', 'admin']);

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $user = $_SESSION['user'] ?? null;
        $role = (string)($user['role'] ?? 'employe');

        // Initialisation Repository et Service pour le dashboard
        $pdo = Database::getConnection();
        $repo = new AdminRepository($pdo);
        $service = new AdminService($repo);

        $dashboardStats = $service->getDashboardStats();

        View::render('admin/admin', [
            'user'  => $user,
            'role'  => $role,
            'stats' => $dashboardStats
        ]);
    }

    // Gestion compte employés
    public function employeManage(): void
    {
        Auth::requireRole(['admin']);

        $pdo = Database::getConnection();
        $repo = new UserRepository($pdo);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)($_POST['utilisateur_id'] ?? 0);

            if ($id > 0) {
                $repo->deactivateUser($id);
            }

            redirect('/employeManage.php');
            exit;
        }

        $employes = $repo->listEmployees();

        View::render('admin/employeManage', [
            'employes' => $employes
        ]);
    }

    // Création compte employés
    public function employeCreate(): void
    {
        Auth::requireRole(['admin']);

        $pdo = Database::getConnection();
        $service = new UserService(new UserRepository($pdo));

        $errors = [];
        $success = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $email = trim((string)($_POST['email'] ?? ''));
            $password = (string)($_POST['password'] ?? '');

            try {

                $service->createEmployee($email, $password);

                $success = "Employé créé avec succès.";

                if ($email !== '') {

                    $to = $email;
                    $subject = "Création de votre compte employé - Vite & Gourmand";

                    $message =
                        "Bonjour,

Un compte employé vient d’être créé pour vous.

Identifiant (email) : {$email}

Le mot de passe n’est pas communiqué par mail.
Merci de contacter l’administrateur pour l’obtenir.

Cordialement,
Vite & Gourmand";

                    $headers = "From: contact@vitegourmand.fr";

                    @mail($to, $subject, $message, $headers);
                }

            } catch (\Throwable $e) {
                $errors[] = $e->getMessage();
            }
        }

        View::render('admin/employeCreate', [
            'errors' => $errors,
            'success' => $success
        ]);
    }

    // Statistiques commandes (MongoDB Atlas)
    public function stats(): void
    {
        Auth::requireRole(['admin']);

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        try {
            $client = MongoDB::createFromEnvOrLocalConfig();
            $repo = new StatsRepository($client);
            $service = new StatsService($repo);

            $menus = $service->getMenusForView();
        } catch (\Throwable $e) {
            http_response_code(500);
            exit("Erreur MongoDB : " . htmlspecialchars($e->getMessage()));
        }

        View::render('admin/stats', compact('menus'));
    }
}