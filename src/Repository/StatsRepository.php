<?php
declare(strict_types=1);

namespace App\Repository;

use MongoDB\Client;
use MongoDB\Collection;
use MongoDB\BSON\UTCDateTime;
use RuntimeException;

final class StatsRepository
{
    private Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function getMenus(): array
    {
        $menus = [];
        $col = $this->client->selectCollection('vitegourmand', 'orders_analytics');

        $menusAgg = $col->aggregate([
            ['$group' => [
                '_id' => '$menuId',
                'titre' => ['$first' => '$menuTitre']
            ]],
            ['$sort' => ['titre' => 1]]
        ]);

        foreach ($menusAgg as $m) {
            $id = (int)($m['_id'] ?? 0);
            if ($id <= 0) continue;

            $menus[] = [
                'menu_id' => $id,
                'titre' => (string)($m['titre'] ?? ('Menu ' . $id))
            ];
        }

        return $menus;
    }

    public function aggregateStats(
        string $start,
        string $end,
        string $group = 'day',
        int $menuId = 0,
        ?int $compareMenuId = null,
        ?string $compareStart = null,
        ?string $compareEnd = null,
        string $compareMode = 'relative'
    ): array {
        $col = $this->client->selectCollection('vitegourmand', 'orders_analytics');

        $startUtc = new UTCDateTime($this->utcMs($start, '00:00:00'));
        $endUtc   = new UTCDateTime($this->utcMs($end, '23:59:59'));

        $current = $this->aggregatePeriod($col, $startUtc, $endUtc, $group, $menuId);
        $compare = null;

        $hasCompareMenu = ($compareMenuId !== null && $compareMenuId > 0);

        $hasComparePeriod =
            !empty($compareStart) &&
            !empty($compareEnd);
        $hasCompare = $hasCompareMenu || $hasComparePeriod;

        if ($hasCompare) {
            if ($hasCompareMenu) {
                $compare = $this->aggregatePeriod($col, $startUtc, $endUtc, $group, $compareMenuId);
            } elseif ($hasComparePeriod) {
                $cStartUtc = new UTCDateTime($this->utcMs($compareStart, '00:00:00'));
                $cEndUtc   = new UTCDateTime($this->utcMs($compareEnd, '23:59:59'));
                $compare = $this->aggregatePeriod($col, $cStartUtc, $cEndUtc, $group, $menuId);
            }

            if ($compareMode === 'relative') {
                [$labels, $current['data'], $compare['data']] = $this->alignRelative($current['data'], $compare['data']);
            } else {
                [$labels, $current['data'], $compare['data']] = $this->alignAbsolute($current['data'], $compare['data']);
            }
        } else {
            $labels = array_map(fn($r) => $r['period'], $current['data']);
        }

        return [
            'labels' => $labels,
            'current' => $current,
            'compare' => $compare
        ];
    }

    private function utcMs(string $ymd, string $time): int
    {
        $ts = strtotime($ymd . ' ' . $time);
        if ($ts === false) throw new RuntimeException("Date invalide: $ymd");
        return $ts * 1000;
    }

    private function buildGroupId(string $group): array
    {
        return match ($group) {
            'day' => ['$dateToString' => ['format' => '%Y-%m-%d', 'date' => '$dateCommande']],
            'week' => ['$dateToString' => ['format' => '%G-W%V', 'date' => '$dateCommande']],
            'month' => ['$dateToString' => ['format' => '%Y-%m', 'date' => '$dateCommande']],
            'year' => ['$dateToString' => ['format' => '%Y', 'date' => '$dateCommande']],
            default => throw new RuntimeException("group invalide"),
        };
    }

    private function aggregatePeriod(
        Collection $col,
        UTCDateTime $startUtc,
        UTCDateTime $endUtc,
        string $group,
        int $menuId
    ): array {
        $match = [
            'dateCommande' => ['$gte' => $startUtc, '$lte' => $endUtc],
            'statut' => ['$ne' => 'annulee'],
        ];
        if ($menuId > 0) $match['menuId'] = $menuId;

        $pipeline = [
            ['$match' => $match],
            ['$group' => [
                '_id' => $this->buildGroupId($group),
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

    private function alignRelative(array $a, array $b): array
    {
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

    private function alignAbsolute(array $a, array $b): array
    {
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
}