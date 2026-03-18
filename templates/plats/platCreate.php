<!doctype html>
<html lang="fr">
<?php require TEMPLATES_PATH . '/partials/head.php'; ?>

<body class="bg-white">
<?php require TEMPLATES_PATH . '/partials/header.php'; ?>

<h1>Créer un plat</h1>
<p><a href="<?= BASE_URL ?>/platManage">Retour</a></p>

<?php if ($success): ?>
    <p style="color:green"><?= htmlspecialchars($success) ?></p>
<?php endif; ?>

<?php foreach ($errors as $e): ?>
    <p style="color:red"><?= htmlspecialchars($e) ?></p>
<?php endforeach; ?>

<form method="post">
    <label>Titre</label><br>
    <input type="text" name="titre_plat" required><br><br>

    <label>Catégorie</label><br>
    <select name="categorie">
        <option value="entree">Entrée</option>
        <option value="plat" selected>Plat</option>
        <option value="dessert">Dessert</option>
        <option value="boisson">Boisson</option>
        <option value="fromage">Fromage</option>
        <option value="mise_en_bouche">Mise en bouche</option>
        <option value="autre">Autre</option>
    </select><br><br>

    <fieldset>
        <legend>Allergènes</legend>
        <?php foreach ($allergenes as $a): ?>
            <label>
                <input type="checkbox" name="allergenes[]" value="<?= (int)$a['allergene_id'] ?>">
                <?= htmlspecialchars((string)$a['libelle']) ?>
            </label><br>
        <?php endforeach; ?>
    </fieldset>

    <br>
    <button type="submit">Créer</button>
</form>

<?php require TEMPLATES_PATH . '/partials/footer.php'; ?>
<?php require TEMPLATES_PATH . '/partials/scripts.php'; ?>
</body>
</html>