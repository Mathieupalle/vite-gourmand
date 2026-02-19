<?php
// commande.php : formulaire de commande (pré-rempli)

session_start();
require_once __DIR__ . '/src/db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$pdo = db();

$menuId = (int)($_GET['menu_id'] ?? 0);
if ($menuId <= 0) {
    die("Menu invalide.");
}

// Infos menu
$stmt = $pdo->prepare("
    SELECT menu_id, titre, prix_par_personne, nombre_personne_minimum
    FROM menu
    WHERE menu_id = ?
");
$stmt->execute([$menuId]);
$menu = $stmt->fetch();
if (!$menu) {
    die("Menu introuvable.");
}

// Infos utilisateur (auto-remplissage)
$stmtUser = $pdo->prepare("SELECT * FROM utilisateur WHERE utilisateur_id = ? LIMIT 1");
$stmtUser->execute([(int)$_SESSION['user']['id']]);
$user = $stmtUser->fetch();
if (!$user) {
    die("Utilisateur introuvable.");
}

$prixParPersonne = (float)$menu['prix_par_personne'];
$minPers = (int)$menu['nombre_personne_minimum'];

$sameDate = isset($_POST['same_date']);

if ($sameDate) {
    $dateLivraison = $datePrestation;
}

$today = date('Y-m-d');
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Commander</title>
</head>
<body>

<h1>Commander : <?php echo htmlspecialchars($menu['titre']); ?></h1>
<p></p><a href="index.php">Accueil</a> |
<a href="menus.php">← Retour aux menus</a></p>

<p>
    Prix par personne : <strong><?php echo number_format($prixParPersonne, 2, ',', ' '); ?> €</strong><br>
    Minimum : <strong><?php echo $minPers; ?> personnes</strong>
</p>
<hr>

<form method="post" action="commande-create.php" id="commandeForm">

    <input type="hidden" name="menu_id" value="<?php echo (int)$menu['menu_id']; ?>">

    <h2>Informations client</h2>

    <label>Nom :</label><br>
    <input type="text" value="<?php echo htmlspecialchars($user['nom'] ?? ''); ?>" readonly><br><br>

    <label>Prénom :</label><br>
    <input type="text" value="<?php echo htmlspecialchars($user['prenom'] ?? ''); ?>" readonly><br><br>

    <label>Email :</label><br>
    <input type="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" readonly><br><br>

    <label>Téléphone :</label><br>
    <input type="text" value="<?php echo htmlspecialchars($user['telephone'] ?? ''); ?>" readonly><br><br>

    <hr>

    <h2>Prestation</h2>

    <label>Date de prestation :</label><br>
    <input type="date" name="date_prestation" min="<?php echo $today; ?>" required><br><br>

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
    <input type="date" name="date_livraison" min="<?php echo $today; ?>" required><br><br>

    <label>Heure souhaitée de livraison :</label><br>
    <input type="time" name="heure_livraison" required><br><br>

    <label>Adresse de livraison :</label><br>
    <input type="text" name="adresse_livraison" id="adresseLivraison" required><br><br>

    <label>Ville de livraison :</label><br>
    <input type="text" name="ville_livraison" id="villeLivraison" required><br><br>

    <label>Distance (km) si la livraison n’est pas à Bordeaux :</label><br>
    <input type="number" step="0.1" min="0" name="distance_km" id="distanceKm" value="0">

    <hr>

    <h2>Menu</h2>

    <label>Nombre de personnes :</label><br>
    <input type="number"
           name="nombre_personne"
           id="nbPersonnes"
           min="<?php echo $minPers; ?>"
           value="<?php echo $minPers; ?>"
           required>
    <br><br>

    <hr>

    <h2>Détail du prix</h2>

    <p>Menu : <span id="prixMenu">0</span> €</p>
    <p>Remise : <span id="prixRemise">0</span> €</p>
    <p>Livraison : <span id="prixLivraison">0</span> €</p>
    <p><strong>Total : <span id="prixTotal">0</span> €</strong></p>

    <button type="submit">Valider la commande</button>

</form>

<p><a href="menu.php?id=<?php echo (int)$menu['menu_id']; ?>">← Retour au menu</a></p>

<script>
    // Prix côté client (juste pour l'affichage, le serveur recalculera aussi)
    const PRIX_PAR_PERSONNE = <?php echo json_encode($prixParPersonne); ?>;
    const MIN_PERSONNES = <?php echo json_encode($minPers); ?>;

    const nbInput = document.getElementById('nbPersonnes');
    const villeLivraisonInput = document.getElementById('villeLivraison');
    const distanceKmInput = document.getElementById('distanceKm');

    const prixMenuEl = document.getElementById('prixMenu');
    const prixRemiseEl = document.getElementById('prixRemise');
    const prixLivraisonEl = document.getElementById('prixLivraison');
    const prixTotalEl = document.getElementById('prixTotal');

    function round2(n) {
        return Math.round(n * 100) / 100;
    }

    function calc() {
        let nb = parseInt(nbInput.value || '0', 10);
        if (nb < MIN_PERSONNES) nb = MIN_PERSONNES;

        const sousTotal = nb * PRIX_PAR_PERSONNE;

        // Remise 10% si nb >= min + 5
        let remise = 0;
        if (nb >= (MIN_PERSONNES + 5)) {
            remise = sousTotal * 0.10;
        }

        // Livraison : 0€ si Bordeaux, sinon 5€ + 0.59€/km
        const ville = (villeLivraisonInput.value || '').trim().toLowerCase();
        let km = parseFloat(distanceKmInput.value || '0');
        if (isNaN(km) || km < 0) km = 0;

        let livraison = 0;
        if (ville !== '' && ville !== 'bordeaux') {
            livraison = 5 + (0.59 * km);
        }

        const total = sousTotal - remise + livraison;

        prixMenuEl.textContent = round2(sousTotal).toFixed(2);
        prixRemiseEl.textContent = '-' + round2(remise).toFixed(2);
        prixLivraisonEl.textContent = round2(livraison).toFixed(2);
        prixTotalEl.textContent = round2(total).toFixed(2);
    }

    // Copier prestation -> livraison si checkbox cochée
    const same = document.getElementById('sameAddress');
    const adressePresta = document.querySelector('input[name="adresse_prestation"]');
    const villePresta = document.querySelector('input[name="ville_prestation"]');
    const adresseLiv = document.getElementById('adresseLivraison');
    const villeLiv = document.getElementById('villeLivraison');

    function syncAddress() {
        if (same.checked) {
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

    // Recalculer prix à chaque changement
    nbInput.addEventListener('input', calc);
    villeLivraisonInput.addEventListener('input', calc);
    distanceKmInput.addEventListener('input', calc);

    // Initialisation
    syncAddress();
    calc();
</script>

<script>
    // Date livraison = Date Prestation
    const cb = document.getElementById('same_date');
    const datePrest = document.querySelector('input[name="date_prestation"]');
    const dateLiv = document.querySelector('input[name="date_livraison"]');

    function syncDates() {
        if (cb.checked) {
            dateLiv.value = datePrest.value;
            dateLiv.readOnly = true;
        } else {
            dateLiv.readOnly = false;
        }
    }

    cb.addEventListener('change', syncDates);
    datePrest.addEventListener('change', syncDates);
</script>

<script>
    const cb = document.getElementById('same_date');
    const datePrest = document.querySelector('input[name="date_prestation"]');
    const dateLiv = document.querySelector('input[name="date_livraison"]');

    function syncDates() {
        if (cb.checked) {
            dateLiv.value = datePrest.value;
            dateLiv.readOnly = true;
        } else {
            dateLiv.readOnly = false;
        }
    }

    cb.addEventListener('change', syncDates);
    datePrest.addEventListener('change', syncDates);
</script>

</body>
</html>
