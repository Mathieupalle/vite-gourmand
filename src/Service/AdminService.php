<?php
declare(strict_types=1);

namespace App\Service;

use App\Repository\AdminRepository;

class AdminService
{
    private AdminRepository $repo;

    public function __construct(AdminRepository $repo)
    {
        $this->repo = $repo;
    }
    
    public function getDashboardStats(): array
    {
        return [
            'total_users'  => $this->repo->countUsers(),
            'total_orders' => $this->repo->countOrders(),
            'total_menus'  => $this->repo->countMenus(),
        ];
    }
}