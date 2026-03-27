<?php require TEMPLATES_PATH . '/partials/head.php'; ?>

<html lang="fr">
<body>

<?php require TEMPLATES_PATH . '/partials/header.php'; ?>

<main class="container my-4">
    <div class="d-flex flex-column flex-md-row align-items-md-end justify-content-between gap-2">
        <h1 class="h3 mb-3">Créer un menu</h1>
        <a class="btn btn-sm btn-outline-secondary mb-3" href="<?= BASE_URL ?>/menuManage">Retour</a>
    </div>

    <?php
    if (!empty($success)) {
        echo '<div class="alert alert-success">';
        echo htmlspecialchars($success);
        echo '</div>';
    }

    if (!empty($errors)) {
        foreach ($errors as $e) {
            echo '<div class="alert alert-danger">';
            echo htmlspecialchars($e);
            echo '</div>';
        }
    }
    ?>

    <form method="post" class="p-3 border rounded bg-white">

        <div class="mb-3">
            <label for="titre" class="form-label">Titre</label>
            <input type="text" name="titre" id="titre" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <input type="text" name="description" id="description" class="form-control">
        </div>

        <div class="mb-3">
            <label for="prix_par_personne" class="form-label">Prix par personne (€)</label>
            <input type="number" step="0.01" name="prix_par_personne" id="prix_par_personne" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="nombre_personne_minimum" class="form-label">Nombre minimum de personnes</label>
            <input type="number" name="nombre_personne_minimum" id="nombre_personne_minimum" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="quantite_restante" class="form-label">Quantité restante</label>
            <input type="number" name="quantite_restante" id="quantite_restante" class="form-control">
        </div>

        <div class="mb-3">
            <label for="theme_id" class="form-label">Thème</label>
            <select name="theme_id" id="theme_id" class="form-select" required>
                <option value="">--</option>
                <?php if (!empty($themes)): ?>
                    <?php foreach ($themes as $t) {
                        $id = (int)$t['theme_id'];
                        $libelle = htmlspecialchars((string)$t['libelle']);
                        echo "<option value='$id'>$libelle</option>";
                    }
                    ?>
                <?php endif; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="regime_id" class="form-label">Régime</label>
            <select name="regime_id" id="regime_id" class="form-select" required>
                <option value="">--</option>
                <?php if (!empty($regimes)): ?>
                    <?php foreach ($regimes as $r) {
                        $id = (int)$r['regime_id'];
                        $libelle = htmlspecialchars((string)$r['libelle']);
                        echo "<option value='$id'>$libelle</option>";
                    }
                    ?>
                <?php endif; ?>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">
            Créer
        </button>

    </form>

    <div class="mb-4"></div>

</main>

<?php
require TEMPLATES_PATH . '/partials/footer.php';
require TEMPLATES_PATH . '/partials/scripts.php';
?>

</body>
</html>