<?php
declare(strict_types=1);

namespace App\Service;

use App\Repository\AvisRepository;
use Exception;

final class AvisService
{
    public function __construct(private AvisRepository $repo) {}

    public function submitAvis(int $commandeId, int $userId, array $post): void
    {
        if ($commandeId <= 0) throw new Exception("Commande invalide.");

        $commande = $this->repo->findCommandeForAvis($commandeId, $userId);
        if (!$commande) throw new Exception("Commande introuvable.");

        if ((string)$commande['statut'] !== 'terminee') {
            throw new Exception("Vous pourrez laisser un avis uniquement une fois la commande terminée.");
        }

        if ($this->repo->existsForCommande($commandeId)) {
            throw new Exception("Un avis a déjà été envoyé pour cette commande.");
        }

        $note = (int)($post['note'] ?? 0);
        $description = trim((string)($post['description'] ?? ''));

        if ($note < 1 || $note > 5) throw new Exception("La note doit être entre 1 et 5.");
        if ($description === '') throw new Exception("Le commentaire est obligatoire.");

        $this->repo->create($note, $description, $userId, $commandeId);
    }

    public function updateAvisStatut(int $avisId, string $newStatut): void
    {
        $allowed = ['valide', 'refuse', 'en_attente'];
        if ($avisId <= 0) return;
        if (!in_array($newStatut, $allowed, true)) return;

        $this->repo->updateStatut($avisId, $newStatut);
    }
}