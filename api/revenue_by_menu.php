<?php
declare(strict_types=1);

session_start();
header('Content-Type: application/json; charset=utf-8');

$user = $_SESSION['user'] ?? null;
$role = $user['role'] ?? null;

$isAdmin = (
    $role === 'admin' ||
    (string)$role === '3' ||
    (int)$role === 3 ||
    (int)($user['role_id'] ?? 0) === 3
);

if (!$isAdmin) {
    http_response_code(403);
    echo json_encode(['error' => 'Accès refusé']);
    exit;
}

require_once __DIR__ . '/../vendor/autoload.php';

$uri = getenv('MONGODB_URI');
if (!$uri) {
    http_response_code(500);
    echo json_encode(['error' => 'MONGODB_URI manquant']);
    exit;
}

function utcMs(string $ymd, string $time): int {
    $ts = strtotime($ymd . ' ' . $time);
    if ($ts === false) {
        throw new RuntimeException("Date invalide: $ymd");
    }
    return $ts * 1000;
}

try {
    $start  = $_GET['start'] ?? '';
    $end    = $_GET['end'] ?? '';
    $menuId = (int)($_GET['menu_id'] ?? 0);

    if ($start === '' || $end === '') {
        http_response_code(400);
        echo json_encode(['error' => 'Paramètres requis: start=YYYY-MM-DD&end=YYYY-MM-DD']);
        exit;
    }

    $startUtc = new MongoDB\BSON\UTCDateTime(utcMs($start, '00:00:00'));
    $endUtc   = new MongoDB\BSON\UTCDateTime(utcMs($end,   '23:59:59'));

    $client = new MongoDB\Client($uri);
    // MongoDB = vitegourmand
    $col = $client->selectCollection('vitegourmand', 'orders_analytics');

    $match = [
        'dateCommande' => ['$gte' => $startUtc, '$lte' => $endUtc],
        'statut' => ['$ne' => 'annulee'],
    ];

    if ($menuId > 0) {
        $match['menuId'] = $menuId;
    }

    $pipeline = [
        ['$match' => $match],
        ['$group' => [
            '_id' => [
                'menuId' => '$menuId',
                'menuTitre' => '$menuTitre'
            ],
            'revenue' => ['$sum' => '$total'],
            'orders' => ['$sum' => 1],
            'qtyPeople' => ['$sum' => '$nombrePersonne'],
        ]],
        ['$sort' => ['revenue' => -1]],
    ];

    $out = [];
    foreach ($col->aggregate($pipeline) as $doc) {
        $out[] = [
            'menuId' => $doc->_id->menuId,
            'menuTitre' => $doc->_id->menuTitre,
            'revenue' => (float)$doc->revenue,
            'orders' => (int)$doc->orders,
            'qtyPeople' => (int)$doc->qtyPeople,
        ];
    }

    echo json_encode($out);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
