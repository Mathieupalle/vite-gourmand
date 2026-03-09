<?php
declare(strict_types=1);

namespace App\Repository;

use PDO;

class AdminRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // Compte le nombre total d'utilisateurs
    public function countUsers(): int
    {
        $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM users");
        $row = $stmt->fetch();
        return (int)($row['total'] ?? 0);
    }

    // Compte le nombre total de commandes
    public function countOrders(): int
    {
        $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM commandes");
        $row = $stmt->fetch();
        return (int)($row['total'] ?? 0);
    }

    // Compte le nombre total de menus
    public function countMenus(): int
    {
        $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM menus");
        $row = $stmt->fetch();
        return (int)($row['total'] ?? 0);
    }
}