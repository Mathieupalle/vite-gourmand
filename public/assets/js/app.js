// Script global qui gère la confirmation pour tous les boutons/éléments ayant [data-confirm] :
document.addEventListener("click", (e) => {
    const el = e.target.closest("[data-confirm]");
    if (!el) return;

    const msg = el.getAttribute("data-confirm") || "Confirmer cette action ?";
    if (!confirm(msg)) e.preventDefault();
});

// Script page menus.php :
document.addEventListener('DOMContentLoaded', () => {

    const menusContainer = document.getElementById('menusContainer');
    if (!menusContainer) return;

    const loadingBadge = document.getElementById('loadingBadge');
    const resultsCount = document.getElementById('resultsCount');

    function setLoading(isLoading) {
        loadingBadge.classList.toggle('d-none', !isLoading);
    }

    async function loadMenus() {
        const form = document.getElementById('filtersForm');
        const params = new URLSearchParams(new FormData(form));

        setLoading(true);
        menusContainer.innerHTML = `
            <div class="d-flex align-items-center gap-2 text-secondary">
                <div class="spinner-border spinner-border-sm"></div>
                <span>Chargement...</span>
            </div>
        `;

        try {
            const res = await fetch('menusAjax?' + params.toString());
            const html = await res.text();

            menusContainer.innerHTML = html;

            const count = (html.match(/menu\\?id=/g) || []).length;
            resultsCount.textContent = count ? `${count} menu(s)` : '';

        } catch (e) {
            menusContainer.innerHTML = `
                <div class="alert alert-danger">
                    Erreur lors du chargement
                </div>
            `;
        } finally {
            setLoading(false);
        }
    }

    document.getElementById('btnFiltrer')?.addEventListener('click', loadMenus);

    document.getElementById('btnReset')?.addEventListener('click', () => {
        document.getElementById('filtersForm').reset();
        loadMenus();
    });

    loadMenus();
});

// Script page commande.php :
document.addEventListener('DOMContentLoaded', () => {
    const nbInput = document.getElementById('nbPersonnes');
    if (!nbInput) return;

    const PRIX_PAR_PERSONNE = window.PRIX_PAR_PERSONNE;
    const MIN_PERSONNES = window.MIN_PERSONNES;

    const villeLivraisonInput = document.getElementById('villeLivraison');
    const distanceKmInput = document.getElementById('distanceKm');

    const prixMenuEl = document.getElementById('prixMenu');
    const prixRemiseEl = document.getElementById('prixRemise');
    const prixLivraisonEl = document.getElementById('prixLivraison');
    const prixTotalEl = document.getElementById('prixTotal');

    function round2(n){ return Math.round(n*100)/100; }

    function calc(){
        let nb = parseInt(nbInput.value || '0', 10);
        if (nb < MIN_PERSONNES) nb = MIN_PERSONNES;

        const sousTotal = nb * PRIX_PAR_PERSONNE;

        let remise = 0;
        if (nb >= (MIN_PERSONNES + 5)) remise = sousTotal * 0.10;

        const ville = (villeLivraisonInput.value || '').trim().toLowerCase();
        let km = parseFloat(distanceKmInput.value || '0');
        if (isNaN(km) || km < 0) km = 0;

        let livraison = 0;
        if (ville !== '' && ville !== 'bordeaux') livraison = 5 + (0.59 * km);

        const total = sousTotal - remise + livraison;

        prixMenuEl.textContent = round2(sousTotal).toFixed(2);
        prixRemiseEl.textContent = '-' + round2(remise).toFixed(2);
        prixLivraisonEl.textContent = round2(livraison).toFixed(2);
        prixTotalEl.textContent = round2(total).toFixed(2);
    }

    const same = document.getElementById('sameAddress');
    const adressePresta = document.querySelector('input[name="adresse_prestation"]');
    const villePresta = document.querySelector('input[name="ville_prestation"]');
    const adresseLiv = document.getElementById('adresseLivraison');
    const villeLiv = document.getElementById('villeLivraison');

    function syncAddress(){
        if (same.checked){
            adresseLiv.value = adressePresta.value;
            villeLiv.value = villePresta.value;
            adresseLiv.readOnly = true;
            villeLiv.readOnly = true;
        } else {
            adresseLiv.readOnly = false;
            villeLiv.readOnly = false;
        }
        calc();
    }

    same.addEventListener('change', syncAddress);
    adressePresta.addEventListener('input', syncAddress);
    villePresta.addEventListener('input', syncAddress);

    nbInput.addEventListener('input', calc);
    villeLivraisonInput.addEventListener('input', calc);
    distanceKmInput.addEventListener('input', calc);

    const cb = document.getElementById('same_date');
    const datePrest = document.querySelector('input[name="date_prestation"]');
    const dateLiv = document.querySelector('input[name="date_livraison"]');

    function syncDates(){
        if (cb.checked){
            dateLiv.value = datePrest.value;
            dateLiv.readOnly = true;
        } else {
            dateLiv.readOnly = false;
        }
    }
    cb.addEventListener('change', syncDates);
    datePrest.addEventListener('change', syncDates);

    syncAddress();
    syncDates();
    calc();
});

// Script page stats.php :
document.addEventListener('DOMContentLoaded', () => {
    const chartEl = document.getElementById('chart');
    if (!chartEl) return;

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

        let url = `/vite-gourmand/public/api/revenusStats.php?start=${encodeURIComponent(p.start)}&end=${encodeURIComponent(p.end)}&group=${encodeURIComponent(p.group)}&compare_mode=${encodeURIComponent(p.compare_mode)}`;
        if (p.menuId) url += `&menu_id=${encodeURIComponent(p.menuId)}`;

        if (p.compareMenuId) {
            url += `&compare_menu_id=${encodeURIComponent(p.compareMenuId)}`;
        } else if (p.compare_start && p.compare_end) {
            url += `&compare_start=${encodeURIComponent(p.compare_start)}&compare_end=${encodeURIComponent(p.compare_end)}`;
        }

        const res = await fetch(url);
        const json = await res.json();

        if(json.error){
            console.error(json.error);
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

        const isMenuCompare = p.compareMenuId && Number(p.compareMenuId) > 0;

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
        document.getElementById('start').value = window.STATS_START;
        document.getElementById('end').value   = window.STATS_END;
        document.getElementById('compare_start').value = "";
        document.getElementById('compare_end').value = "";
        document.getElementById('group').value = "day";
        document.getElementById('compare_mode').value = "relative";
        document.getElementById('menu_id').value = "";
        document.getElementById('compare_menu_id').value = "";
    });
});