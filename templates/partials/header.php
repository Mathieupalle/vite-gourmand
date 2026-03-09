<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$user = $_SESSION['user'] ?? null;
$role = (string)($user['role'] ?? '');
$isStaff = ($role === 'admin' || $role === 'employee');
?>
<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom sticky-top">
    <div class="container">
        <a class="navbar-brand fw-semibold" href="<?= BASE_URL ?>/home.php">
            <span class="me-2 brand-dot"></span>
            Vite & Gourmand
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"
                aria-controls="mainNav" aria-expanded="false" aria-label="Ouvrir le menu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/menus.php">Tous les menus</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/contact.php">Contact</a></li>

                <?php if ($user): ?>
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/mesCommandes.php">Mes commandes</a></li>

                    <?php if ($isStaff): ?>
                        <li class="nav-item">
                            <a class="btn btn-outline-secondary btn-sm px-3" href="<?= BASE_URL ?>/admin.php">Espace gestion</a>
                        </li>
                    <?php endif; ?>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Mon compte
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>/profile.php">Mon profil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>/logout.php">Déconnexion</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item ms-lg-2">
                        <a class="btn btn-outline-secondary btn-sm px-3" href="<?= BASE_URL ?>/login.php">Connexion</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-primary btn-sm px-3"
                           href="<?= BASE_URL ?>/register.php">Créer un compte</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>