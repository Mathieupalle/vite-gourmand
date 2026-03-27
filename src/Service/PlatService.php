<?php
declare(strict_types=1);

namespace App\Service;

use App\Repository\PlatRepository;
use PDO;
use Throwable;
use Exception;

final class PlatService
{
    public function __construct(private PDO $pdo, private PlatRepository $repo) {}

    public function createFromPost(array $post): int
    {
        [$titre, $categorie, $allergenes] = $this->validate($post);

        $this->pdo->beginTransaction();
        try {
            $id = $this->repo->create($titre, $categorie);
            $this->repo->replacePlatAllergenes($id, $allergenes);
            $this->pdo->commit();
            return $id;
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function updateFromPost(int $platId, array $post): void
    {
        if ($platId <= 0) throw new Exception("Plat invalide.");
        [$titre, $categorie, $allergenes] = $this->validate($post);

        $this->pdo->beginTransaction();
        try {
            $this->repo->update($platId, $titre, $categorie);
            $this->repo->replacePlatAllergenes($platId, $allergenes);
            $this->pdo->commit();
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function delete(int $platId): void
    {
        if ($platId <= 0) return;

        $this->pdo->beginTransaction();
        try {
            $this->repo->delete($platId);
            $this->pdo->commit();
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    private function validate(array $post): array
    {
        $titre = trim((string)($post['titre_plat'] ?? ''));
        $categorie = trim((string)($post['categorie'] ?? 'plat'));

        $allowedCat = ['entree', 'plat', 'dessert', 'boisson', 'fromage', 'autre', 'mise_en_bouche'];
        if ($categorie === '') $categorie = 'plat';
        if (!in_array($categorie, $allowedCat, true)) $categorie = 'plat';

        $allergenes = $post['allergenes'] ?? [];
        $allergenes = array_values(array_filter(array_map('intval', (array)$allergenes), fn($v) => $v > 0));

        if ($titre === '' || mb_strlen($titre) < 2) throw new Exception("Titre du plat invalide.");

        return [$titre, $categorie, $allergenes];
    }
}