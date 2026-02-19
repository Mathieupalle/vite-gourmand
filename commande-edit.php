<?php
session_start();
require_once __DIR__ . '/src/db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$pdo = db();
$userId = (int)$_SESSION['user']['id'];

$commandeId = (int)($_GET['id'] ?? 0);
if ($commandeId <= 0) {
    die("Commande invalide.");
}

// Charger la commande (vérifier qu'elle appartient à l'utilisateur)
$stmt = $pdo->prepare("
    SELECT c.*, m.nombre_personne_minimum
    FROM commande c
    JOIN menu m ON m.menu_id = c.menu_id
    WHERE c.commande_id = ? AND c.utilisateur_id = ?
    LIMIT 1
");
$stmt->execute([$commandeId, $userId]);
$c = $stmt->fetch();

if (!$c) {
    die("Commande introuvable.");
}

// On autorise la modification seulement si pas encore acceptée
if ($c['statut'] !== 'en_attente') {
    die("Modification impossible : commande déjà traitée.");
}

$errors = [];
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datePrestation = $_POST['date_prestation'] ?? '';
    $dateLivraison  = $_POST['date_livraison'] ?? '';
    // --- Vérification dates : pas avant aujourd'hui ---
    $today = new DateTimeImmutable('today');
    $dp = DateTimeImmutable::createFromFormat('Y-m-d', $date_prestation);
    $dl = DateTimeImmutable::createFromFormat('Y-m-d', $date_livraison);

    if (!$dp || !$dl) {
        $errors[] = "Dates invalides.";
    } else {
        if ($dp < $today) {
            $errors[] = "La date de prestation ne peut pas être avant aujourd’hui.";
        }
        if ($dl < $today) {
            $errors[] = "La date de livraison ne peut pas être avant aujourd’hui.";
        }
    }
    $heureLivraison = $_POST['heure_livraison'] ?? '';
    $adressePresta  = trim($_POST['adresse_prestation'] ?? '');
    $villePresta    = trim($_POST['ville_prestation'] ?? '');
    $adresseLiv     = trim($_POST['adresse_livraison'] ?? '');
    $villeLiv       = trim($_POST['ville_livraison'] ?? '');
    $distanceKm     = (float)($_POST['distance_km'] ?? 0);
    $nb             = (int)($_POST['nombre_personne'] ?? 0);

    if ($datePrestation === '' || $dateLivraison === '' || $heureLivraison === '') {
        $errors[] = "Dates/heure obligatoires.";
    }
    if ($adressePresta === '' || $villePresta === '' || $adresseLiv === '' || $villeLiv === '') {
        $errors[] = "Adresses et villes obligatoires.";
    }
    if ($nb < (int)$c['nombre_personne_minimum']) {
        $errors[] = "Nombre de personnes insuffisant.";
    }
    if ($distanceKm < 0) $distanceKm = 0;

    // Recalcul livraison + remise (même logique que commande-create)
    $prixPers = (float)$c['prix_menu'];
    $sousTotal = $nb * $prixPers;

    $remise = 0;
    if ($nb >= ((int)$c['nombre_personne_minimum'] + 5)) {
        $remise = $sousTotal * 0.10;
    }

    $prixLivraison = 0;
    if (mb_strtolower($villeLiv) !== 'bordeaux') {
        $prixLivraison = 5 + (0.59 * $distanceKm);
    }

    if (!$errors) {
        $stmt = $pdo->prepare("
            UPDATE commande
            SET
              date_prestation = ?,
              date_livraison = ?,
              heure_livraison = ?,
              adresse_prestation = ?,
              ville_prestation = ?,
              adresse_livraison = ?,
              ville_livraison = ?,
              distance_km = ?,
              nombre_personne = ?,
              prix_livraison = ?,
              remise = ?
            WHERE commande_id = ? AND utilisateur_id = ?
        ");
        $stmt->execute([
                $datePrestation,
                $dateLivraison,
                $heureLivraison,
                $adressePresta,
                $villePresta,
                $adresseLiv,
                $villeLiv,
                $distanceKm,
                $nb,
                $prixLivraison,
                $remise,
                $commandeId,
                $userId
        ]);

        // Suivi
        $stmt2 = $pdo->prepare("INSERT INTO commande_suivi (commande_id, statut) VALUES (?, ?)");
        $stmt2->execute([$commandeId, 'modifiee_par_client']);

        header("Location: mes-commandes.php");
        exit;
    }
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Modifier commande</title>
</head>
<body>

<h1>Modifier ma commande</h1>
<p><a href="mes-commandes.php">← Retour</a></p>

<?php foreach ($errors as $e): ?>
    <p style="color:red"><?php echo htmlspecialchars($e); ?></p>
<?php endforeach; ?>

<form method="post">

    <label>Date prestation :</label><br>
    <input type="date" name="date_prestation" value="<?php echo htmlspecialchars($c['date_prestation']); ?>" required><br><br>

    <label>Adresse prestation :</label><br>
    <input type="text" name="adresse_prestation" value="<?php echo htmlspecialchars($c['adresse_prestation']); ?>" required><br><br>

    <label>Ville prestation :</label><br>
    <input type="text" name="ville_prestation" value="<?php echo htmlspecialchars($c['ville_prestation']); ?>" required><br><br>

    <hr>

    <label>Date livraison :</label><br>
    <input type="date" name="date_livraison" value="<?php echo htmlspecialchars($c['date_livraison']); ?>" required><br><br>

    <label>Heure livraison :</label><br>
    <input type="time" name="heure_livraison" value="<?php echo htmlspecialchars($c['heure_livraison']); ?>" required><br><br>

    <label>Adresse livraison :</label><br>
    <input type="text" name="adresse_livraison" value="<?php echo htmlspecialchars($c['adresse_livraison']); ?>" required><br><br>

    <label>Ville livraison :</label><br>
    <input type="text" name="ville_livraison" value="<?php echo htmlspecialchars($c['ville_livraison']); ?>" required><br><br>

    <label>Distance (km) :</label><br>
    <input type="number" step="0.1" min="0" name="distance_km" value="<?php echo htmlspecialchars((string)($c['distance_km'] ?? 0)); ?>"><br><br>

    <hr>

    <label>Nombre de personnes :</label><br>
    <input type="number" name="nombre_personne" value="<?php echo (int)$c['nombre_personne']; ?>" min="<?php echo (int)$c['nombre_personne_minimum']; ?>" required><br><br>

    <button type="submit">Enregistrer</button>

</form>

</body>
</html>
