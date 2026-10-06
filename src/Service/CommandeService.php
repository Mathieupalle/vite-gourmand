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
    // Libellés affichés au client dans les mails de suivi
    private const LIBELLES_STATUT = [
        CommandeStatus::EN_ATTENTE              => 'en attente de validation',
        CommandeStatus::ACCEPTE                 => 'acceptée',
        CommandeStatus::EN_PREPARATION          => 'en préparation',
        CommandeStatus::EN_COURS_LIVRAISON      => 'en cours de livraison',
        CommandeStatus::LIVRE                   => 'livrée',
        CommandeStatus::ATTENTE_RETOUR_MATERIEL => 'en attente du retour du matériel',
        CommandeStatus::TERMINEE                => 'terminée',
        CommandeStatus::ANNULEE                 => 'annulée',
    ];

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

            // Mail de confirmation : un échec d'envoi n'annule jamais la commande
            $this->envoyerConfirmation($commandeId, $numeroCommande, $menuTitre, $nb, $dp, $totalFinal);

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

        if ($newStatut === CommandeStatus::ANNULEE && $role === 'employe') {
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

            // Mail de suivi : un échec d'envoi n'annule jamais le changement de statut
            $this->envoyerChangementStatut($commandeId, $newStatut);
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    // Lien vers "Mes commandes" (vide si APP_URL n'est pas défini)
    private function lienMesCommandes(): string
    {
        $base = rtrim((string)(getenv('APP_URL') ?: ''), '/');
        return $base !== '' ? $base . '/mesCommandes' : '';
    }

    private function envoyerConfirmation(int $commandeId, string $numero, string $menuTitre, int $nb, DateTimeImmutable $datePrestation, float $total): void
    {
        $lien = $this->lienMesCommandes();

        $corps = "Bonjour,\n\n"
            . "Nous avons bien reçu votre commande {$numero}.\n\n"
            . "Menu : {$menuTitre}\n"
            . "Nombre de personnes : {$nb}\n"
            . "Date de prestation : " . $datePrestation->format('d/m/Y') . "\n"
            . "Total : " . number_format($total, 2, ',', ' ') . " €\n\n"
            . "Vous pouvez suivre son état dans « Mes commandes »"
            . ($lien !== '' ? " : {$lien}" : ".") . "\n\n"
            . "Cordialement,\nVite & Gourmand";

        $this->notifierClient($commandeId, "Confirmation de votre commande {$numero}", $corps);
    }

    private function envoyerChangementStatut(int $commandeId, string $statut): void
    {
        try {
            $stmt = $this->pdo->prepare('SELECT numero_commande FROM commande WHERE commande_id = ? LIMIT 1');
            $stmt->execute([$commandeId]);
            $numero = (string)($stmt->fetchColumn() ?: '');
        } catch (Throwable $e) {
            error_log('Notification commande : ' . $e->getMessage());
            return;
        }

        if ($numero === '') {
            return;
        }

        $libelle = self::LIBELLES_STATUT[$statut] ?? $statut;
        $lien = $this->lienMesCommandes();

        $corps = "Bonjour,\n\n"
            . "Le statut de votre commande {$numero} a changé : elle est désormais {$libelle}.\n\n";

        if ($statut === CommandeStatus::TERMINEE) {
            $corps .= "Merci de votre confiance ! Votre avis nous intéresse : vous pouvez le déposer depuis « Mes commandes »"
                . ($lien !== '' ? " : {$lien}" : ".") . "\n\n";
        } elseif ($lien !== '') {
            $corps .= "Suivre votre commande : {$lien}\n\n";
        }

        $corps .= "Cordialement,\nVite & Gourmand";

        $this->notifierClient($commandeId, "Commande {$numero} : {$libelle}", $corps);
    }

    // Envoie un mail au client de la commande, sans jamais lever d'exception
    private function notifierClient(int $commandeId, string $sujet, string $corps): void
    {
        try {
            $email = $this->repo->findClientEmailByCommandeId($commandeId);
            if ($email === null || $email === '') {
                return;
            }

            // Comptes de démonstration : aucun envoi (domaine qui n'appartient pas au projet)
            if (str_ends_with(strtolower($email), '@demo.fr')) {
                return;
            }

            (new MailService())->send($email, $sujet, $corps);
        } catch (Throwable $e) {
            error_log('Notification commande : ' . $e->getMessage());
        }
    }
}
