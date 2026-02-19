<?php
// index.php : page d'accueil

session_start();
require_once __DIR__ . '/src/db.php';

$user = $_SESSION['user'] ?? null;
$pdo = db();

// Horaires
$horaires = $pdo->query("SELECT jour, heure_ouverture, heure_fermeture FROM horaire ORDER BY horaire_id ASC")->fetchAll();

// Avis validés (affichés sur l'accueil)
$avisValides = $pdo->query("
    SELECT note, description
    FROM avis
    WHERE statut = 'valide'
    ORDER BY avis_id DESC
    LIMIT 5
")->fetchAll();
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Vite & Gourmand - Traiteur en ligne</title>
</head>
<body>

<header>
    <h1>Vite & Gourmand</h1>
    <p>Traiteur événementiel à Bordeaux — 25 ans d’expérience.</p>

    <hr>

    <!-- Menu de navigation -->
    <nav>
        <a href="index.php">Accueil</a> |
        <a href="menus.php">Tous les menus</a> |
        <a href="contact.php">Contact</a>

        <?php if ($user): ?>
            | <a href="mes-commandes.php">Mes commandes</a>
            <?php if (($user['role'] ?? '') === 'employee' || ($user['role'] ?? '') === 'admin'): ?>
                | <a href="admin.php">Espace gestion</a>
            <?php endif; ?>

            | <a href="profil.php">Mon profil</a>
            | <a href="logout.php">Déconnexion</a>
        <?php else: ?>
            | <a href="login.php">Connexion</a>
            | <a href="register.php">Créer un compte</a>
        <?php endif; ?>
    </nav>

    <hr>
</header>

<main>

    <!-- Présentation entreprise -->
    <section>
        <h2>Présentation</h2>
        <p>
            Vite & Gourmand est une entreprise de restauration événementielle basée à Bordeaux.
            Nous proposons des menus adaptés aux mariages, séminaires, anniversaires et événements privés.
        </p>
        <p>
            Cette application permet de consulter nos menus et de passer commande en ligne de manière simple et sécurisée.
        </p>
    </section>

    <hr>

    <!-- Mise en avant professionnalisme -->
    <section>
        <h2>Notre équipe</h2>
        <ul>
            <li><strong>Cuisine :</strong> préparation sur place avec produits sélectionnés.</li>
            <li><strong>Organisation :</strong> prise en compte des régimes et allergènes.</li>
            <li><strong>Logistique :</strong> livraison et prêt de matériel selon la prestation.</li>
        </ul>
        <p>
            Nous mettons l’accent sur la qualité, la ponctualité et le suivi des commandes.
        </p>
    </section>

    <hr>

    <!-- Avis clients validés -->
    <section>
        <h2>Avis clients</h2>

        <?php if (!$avisValides): ?>
            <p>Aucun avis validé pour le moment.</p>
        <?php else: ?>
            <ul>
                <?php foreach ($avisValides as $a): ?>
                    <li>
                        <strong><?php echo (int)$a['note']; ?>/5</strong> —
                        <?php echo htmlspecialchars($a['description']); ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>

</main>

<hr>

<footer>
    <h3>Horaires</h3>
    <ul>
        <?php foreach ($horaires as $h): ?>
            <li>
                <?php echo htmlspecialchars($h['jour']); ?> :
                <?php if ($h['heure_ouverture'] && $h['heure_fermeture']): ?>
                    <?php echo htmlspecialchars(substr($h['heure_ouverture'],0,5)); ?> - <?php echo htmlspecialchars(substr($h['heure_fermeture'],0,5)); ?>
                <?php else: ?>
                    Fermé
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>

    <p>
        <a href="mentions-legales.php">Mentions légales</a> |
        <a href="cgv.php">Conditions générales de vente</a>
    </p>

    <p>© <?php echo date('Y'); ?> Vite & Gourmand</p>
</footer>

</body>
</html>
