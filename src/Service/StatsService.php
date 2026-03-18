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

    public function getAggregatedStats(
        string $start,
        string $end,
        string $group = 'day',
        int $menuId = 0,
        ?int $compareMenuId = null,
        ?string $compareStart = null,
        ?string $compareEnd = null,
        string $compareMode = 'relative'
    ): array {
        return $this->repository->aggregateStats(
            $start,
            $end,
            $group,
            $menuId,
            $compareMenuId,
            $compareStart,
            $compareEnd,
            $compareMode
        );
    }
}