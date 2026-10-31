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

            // Nombre de menus affichés
            const count = menusContainer.querySelectorAll('.menu-item').length;
            if (count > 0) {
                resultsCount.textContent = count + ' menu(s)';
            } else {
                resultsCount.textContent = '';
            }

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

    document.getElementById('btnFiltrer').addEventListener('click', loadMenus);

    document.getElementById('btnReset').addEventListener('click', () => {
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

// Script page stats.php : graphique du nombre de commandes par menu
document.addEventListener('DOMContentLoaded', () => {
    const chartEl = document.getElementById('chart');
    if (!chartEl) return;

    new Chart(chartEl, {
        type: 'bar',
        data: {
            labels: window.STATS_LABELS,
            datasets: [{
                label: 'Nombre de commandes',
                data: window.STATS_COMMANDES
            }]
        },
        options: {
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0 } }
            }
        }
    });
});
