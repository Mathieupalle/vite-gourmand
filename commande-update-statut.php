<?php
// commande-update-statut.php
// Met à jour le statut d’une commande (employee / admin).
// IMPORTANT : si un EMPLOYÉ met le statut "annulee", il doit obligatoirement renseigner :
// - le mode de contact (telephone ou mail)
// - le motif

session_start();
require_once __DIR__ . '/src/db.php';
require_once __DIR__ . '/src/auth.php';

requireRole(['employee', 'admin']);

$user = $_SESSION['user'] ?? null;
$role = $user['role'] ?? 'employee';

$commandeId = (int)($_POST['commande_id'] ?? 0);
$newStatut  = trim($_POST['statut'] ?? '');

if ($commandeId <= 0 || $newStatut === '') {
    http_response_code(400);
    die("Données invalides.");
}

// Statuts autorisés
$allowedStatus = [
    'en_attente',
    'accepte',
    'en_preparation',
    'en_cours_livraison',
    'livre',
    'attente_retour_materiel',
    'terminee',
    'annulee',
];

if (!in_array($newStatut, $allowedStatus, true)) {
    http_response_code(400);
    die("Statut invalide.");
}

// Si employé => on autorise la mise à jour de tous les statuts sauf annulee => motif obligatoire
$modeContact = null;
$motif = null;

if ($newStatut === 'annulee' && $role === 'employee') {
    $modeContact = trim($_POST['mode_contact'] ?? '');
    $motif = trim($_POST['motif'] ?? '');

    $allowedModes = ['telephone', 'mail'];

    if (!in_array($modeContact, $allowedModes, true)) {
        http_response_code(400);
        die("Mode de contact manquant.");
    }
    if ($motif === '' || strlen($motif) < 5) {
        http_response_code(400);
        die("Motif d'annulation manquant (5 caractères minimum).");
    }
}

$pdo = db();

try {
    $pdo->beginTransaction();

    // 1) Récupérer la commande
    $stmt = $pdo->prepare("SELECT statut FROM commande WHERE commande_id = ?");
    $stmt->execute([$commandeId]);
    $commande = $stmt->fetch();

    if (!$commande) {
        throw new Exception("Commande introuvable.");
    }

    $current = (string)$commande['statut'];

    // 2) Règles
    if ($current === 'annulee') {
        throw new Exception("Commande déjà annulée.");
    }
    if ($current === 'terminee') {
        throw new Exception("Commande terminée.");
    }

    // 3) Mettre à jour le statut dans la table commande
    $upd = $pdo->prepare("UPDATE commande SET statut = ? WHERE commande_id = ?");
    $upd->execute([$newStatut, $commandeId]);

    // 4) Envoyer mail retour matériel
    if ($newStatut === 'attente_retour_materiel') {

        // Récupérer l'email du client
        $stmtMail = $pdo->prepare("
        SELECT u.email
        FROM commande c
        JOIN utilisateur u ON u.utilisateur_id = c.utilisateur_id
        WHERE c.commande_id = ?
    ");
        $stmtMail->execute([$commandeId]);
        $client = $stmtMail->fetch();

        if ($client) {
            $to = $client['email'];
            $subject = "Retour de matériel - Vite & Gourmand";

            $message = "
Bonjour,

Votre commande est désormais en attente du retour du matériel prêté.

Nous vous rappelons que vous disposez d’un délai de 10 jours ouvrés
pour restituer le matériel.

Passé ce délai, des frais de 600 € vous seront facturés
conformément à nos conditions générales de vente.

Merci de prendre contact avec notre société
afin d’organiser le retour du matériel.

Cordialement,
L’équipe Vite & Gourmand
        ";

            $headers = "From: contact@vitegourmand.fr";

            mail($to, $subject, $message, $headers);
        }
    }

    // 5) Ajouter une ligne de suivi (commande_suivi)
    // Table commande_suivi : suivi_id, commande_id, statut, date_modif, mode_contact, motif
    $ins = $pdo->prepare("
        INSERT INTO commande_suivi (commande_id, statut, mode_contact, motif)
        VALUES (?, ?, ?, ?)
    ");
    $ins->execute([
        $commandeId,
        $newStatut,
        ($newStatut === 'annulee' ? $modeContact : null),
        ($newStatut === 'annulee' ? $motif : null),
    ]);

    $pdo->commit();

    /**
     * AJOUT MongoDB (NoSQL) : mise à jour du statut analytics
     * Si MongoDB échoue, on ne bloque pas la mise à jour SQL.
     */
    $mongoUri = getenv('MONGODB_URI');
    if ($mongoUri) {
        try {
            require_once __DIR__ . '/vendor/autoload.php';

            $mongo = new MongoDB\Client($mongoUri);
            $col = $mongo->selectCollection('vitegourmand', 'orders_analytics');

            $set = [
                'statut' => $newStatut,
                'updatedAt' => new MongoDB\BSON\UTCDateTime((int) round(microtime(true) * 1000)),
            ];

            // Si annulation employé => on stocke aussi le motif pour l'analyse
            if ($newStatut === 'annulee') {
                $set['annulation'] = [
                    'mode_contact' => $modeContact,
                    'motif' => $motif,
                ];
            }

            $col->updateOne(
                ['sqlCommandeId' => $commandeId],
                ['$set' => $set]
            );
        } catch (Throwable $e) {
            // ignore
        }
    }

    header("Location: commande-manage.php");
    exit;

} catch (Throwable $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo "Erreur : " . htmlspecialchars($e->getMessage());
}

// 6) Envoyer mail commande terminée => laissez un avis
if ($newStatut === 'terminee') {

    // récupérer email du client
    $stmtMail = $pdo->prepare("
        SELECT u.email
        FROM commande c
        JOIN utilisateur u ON u.utilisateur_id = c.utilisateur_id
        WHERE c.commande_id = ?
    ");
    $stmtMail->execute([$commandeId]);
    $client = $stmtMail->fetch();

    if ($client) {
        $to = $client['email'];
        $subject = "Votre commande est terminée - Vite & Gourmand";

        // lien vers avis
        require_once __DIR__ . '/src/helpers.php';
        $link = base_url() . "/avis-create.php?commande_id=" . urlencode((string)$commandeId);

        $message = "Bonjour,\n\n"
            . "Votre commande est terminée.\n"
            . "Vous pouvez vous connecter à votre compte pour laisser un avis :\n"
            . $link . "\n\n"
            . "Merci,\n"
            . "L'équipe Vite & Gourmand";

        $headers = "From: contact@vitegourmand.fr";

        @mail($to, $subject, $message, $headers);
    }
}
