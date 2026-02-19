<?php
// menus.php : vue globale des menus + filtres dynamiques via menus-ajax.php (HTML)

session_start();
require_once __DIR__ . '/src/db.php';

$pdo = db();

// Liste pour SELECTS
$themes = $pdo->query("SELECT theme_id, libelle FROM theme ORDER BY libelle")->fetchAll();
$regimes = $pdo->query("SELECT regime_id, libelle FROM regime ORDER BY libelle")->fetchAll();
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Nos menus</title>
</head>
<body>

<h1>Nos menus</h1>
    <a href="index.php">← Retour accueil</a>
<hr>

<h2>Filtres</h2>

<form id="filtersForm">
    <label>Prix maximum :</label><br>
    <input type="number" step="0.01" name="prix_max" placeholder="Ex: 30">
    <br><br>

    <label>Fourchette de prix :</label><br>
    <input type="number" step="0.01" name="prix_min" placeholder="Min">
    <input type="number" step="0.01" name="prix_max_range" placeholder="Max">
    <br><br>

    <label>Thème :</label><br>
    <select name="theme_id">
        <option value="">-- Tous --</option>
        <?php foreach ($themes as $t): ?>
            <option value="<?php echo (int)$t['theme_id']; ?>">
                <?php echo htmlspecialchars($t['libelle']); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <br><br>

    <label>Régime :</label><br>
    <select name="regime_id">
        <option value="">-- Tous --</option>
        <?php foreach ($regimes as $r): ?>
            <option value="<?php echo (int)$r['regime_id']; ?>">
                <?php echo htmlspecialchars($r['libelle']); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <br><br>

    <label>Nombre de personnes minimum :</label><br>
    <input type="number" name="min_personnes" placeholder="Ex: 10">
    <br><br>

    <button type="button" id="btnFiltrer">Filtrer</button>
    <button type="button" id="btnReset">Réinitialiser</button>
</form>

<hr>

<h2>Résultats</h2>
<div id="menusContainer">
    <p>Chargement...</p>
</div>

<script>
    async function loadMenus() {
        const form = document.getElementById('filtersForm');
        const params = new URLSearchParams(new FormData(form));

        const res = await fetch('menus-ajax.php?' + params.toString());
        const html = await res.text();

        document.getElementById('menusContainer').innerHTML = html;
    }

    // Bouton filtrer
    document.getElementById('btnFiltrer').addEventListener('click', loadMenus);

    // Bouton reset
    document.getElementById('btnReset').addEventListener('click', () => {
        document.getElementById('filtersForm').reset();
        loadMenus();
    });

    // Chargement initial
    loadMenus();
</script>

</body>
</html>
