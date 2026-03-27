<div class="container my-4">

    <div class="list-group">

        <?php if (!empty($menus)): ?>

            <?php foreach ($menus as $m):

                $id    = (int)$m['menu_id'];
                $titre = htmlspecialchars($m['titre']);

                if (!empty($m['description'])) {
                    $desc = htmlspecialchars($m['description']);
                } else {
                    $desc = "Aucune description.";
                }

                if (isset($m['prix_par_personne'])) {
                    $prixLabel = htmlspecialchars($m['prix_par_personne']) . " € / pers";
                } else {
                    $prixLabel = "Prix non défini";
                }

                // Création des informations supplémentaires
                $meta = [];

                if (!empty($m['theme'])) {
                    $meta[] = "Thème : " . htmlspecialchars($m['theme']);
                }

                if (!empty($m['regime'])) {
                    $meta[] = "Régime : " . htmlspecialchars($m['regime']);
                }

                if (!empty($m['nombre_personne_minimum'])) {
                    $meta[] = "Min : " . (int)$m['nombre_personne_minimum'] . " pers";
                }

                // Transformation du tableau en texte
                $metaStr = "";
                if (!empty($meta)) {
                    $metaStr = "<div class='small text-secondary'>" . implode(" • ", $meta) . "</div>";
                }
                ?>

                <a href="menu?id=<?= $id; ?>" class="list-group-item list-group-item-action menu-item">

                    <div class="d-flex justify-content-between align-items-start gap-3">

                        <div>

                            <div class="fw-semibold">
                                <span class="brand-dot"></span>
                                <?= $titre; ?>
                            </div>

                            <!-- Infos supplémentaires -->
                            <?= $metaStr; ?>

                            <div class="mt-1">
                                <?= $desc; ?>
                            </div>

                            <div class="mt-2 text-primary small fw-semibold">
                                Voir le détail →
                            </div>

                        </div>

                        <div class="fw-bold text-nowrap text-primary">
                            <?= $prixLabel; ?>
                        </div>

                    </div>

                </a>

            <?php endforeach; ?>

        <?php else: ?>

            <div class="alert alert-info">
                Aucun menu disponible.
            </div>

        <?php endif; ?>

    </div>

</div>