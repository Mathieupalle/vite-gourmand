<?php
// commande-annuler.php : annuler ou refuser une commande
// Ajoute une ligne dans commande_suivi

session_start();
require_once __DIR__ . '/src/db.php';
require_once __DIR__ . '/src/auth.php';

requireRole(['employee', 'admin']);

$user = $_SESSION['user'] ?? null;
$role = $user['role'] ?? 'employee';

$commandeId = (int)($_POST['commande_id'] ?? 0);
$action = trim($_POST['action'] ?? 'annulee'); // annulee

$mode = trim($_POST['mode_contact'] ?? '');
$motif = trim($_POST['motif'] ?? '');

if ($commandeId <= 0) {
    http_response_code(400);
    die("Données invalides.");
}

if (!in_array($action, ['annulee'], true)) {
    http_response_code(400);
    die("Action invalide.");
}

// Obligation de contact pour l'employé
if ($role === 'employee') {
    if (!in_array($mode, ['telephone', 'mail'], true)) {
        http_response_code(400);
        die("Préciser le mode de contact.");
    }
    if ($motif === '') {
        http_response_code(400);
        die("Motif obligatoire.");
    }
} else {
    if (!in_array($mode, ['telephone', 'mail'], true)) $mode = null;
    if ($motif === '') $motif = null;
}

$pdo = db();

try {
    $pdo->beginTransaction();

    // Vérifier commande
    $stmt = $pdo->prepare("SELECT commande_id, statut FROM commande WHERE commande_id = ? FOR UPDATE");
    $stmt->execute([$commandeId]);
    $c = $stmt->fetch();

    if (!$c) {
        throw new Exception("Commande introuvable.");
    }

    $current = (string)$c['statut'];

    // Si déjà terminée : pas d'annulation
    if ($current === 'terminee') {
        throw new Exception("Commande terminée, impossible d'annuler.");
    }

    // Mettre à jour statut
    $upd = $pdo->prepare("UPDATE commande SET statut = ? WHERE commande_id = ?");
    $upd->execute([$action, $commandeId]);

    // Historique
    $ins = $pdo->prepare("
        INSERT INTO commande_suivi (commande_id, statut, date_modif, mode_contact, motif)
        VALUES (?, ?, NOW(), ?, ?)
    ");
    $ins->execute([$commandeId, $action, $mode, $motif]);

    $pdo->commit();

    header("Location: commande-manage.php");
    exit;

} catch (Throwable $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo "Erreur : " . htmlspecialchars($e->getMessage());
}
