<?php
// menus-ajax.php : retourne la liste des menus filtrés (HTML) sans recharger la page

require_once __DIR__ . '/src/db.php';

$pdo = db();

// 1) Récupération des filtres (GET)
$prixMax = ($_GET['prix_max'] ?? '') !== '' ? (float)$_GET['prix_max'] : null;
$prixMin = ($_GET['prix_min'] ?? '') !== '' ? (float)$_GET['prix_min'] : null;
$prixMaxRange = ($_GET['prix_max_range'] ?? '') !== '' ? (float)$_GET['prix_max_range'] : null;

$themeId = ($_GET['theme_id'] ?? '') !== '' ? (int)$_GET['theme_id'] : null;
$regimeId = ($_GET['regime_id'] ?? '') !== '' ? (int)$_GET['regime_id'] : null;

$minPersonnes = ($_GET['min_personnes'] ?? '') !== '' ? (int)$_GET['min_personnes'] : null;

// 2) Construction SQL dynamique
$sql = "
  SELECT m.menu_id, m.titre, m.description, m.prix_par_personne, m.nombre_personne_minimum,
         t.libelle AS theme, r.libelle AS regime
  FROM menu m
  JOIN theme t ON t.theme_id = m.theme_id
  JOIN regime r ON r.regime_id = m.regime_id
";

$where = [];
$params = [];

// Prix maximum
if ($prixMax !== null && $prixMax > 0) {
    $where[] = "m.prix_par_personne <= ?";
    $params[] = $prixMax;
}

// Fourchette de prix
if ($prixMin !== null && $prixMin >= 0) {
    $where[] = "m.prix_par_personne >= ?";
    $params[] = $prixMin;
}
if ($prixMaxRange !== null && $prixMaxRange > 0) {
    $where[] = "m.prix_par_personne <= ?";
    $params[] = $prixMaxRange;
}

// Thème
if ($themeId !== null && $themeId > 0) {
    $where[] = "m.theme_id = ?";
    $params[] = $themeId;
}

// Régime
if ($regimeId !== null && $regimeId > 0) {
    $where[] = "m.regime_id = ?";
    $params[] = $regimeId;
}

// Nombre minimum de personnes
if ($minPersonnes !== null && $minPersonnes > 0) {
    $where[] = "m.nombre_personne_minimum <= ?";
    $params[] = $minPersonnes;
}

if ($where) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY m.menu_id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$menus = $stmt->fetchAll();

// 3) Sortie HTML
if (!$menus) {
    echo "<p><em>Aucun menu ne correspond aux filtres.</em></p>";
    exit;
}

echo "<ul>";
foreach ($menus as $m) {
    $desc = trim((string)($m['description'] ?? ''));
    $descShort = $desc !== '' ? htmlspecialchars($desc) : "Aucune description.";

    echo "<li>";
    echo "<strong>" . htmlspecialchars($m['titre']) . "</strong>";
    echo " — " . htmlspecialchars((string)$m['prix_par_personne']) . " € / pers";
    echo "<br>";
    echo "<small>Thème : " . htmlspecialchars($m['theme']) . " | Régime : " . htmlspecialchars($m['regime']) . "</small>";
    echo "<br>";
    echo "<small>Min : " . (int)$m['nombre_personne_minimum'] . " personnes</small>";
    echo "<br>";
    echo "<span>" . $descShort . "</span>";
    echo "<br>";
    echo "<a href=\"menu.php?id=" . (int)$m['menu_id'] . "\">Voir le détail</a>";
    echo "</li><hr>";
}
echo "</ul>";
