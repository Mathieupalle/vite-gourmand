<?php
// register.php : inscription complète (nom/prénom/téléphone/adresse + email + mot de passe sécurisé)
// Rôle attribué : utilisateur (role_id = 1)

session_start();
require_once __DIR__ . '/src/db.php';

$errors = [];
$success = null;

// Petite fonction pour vérifier le mot de passe
function isStrongPassword(string $password): bool
{
    if (strlen($password) < 10) return false;
    if (!preg_match('/[A-Z]/', $password)) return false;         // 1 majuscule
    if (!preg_match('/[a-z]/', $password)) return false;         // 1 minuscule
    if (!preg_match('/[0-9]/', $password)) return false;         // 1 chiffre
    if (!preg_match('/[^A-Za-z0-9]/', $password)) return false;  // 1 caractère spécial
    return true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1) Récupération des champs
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $adresse = trim($_POST['adresse_postale'] ?? '');
    $password = $_POST['password'] ?? '';

    // 2) Validations
    if ($nom === '') $errors[] = "Nom obligatoire.";
    if ($prenom === '') $errors[] = "Prénom obligatoire.";
    if ($adresse === '') $errors[] = "Adresse postale obligatoire.";

    // Téléphone : contrôle (chiffres + + + espaces acceptés)
    if ($telephone === '') {
        $errors[] = "Numéro de téléphone obligatoire.";
    } elseif (!preg_match('/^[0-9 +().-]{8,20}$/', $telephone)) {
        $errors[] = "Numéro de téléphone invalide.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email invalide.";
    }

    if (!isStrongPassword($password)) {
        $errors[] = "Mot de passe trop faible : 10 caractères minimum avec majuscule, minuscule, chiffre et caractère spécial.";
    }

    if (!$errors) {
        $pdo = db();

        // 3) Vérifier si email déjà utilisé
        $stmt = $pdo->prepare("SELECT utilisateur_id FROM utilisateur WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $errors[] = "Email déjà utilisé.";
        } else {
            // 4) Insertion user (role_id=1)
            $stmt = $pdo->prepare("
                INSERT INTO utilisateur (nom, prenom, telephone, adresse_postale, email, password, role_id)
                VALUES (?, ?, ?, ?, ?, ?, 1)
            ");
            $stmt->execute([$nom, $prenom, $telephone, $adresse, $email, $password]);

            // 5) Démarrer la session
            $_SESSION['user'] = [
                    'id' => (int)$pdo->lastInsertId(),
                    'email' => $email,
                    'role' => 'user',
                    'nom' => $nom,
                    'prenom' => $prenom,
                    'telephone' => $telephone,
                    'adresse_postale' => $adresse
            ];

            // 6) Simulation du mail de bienvenue
            $success = "Inscription réussie. Un email de bienvenue vous a été envoyé.";
        }
    }
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Inscription</title>
</head>
<body>

<h1>Créer un compte</h1>
<?php if ($success): ?>
    <p style="color:green"><?php echo htmlspecialchars($success); ?></p>
<?php endif; ?>

<?php foreach ($errors as $e): ?>
    <p style="color:red"><?php echo htmlspecialchars($e); ?></p>
<?php endforeach; ?>

<form method="post">
    <label>Nom :</label><br>
    <input type="text" name="nom" value="<?php echo htmlspecialchars($_POST['nom'] ?? ''); ?>" required><br><br>

    <label>Prénom :</label><br>
    <input type="text" name="prenom" value="<?php echo htmlspecialchars($_POST['prenom'] ?? ''); ?>" required><br><br>

    <label>Téléphone :</label><br>
    <input type="text" name="telephone" value="<?php echo htmlspecialchars($_POST['telephone'] ?? ''); ?>" required><br><br>

    <label>Email :</label><br>
    <input type="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required><br><br>

    <label>Adresse postale :</label><br>
    <input type="text" name="adresse_postale" value="<?php echo htmlspecialchars($_POST['adresse_postale'] ?? ''); ?>" required><br><br>

    <label>Mot de passe sécurisé :</label><br>
    <input type="password" name="password" required><br>
    <small>(10 caractères minimum : 1 majuscule, 1 minuscule, 1 chiffre, 1 caractère spécial).</small>
    <br><br>

    <button type="submit">Créer un compte</button>
</form>
<p><a href="index.php">← Retour accueil</a></p>
</body>
</html>
