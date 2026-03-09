<?php
declare(strict_types=1);

namespace App\Controller;

use App\Core\View;

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

            if (!$errors) {
                $to = "contact@vitegourmand.fr";
                $subject = "Contact - " . $titre;
                $body =
                    "Email: {$email}\n" .
                    "Titre: {$titre}\n\n" .
                    "Message:\n{$message}\n";

                $headers = "From: {$email}";
                @mail($to, $subject, $body, $headers);

                $success = "Merci ! Votre message a bien été envoyé.";
            }
        }

        View::render('contact', compact('success', 'errors'));
    }
}