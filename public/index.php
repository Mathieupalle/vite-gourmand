<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

$configLocal = __DIR__ . '/../config.local.php';
$configProd = __DIR__ . '/../config.php';

if (file_exists(__DIR__ . '/../config.local.php')) {
    require_once __DIR__ . '/../config.local.php';
} else {
    require_once __DIR__ . '/../config.php';
}

use App\Core\View;
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

    '/avisCreate' => ['App\Controller\AvisController', 'avisCreate'],
    '/avisManage' => ['App\Controller\AvisController', 'avisManage'],
];

// Récupérer le chemin demandé depuis l'URL
$uri = (string)parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$base = (string)(parse_url(BASE_URL, PHP_URL_PATH) ?: '/');

// Retirer le base path
$relativePath = substr($uri, strlen($base));
$path = '/' . ltrim((string)$relativePath, '/');

// Route par défaut
if ($path === '/' || $path === '') {
    $path = '/home';
}

$parts = explode('/', trim($path, '/'));
$id = isset($parts[1]) ? (int)$parts[1] : ($_GET['menu_id'] ?? null);

// Vérifier si la route existe
if (!isset($routes[$path])) {
    http_response_code(404);
    die("Page introuvable.");
}

// Récupérer le controller et la méthode
[$controllerClass, $method] = $routes[$path];

// Instanciation des controllers avec leurs services
$pdo = Database::getConnection();

$controller = match($controllerClass) {
    // Home
    App\Controller\HomeController::class =>
    new App\Controller\HomeController(
        new App\Service\HomeService(
            new App\Repository\HomeRepository($pdo)
        )
    ),

    // Menu
    App\Controller\MenuController::class => new App\Controller\MenuController(
        new App\Service\MenuService(
            App\Infrastructure\Database::getConnection(),
            new App\Repository\MenuRepository(
                App\Infrastructure\Database::getConnection()
            )
        )
    ),

    // Plat
    App\Controller\PlatController::class => new App\Controller\PlatController(
        new App\Service\PlatService(
            App\Infrastructure\Database::getConnection(),
            new App\Repository\PlatRepository(
                App\Infrastructure\Database::getConnection()
            )
        )
    ),

    // Commande
    App\Controller\CommandeController::class =>
    new App\Controller\CommandeController(
        new App\Service\CommandeService($pdo,
            new App\Repository\CommandeRepository($pdo)
        )
    ),

    // Admin
    App\Controller\AdminController::class =>
    new App\Controller\AdminController(
        new App\Service\AdminService(
            new App\Repository\AdminRepository($pdo)
        )
    ),

    // HoraireManage
    App\Controller\HoraireManageController::class =>
    new App\Controller\HoraireManageController(
        new App\Service\HoraireService(
            new App\Repository\HoraireRepository($pdo)
        )
    ),

    // Avis
    App\Controller\AvisController::class =>
    new App\Controller\AvisController(
        new App\Service\AvisService(
            new App\Repository\AvisRepository($pdo)
        )
    ),

    // Controllers sans service
    App\Controller\AuthController::class => new App\Controller\AuthController(),
    App\Controller\ProfileController::class => new App\Controller\ProfileController(),
    App\Controller\ContactController::class => new App\Controller\ContactController(),
    App\Controller\CgvController::class => new App\Controller\CgvController(),
    App\Controller\MentionsLegalesController::class => new App\Controller\MentionsLegalesController(),

    default => throw new Exception("Controller inconnu : $controllerClass"),
};

// Récupérer un ID si présent
$id = isset($_GET['menu_id']) ? (int)$_GET['menu_id'] : (isset($_GET['id']) ? (int)$_GET['id'] : 0);

// Vérifier si la méthode attend un argument
$ref = new ReflectionMethod($controllerClass, $method);
$numParams = $ref->getNumberOfParameters();

if ($method === 'menu' || $method === 'menuEdit') {
    if ($id <= 0) {
        http_response_code(404);
        die("Aucun menu sélectionné.");
    }
    $controller->$method($id);
} else {
    $controller->$method();
}