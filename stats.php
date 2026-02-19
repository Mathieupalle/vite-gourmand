<?php
session_start();

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
    exit('Accès refusé');
}

require_once __DIR__ . '/vendor/autoload.php';

$mongoUri = getenv('MONGODB_URI');
if (!$mongoUri) {
    http_response_code(500);
    exit('MONGODB_URI manquant');
}

$client = new MongoDB\Client($mongoUri);
$col = $client->selectCollection('vitegourmand', 'orders_analytics');

// Récupération des menus distincts (pour le filtre)
$menusAgg = $col->aggregate([
        ['$group' => ['_id' => '$menuId', 'titre' => ['$first' => '$menuTitre']]],
        ['$sort' => ['titre' => 1]],
]);
$menus = [];
foreach ($menusAgg as $m) {
    $id = (int)($m['_id'] ?? 0);
    if ($id <= 0) continue;
    $menus[] = ['menu_id' => $id, 'titre' => (string)($m['titre'] ?? ('Menu ' . $id))];
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Stats (MongoDB)</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif; padding:16px; }
        .row { display:flex; gap:12px; flex-wrap:wrap; align-items:flex-start; }
        .card { border:1px solid #ddd; border-radius:8px; padding:12px; min-width:240px; }
        table { width:100%; border-collapse:collapse; }
        th, td { border:1px solid #ddd; padding:8px; }
        th { background:#f6f6f6; text-align:left; }
        .kpis { display:flex; gap:12px; flex-wrap:wrap; margin-top:12px; }
        .muted { color:#666; font-size: 0.95em; }
        .btn { height:38px; width:190px; }
    </style>
</head>
<body>
<h1>statistiques (mongodb)</h1>
<p><a href="admin.php">← retour à l'espace gestion</a></p>

<div class="row">
    <div class="card">
        <div class="muted"><strong>Période A</strong></div>
        <br><br>
        <label>Début</label><br>
        <input type="date" id="start" value="<?= htmlspecialchars(date('Y-m-01')) ?>"><br><br>
        <label>Fin</label><br>
        <input type="date" id="end" value="<?= htmlspecialchars(date('Y-m-d')) ?>">
    </div>

    <div class="card">
        <div class="muted"><strong>Période B</strong></div>
        <br><br>
        <label>Début</label><br>
        <input type="date" id="compare_start"><br><br>
        <label>Fin</label><br>
        <input type="date" id="compare_end">
        <div class="muted" style="margin-top:8px;">
        </div>
    </div>

    <div class="card">
        <label>Durée</label><br>
        <select id="group">
            <option value="day">Jour</option>
            <option value="week">Semaine</option>
            <option value="month">Mois</option>
            <option value="year">Année</option>
        </select><br><br>

        <!-- AJOUT: compare_mode (évite le JS qui casse et pilote l'alignement) -->
        <label>Alignement comparaison</label><br>
        <select id="compare_mode">
            <option value="relative" selected>Relatif (J0 aligné)</option>
            <option value="absolute">Absolu (dates réelles)</option>
        </select><br><br>
    </div>

    <div class="card">
        <label>Menus</label><br>
        <select id="menu_id" style="min-width:220px;">
            <option value="">Tous les menus</option>
            <?php foreach ($menus as $m): ?>
                <option value="<?= (int)$m['menu_id'] ?>">
                    <?= htmlspecialchars($m['titre']) ?> (ID <?= (int)$m['menu_id'] ?>)
                </option>
            <?php endforeach; ?>
        </select>

        <!-- AJOUT: menu B pour comparaison Menu A vs Menu B -->
        <br><br>
        <label>Comparer avec (Menu B)</label><br>
        <select id="compare_menu_id" style="min-width:220px;">
            <option value="">— Aucun —</option>
            <?php foreach ($menus as $m): ?>
                <option value="<?= (int)$m['menu_id'] ?>">
                    <?= htmlspecialchars($m['titre']) ?> (ID <?= (int)$m['menu_id'] ?>)
                </option>
            <?php endforeach; ?>
        </select>
        <div class="muted" style="margin-top:10px;">
            Si Menu B est choisi, comparaison <strong>Menu A vs Menu B</strong> sur la même période A.
        </div>
    </div>

    <div class="card">
        <button id="btnLoad" class="btn">Afficher</button>
        <button id="btnClear" class="btn" style="margin-top:10px;">Reset</button>
    </div>
</div>

<div class="kpis">
    <div class="card">
        <div class="muted">CA Période A</div>
        <div style="font-size:24px;" id="kpiA">—</div>
        <div class="muted">Commandes: <span id="kpiAOrders">—</span></div>
    </div>
    <div class="card">
        <div class="muted" id="kpiBLabel">CA Période B</div>
        <div style="font-size:24px;" id="kpiB">—</div>
        <div class="muted">Commandes: <span id="kpiBOrders">—</span></div>
    </div>
    <div class="card">
        <div class="muted">Écart (A - B)</div>
        <div style="font-size:24px;" id="kpiDiff">—</div>
        <div class="muted">Évolution: <span id="kpiPct">—</span></div>
    </div>
</div>

<hr>

<h2>Comparaison commandes / CA (graphique)</h2>
<canvas id="chart" height="90"></canvas>

<hr>

<h2>Détails (table)</h2>
<table>
    <thead>
    <tr>
        <th>Période</th>
        <th>CA A (€)</th>
        <th>Commandes A</th>
        <th id="thCaB">CA B (€)</th>
        <th id="thOrdersB">Commandes B</th>
    </tr>
    </thead>
    <tbody id="tbody"></tbody>
</table>

<script>
    let chart;

    function getParams() {
        return {
            start: document.getElementById('start').value,
            end: document.getElementById('end').value,
            compare_start: document.getElementById('compare_start').value,
            compare_end: document.getElementById('compare_end').value,
            group: document.getElementById('group').value,
            compare_mode: document.getElementById('compare_mode').value,
            menuId: document.getElementById('menu_id').value.trim(),
            compareMenuId: document.getElementById('compare_menu_id').value.trim()
        };
    }

    function eur(n) { return Number(n || 0).toFixed(2) + " €"; }

    async function loadStats() {
        const p = getParams();

        let url = `/api/revenus-stats.php?start=${encodeURIComponent(p.start)}&end=${encodeURIComponent(p.end)}&group=${encodeURIComponent(p.group)}&compare_mode=${encodeURIComponent(p.compare_mode)}`;
        if (p.menuId) url += `&menu_id=${encodeURIComponent(p.menuId)}`;

        // AJOUT: priorité à compare_menu_id (comparaison menus)
        if (p.compareMenuId) {
            url += `&compare_menu_id=${encodeURIComponent(p.compareMenuId)}`;
        } else if (p.compare_start && p.compare_end) {
            url += `&compare_start=${encodeURIComponent(p.compare_start)}&compare_end=${encodeURIComponent(p.compare_end)}`;
        }

        const res = await fetch(url);
        const json = await res.json();

        if (json.error) {
            alert(json.error);
            return;
        }

        const labels = json.labels || [];
        const curRows = json.current?.data || [];
        const cmpRows = json.compare?.data || [];

        const curMap = new Map(curRows.map(r => [r.period, r]));
        const cmpMap = new Map(cmpRows.map(r => [r.period, r]));

        const curRevenue = labels.map(p => (curMap.get(p)?.revenue ?? 0));
        const cmpRevenue = labels.map(p => (cmpMap.get(p)?.revenue ?? 0));

        // KPI
        const aRev = json.current?.totals?.revenue ?? 0;
        const aOrd = json.current?.totals?.orders ?? 0;
        const hasCompare = !!json.compare;
        const bRev = hasCompare ? (json.compare?.totals?.revenue ?? 0) : 0;
        const bOrd = hasCompare ? (json.compare?.totals?.orders ?? 0) : 0;

        // AJOUT: libellés dynamiques selon comparaison menus ou périodes
        const isMenuCompare = hasCompare && (json.compare_menu_id !== null && json.compare_menu_id !== undefined && String(json.compare_menu_id) !== '' && Number(json.compare_menu_id) > 0);

        const labelB = isMenuCompare ? "Menu B" : "Période B";
        const kpiBLabelEl = document.getElementById('kpiBLabel');
        if (kpiBLabelEl) kpiBLabelEl.textContent = "CA " + labelB;

        const thCaB = document.getElementById('thCaB');
        if (thCaB) thCaB.textContent = isMenuCompare ? "CA Menu B (€)" : "CA B (€)";

        const thOrdersB = document.getElementById('thOrdersB');
        if (thOrdersB) thOrdersB.textContent = isMenuCompare ? "Commandes Menu B" : "Commandes B";

        document.getElementById('kpiA').textContent = eur(aRev);
        document.getElementById('kpiAOrders').textContent = aOrd;

        document.getElementById('kpiB').textContent = hasCompare ? eur(bRev) : '—';
        document.getElementById('kpiBOrders').textContent = hasCompare ? bOrd : '—';

        const diff = aRev - bRev;
        document.getElementById('kpiDiff').textContent = hasCompare ? eur(diff) : '—';
        const pct = (bRev > 0) ? ((aRev - bRev) / bRev * 100) : null;
        document.getElementById('kpiPct').textContent = (hasCompare && pct !== null) ? (pct.toFixed(1) + '%') : '—';

        // Table
        const tbody = document.getElementById('tbody');
        tbody.innerHTML = '';

        labels.forEach(period => {
            const a = curMap.get(period) || { revenue: 0, orders: 0 };
            const b = cmpMap.get(period) || { revenue: 0, orders: 0 };

            const tr = document.createElement('tr');
            tr.innerHTML = `
          <td>${period}</td>
          <td>${Number(a.revenue).toFixed(2)}</td>
          <td>${a.orders}</td>
          <td>${hasCompare ? Number(b.revenue).toFixed(2) : '—'}</td>
          <td>${hasCompare ? b.orders : '—'}</td>
        `;
            tbody.appendChild(tr);
        });

        // Chart
        if (chart) chart.destroy();

        const datasets = [{
            label: "CA Période A (€)",
            data: curRevenue
        }];

        if (hasCompare) {
            datasets.push({
                label: (isMenuCompare ? "CA Menu B (€)" : "CA Période B (€)"),
                data: cmpRevenue
            });
        }

        const ctx = document.getElementById('chart').getContext('2d');
        chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels,
                datasets
            },
            options: {
                responsive: true,
                interaction: { mode: 'index', intersect: false },
                plugins: { legend: { display: true } },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    }

    document.getElementById('btnLoad').addEventListener('click', loadStats);

    document.getElementById('btnClear').addEventListener('click', () => {
        document.getElementById('start').value = "<?= htmlspecialchars(date('Y-m-01')) ?>";
        document.getElementById('end').value = "<?= htmlspecialchars(date('Y-m-d')) ?>";
        document.getElementById('compare_start').value = "";
        document.getElementById('compare_end').value = "";
        document.getElementById('group').value = "day";
        document.getElementById('compare_mode').value = "relative";
        document.getElementById('menu_id').value = "";
        document.getElementById('compare_menu_id').value = "";
    });
</script>
</body>
</html>
