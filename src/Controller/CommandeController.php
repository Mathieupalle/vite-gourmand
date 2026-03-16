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
use MongoDB\BSON\UTCDateTime;
use Throwable;

class CommandeController
{

    private function redirect(string $path): void
    {
        header('Location: ' . rtrim(BASE_URL, '/') . '/' . ltrim($path, '/'));
        exit;
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

        $menuStats = $pdo->query("
          SELECT m.menu_id, m.titre, COUNT(c.commande_id) AS nb
          FROM menu m
          LEFT JOIN commande c ON c.menu_id = m.menu_id
          GROUP BY m.menu_id, m.titre
          ORDER BY m.titre
        ")->fetchAll();

        $whereParts = [];
        $params = [];

        $apply = (string)($_GET['apply'] ?? '');
        $statutFiltre = trim((string)($_GET['statut'] ?? ''));
        $menuFiltre   = (int)($_GET['menu_id'] ?? 0);

        if ($apply === 'statut') {
            if ($statutFiltre !== '' && in_array($statutFiltre, $allowedStatus, true)) {
                $whereParts[] = "c.statut = ?";
                $params[] = $statutFiltre;
            }
            $menuFiltre = 0;
        }

        if ($apply === 'menu') {
            if ($menuFiltre > 0) {
                $whereParts[] = "c.menu_id = ?";
                $params[] = $menuFiltre;
            }
            $statutFiltre = '';
        }

        $whereSql = $whereParts ? ('WHERE ' . implode(' AND ', $whereParts)) : '';

        $stmt = $pdo->prepare("
            SELECT
                c.commande_id,
                c.numero_commande,
                c.date_commande,
                c.date_prestation,
                c.date_livraison,
                c.heure_livraison,
                c.adresse_livraison,
                c.ville_livraison,
                c.nombre_personne,
                c.prix_menu,
                c.prix_livraison,
                c.remise,
                c.pret_materiel,
                c.restitution_materiel,
                c.statut,
                u.email AS client_email,
                m.titre AS menu_titre,
                m.prix_par_personne
            FROM commande c
            JOIN utilisateur u ON u.utilisateur_id = c.utilisateur_id
            JOIN menu m ON m.menu_id = c.menu_id
            $whereSql
            ORDER BY c.date_commande DESC
        ");
        $stmt->execute($params);
        $commandes = $stmt->fetchAll();

        View::render('commandes/commandeManage', [
            'role'          => $role,
            'allowedStatus' => $allowedStatus,
            'statusLabels'  => $statusLabels,
            'menuStats'     => $menuStats,
            'commandes'     => $commandes,
            'statutFiltre'  => $statutFiltre,
            'menuFiltre'    => $menuFiltre,
        ]);
    }

    // Annuler une commande
    public function commandeAnnuler(): void
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
        $stmt = $pdo->prepare("SELECT statut FROM commande WHERE commande_id = ? AND utilisateur_id = ? LIMIT 1");
        $stmt->execute([$commandeId, $userId]);
        $row = $stmt->fetch();

        if (!$row) {
            http_response_code(404);
            exit("Commande introuvable.");
        }

        if ((string)$row['statut'] !== CommandeStatus::EN_ATTENTE) {
            http_response_code(403);
            exit("Annulation impossible : commande déjà traitée.");
        }

        $pdo = Database::getConnection();
        $repo = new CommandeRepository($pdo);
        $service = new CommandeService($pdo, $repo);

        try {
            $service->changerStatut($commandeId, CommandeStatus::ANNULEE, 'client', null, null);
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
            $result = $service->creerCommandeDepuisPost($userId, $_POST);

            // 🔹 Migration automatique vers MongoDB
            $mongoUri = getenv('MONGODB_URI'); // défini dans ton config
            if ($mongoUri) {
                try {
                    $mongo = new MongoClient($mongoUri);

                    // Utiliser la classe de migration
                    $migrator = new MigrateOrders($pdo, $mongo);
                    $inserted = $migrator->migrateNewOrders();

                    // Optionnel : log pour debug
                    // error_log("MongoDB migration : $inserted commandes insérées.");

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
        $stmt = $pdo->prepare("
            SELECT c.*,
                   m.prix_par_personne,
                   m.nombre_personne_minimum,
                   m.titre AS menu_titre
            FROM commande c
            JOIN menu m ON m.menu_id = c.menu_id
            WHERE c.commande_id = ? AND c.utilisateur_id = ?
            LIMIT 1
        ");
        $stmt->execute([$commandeId, $userId]);
        $commande = $stmt->fetch();

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
            $this->processPost($commande, $userId, $errors, $pdo);
        }

        View::render('commandes/commandeEdit', [
            'c' => $commande,
            'errors' => $errors,
        ]);
    }

    private function processPost(array &$c, int $userId, array &$errors, \PDO $pdo): void
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

            // Mettre à jour la commande
            $stmt = $pdo->prepare("
            UPDATE commande
            SET date_prestation = ?, adresse_prestation = ?, ville_prestation = ?,
                date_livraison = ?, heure_livraison = ?, adresse_livraison = ?, ville_livraison = ?, distance_km = ?, nombre_personne = ?
            WHERE commande_id = ? AND utilisateur_id = ?
        ");
            $stmt->execute([
                $datePrestation, $adressePrestation, $villePrestation,
                $dateLivraison, $heureLivraison, $adresseLivraison, $villeLivraison, $distanceKm, $nombrePersonne,
                $c['commande_id'], $userId
            ]);

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

        $commandeIds = array_map(fn($c) => (int)$c['commande_id'], $commandes);
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

        // règle métier obligatoire pour les employés

        if ($modeContact === '') {
            http_response_code(400);
            exit("Vous devez préciser le mode de contact.");
        }

        if ($motif === '') {
            http_response_code(400);
            exit("Vous devez préciser le motif.");
        }

        if (!in_array($modeContact, ['telephone', 'email'], true)) {
            http_response_code(400);
            exit("Type de contact invalide.");
        }

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

            header('Location: ' . rtrim(BASE_URL, '/') . '/commandeManage');
            exit;

        } catch (\Throwable $e) {
            http_response_code(500);
            echo "Erreur : " . htmlspecialchars($e->getMessage());
        }
    }
}