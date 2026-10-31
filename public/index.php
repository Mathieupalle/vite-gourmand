<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    // Cookie de session : inaccessible au JavaScript (httponly), non envoyé depuis un autre site (samesite)
    // et transmis uniquement en HTTPS quand le site est en HTTPS (Heroku passe par un proxy)
    $https = ($_SERVER['HTTPS'] ?? '') === 'on' || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => $https,
    ]);
    session_start();
}

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/helpers.php';

if (file_exists(__DIR__ . '/../config.local.php')) {
    require_once __DIR__ . '/../config.local.php';
} else {
    require_once __DIR__ . '/../config.php';
}

use App\Infrastructure\Database;

// Liste des routes : [URL => [Controller, méthode]]
$routes = [
    '/' => ['App\Controller\HomeController', 'home'],
    '/home' => ['App\Controller\HomeController', 'home'],

    '/login' => ['App\Controller\AuthController', 'login'],
    '/register' => ['App\Controller\AuthController', 'register'],
    '/logout' => ['App\Controller\AuthController', 'logout'],
    '/forgotPassword' => ['App\Controller\AuthController', 'forgotPassword'],
    '/resetPassword' => ['App\Controller\AuthController', 'resetPassword'],
    '/profile' => ['App\Controller\ProfileController', 'profile'],

    '/menus' => ['App\Controller\MenuController', 'menus'],
    '/menu' => ['App\Controller\MenuController', 'menu'],
    '/menusAjax' => ['App\Controller\MenuController', 'menusAjax'],
    '/menuCreate' => ['App\Controller\MenuController', 'menuCreate'],
    '/menuEdit' => ['App\Controller\MenuController', 'menuEdit'],
    '/menuEditPlats' => ['App\Controller\MenuController', 'menuEditPlats'],
    '/menuManage' => ['App\Controller\MenuController', 'menuManage'],

    '/platManage' => ['App\Controller\PlatController', 'platManage'],
    '/platCreate' => ['App\Controller\PlatController', 'platCreate'],
    '/platEdit' => ['App\Controller\PlatController', 'platEdit'],

    '/commande' => ['App\Controller\CommandeController', 'commande'],
    '/commandeCreate' => ['App\Controller\CommandeController', 'commandeCreate'],
    '/commandeEdit' => ['App\Controller\CommandeController', 'commandeEdit'],
    '/commandeManage' => ['App\Controller\CommandeController', 'commandeManage'],
    '/mesCommandes' => ['App\Controller\CommandeController', 'mesCommandes'],
    '/commandeUpdateStatut' => ['App\Controller\CommandeController', 'commandeUpdateStatut'],
    '/commandeAnnuler' => ['App\Controller\CommandeController', 'commandeAnnuler'],

    '/admin' => ['App\Controller\AdminController', 'admin'],
    '/employeCreate' => ['App\Controller\AdminController', 'employeCreate'],
    '/employeManage' => ['App\Controller\AdminController', 'employeManage'],
    '/stats' => ['App\Controller\AdminController', 'stats'],

    '/contact' => ['App\Controller\ContactController', 'contact'],
    '/cgv' => ['App\Controller\CgvController', 'cgv'],
    '/mentionsLegales' => ['App\Controller\MentionsLegalesController', 'mentionsLegales'],
    '/horaireManage' => ['App\Controller\HoraireManageController', 'horaireManage'],
    '/referentielManage' => ['App\Controller\ReferentielController', 'referentielManage'],

    '/avisCreate' => ['App\Controller\AvisController', 'avisCreate'],
    '/avisManage' => ['App\Controller\AvisController', 'avisManage'],
];

// Récupérer le chemin demandé depuis l'URL
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$base = parse_url(BASE_URL, PHP_URL_PATH) ?: '/';

// Retirer le base path (en string)
$relativePath = substr((string)$uri, strlen((string)$base));

// Normalisation
$path = '/' . ltrim((string)$relativePath, '/');
if ($path === '/' || $path === '') {
    $path = '/home';
}

// Vérifier si la route existe
if (!isset($routes[$path])) {
    http_response_code(404);
    die("Page introuvable.");
}

// Protection CSRF : chaque formulaire POST doit renvoyer le jeton de la session
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !csrf_valide((string)($_POST['csrf_token'] ?? ''))) {
    http_response_code(403);
    die("Formulaire expiré ou invalide. Rechargez la page et réessayez.");
}

// Récupérer le controller et la méthode
[$controllerClass, $method] = $routes[$path];

// Instanciation du controller
// Home et Menu reçoivent leur service, les autres créent ce dont ils ont besoin
$pdo = Database::getConnection();

if ($controllerClass === 'App\Controller\HomeController') {
    $controller = new App\Controller\HomeController(
        new App\Service\HomeService(new App\Repository\HomeRepository($pdo))
    );
} elseif ($controllerClass === 'App\Controller\MenuController') {
    $controller = new App\Controller\MenuController(
        new App\Service\MenuService($pdo, new App\Repository\MenuRepository($pdo))
    );
} else {
    $controller = new $controllerClass();
}

// Les pages menu et menuEdit ont besoin de l'id du menu
$id = isset($_GET['menu_id']) ? (int)$_GET['menu_id'] : (isset($_GET['id']) ? (int)$_GET['id'] : 0);

if ($method === 'menu' || $method === 'menuEdit') {
    if ($id <= 0) {
        http_response_code(404);
        die("Aucun menu sélectionné.");
    }
    $controller->$method($id);
} else {
    $controller->$method();
}