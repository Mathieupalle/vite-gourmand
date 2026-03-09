<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\CommandeStatus;
use App\Repository\CommandeRepository;
use DateTimeImmutable;
use Exception;
use PDO;
use Throwable;

final class CommandeService
{
    public function __construct(
        private PDO $pdo,
        private CommandeRepository $repo
    ) {}

    public function creerCommandeDepuisPost(int $utilisateurId, array $post): array
    {
        $menuId = (int)($post['menu_id'] ?? 0);
        $nb     = (int)($post['nombre_personne'] ?? 0);

        $datePrestation = (string)($post['date_prestation'] ?? '');
        $dateLivraison  = (string)($post['date_livraison'] ?? '');
        if (isset($post['same_date'])) {
            $dateLivraison = $datePrestation;
        }

        $heureLivraison = (string)($post['heure_livraison'] ?? '');

        $adressePrestation = trim((string)($post['adresse_prestation'] ?? ''));
        $villePrestation   = trim((string)($post['ville_prestation'] ?? ''));

        $adresseLivraison  = trim((string)($post['adresse_livraison'] ?? ''));
        $villeLivraison    = trim((string)($post['ville_livraison'] ?? ''));

        $distanceKm = (float)($post['distance_km'] ?? 0);
        if ($distanceKm < 0) $distanceKm = 0;

        if ($menuId <= 0 || $nb <= 0 || $datePrestation === '' || $dateLivraison === '' || $heureLivraison === '') {
            throw new Exception("Données invalides.");
        }
        if ($adressePrestation === '' || $villePrestation === '' || $adresseLivraison === '' || $villeLivraison === '') {
            throw new Exception("Données invalides.");
        }

        $today = new DateTimeImmutable('today');
        $dp = DateTimeImmutable::createFromFormat('Y-m-d', $datePrestation);
        $dl = DateTimeImmutable::createFromFormat('Y-m-d', $dateLivraison);

        if (!$dp || !$dl) throw new Exception("Dates invalides.");
        if ($dp < $today) throw new Exception("La date de prestation ne peut pas être avant aujourd’hui.");
        if ($dl < $today) throw new Exception("La date de livraison ne peut pas être avant aujourd’hui.");

        $menu = $this->repo->findMenuById($menuId);
        if (!$menu) throw new Exception("Menu introuvable.");

        $min = (int)$menu['nombre_personne_minimum'];
        if ($nb < $min) throw new Exception("Nombre minimum non respecté.");

        $menuTitre = (string)$menu['titre'];
        $prixPers  = (float)$menu['prix_par_personne'];

        // IMPORTANT : prix_menu stocke le TOTAL (nb * prix/pers)
        $sousTotal = $nb * $prixPers;

        $remise = 0.0;
        if ($nb >= ($min + 5)) {
            $remise = $sousTotal * 0.10;
        }

        $prixLivraison = 0.0;
        if (mb_strtolower($villeLivraison) !== 'bordeaux') {
            $prixLivraison = 5 + (0.59 * $distanceKm);
        }

        $totalFinal = $sousTotal - $remise + $prixLivraison;

        $numeroCommande = 'CMD-' . date('Ymd-His') . '-' . random_int(1000, 9999);

        $this->pdo->beginTransaction();
        try {
            $commandeId = $this->repo->createCommande([
                'numero_commande'      => $numeroCommande,
                'date_prestation'      => $datePrestation,
                'heure_livraison'      => $heureLivraison,
                'prix_menu'            => $sousTotal,
                'nombre_personne'      => $nb,
                'prix_livraison'       => $prixLivraison,
                'statut'               => CommandeStatus::EN_ATTENTE,
                'pret_materiel'        => 0,
                'restitution_materiel' => 0,
                'utilisateur_id'       => $utilisateurId,
                'menu_id'              => $menuId,
                'adresse_prestation'   => $adressePrestation,
                'ville_prestation'     => $villePrestation,
                'distance_km'          => $distanceKm,
                'remise'               => $remise,
                'date_livraison'       => $dateLivraison,
                'ville_livraison'      => $villeLivraison,
                'adresse_livraison'    => $adresseLivraison,
            ]);

            $this->repo->insertSuivi($commandeId, CommandeStatus::EN_ATTENTE, null, null);

            $this->pdo->commit();

            return [
                'commandeId' => $commandeId,
                'menuTitre'  => $menuTitre,
                'totalFinal' => $totalFinal,
                'numero'     => $numeroCommande,
                'statut'     => CommandeStatus::EN_ATTENTE,
            ];
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function changerStatut(int $commandeId, string $newStatut, string $role, ?string $modeContact, ?string $motif): void
    {
        if ($commandeId <= 0) throw new Exception("Données invalides.");
        if (!CommandeStatus::isValid($newStatut)) throw new Exception("Statut invalide.");

        if ($newStatut === CommandeStatus::ANNULEE && $role === 'employee') {
            $allowedModes = ['telephone', 'mail'];
            if ($modeContact === null || !in_array($modeContact, $allowedModes, true)) {
                throw new Exception("Mode de contact manquant.");
            }
            if ($motif === null || trim($motif) === '' || mb_strlen($motif) < 5) {
                throw new Exception("Motif d'annulation manquant (5 caractères minimum).");
            }
        }

        $this->pdo->beginTransaction();
        try {
            $current = $this->repo->findCommandeStatutById($commandeId);
            if ($current === null) throw new Exception("Commande introuvable.");
            if ($current === CommandeStatus::ANNULEE) throw new Exception("Commande déjà annulée.");
            if ($current === CommandeStatus::TERMINEE) throw new Exception("Commande terminée.");

            $this->repo->updateCommandeStatut($commandeId, $newStatut);

            $this->repo->insertSuivi(
                $commandeId,
                $newStatut,
                ($newStatut === CommandeStatus::ANNULEE ? $modeContact : null),
                ($newStatut === CommandeStatus::ANNULEE ? $motif : null)
            );

            $this->pdo->commit();
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}