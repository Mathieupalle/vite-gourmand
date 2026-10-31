<?php
declare(strict_types=1);

namespace App\Service;

use App\Repository\ReferentielRepository;
use Exception;
use PDOException;

final class ReferentielService
{
    public function __construct(private ReferentielRepository $repo) {}

    // Traite un formulaire de la page : ajouter, renommer ou supprimer
    public function traiterPost(array $post): string
    {
        $type = (string)($post['type'] ?? '');
        $action = (string)($post['action'] ?? '');
        $id = (int)($post['id'] ?? 0);
        $libelle = trim((string)($post['libelle'] ?? ''));

        if (!ReferentielRepository::typeValide($type)) {
            throw new Exception("Type invalide.");
        }

        $nom = $type === 'theme' ? 'Thème' : 'Allergène';

        try {
            if ($action === 'ajouter') {
                $this->verifierLibelle($libelle);
                $this->repo->create($type, $libelle);
                return "$nom ajouté.";
            }

            if ($action === 'renommer' && $id > 0) {
                $this->verifierLibelle($libelle);
                $this->repo->update($type, $id, $libelle);
                return "$nom modifié.";
            }

            if ($action === 'supprimer' && $id > 0) {
                $this->repo->delete($type, $id);
                return "$nom supprimé.";
            }
        } catch (PDOException $e) {
            // 23000 : contrainte non respectée (libellé déjà utilisé, ou thème utilisé par un menu)
            if ($e->getCode() === '23000') {
                throw new Exception("Action impossible : ce libellé existe déjà ou il est utilisé par un menu.");
            }
            throw $e;
        }

        throw new Exception("Action invalide.");
    }

    private function verifierLibelle(string $libelle): void
    {
        if (mb_strlen($libelle) < 2 || mb_strlen($libelle) > 50) {
            throw new Exception("Le libellé doit faire entre 2 et 50 caractères.");
        }
    }
}
