<?php require TEMPLATES_PATH . '/partials/head.php'; ?>

<html lang="fr">
<body class="bg-light">

<?php require TEMPLATES_PATH . '/partials/header.php'; ?>

<main class="container my-4">
    <div class="d-flex flex-column flex-md-row align-items-md-end justify-content-between gap-2">
        <h1 class="h3 mb-3">Créer un plat</h1>
        <a href="<?= BASE_URL ?>/platManage" class="btn btn-sm btn-outline-secondary mb-3">Retour</a>
    </div>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <?php foreach ($errors as $e): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($e) ?></div>
        <?php endforeach; ?>
    <?php endif; ?>

    <form method="post">

        <div class="mb-3">
            <label for="titre_plat" class="form-label">Titre</label>
            <input
                    type="text"
                    name="titre_plat"
                    id="titre_plat"
                    class="form-control"
                    required
            >
        </div>

        <div class="mb-3">
            <label for="categorie" class="form-label">Catégorie</label>
            <select name="categorie" id="categorie" class="form-select">
                <?php
                $cats = [
                        'entree' => 'Entrée',
                        'plat' => 'Plat',
                        'dessert' => 'Dessert',
                        'boisson' => 'Boisson',
                        'fromage' => 'Fromage',
                        'mise_en_bouche' => 'Mise en bouche',
                        'autre' => 'Autre'
                ];
                foreach ($cats as $key => $label):
                    $selected = $key === 'plat' ? 'selected' : '';
                    ?>
                    <option value="<?= $key ?>" <?= $selected ?>><?= htmlspecialchars($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <fieldset class="mb-3">
            <legend class="col-form-label pt-0">Allergènes</legend>
            <?php if (!empty($allergenes)): ?>
                <?php foreach ($allergenes as $a): ?>
                    <?php $aid = (int)($a['allergene_id'] ?? 0); ?>
                    <div class="form-check">
                        <input
                                type="checkbox"
                                class="form-check-input"
                                name="allergenes[]"
                                id="allergene_<?= $aid ?>"
                                value="<?= $aid ?>"
                        >
                        <label class="form-check-label" for="allergene_<?= $aid ?>">
                            <?= htmlspecialchars((string)($a['libelle'] ?? '')) ?>
                        </label>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </fieldset>

        <div class="mb-4">
            <button type="submit" class="btn btn-primary">
                Créer
            </button>
        </div>

    </form>

</main>

<?php require TEMPLATES_PATH . '/partials/footer.php'; ?>
<?php require TEMPLATES_PATH . '/partials/scripts.php'; ?>

</body>
</html>