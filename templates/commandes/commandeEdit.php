<!doctype html>
<html lang="fr">
<?php require TEMPLATES_PATH . '/partials/head.php'; ?>

<body class="bg-white">
<?php require TEMPLATES_PATH . '/partials/header.php'; ?>

<h1>Modifier ma commande</h1>
<p><a href="<?= BASE_URL ?>/mesCommandes">← Retour</a></p>

<?php foreach ($errors as $e): ?>
    <p style="color:red"><?= htmlspecialchars($e) ?></p>
<?php endforeach; ?>

<form method="post">
    <p><b>Menu :</b> <?= htmlspecialchars((string)$c['menu_titre']) ?></p>

    <label>Date prestation :</label><br>
    <input type="date" name="date_prestation" value="<?= htmlspecialchars((string)$c['date_prestation']) ?>" required><br><br>

    <label>Adresse prestation :</label><br>
    <input type="text" name="adresse_prestation" value="<?= htmlspecialchars((string)$c['adresse_prestation']) ?>" required><br><br>

    <label>Ville prestation :</label><br>
    <input type="text" name="ville_prestation" value="<?= htmlspecialchars((string)$c['ville_prestation']) ?>" required><br><br>

    <hr>

    <label>Date livraison :</label><br>
    <input type="date" name="date_livraison" value="<?= htmlspecialchars((string)$c['date_livraison']) ?>" required><br><br>

    <label>Heure livraison :</label><br>
    <input type="time" name="heure_livraison" value="<?= htmlspecialchars((string)$c['heure_livraison']) ?>" required><br><br>

    <label>Adresse livraison :</label><br>
    <input type="text" name="adresse_livraison" value="<?= htmlspecialchars((string)$c['adresse_livraison']) ?>" required><br><br>

    <label>Ville livraison :</label><br>
    <input type="text" name="ville_livraison" value="<?= htmlspecialchars((string)$c['ville_livraison']) ?>" required><br><br>

    <label>Distance (km) :</label><br>
    <input type="number" step="0.1" min="0" name="distance_km" value="<?= htmlspecialchars((string)($c['distance_km'] ?? 0)) ?>"><br><br>

    <hr>

    <label>Nombre de personnes :</label><br>
    <input type="number"
           name="nombre_personne"
           value="<?= (int)$c['nombre_personne'] ?>"
           min="<?= (int)$c['nombre_personne_minimum'] ?>"
           required><br><br>

    <button type="submit">Enregistrer</button>
</form>
<?php require TEMPLATES_PATH . '/partials/footer.php'; ?>
<?php require TEMPLATES_PATH . '/partials/scripts.php'; ?>
</body>
</html>