<?php
declare(strict_types=1);

namespace App\Service;

use App\Repository\HoraireRepository;

final class HoraireService
{
    public function __construct(private HoraireRepository $repo) {}

    public function updateFromPost(array $post): void
    {
        $horaires = $post['horaires'] ?? [];
        foreach ($horaires as $horaireId => $values) {
            $id = (int)$horaireId;
            if ($id <= 0) continue;

            $ouverture = trim((string)($values['ouverture'] ?? ''));
            $fermeture = trim((string)($values['fermeture'] ?? ''));

            $this->repo->updateOne($id, $ouverture, $fermeture);
        }
    }
}