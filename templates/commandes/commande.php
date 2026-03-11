<!doctype html>
<html lang="fr">
<?php require TEMPLATES_PATH . '/partials/head.php'; ?>

<body class="bg-white">
<?php require TEMPLATES_PATH . '/partials/header.php'; ?>

<h1>Commander : <?= htmlspecialchars((string)$menu['titre']) ?></h1>
<p><a href="<?= BASE_URL ?>/menus">Retour aux menus</a></p>

<p>
    Prix par personne : <strong><?= number_format($prixParPersonne, 2, ',', ' ') ?> €</strong><br>
    Minimum : <strong><?= (int)$minPers ?> personnes</strong>
</p>
<hr>

<form method="post" action="<?= BASE_URL ?>/commandeCreate" id="commandeForm">
    <input type="hidden" name="menu_id" value="<?= (int)$menu['menu_id'] ?>">

    <h2>Prestation</h2>

    <label>Date de prestation :</label><br>
    <input type="date" name="date_prestation" min="<?= $today ?>" required><br><br>

    <label>Adresse de prestation :</label><br>
    <input type="text" name="adresse_prestation" required><br><br>

    <label>Ville de prestation :</label><br>
    <input type="text" name="ville_prestation" required><br><br>

    <hr>

    <h2>Livraison</h2>

    <p>
        <label>
            <input type="checkbox" id="sameAddress" checked>
            Livraison à la même adresse que la prestation
        </label><br><br>

        <label>
            <input type="checkbox" id="same_date" name="same_date">
            Livraison à la même date que la prestation
        </label>
    </p>

    <label>Date de livraison :</label><br>
    <input type="date" name="date_livraison" min="<?= $today ?>" required><br><br>

    <label>Heure souhaitée de livraison :</label><br>
    <input type="time" name="heure_livraison" required><br><br>

    <label>Adresse de livraison :</label><br>
    <input type="text" name="adresse_livraison" id="adresseLivraison" required><br><br>

    <label>Ville de livraison :</label><br>
    <input type="text" name="ville_livraison" id="villeLivraison" required><br><br>

    <label>Distance (km) si livraison hors Bordeaux :</label><br>
    <input type="number" step="0.1" min="0" name="distance_km" id="distanceKm" value="0"><br><br>

    <hr>

    <h2>Menu</h2>

    <label>Nombre de personnes :</label><br>
    <input type="number"
           name="nombre_personne"
           id="nbPersonnes"
           min="<?= (int)$minPers ?>"
           value="<?= (int)$minPers ?>"
           required>
    <br><br>

    <hr>

    <h2>Détail du prix</h2>
    <p>Menu : <span id="prixMenu">0.00</span> €</p>
    <p>Remise : <span id="prixRemise">-0.00</span> €</p>
    <p>Livraison : <span id="prixLivraison">0.00</span> €</p>
    <p><strong>Total : <span id="prixTotal">0.00</span> €</strong></p>

    <button type="submit">Valider la commande</button>
</form>

<?php require TEMPLATES_PATH . '/partials/footer.php'; ?>
<?php require TEMPLATES_PATH . '/partials/scripts.php'; ?>

<script>
    const PRIX_PAR_PERSONNE = <?= json_encode($prixParPersonne) ?>;
    const MIN_PERSONNES = <?= json_encode($minPers) ?>;

    const nbInput = document.getElementById('nbPersonnes');
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
</script>

</body>
</html>