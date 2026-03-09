<?php
declare(strict_types=1);

namespace App\Controller;

use App\Core\View;
use App\Infrastructure\Database;
use App\Repository\AvisRepository;
use App\Security\Auth;
use App\Service\AvisService;

final class AvisController
{
    // Gestion des avis
    public function avisManage(): void
    {
        Auth::requireRole(['employee', 'admin']);

        $pdo = Database::getConnection();
        $repo = new AvisRepository($pdo);
        $service = new AvisService($repo);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $avisId = (int)($_POST['avis_id'] ?? 0);
            $statut = trim((string)($_POST['statut'] ?? ''));

            $service->updateAvisStatut($avisId, $statut);

            redirect('/avisManage.php');
            exit;
        }

        $avis = $repo->listAllForManage();

        View::render('avis/avisManage', [
            'avis' => $avis
        ]);
    }

    // Création d'avis
    public function avisCreate(): void
    {
        Auth::requireLogin();

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $userId = (int)($_SESSION['user']['id'] ?? $_SESSION['user']['utilisateur_id'] ?? 0);
        if ($userId <= 0) {
            redirect('/login.php');
            exit;
        }

        $commandeId = (int)($_GET['commande_id'] ?? 0);
        if ($commandeId <= 0) {
            http_response_code(400);
            exit("Commande invalide.");
        }

        $pdo = Database::getConnection();
        $repo = new AvisRepository($pdo);
        $service = new AvisService($repo);

        $commande = $repo->findCommandeForAvis($commandeId, $userId);

        if (!$commande) {
            http_response_code(404);
            exit("Commande introuvable.");
        }

        $errors = [];
        $success = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {

                $service->submitAvis($commandeId, $userId, $_POST);

                $success = "Merci ! Votre avis a été envoyé et sera visible après validation.";

            } catch (\Throwable $e) {

                $errors[] = $e->getMessage();

            }
        }

        View::render('avis/avisCreate', [
            'commande' => $commande,
            'errors' => $errors,
            'success' => $success
        ]);
    }
}