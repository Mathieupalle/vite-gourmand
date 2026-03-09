<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config.local.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Security\Auth;
use App\Service\StatsService;
use App\Repository\StatsRepository;
use App\Infrastructure\MongoClientFactory;

if (session_status() !== PHP_SESSION_ACTIVE) session_start();
Auth::requireRole(['admin']);

try {
    $repo = new StatsRepository(MongoClientFactory::createFromEnvOrLocalConfig());
    $service = new StatsService($repo);

    $data = $service->getAggregatedStats(
        $_GET['start'] ?? '',
        $_GET['end'] ?? '',
        $_GET['group'] ?? 'day',
        (int)($_GET['menu_id'] ?? 0),
        isset($_GET['compare_menu_id']) ? (int)$_GET['compare_menu_id'] : null,
        $_GET['compare_start'] ?? null,
        $_GET['compare_end'] ?? null,
        $_GET['compare_mode'] ?? 'relative'
    );

    echo json_encode($data);
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}