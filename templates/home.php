<!doctype html>
<html lang="fr">
<?php require TEMPLATES_PATH . '/partials/head.php'; ?>

<body class="bg-white">
<?php require TEMPLATES_PATH . '/partials/header.php'; ?>

<header class="py-5 hero">
    <div class="container py-2">
        <div class="row align-items-center g-4">
            <div class="col-lg-7">
        <span class="badge rounded-pill mb-3"
              style="background:#f7efe6; color:#553820; border:1px solid rgba(0,0,0,.06);">
  Traiteur en ligne • Bordeaux • 25 ans d’expérience
</span>

                <h1 class="display-6 fw-semibold">Menus & prestations pour vos événements</h1>
                <p class="text-muted mt-3 mb-4">
                    Mariages, séminaires, anniversaires ou événements privés : consultez nos menus et passez commande en ligne
                    de manière simple et sécurisée.
                </p>

                <div class="d-flex gap-2 flex-wrap">
                    <a class="btn btn-primary px-4"
                       href="<?= BASE_URL ?>/menus">Voir les menus</a>
                    <a class="btn btn-outline-secondary px-4" href="<?= BASE_URL ?>/contact">Demander un devis</a>
                </div>

                <?php if ($user): ?>
                    <div class="small text-muted mt-3">
                        Connecté<?= isset($user['prenom']) ? ' en tant que ' . htmlspecialchars($user['prenom']) : '' ?>.
                    </div>
                <?php else: ?>
                    <div class="small text-muted mt-3">Créez un compte pour suivre vos commandes et laisser un avis.</div>
                <?php endif; ?>
            </div>

            <div class="col-lg-5">
                <div class="p-4 bg-white shadow-sm" style="border:1px solid rgba(0,0,0,.08);border-radius:16px;">
                    <div class="fw-semibold mb-2">Vite & Gourmand c'est :</div>
                    <ul class="text-muted small mb-0">
                        <li class="mb-2"><strong>Qualité :</strong> produits sélectionnés, préparation soignée.</li>
                        <li class="mb-2"><strong>Organisation :</strong> régimes & allergènes pris en compte.</li>
                        <li class="mb-2"><strong>Logistique :</strong> livraison rapide et matériel selon prestation.</li>
                        <li><strong>Suivi :</strong> statut et historique de la commande.</li>
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
                    <div class="fw-semibold">Nos menus</div>
                    <div class="text-muted small mt-2">Découvrez nos menus pour tous types d’événements.</div>
                    <a class="btn btn-sm btn-link px-0 mt-2" href="<?= BASE_URL ?>/menus">Explorer →</a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-4 h-100" style="border:1px solid rgba(0,0,0,.08);border-radius:16px;">
                    <div class="fw-semibold">Commander</div>
                    <div class="text-muted small mt-2">Passez commande en ligne et suivez vos demandes.</div>
                    <?php if ($user): ?>
                        <a class="btn btn-sm btn-link px-0 mt-2" href="<?= BASE_URL ?>/mesCommandes">Voir mes commandes →</a>
                    <?php else: ?>
                        <a class="btn btn-sm btn-link px-0 mt-2" href="<?= BASE_URL ?>/login">Se connecter →</a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-4 h-100" style="border:1px solid rgba(0,0,0,.08);border-radius:16px;">
                    <div class="fw-semibold">Prestation & devis</div>
                    <div class="text-muted small mt-2">Un besoin spécifique ? Contactez-nous pour un devis.</div>
                    <a class="btn btn-sm btn-link px-0 mt-2" href="<?= BASE_URL ?>/contact">Contacter →</a>
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
                <span class="badge rounded-pill mt-1 badge-brand">
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
                    Besoin d’un créneau particulier ? <a href="<?= BASE_URL ?>/contact">Contactez-nous</a>.
                </div>
            </div>
        </div>
    </section>
</main>
<?php require TEMPLATES_PATH . '/partials/footer.php'; ?>

<?php require TEMPLATES_PATH . '/partials/scripts.php'; ?>
</body>
</html>
