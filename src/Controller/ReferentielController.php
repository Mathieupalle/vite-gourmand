<?php
declare(strict_types=1);

namespace App\Controller;

use App\Core\View;
use App\Infrastructure\Database;
use App\Repository\ReferentielRepository;
use App\Security\Auth;
use App\Service\ReferentielService;

final class ReferentielController
{
    // Gestion des thèmes et des allergènes (employé et administrateur)
    public function referentielManage(): void
    {
        Auth::requireRole(['employe', 'admin']);

        $pdo = Database::getConnection();
        $repo = new ReferentielRepository($pdo);
        $service = new ReferentielService($repo);

        $errors = [];
        $success = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $success = $service->traiterPost($_POST);
            } catch (\Throwable $e) {
                $errors[] = $e->getMessage();
            }
        }

        View::render('referentielManage', [
            'themes' => $repo->listAll('theme'),
            'allergenes' => $repo->listAll('allergene'),
            'errors' => $errors,
            'success' => $success,
        ]);
    }
}
