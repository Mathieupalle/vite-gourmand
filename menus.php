<?php
// menus.php : vue globale des menus + filtres dynamiques via menus-ajax.php (HTML)

session_start();
require_once __DIR__ . '/src/db.php';

$pdo = db();

// Liste pour SELECTS
$themes = $pdo->query("SELECT theme_id, libelle FROM theme ORDER BY libelle")->fetchAll();
$regimes = $pdo->query("SELECT regime_id, libelle FROM regime ORDER BY libelle")->fetchAll();

$user = $_SESSION['user'] ?? null;
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Nos menus</title>

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
                <li class="nav-item"><a class="nav-link active" href="menus.php">Tous les menus</a></li>
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

<header class="py-4 bg-white border-bottom">
    <div class="container">
        <div class="d-flex flex-column flex-md-row align-items-md-end justify-content-between gap-2">
            <div>
                <h1 class="h3 mb-1">Nos menus</h1>
            </div>
            <a class="btn btn-sm btn-outline-secondary" href="index.php">Retour Accueil</a>
        </div>
    </div>
</header>

<main class="container my-4">
    <div class="row g-4">
        <!-- Filters -->
        <aside class="col-12 col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <div class="d-flex align-items-center justify-content-between">
                        <h2 class="h6 mb-0">Filtres</h2>
                    </div>
                </div>
                <div class="card-body">
                    <form id="filtersForm">
                        <div class="mb-3">
                            <label class="form-label">Prix maximum</label>
                            <input type="number" step="0.01" name="prix_max" class="form-control" placeholder="Ex: 30">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Fourchette de prix</label>
                            <div class="row g-2">
                                <div class="col">
                                    <input type="number" step="0.01" name="prix_min" class="form-control" placeholder="Min">
                                </div>
                                <div class="col">
                                    <input type="number" step="0.01" name="prix_max_range" class="form-control" placeholder="Max">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Thème</label>
                            <select name="theme_id" class="form-select">
                                <option value="">-- Tous --</option>
                                <?php foreach ($themes as $t): ?>
                                    <option value="<?php echo (int)$t['theme_id']; ?>">
                                        <?php echo htmlspecialchars($t['libelle']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Régime</label>
                            <select name="regime_id" class="form-select">
                                <option value="">-- Tous --</option>
                                <?php foreach ($regimes as $r): ?>
                                    <option value="<?php echo (int)$r['regime_id']; ?>">
                                        <?php echo htmlspecialchars($r['libelle']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nombre minimum de personnes</label>
                            <input type="number" name="min_personnes" class="form-control" placeholder="Ex: 10">
                        </div>

                        <div class="d-flex gap-2">
                            <button type="button" id="btnFiltrer" class="btn btn-primary w-100">Filtrer</button>
                            <button type="button" id="btnReset" class="btn btn-outline-secondary w-100">Réinitialiser</button>
                        </div>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Results -->
        <section class="col-12 col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <div class="d-flex align-items-center justify-content-between">
                        <h2 class="h6 mb-0">Résultats</h2>
                        <div class="d-flex align-items-center gap-2">
                            <div id="resultsCount" class="text-secondary small"></div>
                            <div id="loadingBadge" class="badge text-bg-light border d-none">Chargement…</div>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div id="menusContainer">
                        <div class="d-flex align-items-center gap-2 text-secondary">
                            <div class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></div>
                            <span>Chargement...</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</main>

<footer class="border-top">
    <div class="container py-4">
        <div class="row g-3 align-items-center">
            <div class="col-md-6">
                <div class="fw-semibold">Vite & Gourmand</div>
                <div class="text-muted small">Traiteur en ligne</div>
            </div>
            <div class="col-md-6 text-md-end">
                <div class="small">
                    <a class="link-secondary me-3" href="<?= $baseUrl ?>/mentions-legales.php">Mentions légales</a>
                    <a class="link-secondary" href="<?= $baseUrl ?>/cgv.php">CGV</a>
                </div>
                <div class="text-muted small mt-2">© <?= date('Y'); ?> Vite & Gourmand</div>
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    const menusContainer = document.getElementById('menusContainer');
    const loadingBadge = document.getElementById('loadingBadge');
    const resultsCount = document.getElementById('resultsCount');

    function setLoading(isLoading) {
        loadingBadge.classList.toggle('d-none', !isLoading);
    }

    async function loadMenus() {
        const form = document.getElementById('filtersForm');
        const params = new URLSearchParams(new FormData(form));

        setLoading(true);
        menusContainer.innerHTML = `
            <div class="d-flex align-items-center gap-2 text-secondary">
                <div class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></div>
                <span>Chargement...</span>
            </div>
        `;

        try {
            const res = await fetch('menus-ajax.php?' + params.toString());
            const html = await res.text();

            menusContainer.innerHTML = html;

            // Petit compteur approximatif : compte les liens vers menu.php?id=
            const count = (menusContainer.innerHTML.match(/menu\.php\?id=/g) || []).length;
            resultsCount.textContent = count ? `${count} menu(s)` : '';
        } catch (e) {
            menusContainer.innerHTML = `
                <div class="alert alert-danger mb-0">
                    Erreur lors du chargement des menus. Vérifie <code>menus-ajax.php</code>.
                </div>
            `;
        } finally {
            setLoading(false);
        }
    }

    document.getElementById('btnFiltrer').addEventListener('click', loadMenus);

    document.getElementById('btnReset').addEventListener('click', () => {
        document.getElementById('filtersForm').reset();
        loadMenus();
    });

    // Chargement initial
    loadMenus();
</script>

</body>
</html>