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

$mongoUri = getenv('MONGODB_URI');

if (!$mongoUri && file_exists(__DIR__ . '/config.local.php')) {
    $cfg = require __DIR__ . '/config.local.php';
    $mongoUri = $cfg['MONGODB_URI'] ?? null;
}

if (!$mongoUri) {
    http_response_code(500);
    exit('MONGODB_URI manquant');
}

$client = new MongoDB\Client($mongoUri);

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
            'qtyPeople' => ['$sum' => '$qtyPeople'],
        ]],
        ['$sort' => ['_id' => 1]],
    ];

    $rows = [];
    $totals = ['revenue' => 0.0, 'orders' => 0, 'qtyPeople' => 0];

    foreach ($col->aggregate($pipeline) as $r) {
        $period = (string)($r['_id'] ?? '');
        $revenue = (float)($r['revenue'] ?? 0);
        $orders = (int)($r['orders'] ?? 0);
        $qtyPeople = (int)($r['qtyPeople'] ?? 0);

        $rows[] = [
            'period' => $period,
            'revenue' => $revenue,
            'orders' => $orders,
            'qtyPeople' => $qtyPeople,
        ];

        $totals['revenue'] += $revenue;
        $totals['orders'] += $orders;
        $totals['qtyPeople'] += $qtyPeople;
    }

    return ['data' => $rows, 'totals' => $totals];
}

function alignRelative(array $a, array $b): array {
    $len = max(count($a), count($b));
    $labels = [];
    $alignedA = [];
    $alignedB = [];

    for ($i = 0; $i < $len; $i++) {
        $labels[] = 'T' . ($i + 1);
        $alignedA[] = $a[$i] ?? ['period' => 'T' . ($i + 1), 'revenue' => 0.0, 'orders' => 0, 'qtyPeople' => 0];
        $alignedB[] = $b[$i] ?? ['period' => 'T' . ($i + 1), 'revenue' => 0.0, 'orders' => 0, 'qtyPeople' => 0];

        $alignedA[$i]['period'] = 'T' . ($i + 1);
        $alignedB[$i]['period'] = 'T' . ($i + 1);
    }

    return [$labels, $alignedA, $alignedB];
}

function alignAbsolute(array $a, array $b): array {
    $labels = [];

    $mapA = [];
    foreach ($a as $r) $mapA[$r['period']] = $r;

    $mapB = [];
    foreach ($b as $r) $mapB[$r['period']] = $r;

    $periods = array_unique(array_merge(array_keys($mapA), array_keys($mapB)));
    sort($periods);

    $alignedA = [];
    $alignedB = [];

    foreach ($periods as $p) {
        $labels[] = $p;
        $alignedA[] = $mapA[$p] ?? ['period' => $p, 'revenue' => 0.0, 'orders' => 0, 'qtyPeople' => 0];
        $alignedB[] = $mapB[$p] ?? ['period' => $p, 'revenue' => 0.0, 'orders' => 0, 'qtyPeople' => 0];
    }

    return [$labels, $alignedA, $alignedB];
}

try {
    $start = $_GET['start'] ?? '';
    $end   = $_GET['end'] ?? '';
    $menuId = (int)($_GET['menu_id'] ?? 0);
    $compareMenuId = isset($_GET['compare_menu_id']) && $_GET['compare_menu_id'] !== '' ? (int)$_GET['compare_menu_id'] : null;

    $group = strtolower(trim($_GET['group'] ?? 'day')); // day|week|month|year
    $allowed = ['day', 'week', 'month', 'year'];
    if (!in_array($group, $allowed, true)) {
        http_response_code(400);
        echo json_encode(['error' => 'group invalide']);
        exit;
    }

    if (!$start || !$end) {
        http_response_code(400);
        echo json_encode(['error' => 'Paramètres requis: start=YYYY-MM-DD&end=YYYY-MM-DD']);
        exit;
    }

    $compareStart = $_GET['compare_start'] ?? '';
    $compareEnd   = $_GET['compare_end'] ?? '';
    $hasComparePeriod = ($compareStart !== '' && $compareEnd !== '');
    $hasCompareMenu   = ($compareMenuId !== null && $compareMenuId > 0);
    $hasCompare = ($hasCompareMenu || $hasComparePeriod);

    $compareMode = strtolower(trim($_GET['compare_mode'] ?? 'relative')); // relative|absolute
    if (!in_array($compareMode, ['relative', 'absolute'], true)) {
        http_response_code(400);
        echo json_encode(['error' => 'compare_mode invalide (relative|absolute)']);
        exit;
    }

    $startUtc = new MongoDB\BSON\UTCDateTime(utcMs($start, '00:00:00'));
    $endUtc   = new MongoDB\BSON\UTCDateTime(utcMs($end,   '23:59:59'));

    $driverOpts = [];

    if (PHP_SAPI !== 'cli' && (($_SERVER['HTTP_HOST'] ?? '') === 'localhost')) {
        $driverOpts['tlsCAFile'] = '/Applications/XAMPP/xamppfiles/etc/ssl/cacert.pem';
    }

    $client = new MongoDB\Client($mongoUri, [], $driverOpts);

    $col = $client->selectCollection('vitegourmand', 'orders_analytics');

    $current = aggregatePeriod($col, $startUtc, $endUtc, $group, $menuId);

    $labels = array_map(fn($r) => $r['period'], $current['data']);
    $compare = null;

    if ($hasCompare) {
        if ($hasCompareMenu) {
            // Comparaison Menu A vs Menu B sur la même période A
            $compare = aggregatePeriod($col, $startUtc, $endUtc, $group, (int)$compareMenuId);

            // Pour l'affichage, on considère que la période B = période A (comparaison de menus)
            $compareStart = $start;
            $compareEnd   = $end;
        } else {
            // Comparaison période A vs période B (même menu / tous menus)
            $cStartUtc = new MongoDB\BSON\UTCDateTime(utcMs($compareStart, '00:00:00'));
            $cEndUtc   = new MongoDB\BSON\UTCDateTime(utcMs($compareEnd,   '23:59:59'));

            $compare = aggregatePeriod($col, $cStartUtc, $cEndUtc, $group, $menuId);
        }

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
        'compare_menu_id' => $compareMenuId,
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
