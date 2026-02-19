<?php
// index.php : page d'accueil

session_start();
require_once __DIR__ . '/src/db.php';

$user = $_SESSION['user'] ?? null;
$pdo = db();

// Base URL automatique (local: /vite-gourmand, heroku: "")
$baseUrl = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
if ($baseUrl === '/.') $baseUrl = '';

// Horaires
$horaires = $pdo->query("
    SELECT jour, heure_ouverture, heure_fermeture
    FROM horaire
    ORDER BY horaire_id ASC
")->fetchAll();

// Avis validés (affichés sur l'accueil)
$avisValides = $pdo->query("
    SELECT note, description
    FROM avis
    WHERE statut = 'valide'
    ORDER BY avis_id DESC
    LIMIT 5
")->fetchAll();

// Rôle
$role = $user['role'] ?? '';
$isStaff = ($role === 'employee' || $role === 'admin');
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Vite & Gourmand - Traiteur en ligne</title>
    <meta name="description" content="Traiteur événementiel à Bordeaux : menus, plats, livraison, prestations. Commande en ligne simple et sécurisée.">

    <!-- Bootstrap 5 (CDN) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Assets -->
    <link rel="stylesheet" href="<?= $baseUrl ?>/assets/css/style.css">
</head>
<body class="bg-white">

<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom sticky-top">
    <div class="container">
        <a class="navbar-brand fw-semibold" href="<?= $baseUrl ?>/index.php">
            <span class="me-2" style="display:inline-block;width:10px;height:10px;border-radius:999px;background:#8b5e34;"></span>
            Vite & Gourmand
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"
                aria-controls="mainNav" aria-expanded="false" aria-label="Ouvrir le menu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                <li class="nav-item"><a class="nav-link" href="<?= $baseUrl ?>/menus.php">Tous les menus</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= $baseUrl ?>/contact.php">Contact</a></li>

                <?php if ($user): ?>
                    <li class="nav-item"><a class="nav-link" href="<?= $baseUrl ?>/mes-commandes.php">Mes commandes</a></li>

                    <?php if ($isStaff): ?>
                        <li class="nav-item">
                            <a class="btn btn-outline-secondary btn-sm px-3" href="<?= $baseUrl ?>/admin.php">Espace gestion</a>
                        </li>
                    <?php endif; ?>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Mon compte
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?= $baseUrl ?>/profil.php">Mon profil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?= $baseUrl ?>/logout.php">Déconnexion</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item ms-lg-2">
                        <a class="btn btn-outline-secondary btn-sm px-3" href="<?= $baseUrl ?>/login.php">Connexion</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-primary btn-sm px-3" style="background:#8b5e34;border-color:#8b5e34;"
                           href="<?= $baseUrl ?>/register.php">Créer un compte</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<header class="py-5" style="background:linear-gradient(180deg,#f7efe6,#fff);border-bottom:1px solid rgba(0,0,0,.06);">
    <div class="container py-2">
        <div class="row align-items-center g-4">
            <div class="col-lg-7">
        <span class="badge rounded-pill mb-3" style="background:#f7efe6;color:#553820;border:1px solid rgba(0,0,0,.06);">
          Traiteur événementiel • Bordeaux • 25 ans d’expérience
        </span>

                <h1 class="display-6 fw-semibold">Menus & prestations pour vos événements</h1>
                <p class="text-muted mt-3 mb-4">
                    Mariages, séminaires, anniversaires ou événements privés : consultez nos menus et passez commande en ligne
                    de manière simple et sécurisée.
                </p>

                <div class="d-flex gap-2 flex-wrap">
                    <a class="btn btn-primary px-4" style="background:#8b5e34;border-color:#8b5e34;"
                       href="<?= $baseUrl ?>/menus.php">Voir les menus</a>
                    <a class="btn btn-outline-secondary px-4" href="<?= $baseUrl ?>/contact.php">Demander un devis</a>
                </div>

                <?php if ($user): ?>
                    <div class="small text-muted mt-3">
                        Connecté<?= isset($user['prenom']) ? ' en tant que ' . htmlspecialchars($user['prenom']) : '' ?>.
                    </div>
                <?php else: ?>
                    <div class="small text-muted mt-3">Astuce : crée un compte pour suivre tes commandes et laisser un avis.</div>
                <?php endif; ?>
            </div>

            <div class="col-lg-5">
                <div class="p-4 bg-white shadow-sm" style="border:1px solid rgba(0,0,0,.08);border-radius:16px;">
                    <div class="fw-semibold mb-2">Ce que vous obtenez</div>
                    <ul class="text-muted small mb-0">
                        <li class="mb-2"><strong>Qualité :</strong> produits sélectionnés, préparation soignée.</li>
                        <li class="mb-2"><strong>Organisation :</strong> régimes & allergènes pris en compte.</li>
                        <li class="mb-2"><strong>Logistique :</strong> livraison et matériel selon prestation.</li>
                        <li><strong>Suivi :</strong> commande en ligne + statut et historique.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</header>

<main class="container my-5">

    <section class="mb-5">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="p-4 h-100" style="border:1px solid rgba(0,0,0,.08);border-radius:16px;">
                    <div class="fw-semibold">Menus</div>
                    <div class="text-muted small mt-2">Découvrez nos menus pour tous types d’événements.</div>
                    <a class="btn btn-sm btn-link px-0 mt-2" href="<?= $baseUrl ?>/menus.php">Explorer →</a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-4 h-100" style="border:1px solid rgba(0,0,0,.08);border-radius:16px;">
                    <div class="fw-semibold">Commande</div>
                    <div class="text-muted small mt-2">Passez commande en ligne et suivez vos demandes.</div>
                    <?php if ($user): ?>
                        <a class="btn btn-sm btn-link px-0 mt-2" href="<?= $baseUrl ?>/mes-commandes.php">Voir mes commandes →</a>
                    <?php else: ?>
                        <a class="btn btn-sm btn-link px-0 mt-2" href="<?= $baseUrl ?>/login.php">Se connecter →</a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-4 h-100" style="border:1px solid rgba(0,0,0,.08);border-radius:16px;">
                    <div class="fw-semibold">Prestation & devis</div>
                    <div class="text-muted small mt-2">Un besoin spécifique ? Contactez-nous pour un devis.</div>
                    <a class="btn btn-sm btn-link px-0 mt-2" href="<?= $baseUrl ?>/contact.php">Contacter →</a>
                </div>
            </div>
        </div>
    </section>

    <section class="mb-5">
        <div class="row g-4">
            <div class="col-lg-7">
                <h2 class="h4 fw-semibold">Avis clients</h2>
                <p class="text-muted mb-3">Derniers avis validés.</p>

                <?php if (!$avisValides): ?>
                    <div class="alert alert-secondary mb-0">Aucun avis validé pour le moment.</div>
                <?php else: ?>
                    <ul class="list-group">
                        <?php foreach ($avisValides as $a): ?>
                            <li class="list-group-item d-flex gap-3 align-items-start">
                <span class="badge rounded-pill mt-1" style="background:#f7efe6;color:#553820;border:1px solid rgba(0,0,0,.06);">
                  <?= (int)$a['note']; ?>/5
                </span>
                                <div>
                                    <div class="text-muted small">Avis</div>
                                    <div><?= htmlspecialchars($a['description']); ?></div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>

            <div class="col-lg-5">
                <h2 class="h4 fw-semibold">Horaires</h2>
                <p class="text-muted mb-3">Ouverture & fermeture.</p>

                <div class="bg-white p-3" style="border:1px solid rgba(0,0,0,.08);border-radius:16px;">
                    <ul class="list-unstyled mb-0">
                        <?php foreach ($horaires as $h): ?>
                            <li class="d-flex justify-content-between py-2 border-bottom">
                                <span class="fw-semibold"><?= htmlspecialchars($h['jour']); ?></span>
                                <span class="text-muted">
                  <?php if ($h['heure_ouverture'] && $h['heure_fermeture']): ?>
                      <?= htmlspecialchars(substr($h['heure_ouverture'], 0, 5)); ?> - <?= htmlspecialchars(substr($h['heure_fermeture'], 0, 5)); ?>
                  <?php else: ?>
                      Fermé
                  <?php endif; ?>
                </span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="small text-muted mt-3">
                    Besoin d’un créneau particulier ? <a href="<?= $baseUrl ?>/contact.php">Contactez-nous</a>.
                </div>
            </div>
        </div>
    </section>

</main>

<footer class="border-top">
    <div class="container py-4">
        <div class="row g-3 align-items-center">
            <div class="col-md-6">
                <div class="fw-semibold">Vite & Gourmand</div>
                <div class="text-muted small">Traiteur en ligne • Menus • Livraison • Prestations</div>
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

<!-- JS -->
<script src="<?= $baseUrl ?>/assets/js/app.js"></script>
</body>
</html>
