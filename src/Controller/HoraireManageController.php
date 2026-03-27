<?php
declare(strict_types=1);

namespace App\Controller;

use App\Core\View;
use App\Infrastructure\Database;
use App\Repository\HoraireRepository;
use App\Security\Auth;
use App\Service\HoraireService;

final class HoraireManageController
{
    public function horaireManage(): void
    {
        Auth::requireRole(['employe', 'admin']);

        $pdo = Database::getConnection();
        $repo = new HoraireRepository($pdo);
        $service = new HoraireService($repo);

        $success = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $service->updateFromPost($_POST);
            $success = "Horaires mis à jour.";
        }

        $rows = $repo->listAll();

        View::render('horaireManage', [
            'rows' => $rows,
            'success' => $success
        ]);
    }
}