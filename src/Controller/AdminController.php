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

            redirect('/employeManage');
            exit;
        }

        $employes = $repo->listEmployees();

        View::render('admin/employeManage', [
            'employes' => $employes
        ]);
    }

    // Création d'un compte employé
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
                // Création sécurisée de l'employé avec hashage bcrypt
                $service->createEmployee($email, $password);

                $success = "Employé créé avec succès.";

                // Envoi mail notification via MailService
                if ($email !== '') {
                    $mailService = new \App\Service\MailService();

                    $message = "Bonjour,

Un compte employé vient d'être créé pour vous.

Identifiant (email) : {$email}

Le mot de passe n'est pas communiqué par mail.
Merci de contacter l'administrateur pour l'obtenir.

Cordialement,
Vite & Gourmand";

                    $sent = $mailService->send(
                        $email,
                        "Création de votre compte employé - Vite & Gourmand",
                        $message
                    );

                    if (!$sent) {
                        $errors[] = "Impossible d'envoyer le mail de notification.";
                    }
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

    // Statistiques : commandes et chiffre d'affaires par menu (MongoDB Atlas)
    public function stats(): void
    {
        Auth::requireRole(['admin']);

        // Filtres envoyés par le formulaire (méthode GET)
        $dateDebut = (string)($_GET['date_debut'] ?? date('Y-01-01'));
        $dateFin = (string)($_GET['date_fin'] ?? date('Y-m-d'));
        $menuId = (int)($_GET['menu_id'] ?? 0);

        try {
            $client = MongoDB::createFromEnvOrLocalConfig();
            $service = new StatsService(new StatsRepository($client));

            $menus = $service->getMenusForView();
            $stats = $service->getStatsParMenu($dateDebut, $dateFin, $menuId);
        } catch (\Throwable $e) {
            error_log('Statistiques MongoDB : ' . $e->getMessage());
            http_response_code(503);
            exit("Statistiques indisponibles pour le moment.");
        }

        // Totaux pour la dernière ligne du tableau
        $totalCommandes = 0;
        $totalCA = 0.0;
        foreach ($stats as $ligne) {
            $totalCommandes += $ligne['nbCommandes'];
            $totalCA += $ligne['chiffreAffaires'];
        }

        View::render('admin/stats', [
            'menus' => $menus,
            'stats' => $stats,
            'dateDebut' => $dateDebut,
            'dateFin' => $dateFin,
            'menuId' => $menuId,
            'totalCommandes' => $totalCommandes,
            'totalCA' => $totalCA,
        ]);
    }
}