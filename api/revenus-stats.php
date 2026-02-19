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
    if ($ts === false) throw new RuntimeException("Date invalide: $ymd");
    return $ts * 1000;
}

function buildGroupId(string $group): array {
    return match ($group) {
        'day' => ['$dateToString' => ['format' => '%Y-%m-%d', 'date' => '$dateCommande']],
        'month' => ['$dateToString' => ['format' => '%Y-%m', 'date' => '$dateCommande']],
        'year' => ['$dateToString' => ['format' => '%Y', 'date' => '$dateCommande']],
        'week' => ['$dateToString' => ['format' => '%G-W%V', 'date' => '$dateCommande']],
        default => throw new RuntimeException("group invalide"),
    };
}

function aggregatePeriod(
    MongoDB\Collection $col,
    MongoDB\BSON\UTCDateTime $startUtc,
    MongoDB\BSON\UTCDateTime $endUtc,
    string $group,
    int $menuId
): array {
    $match = [
        'dateCommande' => ['$gte' => $startUtc, '$lte' => $endUtc],
        'statut' => ['$ne' => 'annulee'],
    ];
    if ($menuId > 0) $match['menuId'] = $menuId;

    $groupId = buildGroupId($group);

    $pipeline = [
        ['$match' => $match],
        ['$group' => [
            '_id' => $groupId,
            'revenue' => ['$sum' => '$total'],
            'orders' => ['$sum' => 1],
            'qtyPeople' => ['$sum' => '$nombrePersonne'],
        ]],
        ['$sort' => ['_id' => 1]],
    ];

    $data = [];
    $totalRevenue = 0.0;
    $totalOrders = 0;
    $totalPeople = 0;

    foreach ($col->aggregate($pipeline) as $doc) {
        $row = [
            'period' => (string)$doc->_id,
            'revenue' => (float)$doc->revenue,
            'orders' => (int)$doc->orders,
            'qtyPeople' => (int)$doc->qtyPeople,
        ];
        $data[] = $row;
        $totalRevenue += $row['revenue'];
        $totalOrders += $row['orders'];
        $totalPeople += $row['qtyPeople'];
    }

    return [
        'data' => $data,
        'totals' => [
            'revenue' => $totalRevenue,
            'orders' => $totalOrders,
            'qtyPeople' => $totalPeople,
        ]
    ];
}

function alignRelative(array $currentRows, array $compareRows): array {
    $labels = array_map(fn($r) => $r['period'], $currentRows);

    $a = $currentRows;
    $b = [];

    $n = count($labels);
    for ($i = 0; $i < $n; $i++) {
        $b[] = $compareRows[$i] ?? [
            'period' => $labels[$i],
            'revenue' => 0.0,
            'orders' => 0,
            'qtyPeople' => 0,
        ];
        $b[$i]['period'] = $labels[$i];
    }

    return [$labels, $a, $b];
}

function alignAbsolute(array $currentRows, array $compareRows): array {

    $labels = [];
    foreach ($currentRows as $r) $labels[$r['period']] = true;
    foreach ($compareRows as $r) $labels[$r['period']] = true;

    $labels = array_keys($labels);
    sort($labels);

    $mapA = [];
    foreach ($currentRows as $r) $mapA[$r['period']] = $r;

    $mapB = [];
    foreach ($compareRows as $r) $mapB[$r['period']] = $r;

    $alignedA = [];
    $alignedB = [];

    foreach ($labels as $p) {
        $alignedA[] = $mapA[$p] ?? ['period' => $p, 'revenue' => 0.0, 'orders' => 0, 'qtyPeople' => 0];
        $alignedB[] = $mapB[$p] ?? ['period' => $p, 'revenue' => 0.0, 'orders' => 0, 'qtyPeople' => 0];
    }

    return [$labels, $alignedA, $alignedB];
}

try {
    $start = $_GET['start'] ?? '';
    $end   = $_GET['end'] ?? '';
    $menuId = (int)($_GET['menu_id'] ?? 0);

    $group = strtolower(trim($_GET['group'] ?? 'day')); // day|week|month|year
    $allowed = ['day', 'week', 'month', 'year'];
    if (!in_array($group, $allowed, true)) {
        http_response_code(400);
        echo json_encode(['error' => 'group invalide (day|week|month|year)']);
        exit;
    }

    if ($start === '' || $end === '') {
        http_response_code(400);
        echo json_encode(['error' => 'Paramètres requis: start=YYYY-MM-DD&end=YYYY-MM-DD']);
        exit;
    }

    $compareStart = $_GET['compare_start'] ?? '';
    $compareEnd   = $_GET['compare_end'] ?? '';
    $hasCompare = ($compareStart !== '' && $compareEnd !== '');

    $compareMode = strtolower(trim($_GET['compare_mode'] ?? 'relative')); // relative|absolute
    if (!in_array($compareMode, ['relative', 'absolute'], true)) {
        http_response_code(400);
        echo json_encode(['error' => 'compare_mode invalide (relative|absolute)']);
        exit;
    }

    $startUtc = new MongoDB\BSON\UTCDateTime(utcMs($start, '00:00:00'));
    $endUtc   = new MongoDB\BSON\UTCDateTime(utcMs($end,   '23:59:59'));

    $client = new MongoDB\Client($uri);
    $col = $client->selectCollection('vitegourmand', 'orders_analytics');

    $current = aggregatePeriod($col, $startUtc, $endUtc, $group, $menuId);

    $labels = array_map(fn($r) => $r['period'], $current['data']);
    $compare = null;

    if ($hasCompare) {
        $cStartUtc = new MongoDB\BSON\UTCDateTime(utcMs($compareStart, '00:00:00'));
        $cEndUtc   = new MongoDB\BSON\UTCDateTime(utcMs($compareEnd,   '23:59:59'));

        $compare = aggregatePeriod($col, $cStartUtc, $cEndUtc, $group, $menuId);

        if ($compareMode === 'relative') {
            [$labels, $a, $b] = alignRelative($current['data'], $compare['data']);
        } else {
            [$labels, $a, $b] = alignAbsolute($current['data'], $compare['data']);
        }

        $current['data'] = $a;
        $compare['data'] = $b;
    }

    echo json_encode([
        'group' => $group,
        'menu_id' => $menuId,
        'compare_mode' => $compareMode,
        'labels' => $labels,

        'current' => [
            'start' => $start,
            'end' => $end,
            'totals' => $current['totals'],
            'data' => $current['data'],
        ],

        'compare' => $hasCompare ? [
            'start' => $compareStart,
            'end' => $compareEnd,
            'totals' => $compare['totals'],
            'data' => $compare['data'],
        ] : null,
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
