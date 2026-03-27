<?php
$menu = $menu ?? [];
$user = $user ?? null;
$prix = $prix ?? '';
$minP = $minP ?? 0;
$stock = $stock ?? null;
?>

<?php require TEMPLATES_PATH . '/partials/head.php'; ?>

<html lang="fr">
<body class="bg-light">

<?php require TEMPLATES_PATH . '/partials/header.php'; ?>

<main class="container py-4">

    <div class="d-flex align-items-start justify-content-between gap-3 mb-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/menus">Menus</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($menu['titre']); ?></li>
                </ol>
            </nav>
            <h1 class="h3 mb-0"><?= htmlspecialchars($menu['titre']); ?></h1>
        </div>
        <a class="btn btn-sm btn-outline-secondary" href="<?= BASE_URL ?>/menus">Retour aux menus</a>
    </div>

    <div class="row g-4">

        <div class="col-12 col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h2 class="h6 mb-0">Galerie</h2>
                </div>
                <div class="card-body">
                    <?php if (empty($imagesWeb)): ?>
                        <div class="ratio ratio-16x9 bg-secondary-subtle border rounded d-flex align-items-center justify-content-center">
                            <div class="text-secondary">Aucune image disponible</div>
                        </div>
                    <?php else: ?>
                        <div id="menuCarousel" class="carousel slide" data-bs-ride="carousel">
                            <div class="carousel-inner rounded overflow-hidden border">
                                <?php foreach ($imagesWeb as $i => $src): ?>
                                    <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
                                        <img src="<?= htmlspecialchars($src); ?>" class="d-block w-100" alt="Image du menu">
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
                                <div class="small text-secondary mt-2">Glisse ou utilise les flèches pour voir les images.</div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex align-items-center justify-content-between">
                    <h2 class="h6 mb-0">Informations</h2>
                    <div class="d-flex gap-2">
                        <span class="badge text-bg-light border"><?= htmlspecialchars($menu['theme']); ?></span>
                        <span class="badge text-bg-light border"><?= htmlspecialchars($menu['regime']); ?></span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap align-items-end justify-content-between gap-2">
                        <div>
                            <div class="text-secondary small">Prix / personne</div>
                            <div class="fs-3 fw-bold"><?= $prix !== '' ? $prix : '—' ?> €</div>
                        </div>
                        <div class="text-end">
                            <div class="text-secondary small">Minimum</div>
                            <div class="fw-semibold"><?= $minP > 0 ? $minP : '—' ?> pers.</div>
                        </div>
                    </div>

                    <hr>

                    <?php if ($stock !== null): ?>
                        <div class="mb-3">
                            <div class="text-secondary small">Stock disponible</div>
                            <span class="fw-semibold"><?= (int)$stock ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($menu['description'])): ?>
                        <div class="mb-3">
                            <div class="text-secondary small">Description</div>
                            <div><?= nl2br(htmlspecialchars($menu['description'])); ?></div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($menu['conditions'])): ?>
                        <div class="alert alert-warning mb-3">
                            <div class="fw-semibold mb-1">⚠ Conditions importantes</div>
                            <div><?= nl2br(htmlspecialchars($menu['conditions'])); ?></div>
                        </div>
                    <?php endif; ?>

                    <?php if ($user): ?>
                        <form method="get" action="<?= BASE_URL ?>/commande" class="d-grid">
                            <input type="hidden" name="menu_id" value="<?= (int)$menu['menu_id'] ?>">
                            <button type="submit" class="btn btn-primary btn-lg">Commander ce menu</button>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-info mb-0">
                            <div class="fw-semibold">Connexion requise</div>
                            <div class="small">Vous devez être connecté pour commander.</div>
                            <div class="mt-2 d-flex flex-wrap gap-2">
                                <a href="<?= BASE_URL ?>/login" class="btn btn-sm btn-outline-secondary">Se connecter</a>
                                <a href="<?= BASE_URL ?>/register" class="btn btn-sm btn-primary">Créer un compte</a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>

    <div class="card shadow-sm mt-4">
        <div class="card-header bg-white">
            <h2 class="h6 mb-0">Plats inclus dans le menu</h2>
        </div>
        <div class="card-body">
            <div class="accordion" id="platsAccordion">

                <?php
                $categories = [
                        'entree' => 'Entrées',
                        'plat' => 'Plats',
                        'dessert' => 'Desserts',
                ];
                ?>

                <?php foreach ($categories as $catKey => $catLabel): ?>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading<?= ucfirst($catKey) ?>">
                            <button class="accordion-button <?= $catKey !== 'entree' ? 'collapsed' : '' ?>" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#collapse<?= ucfirst($catKey) ?>"
                                    aria-expanded="<?= $catKey === 'entree' ? 'true' : 'false' ?>"
                                    aria-controls="collapse<?= ucfirst($catKey) ?>">
                                <?= $catLabel ?>
                            </button>
                        </h2>
                        <div id="collapse<?= ucfirst($catKey) ?>" class="accordion-collapse collapse <?= $catKey === 'entree' ? 'show' : '' ?>"
                             aria-labelledby="heading<?= ucfirst($catKey) ?>" data-bs-parent="#platsAccordion">
                            <div class="accordion-body">
                                <?php if (!empty($menu['plats_grouped'][$catKey])): ?>
                                    <?php foreach ($menu['plats_grouped'][$catKey] as $p): ?>
                                        <div class="mb-2">
                                            <div class="fw-semibold"><?= htmlspecialchars($p['titre']); ?></div>
                                            <?php if (!empty($p['allergenes'])): ?>
                                                <div class="small text-secondary">
                                                    Allergènes : <?= implode(', ', array_map('htmlspecialchars', $p['allergenes'])) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-muted small">Aucun <?= strtolower($catLabel) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

            </div>
        </div>
    </div>

</main>

<?php
require TEMPLATES_PATH . '/partials/footer.php';
require TEMPLATES_PATH . '/partials/scripts.php';
?>

</body>
</html>