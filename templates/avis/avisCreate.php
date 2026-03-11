<!doctype html>
<html lang="fr">

<?php require TEMPLATES_PATH . '/partials/head.php'; ?>

<body class="bg-white">

<?php require TEMPLATES_PATH . '/partials/header.php'; ?>

<h1>Laisser un avis</h1>

<p>
    <a href="<?= BASE_URL ?>/home">Accueil</a>
    <a href="<?= BASE_URL ?>/mesCommandes">Retour</a>
</p>

<p>
    <strong>Commande :</strong>
    <?= htmlspecialchars((string)$commande['menu_titre']) ?>
</p>

<?php if (!empty($success)): ?>

    <p style="color:green">
        <?= htmlspecialchars($success) ?>
    </p>

<?php endif; ?>

<?php if (!empty($errors)): ?>

    <?php foreach ($errors as $e): ?>

        <p style="color:red">
            <?= htmlspecialchars($e) ?>
        </p>

    <?php endforeach; ?>

<?php endif; ?>

<?php if (!$success): ?>

    <form method="post">

        <label>Note (1 à 5) :</label>
        <br>

        <select name="note" required>

            <option value="">-- choisir --</option>

            <?php for ($i=1; $i<=5; $i++): ?>

                <option value="<?= $i ?>">
                    <?= $i ?>
                </option>

            <?php endfor; ?>

        </select>

        <br><br>

        <label>Commentaire :</label>
        <br>

        <textarea name="description" rows="4" cols="40" required></textarea>

        <br><br>

        <button type="submit">
            Envoyer l'avis
        </button>

    </form>

<?php endif; ?>

<?php require TEMPLATES_PATH . '/partials/footer.php'; ?>
<?php require TEMPLATES_PATH . '/partials/scripts.php'; ?>

</body>
</html>