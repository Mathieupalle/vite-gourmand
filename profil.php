<?php
// profil.php : espace profil utilisateur
// Permet à l'utilisateur connecté de consulter et modifier ses informations personnelles.

session_start();
require_once __DIR__ . '/src/db.php';
require_once __DIR__ . '/src/auth.php';

requireLogin();

$pdo = db();

$userId = (int)$_SESSION['user']['id'];

$errors = [];
$success = null;

// 1) Charger les infos actuelles depuis la BDD
$stmt = $pdo->prepare("
    SELECT utilisateur_id, email, nom, prenom, telephone, ville, adresse_postale
    FROM utilisateur
    WHERE utilisateur_id = ?
    LIMIT 1
");
$stmt->execute([$userId]);
$userDb = $stmt->fetch();

if (!$userDb) {
    http_response_code(404);
    die("Utilisateur introuvable.");
}

// 2) Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    $ville = trim($_POST['ville'] ?? '');
    $adresse = trim($_POST['adresse_postale'] ?? '');

    // Contrôle nom
    if ($nom === '') {
        $errors[] = "Le nom est obligatoire.";
    }

    // Contrôle téléphone
    if ($telephone !== '' && strlen($telephone) < 6) {
        $errors[] = "Le téléphone semble trop court.";
    }

    if (!$errors) {
        $upd = $pdo->prepare("
            UPDATE utilisateur
            SET nom = ?, prenom = ?, telephone = ?, ville = ?, adresse_postale = ?
            WHERE utilisateur_id = ?
        ");
        $upd->execute([$nom, $prenom ?: null, $telephone ?: null, $ville ?: null, $adresse ?: null, $userId]);

        $success = "Profil mis à jour.";

        // Recharger les données mises à jour
        $stmt->execute([$userId]);
        $userDb = $stmt->fetch();

        // Mettre à jour la session si stockage de ces infos dedans (auto-remplissage commande)
        $_SESSION['user']['nom'] = $userDb['nom'];
        $_SESSION['user']['prenom'] = $userDb['prenom'];
        $_SESSION['user']['telephone'] = $userDb['telephone'];
        $_SESSION['user']['ville'] = $userDb['ville'];
        $_SESSION['user']['adresse_postale'] = $userDb['adresse_postale'];
    }
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Mon profil</title>
</head>
<body>

<h1>Mon profil</h1>
<p><a href="index.php">← Retour accueil</a></p>
<hr>

<?php if ($success): ?>
    <p style="color:green"><?php echo htmlspecialchars($success); ?></p>
<?php endif; ?>

<?php foreach ($errors as $e): ?>
    <p style="color:red"><?php echo htmlspecialchars($e); ?></p>
<?php endforeach; ?>

<form method="post">
    <label>Email :</label><br>
    <input type="email" value="<?php echo htmlspecialchars($userDb['email']); ?>" disabled>
    <br><br>

    <label>Nom :</label><br>
    <input name="nom" value="<?php echo htmlspecialchars($userDb['nom']); ?>" required>
    <br><br>

    <label>Prénom :</label><br>
    <input name="prenom" value="<?php echo htmlspecialchars($userDb['prenom'] ?? ''); ?>">
    <br><br>

    <label>Téléphone :</label><br>
    <input name="telephone" value="<?php echo htmlspecialchars($userDb['telephone'] ?? ''); ?>">
    <br><br>

    <label>Ville :</label><br>
    <input name="ville" value="<?php echo htmlspecialchars($userDb['ville'] ?? ''); ?>">
    <br><br>

    <label>Adresse postale :</label><br>
    <input name="adresse_postale" value="<?php echo htmlspecialchars($userDb['adresse_postale'] ?? ''); ?>">
    <br><br>

    <button type="submit">Enregistrer</button>
</form>

</body>
</html>
