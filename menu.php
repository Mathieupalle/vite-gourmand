<?php
// menu.php : détail d’un menu (galerie + plats par catégories + allergènes + bouton commander)

session_start();
require_once __DIR__ . '/src/db.php';

$pdo = db();

// 1) ID menu
$menuId = (int)($_GET['id'] ?? 0);
if ($menuId <= 0) {
    http_response_code(400);
    die("ID de menu invalide.");
}

// 2) Menu + thème + régime
$stmt = $pdo->prepare("
    SELECT m.*, t.libelle AS theme, r.libelle AS regime
    FROM menu m
    JOIN theme t ON t.theme_id = m.theme_id
    JOIN regime r ON r.regime_id = m.regime_id
    WHERE m.menu_id = ?
    LIMIT 1
");
$stmt->execute([$menuId]);
$menu = $stmt->fetch();

if (!$menu) {
    http_response_code(404);
    die("Menu introuvable.");
}

// 3) Plats + allergènes (avec catégorie)
$stmtPlats = $pdo->prepare("
    SELECT
        p.plat_id,
        p.titre_plat,
        p.categorie,
        a.libelle AS allergene
    FROM menu_plat mp
    JOIN plat p ON p.plat_id = mp.plat_id
    LEFT JOIN plat_allergene pa ON pa.plat_id = p.plat_id
    LEFT JOIN allergene a ON a.allergene_id = pa.allergene_id
    WHERE mp.menu_id = ?
    ORDER BY
        FIELD(p.categorie, 'entree', 'plat', 'dessert'),
        p.titre_plat,
        a.libelle
");
$stmtPlats->execute([$menuId]);
$rows = $stmtPlats->fetchAll();

// 4) Regrouper par catégories + regrouper allergènes
$group = [
        'entree' => [],
        'plat' => [],
        'dessert' => []
];

foreach ($rows as $row) {
    $cat = $row['categorie'] ?? 'plat';
    if (!isset($group[$cat])) {
        $cat = 'plat';
    }

    $pid = (int)$row['plat_id'];

    if (!isset($group[$cat][$pid])) {
        $group[$cat][$pid] = [
                'titre' => $row['titre_plat'],
                'allergenes' => []
        ];
    }

    if (!empty($row['allergene'])) {
        $group[$cat][$pid]['allergenes'][] = $row['allergene'];
    }
}

// 5) Galerie d’images
$imagesWeb = [];
$dir = __DIR__ . "/assets/menus/menu-" . $menuId;

if (is_dir($dir)) {
    $files = glob($dir . "/*.{jpg,jpeg,png,webp}", GLOB_BRACE);
    foreach ($files as $f) {
        $imagesWeb[] = "assets/menus/menu-" . $menuId . "/" . basename($f);
    }
}

// 6) Affichage des plats
function renderPlats(array $plats): void
{
    if (!$plats) {
        echo "<p>Aucun.</p>";
        return;
    }

    echo "<ul>";
    foreach ($plats as $p) {
        echo "<li><strong>" . htmlspecialchars($p['titre']) . "</strong>";

        if (!empty($p['allergenes'])) {
            echo "<br><small>Allergènes : " . htmlspecialchars(implode(', ', $p['allergenes'])) . "</small>";
        } else {
            echo "<br><small>Allergènes : aucun</small>";
        }

        echo "</li><br>";
    }
    echo "</ul>";
}

$user = $_SESSION['user'] ?? null;
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title><?php echo htmlspecialchars($menu['titre']); ?></title>
</head>
<body>

<h1><?php echo htmlspecialchars($menu['titre']); ?></h1>
<a href="menus.php">← Retour aux menus</a>
<hr>
<p><strong>Thème :</strong> <?php echo htmlspecialchars($menu['theme']); ?></p>
<p><strong>Régime :</strong> <?php echo htmlspecialchars($menu['regime']); ?></p>

<p><strong>Prix :</strong>
    <?php echo number_format((float)$menu['prix_par_personne'], 2, ',', ' '); ?> € / personne
</p>

<p><strong>Minimum :</strong>
    <?php echo (int)$menu['nombre_personne_minimum']; ?> personnes
</p>

<?php if ($menu['quantite_restante'] !== null): ?>
    <p><strong>Stock disponible :</strong> <?php echo (int)$menu['quantite_restante']; ?></p>
<?php endif; ?>

<?php if (!empty($menu['description'])): ?>
    <p><strong>Description :</strong> <?php echo htmlspecialchars($menu['description']); ?></p>
<?php endif; ?>

<?php if (!empty($menu['conditions'])): ?>
    <div style="border:2px solid red; padding:12px; margin:15px 0; background:#fff3f3;">
        <strong>⚠ Conditions importantes :</strong><br>
        <?php echo nl2br(htmlspecialchars($menu['conditions'])); ?>
    </div>
<?php endif; ?>

<hr>

<h2>Galerie d’images</h2>
<?php if (!$imagesWeb): ?>
    <p>Aucune image disponible pour ce menu.</p>
<?php else: ?>
    <?php foreach ($imagesWeb as $src): ?>
        <img
                src="<?php echo htmlspecialchars($src); ?>"
                alt="Image du menu"
                style="max-width:220px; margin:6px; border:1px solid #ccc;"
        >
    <?php endforeach; ?>
<?php endif; ?>

<hr>

<h2>Entrées</h2>
<?php renderPlats($group['entree']); ?>

<h2>Plats</h2>
<?php renderPlats($group['plat']); ?>

<h2>Desserts</h2>
<?php renderPlats($group['dessert']); ?>

<hr>

<?php if ($user): ?>
    <form method="get" action="commande.php">
        <input type="hidden" name="menu_id" value="<?php echo (int)$menu['menu_id']; ?>">
        <button type="submit">Commander ce menu</button>
    </form>
<?php else: ?>
    <p><strong>Vous devez être connecté pour commander.</strong></p>
    <p><a href="login.php">Se connecter</a> | <a href="register.php">Créer un compte</a></p>
<?php endif; ?>

</body>
</html>
