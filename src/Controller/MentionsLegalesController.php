<?php
declare(strict_types=1);

namespace App\Controller;

use App\Core\View;

final class MentionsLegalesController
{
    public function mentionsLegales(): void
    {
        View::render('mentionsLegales');
    }
}