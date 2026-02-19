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
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/src/db.php';
$pdo = db();

// Dropdown des menus
$menus = [];
try {
    $stmt = $pdo->query("SELECT menu_id, titre FROM menu ORDER BY titre ASC");
    $menus = $stmt->fetchAll() ?: [];
} catch (Throwable $e) {
    $menus = [];
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8"/>
    <title>Statistiques</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: Arial, sans-serif; }
        .row { display:flex; gap:12px; align-items:end; flex-wrap:wrap; }
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
<h1>Statistiques (MongoDB)</h1>
<p><a href="admin.php">← Retour à l'espace Gestion</a></p>

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
        <div class="muted" style="margin-top:10px;">
        </div>
    </div>

    <div class="card">
        <button id="btnLoad" class="btn">Afficher</button>
        <button id="btnClearCompare" class="btn" style="margin-top:8px;">Vider comparaison</button>
    </div>
</div>

<div class="kpis">
    <div class="card">
        <div class="muted">CA Période A</div>
        <div style="font-size:24px;" id="kpiA">—</div>
        <div class="muted">Commandes: <span id="kpiAOrders">—</span></div>
    </div>
    <div class="card">
        <div class="muted">CA Période B</div>
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
        <th>CA B (€)</th>
        <th>Commandes B</th>
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
            menuId: document.getElementById('menu_id').value.trim()
        };
    }

    function eur(n) { return Number(n || 0).toFixed(2) + " €"; }

    async function loadStats() {
        const p = getParams();

        let url = `/api/revenus-stats.php?start=${encodeURIComponent(p.start)}&end=${encodeURIComponent(p.end)}&group=${encodeURIComponent(p.group)}&compare_mode=${encodeURIComponent(p.compare_mode)}`;
        if (p.menuId) url += `&menu_id=${encodeURIComponent(p.menuId)}`;
        if (p.compare_start && p.compare_end) url += `&compare_start=${encodeURIComponent(p.compare_start)}&compare_end=${encodeURIComponent(p.compare_end)}`;

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

        document.getElementById('kpiA').textContent = eur(aRev);
        document.getElementById('kpiAOrders').textContent = aOrd;
        document.getElementById('kpiB').textContent = hasCompare ? eur(bRev) : '—';
        document.getElementById('kpiBOrders').textContent = hasCompare ? bOrd : '—';

        if (hasCompare) {
            const diff = aRev - bRev;
            document.getElementById('kpiDiff').textContent = eur(diff);
            const pct = (bRev === 0) ? null : (diff / bRev) * 100;
            document.getElementById('kpiPct').textContent = (pct === null) ? '—' : (pct.toFixed(1) + ' %');
        } else {
            document.getElementById('kpiDiff').textContent = '—';
            document.getElementById('kpiPct').textContent = '—';
        }

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
                label: "CA Période B (€)",
                data: cmpRevenue
            });
        }

        chart = new Chart(document.getElementById('chart'), {
            type: 'bar',
            data: { labels, datasets }
        });
    }

    document.getElementById('btnLoad').addEventListener('click', loadStats);
    document.getElementById('btnClearCompare').addEventListener('click', () => {
        document.getElementById('compare_start').value = '';
        document.getElementById('compare_end').value = '';
        loadStats();
    });

    loadStats();
</script>
</body>
</html>
