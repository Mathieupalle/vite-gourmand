<?php
declare(strict_types=1);

namespace App\Controller;

use App\Core\View;
use App\Service\MailService;

final class ContactController
{
    public function contact(): void
    {
        $success = null;
        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim((string)($_POST['email'] ?? ''));
            $titre = trim((string)($_POST['titre'] ?? ''));
            $message = trim((string)($_POST['message'] ?? ''));

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Email invalide.";
            }
            if ($titre === '') {
                $errors[] = "Le titre est obligatoire.";
            }
            if ($message === '') {
                $errors[] = "Le message est obligatoire.";
            }
            if (mb_strlen($titre) > 150) {
                $errors[] = "Le titre est trop long (150 caractères maximum).";
            }
            if (mb_strlen($message) > 5000) {
                $errors[] = "Le message est trop long (5000 caractères maximum).";
            }

            if (!$errors) {
                // Destinataire : variable CONTACT_TO, à défaut la boîte utilisée pour l'envoi (SMTP_FROM)
                $to = (string)(getenv('CONTACT_TO') ?: getenv('SMTP_FROM') ?: '');

                $body =
                    "Email: {$email}\n" .
                    "Titre: {$titre}\n\n" .
                    "Message:\n{$message}\n";

                $sent = false;
                if ($to !== '') {
                    // L'adresse du visiteur sert de "Répondre à", jamais d'expéditeur
                    $sent = (new MailService())->send($to, "Contact - " . $titre, $body, $email);
                } else {
                    error_log('Formulaire de contact : aucun destinataire configuré (CONTACT_TO ou SMTP_FROM).');
                }

                if ($sent) {
                    $success = "Merci ! Votre message a bien été envoyé.";
                } else {
                    $errors[] = "Votre message n'a pas pu être envoyé pour le moment. Merci de réessayer plus tard.";
                }
            }
        }

        View::render('contact', compact('success', 'errors'));
    }
}
