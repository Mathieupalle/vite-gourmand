<?php
declare(strict_types=1);

namespace App\Service;

use App\Repository\StatsRepository;

final class StatsService
{
    private StatsRepository $repository;

    public function __construct(StatsRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getMenusForView(): array
    {
        return $this->repository->getMenus();
    }

    public function getStatsParMenu(string $dateDebut, string $dateFin, int $menuId): array
    {
        // Dates par défaut : du 1er janvier à aujourd'hui
        if (!$this->dateValide($dateDebut)) {
            $dateDebut = date('Y-01-01');
        }
        if (!$this->dateValide($dateFin)) {
            $dateFin = date('Y-m-d');
        }

        return $this->repository->getStatsParMenu($dateDebut, $dateFin, $menuId);
    }

    // Vérifie qu'une date est au format AAAA-MM-JJ
    private function dateValide(string $date): bool
    {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d !== false && $d->format('Y-m-d') === $date;
    }
}
