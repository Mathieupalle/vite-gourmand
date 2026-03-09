<div class="list-group">
    <?php foreach ($menus as $m): ?>
        <?php
        $id = (int)$m['menu_id'];
        $titre = htmlspecialchars($m['titre']);
        $desc = trim((string)($m['description'] ?? '')) ?: 'Aucune description.';
        $prixLabel = isset($m['prix_par_personne']) ? htmlspecialchars($m['prix_par_personne']) . " € / pers" : "Prix non défini";

        $meta = [];
        if (!empty($m['theme']))  $meta[] = "Thème : " . htmlspecialchars($m['theme']);
        if (!empty($m['regime'])) $meta[] = "Régime : " . htmlspecialchars($m['regime']);
        if (!empty($m['nombre_personne_minimum'])) $meta[] = "Min : " . (int)$m['nombre_personne_minimum'] . " pers";
        $metaStr = $meta ? "<div class='small text-secondary'>" . implode(" • ", $meta) . "</div>" : "";
        ?>
        <a class="list-group-item list-group-item-action" href="menu?id=<?= $id ?>">
            <div class="d-flex justify-content-between align-items-start gap-3">
                <div>
                    <div class="fw-semibold"><?= $titre ?></div>
                    <?= $metaStr ?>
                    <div class="mt-1"><?= $desc ?></div>
                    <div class="mt-2 text-primary small fw-semibold">Voir le détail →</div>
                </div>
                <div class="fw-bold text-nowrap"><?= $prixLabel ?></div>
            </div>
        </a>
    <?php endforeach; ?>
</div>