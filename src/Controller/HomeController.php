<?php
declare(strict_types=1);

namespace App\Controller;

use App\Core\View;
use App\Service\HomeService;

final class HomeController
{
    public function __construct(private HomeService $service) {}

    public function home(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $user = $_SESSION['user'] ?? null;

        $horaires = [];
        $avisValides = [];

        try {
            $horaires = $this->service->getHoraires();
            $avisValides = $this->service->getAvisValides(5);
        } catch (\Throwable $e) {
            // Pour debug local
            error_log("HomeController error: " . $e->getMessage());
        }

        View::render('home', compact('user', 'horaires', 'avisValides'));
    }
}