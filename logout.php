<?php
// logout.php : déconnexion.
// Ferme la session et redirige vers l’accueil.

session_start();
session_unset();
session_destroy();

header("Location: index.php");
exit;
