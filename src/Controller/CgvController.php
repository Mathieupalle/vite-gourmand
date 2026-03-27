<?php
declare(strict_types=1);

namespace App\Controller;

use App\Core\View;

final class CgvController
{
    public function cgv(): void
    {
        View::render('cgv');
    }
}