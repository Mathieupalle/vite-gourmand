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

// 3) Plats + allergènes (avec catégories)
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
$dir = __DIR__ . "/assets/img/menus/menu-" . $menuId;

if (is_dir($dir)) {
    $files = glob($dir . "/*.{jpg,jpeg,png,webp}", GLOB_BRACE);
    foreach ($files as $f) {
        $imagesWeb[] = "assets/img/menus/menu-" . $menuId . "/" . basename($f);
    }
}

// 6) Affichage des plats (Bootstrap)
function renderPlatsBootstrap(array $plats): void
{
    if (!$plats) {
        echo '<div class="text-secondary">Aucun plat.</div>';
        return;
    }

    echo '<div class="list-group">';
    foreach ($plats as $p) {
        $titre = htmlspecialchars($p['titre']);

        echo '<div class="list-group-item">';
        echo '<div class="d-flex justify-content-between align-items-start gap-3">';
        echo '<div>';
        echo '<div class="fw-semibold">' . $titre . '</div>';

        if (!empty($p['allergenes'])) {
            echo '<div class="mt-2 d-flex flex-wrap gap-1">';
            foreach ($p['allergenes'] as $a) {
                echo '<span class="badge text-bg-light border">' . htmlspecialchars($a) . '</span>';
            }
            echo '</div>';
        } else {
            echo '<div class="mt-2"><span class="badge text-bg-success">Aucun allergène</span></div>';
        }

        echo '</div>';
        echo '</div>';
        echo '</div>';
    }
    echo '</div>';
}

$user = $_SESSION['user'] ?? null;

// Helpers affichage
$prix = number_format((float)$menu['prix_par_personne'], 2, ',', ' ');
$minP = (int)$menu['nombre_personne_minimum'];
$stock = $menu['quantite_restante'];
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($menu['titre']); ?></title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg bg-white border-bottom">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">Vite &amp; Gourmand</a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain" aria-controls="navMain" aria-expanded="false" aria-label="Menu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navMain">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                <li class="nav-item"><a class="nav-link" href="menus.php">Tous les menus</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php#contact">Contact</a></li>

                <?php if ($user): ?>
                    <li class="nav-item"><a class="btn btn-sm btn-outline-secondary" href="profil.php">Mon compte</a></li>
                    <li class="nav-item"><a class="btn btn-sm btn-outline-danger" href="logout.php">Déconnexion</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="btn btn-sm btn-outline-secondary" href="login.php">Connexion</a></li>
                    <li class="nav-item"><a class="btn btn-sm btn-primary" href="register.php">Créer un compte</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<main class="container my-4">
    <!-- Breadcrumb + title -->
    <div class="d-flex flex-column flex-md-row align-items-md-end justify-content-between gap-2 mb-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2">
                    <li class="breadcrumb-item"><a href="index.php">Accueil</a></li>
                    <li class="breadcrumb-item"><a href="menus.php">Menus</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($menu['titre']); ?></li>
                </ol>
            </nav>
            <h1 class="h3 mb-0"><?php echo htmlspecialchars($menu['titre']); ?></h1>
        </div>
        <a class="btn btn-sm btn-outline-secondary" href="menus.php">← Retour aux menus</a>
    </div>

    <div class="row g-4">
        <!-- Galerie -->
        <div class="col-12 col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h2 class="h6 mb-0">Galerie</h2>
                </div>
                <div class="card-body">
                    <?php if (!$imagesWeb): ?>
                        <div class="ratio ratio-16x9 bg-secondary-subtle border rounded d-flex align-items-center justify-content-center">
                            <div class="text-secondary">Aucune image disponible</div>
                        </div>
                    <?php else: ?>
                        <div id="menuCarousel" class="carousel slide" data-bs-ride="carousel">
                            <div class="carousel-inner rounded overflow-hidden border">
                                <?php foreach ($imagesWeb as $i => $src): ?>
                                    <div class="carousel-item <?php echo $i === 0 ? 'active' : ''; ?>">
                                        <img src="<?php echo htmlspecialchars($src); ?>" class="d-block w-100" alt="Image du menu">
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <?php if (count($imagesWeb) > 1): ?>
                                <button class="carousel-control-prev" type="button" data-bs-target="#menuCarousel" data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Précédent</span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#menuCarousel" data-bs-slide="next">
                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Suivant</span>
                                </button>
                            <?php endif; ?>
                        </div>

                        <?php if (count($imagesWeb) > 1): ?>
                            <div class="small text-secondary mt-2">Glisse ou utilise les flèches pour voir les images.</div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Infos -->
        <div class="col-12 col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <div class="d-flex align-items-center justify-content-between">
                        <h2 class="h6 mb-0">Informations</h2>
                        <div class="d-flex gap-2">
                            <span class="badge text-bg-light border"><?php echo htmlspecialchars($menu['theme']); ?></span>
                            <span class="badge text-bg-light border"><?php echo htmlspecialchars($menu['regime']); ?></span>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="d-flex flex-wrap align-items-end justify-content-between gap-2">
                        <div>
                            <div class="text-secondary small">Prix / personne</div>
                            <div class="fs-3 fw-bold"><?php echo $prix; ?> €</div>
                        </div>
                        <div class="text-end">
                            <div class="text-secondary small">Minimum</div>
                            <div class="fw-semibold"><?php echo $minP; ?> pers.</div>
                        </div>
                    </div>

                    <hr>

                    <?php if ($stock !== null): ?>
                        <div class="mb-2">
                            <span class="text-secondary small">Stock disponible</span><br>
                            <span class="fw-semibold"><?php echo (int)$stock; ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($menu['description'])): ?>
                        <div class="mb-3">
                            <div class="text-secondary small">Description</div>
                            <div><?php echo nl2br(htmlspecialchars($menu['description'])); ?></div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($menu['conditions'])): ?>
                        <div class="alert alert-warning mb-3">
                            <div class="fw-semibold mb-1">⚠ Conditions importantes</div>
                            <div><?php echo nl2br(htmlspecialchars($menu['conditions'])); ?></div>
                        </div>
                    <?php endif; ?>

                    <?php if ($user): ?>
                        <form method="get" action="commande.php" class="d-grid">
                            <input type="hidden" name="menu_id" value="<?php echo (int)$menu['menu_id']; ?>">
                            <button type="submit" class="btn btn-primary btn-lg">Commander ce menu</button>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-info mb-0">
                            <div class="fw-semibold">Connexion requise</div>
                            <div class="small">Vous devez être connecté pour commander.</div>
                            <div class="mt-2 d-flex flex-wrap gap-2">
                                <a href="login.php" class="btn btn-sm btn-outline-secondary">Se connecter</a>
                                <a href="register.php" class="btn btn-sm btn-primary">Créer un compte</a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Plats -->
    <div class="card shadow-sm mt-4">
        <div class="card-header bg-white">
            <h2 class="h6 mb-0">Plats inclus</h2>
        </div>
        <div class="card-body">
            <div class="accordion" id="platsAccordion">
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingEntrees">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseEntrees" aria-expanded="true" aria-controls="collapseEntrees">
                            Entrées
                        </button>
                    </h2>
                    <div id="collapseEntrees" class="accordion-collapse collapse show" aria-labelledby="headingEntrees" data-bs-parent="#platsAccordion">
                        <div class="accordion-body">
                            <?php renderPlatsBootstrap($group['entree']); ?>
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingPlats">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePlats" aria-expanded="false" aria-controls="collapsePlats">
                            Plats
                        </button>
                    </h2>
                    <div id="collapsePlats" class="accordion-collapse collapse" aria-labelledby="headingPlats" data-bs-parent="#platsAccordion">
                        <div class="accordion-body">
                            <?php renderPlatsBootstrap($group['plat']); ?>
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingDesserts">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDesserts" aria-expanded="false" aria-controls="collapseDesserts">
                            Desserts
                        </button>
                    </h2>
                    <div id="collapseDesserts" class="accordion-collapse collapse" aria-labelledby="headingDesserts" data-bs-parent="#platsAccordion">
                        <div class="accordion-body">
                            <?php renderPlatsBootstrap($group['dessert']); ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="small text-secondary mt-3">
                Les allergènes sont affichés sous forme de badges (ou “Aucun allergène” si vide).
            </div>
        </div>
    </div>
</main>

<footer class="py-4 border-top bg-white mt-4">
    <div class="container small text-secondary d-flex flex-column flex-md-row justify-content-between gap-2">
        <div>© <?php echo date('Y'); ?> Vite &amp; Gourmand</div>
        <div>Mentions légales • CGV • Contact</div>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>