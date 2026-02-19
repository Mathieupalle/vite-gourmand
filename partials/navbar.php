<?php
// $baseUrl vient de head.php (inclu avant navbar.php)
?>
<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom sticky-top">
    <div class="container">
        <a class="navbar-brand fw-semibold" href="<?= $baseUrl ?>/index.php">
            <span class="brand-dot me-2"></span>Vite & Gourmand
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"
                aria-controls="mainNav" aria-expanded="false" aria-label="Ouvrir le menu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                <!-- Public -->
                <li class="nav-item"><a class="nav-link" href="<?= $baseUrl ?>/menu.php">Menu</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= $baseUrl ?>/commande-create.php">Commander</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= $baseUrl ?>/contact.php">Contact</a></li>

                <!-- Compte -->
                <li class="nav-item ms-lg-2">
                    <a class="btn btn-outline-secondary btn-sm px-3" href="<?= $baseUrl ?>/login.php">Connexion</a>
                </li>
                <li class="nav-item">
                    <a class="btn btn-primary btn-sm px-3" href="<?= $baseUrl ?>/admin.php">Admin</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
