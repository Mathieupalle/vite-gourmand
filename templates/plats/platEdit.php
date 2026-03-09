<!doctype html>
<html lang="fr">

<?php require TEMPLATES_PATH . '/partials/head.php'; ?>

<body class="bg-white">

<?php require TEMPLATES_PATH . '/partials/header.php'; ?>

<h1>Modifier un plat</h1>

<p>
    <a href="<?= BASE_URL ?>/platManage">← Retour</a>
</p>

<?php if ($success): ?>
    <p style="color:green">
        <?= htmlspecialchars($success) ?>
    </p>
<?php endif; ?>

<?php foreach ($errors as $e): ?>
    <p style="color:red">
        <?= htmlspecialchars($e) ?>
    </p>
<?php endforeach; ?>

<form method="post">

    <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">

    <label>Titre</label><br>
    <input
            type="text"
            name="titre_plat"
            value="<?= htmlspecialchars((string)$plat['titre_plat']) ?>"
            required
    >

    <br><br>

    <label>Catégorie</label><br>

    <select name="categorie">

        <?php
        $cats = [
                'entree'=>'Entrée',
                'plat'=>'Plat',
                'dessert'=>'Dessert',
                'boisson'=>'Boisson',
                'fromage'=>'Fromage',
                'mise_en_bouche'=>'Mise en bouche',
                'autre'=>'Autre'
        ];

        foreach ($cats as $key => $label):
            ?>

            <option
                    value="<?= $key ?>"
                    <?= $plat['categorie'] === $key ? 'selected' : '' ?>
            >
                <?= htmlspecialchars($label) ?>
            </option>

        <?php endforeach; ?>

    </select>

    <br><br>

    <fieldset>

        <legend>Allergènes</legend>

        <?php foreach ($allergenes as $a): ?>

            <?php $aid = (int)$a['allergene_id']; ?>

            <label>

                <input
                        type="checkbox"
                        name="allergenes[]"
                        value="<?= $aid ?>"
                        <?= in_array($aid, $selected, true) ? 'checked' : '' ?>
                >

                <?= htmlspecialchars((string)$a['libelle']) ?>

            </label>

            <br>

        <?php endforeach; ?>

    </fieldset>

    <br>

    <button type="submit">
        Enregistrer
    </button>

</form>

<?php require TEMPLATES_PATH . '/partials/footer.php'; ?>
<?php require TEMPLATES_PATH . '/partials/scripts.php'; ?>

</body>
</html>