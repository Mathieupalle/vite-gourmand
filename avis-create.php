<?php
// avis-create.php : permet à un utilisateur de laisser un avis sur une commande livrée.
// L'avis est enregistré avec le statut "en_attente" (il devra être validé par employé/admin).

session_start();
require_once __DIR__ . '/src/db.php';

$userId = (int)($_SESSION['user']['id'] ?? 0);
if ($userId <= 0) {
    header("Location: login.php");
    exit;
}

$pdo = db();

$commandeId = (int)($_GET['commande_id'] ?? 0);
if ($commandeId <= 0) {
    http_response_code(400);
    die("Commande invalide.");
}

// 1) Vérifier que la commande appartient bien à l'utilisateur
$stmt = $pdo->prepare("
    SELECT c.commande_id, c.statut, m.titre AS menu_titre
    FROM commande c
    JOIN menu m ON m.menu_id = c.menu_id
    WHERE c.commande_id = ? AND c.utilisateur_id = ?
    LIMIT 1
");
$stmt->execute([$commandeId, $userId]);
$commande = $stmt->fetch();

if (!$commande) {
    http_response_code(404);
    die("Commande introuvable.");
}

// 2) Autoriser avis seulement si commande terminée
$allowedStatuts = ['terminee'];
if (!in_array((string)$commande['statut'], $allowedStatuts, true)) {
    die("Vous pourrez laisser un avis uniquement une fois la commande terminée.");
}

// 3) Empêcher plusieurs avis pour la même commande
$stmt = $pdo->prepare("SELECT avis_id FROM avis WHERE commande_id = ? LIMIT 1");
$stmt->execute([$commandeId]);
if ($stmt->fetch()) {
    die("Un avis a déjà été envoyé pour cette commande.");
}

$errors = [];
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Récupération des champs
    $note = (int)($_POST['note'] ?? 0);
    $description = trim($_POST['description'] ?? '');

    // Contrôles
    if ($note < 1 || $note > 5) {
        $errors[] = "La note doit être entre 1 et 5.";
    }
    if ($description === '') {
        $errors[] = "Le commentaire est obligatoire.";
    }

    if (!$errors) {
        $stmt = $pdo->prepare("
            INSERT INTO avis (note, description, statut, utilisateur_id, commande_id)
            VALUES (?, ?, 'en_attente', ?, ?)
        ");
        $stmt->execute([$note, $description, $userId, $commandeId]);

        $success = "Merci ! Votre avis a été envoyé et sera visible après validation.";
    }
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Laisser un avis</title>
</head>
<body>

<h1>Laisser un avis</h1>
<p><a href="mes-commandes.php">← Retour à mes commandes</a></p>

<p><strong>Commande :</strong> <?php echo htmlspecialchars($commande['menu_titre']); ?></p>

<?php if ($success): ?>
    <p style="color:green"><?php echo htmlspecialchars($success); ?></p>
<?php endif; ?>

<?php foreach ($errors as $e): ?>
    <p style="color:red"><?php echo htmlspecialchars($e); ?></p>
<?php endforeach; ?>

<?php if (!$success): ?>
    <form method="post">
        <label>Note (1 à 5) :</label><br>
        <select name="note" required>
            <option value="">-- choisir --</option>
            <?php for ($i=1; $i<=5; $i++): ?>
                <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
            <?php endfor; ?>
        </select>
        <br><br>

        <label>Commentaire :</label><br>
        <textarea name="description" rows="4" cols="40" required></textarea>
        <br><br>

        <button type="submit">Envoyer l'avis</button>
    </form>
<?php endif; ?>

</body>
</html>
