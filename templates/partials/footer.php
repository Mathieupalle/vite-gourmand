<?php
// Horaires affichés sur toutes les pages
$horairesFooter = [];
try {
    $horaireRepo = new App\Repository\HoraireRepository(App\Infrastructure\Database::getConnection());
    $horairesFooter = $horaireRepo->listAll();
} catch (Throwable $e) {
    error_log('Horaires du pied de page : ' . $e->getMessage());
}
?>
<footer class="border-top">

    <div class="container py-4">

        <div class="row g-3">

            <div class="col-md-4">
                <div class="fw-semibold">Vite & Gourmand</div>
                <div class="text-muted small">Traiteur en ligne</div>
            </div>

            <div class="col-md-4">
                <div class="fw-semibold small mb-1">Horaires</div>
                <ul class="list-unstyled small text-muted mb-0">
                    <?php foreach ($horairesFooter as $h): ?>
                        <li>
                            <?= htmlspecialchars($h['jour']) ?> :
                            <?php if (!empty($h['heure_ouverture']) && !empty($h['heure_fermeture'])): ?>
                                <?= htmlspecialchars(substr($h['heure_ouverture'], 0, 5)) ?> - <?= htmlspecialchars(substr($h['heure_fermeture'], 0, 5)) ?>
                            <?php else: ?>
                                Fermé
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="col-md-4 text-md-end">
                <div class="small">
                    <a class="link-secondary me-3" href="<?= BASE_URL ?>/mentionsLegales">Mentions légales</a>
                    <a class="link-secondary" href="<?= BASE_URL ?>/cgv">CGV</a>
                </div>
                <div class="text-muted small mt-2">© <?= date('Y'); ?> Vite & Gourmand</div>
            </div>

        </div>

    </div>

</footer>
