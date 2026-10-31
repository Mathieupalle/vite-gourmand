<?php
declare(strict_types=1);

namespace App\Controller;

use App\Infrastructure\Database;
use App\Entity\CommandeStatus;
use App\Service\CommandeService;
use App\Repository\CommandeRepository;
use App\Security\Auth;
use App\Core\View;
use App\Migration\MigrateOrders;
use MongoDB\Client as MongoClient;
use DateTimeImmutable;
use Throwable;

class CommandeController
{

    private function redirect(string $path): void
    {
        header('Location: ' . rtrim(BASE_URL, '/') . '/' . ltrim($path, '/'));
        exit;
    }

    // Met à jour la commande dans MongoDB (statistiques)
    // Si MongoDB est indisponible, l'erreur est notée dans les logs sans bloquer l'utilisateur
    private function syncMongo(\PDO $pdo, int $commandeId): void
    {
        $mongoUri = getenv('MONGODB_URI');
        if (!$mongoUri) {
            return;
        }

        try {
            $migrator = new MigrateOrders($pdo, new MongoClient($mongoUri));
            $migrator->syncCommande($commandeId);
        } catch (Throwable $e) {
            error_log("Erreur synchronisation MongoDB : " . $e->getMessage());
        }
    }

    // Commande
    public function commande(): void
    {
        Auth::requireLogin();

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $userId = (int)($_SESSION['user']['utilisateur_id'] ?? $_SESSION['user']['id'] ?? 0);
        if ($userId <= 0) {
            $this->redirect('/login');
        }

        $menuId = (int)($_GET['menu_id'] ?? 0);
        if ($menuId <= 0) {
            http_response_code(400);
            exit("Menu invalide.");
        }

        $pdo = Database::getConnection();
        $repo = new CommandeRepository($pdo);

        $menu = $repo->findMenuById($menuId);
        if (!$menu) {
            http_response_code(404);
            exit("Menu introuvable.");
        }

        $prixParPersonne = (float)$menu['prix_par_personne'];
        $minPers         = (int)$menu['nombre_personne_minimum'];
        $today           = (new DateTimeImmutable('today'))->format('Y-m-d');

        View::render('commandes/commande', [
            'client'          => $_SESSION['user'],
            'menu'            => $menu,
            'prixParPersonne' => $prixParPersonne,
            'minPers'         => $minPers,
            'today'           => $today,
        ]);
    }

    // Gestion de commandes (employé/admin)
    public function commandeManage(): void
    {
        Auth::requireRole(['employe', 'admin']);

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $user = $_SESSION['user'] ?? [];
        $role = (string)($user['role'] ?? 'employe');

        $pdo = Database::getConnection();
        $repo = new CommandeRepository($pdo);

        $allowedStatus = CommandeStatus::all();
        $statusLabels = [
            CommandeStatus::EN_ATTENTE              => 'en attente',
            CommandeStatus::ACCEPTE                 => 'accepté',
            CommandeStatus::EN_PREPARATION          => 'en préparation',
            CommandeStatus::EN_COURS_LIVRAISON      => 'en cours de livraison',
            CommandeStatus::LIVRE                   => 'livré',
            CommandeStatus::ATTENTE_RETOUR_MATERIEL => 'en attente du retour de matériel',
            CommandeStatus::TERMINEE                => 'terminée',
            CommandeStatus::ANNULEE                 => 'annulée',
        ];

        // Un seul filtre à la fois : statut, menu ou client
        $apply = (string)($_GET['apply'] ?? '');
        $statutFiltre = trim((string)($_GET['statut'] ?? ''));
        $menuFiltre   = (int)($_GET['menu_id'] ?? 0);
        $clientFiltre = trim((string)($_GET['client'] ?? ''));

        if ($apply !== 'statut' || !in_array($statutFiltre, $allowedStatus, true)) {
            $statutFiltre = '';
        }
        if ($apply !== 'menu') {
            $menuFiltre = 0;
        }
        if ($apply !== 'client') {
            $clientFiltre = '';
        }

        $menuStats = $repo->countCommandesParMenu();
        $commandes = $repo->findCommandesForManage($statutFiltre, $menuFiltre, $clientFiltre);

        View::render('commandes/commandeManage', [
            'role'          => $role,
            'allowedStatus' => $allowedStatus,
            'statusLabels'  => $statusLabels,
            'menuStats'     => $menuStats,
            'commandes'     => $commandes,
            'statutFiltre'  => $statutFiltre,
            'menuFiltre'    => $menuFiltre,
            'clientFiltre'  => $clientFiltre,
        ]);
    }

    // Annuler une commande (formulaire POST depuis "Mes commandes")
    public function commandeAnnuler(): void
    {
        Auth::requireLogin();
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit("Méthode non autorisée.");
        }

        $userId = (int)($_SESSION['user']['utilisateur_id'] ?? $_SESSION['user']['id'] ?? 0);
        if ($userId <= 0) $this->redirect('/login');

        $commandeId = (int)($_POST['commande_id'] ?? 0);
        if ($commandeId <= 0) {
            http_response_code(400);
            exit("Commande invalide.");
        }

        $pdo = Database::getConnection();
        $repo = new CommandeRepository($pdo);
        $service = new CommandeService($pdo, $repo);

        $commande = $repo->findCommandeOfUser($commandeId, $userId);
        if (!$commande) {
            http_response_code(404);
            exit("Commande introuvable.");
        }

        if ((string)$commande['statut'] !== CommandeStatus::EN_ATTENTE) {
            http_response_code(403);
            exit("Annulation impossible : commande déjà traitée.");
        }

        try {
            $service->changerStatut($commandeId, CommandeStatus::ANNULEE, 'client', null, null);
            $this->syncMongo($pdo, $commandeId);
            $this->redirect('/mesCommandes');
        } catch (Throwable $e) {
            http_response_code(500);
            echo "Erreur : " . htmlspecialchars($e->getMessage());
        }
    }

    // Créer une commande
    public function commandeCreate(): void
    {
        Auth::requireLogin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit("Méthode non autorisée.");
        }

        if (session_status() !== PHP_SESSION_ACTIVE) session_start();

        $userId = (int)($_SESSION['user']['utilisateur_id'] ?? $_SESSION['user']['id'] ?? 0);
        if ($userId <= 0) {
            http_response_code(400);
            exit("Utilisateur invalide.");
        }

        $pdo = Database::getConnection();
        $repo = new CommandeRepository($pdo);
        $service = new CommandeService($pdo, $repo);

        try {
            // Création de la commande dans MySQL
            $service->creerCommandeDepuisPost($userId, $_POST);

            // Copie des nouvelles commandes dans MongoDB pour les statistiques
            $mongoUri = getenv('MONGODB_URI');
            if ($mongoUri) {
                try {
                    $mongo = new MongoClient($mongoUri);
                    $migrator = new MigrateOrders($pdo, $mongo);
                    $migrator->migrateNewOrders();
                } catch (\Throwable $e) {
                    // Pas d'erreur critique : la commande est déjà dans MySQL
                    error_log("Erreur migration MongoDB : " . $e->getMessage());
                }
            }

            $this->redirect('/mesCommandes');

        } catch (\Throwable $e) {
            http_response_code(500);
            echo "Erreur : " . htmlspecialchars($e->getMessage());
        }
    }

    // Modifier commande
    public function commandeEdit(): void
    {
        Auth::requireLogin();
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();

        $userId = (int)($_SESSION['user']['utilisateur_id'] ?? $_SESSION['user']['id'] ?? 0);
        if ($userId <= 0) $this->redirect('/login');

        $commandeId = (int)($_GET['id'] ?? 0);
        if ($commandeId <= 0) {
            http_response_code(400);
            exit("Commande invalide.");
        }

        $pdo = Database::getConnection();
        $repo = new CommandeRepository($pdo);
        $commande = $repo->findCommandeOfUser($commandeId, $userId);

        if (!$commande) {
            http_response_code(404);
            exit("Commande introuvable.");
        }

        if ((string)$commande['statut'] !== CommandeStatus::EN_ATTENTE) {
            http_response_code(403);
            exit("Modification impossible : commande déjà traitée.");
        }

        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processPost($commande, $userId, $errors, $pdo, $repo);
        }

        View::render('commandes/commandeEdit', [
            'c' => $commande,
            'errors' => $errors,
        ]);
    }

    private function processPost(array &$c, int $userId, array &$errors, \PDO $pdo, CommandeRepository $repo): void
    {
        try {
            // Récupérer les données du POST
            $datePrestation   = trim((string)($_POST['date_prestation'] ?? ''));
            $adressePrestation = trim((string)($_POST['adresse_prestation'] ?? ''));
            $villePrestation  = trim((string)($_POST['ville_prestation'] ?? ''));

            $dateLivraison    = trim((string)($_POST['date_livraison'] ?? ''));
            $heureLivraison   = trim((string)($_POST['heure_livraison'] ?? ''));
            $adresseLivraison = trim((string)($_POST['adresse_livraison'] ?? ''));
            $villeLivraison   = trim((string)($_POST['ville_livraison'] ?? ''));
            $distanceKm       = (float)($_POST['distance_km'] ?? 0);

            $nombrePersonne   = (int)($_POST['nombre_personne'] ?? $c['nombre_personne']);

            // Validation simple
            if ($datePrestation === '' || $adressePrestation === '' || $villePrestation === '') {
                throw new \Exception("Informations prestation invalides.");
            }
            if ($dateLivraison === '' || $heureLivraison === '' || $adresseLivraison === '' || $villeLivraison === '') {
                throw new \Exception("Informations livraison invalides.");
            }
            if ($nombrePersonne < (int)$c['nombre_personne_minimum']) {
                throw new \Exception("Nombre de personnes trop faible.");
            }

            // Recalcul du prix côté serveur
            $prix = CommandeService::calculerPrix(
                (float)$c['prix_par_personne'],
                $nombrePersonne,
                (int)$c['nombre_personne_minimum'],
                $villeLivraison,
                $distanceKm
            );

            // Mettre à jour la commande (le menu ne peut pas changer)
            $repo->updateCommandeClient((int)$c['commande_id'], $userId, [
                'date_prestation'    => $datePrestation,
                'adresse_prestation' => $adressePrestation,
                'ville_prestation'   => $villePrestation,
                'date_livraison'     => $dateLivraison,
                'heure_livraison'    => $heureLivraison,
                'adresse_livraison'  => $adresseLivraison,
                'ville_livraison'    => $villeLivraison,
                'distance_km'        => max(0.0, $distanceKm),
                'nombre_personne'    => $nombrePersonne,
                'prix_menu'          => $prix['sousTotal'],
                'remise'             => $prix['remise'],
                'prix_livraison'     => $prix['prixLivraison'],
            ]);

            // Le nouveau total est reporté dans MongoDB
            $this->syncMongo($pdo, (int)$c['commande_id']);

            // Redirection vers mesCommandes après succès
            header('Location: ' . rtrim(BASE_URL, '/') . '/mesCommandes');
            exit;

        } catch (\Throwable $e) {
            $errors[] = $e->getMessage();
        }
    }

    // Mes commandes (client)
    public function mesCommandes(): void
    {
        Auth::requireLogin();
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();

        $userId = (int)($_SESSION['user']['utilisateur_id'] ?? $_SESSION['user']['id'] ?? 0);
        if ($userId <= 0) {
            exit("Utilisateur invalide.");
        }

        $pdo = Database::getConnection();
        $repo = new CommandeRepository($pdo);

        $statusLabels = [
            CommandeStatus::EN_ATTENTE              => 'En attente',
            CommandeStatus::ACCEPTE                 => 'Acceptée',
            CommandeStatus::EN_PREPARATION          => 'En préparation',
            CommandeStatus::EN_COURS_LIVRAISON      => 'En cours de livraison',
            CommandeStatus::LIVRE                   => 'Livrée',
            CommandeStatus::ATTENTE_RETOUR_MATERIEL => 'Attente retour matériel',
            CommandeStatus::TERMINEE                => 'Terminée',
            CommandeStatus::ANNULEE                 => 'Annulée',
        ];

        $commandes = $repo->findCommandesByUser($userId);

        $commandeIds = [];
        foreach ($commandes as $c) {
            $commandeIds[] = (int)$c['commande_id'];
        }
        $suivisRows = $repo->findSuivisForCommandeIds($commandeIds);

        $suivisByCommande = [];
        foreach ($suivisRows as $row) {
            $cid = (int)$row['commande_id'];
            $suivisByCommande[$cid][] = $row;
        }

        View::render('commandes/mesCommandes', [
            'commandes' => $commandes,
            'suivisByCommande' => $suivisByCommande,
            'statusLabels' => $statusLabels,
        ]);
    }

    // Mise à jour du statut de la commande
    public function commandeUpdateStatut(): void
    {
        Auth::requireRole(['employe', 'admin']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit("Méthode non autorisée.");
        }

        $commandeId = (int)($_POST['commande_id'] ?? 0);
        $statut = trim((string)($_POST['statut'] ?? ''));
        $modeContact = trim((string)($_POST['mode_contact'] ?? ''));
        $motif = trim((string)($_POST['motif'] ?? ''));

        if ($commandeId <= 0 || $statut === '') {
            http_response_code(400);
            exit("Paramètres invalides.");
        }

        // Annulation par un employé : le client doit avoir été contacté (téléphone ou mail)
        // et un motif est obligatoire. Ces règles sont vérifiées dans CommandeService.
        $modeContact = $modeContact === '' ? null : $modeContact;
        $motif = $motif === '' ? null : $motif;

        $pdo = Database::getConnection();
        $repo = new CommandeRepository($pdo);
        $service = new CommandeService($pdo, $repo);

        try {
            $service->changerStatut(
                $commandeId,
                $statut,
                'employe',
                $modeContact,
                $motif
            );

            $this->syncMongo($pdo, $commandeId);

            header('Location: ' . rtrim(BASE_URL, '/') . '/commandeManage');
            exit;

        } catch (\Throwable $e) {
            http_response_code(500);
            echo "Erreur : " . htmlspecialchars($e->getMessage());
        }
    }
}