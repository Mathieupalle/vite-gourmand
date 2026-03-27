<?php
declare(strict_types=1);

namespace App\Service;

use App\Repository\HomeRepository;

final class HomeService
{
    public function __construct(private HomeRepository $repo) {}

    public function getHoraires(): array
    {
        return $this->repo->getHoraires();
    }

    public function getAvisValides(int $limit = 5): array
    {
        return $this->repo->getAvisValides($limit);
    }
}