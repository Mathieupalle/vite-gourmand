<?php
declare(strict_types=1);

namespace App\Controller;

use App\Infrastructure\Database;
use App\Repository\CommandeRepository;
use App\Security\Auth;
use App\Core\View;
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
            $this->redirect('/login.php');
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
        Auth::requireRole(['employee', 'admin']);

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $user = $_SESSION['user'] ?? [];
        $role = (string)($user['role'] ?? 'employee');

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
        if ($userId <= 0) $this->redirect('/login.php');

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
            $this->redirect('/mesCommandes.php');
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
            $result = $service->creerCommandeDepuisPost($userId, $_POST);

            $mongoUri = getenv('MONGODB_URI');
            if ($mongoUri) {
                try {
                    $mongo = new MongoClient($mongoUri);
                    $col = $mongo->selectCollection('vitegourmand', 'orders_analytics');

                    $col->insertOne([
                        'sqlCommandeId' => (int)$result['commandeId'],
                        'menuTitre'     => (string)$result['menuTitre'],
                        'totalFinal'    => (float)$result['totalFinal'],
                        'createdAt'     => new UTCDateTime((int) round(microtime(true) * 1000)),
                        'statut'        => (string)$result['statut'],
                    ]);
                } catch (Throwable $e) {
                }
            }

            $this->redirect('/mesCommandes.php');

        } catch (Throwable $e) {
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
        if ($userId <= 0) $this->redirect('/login.php');

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
    {}

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
}