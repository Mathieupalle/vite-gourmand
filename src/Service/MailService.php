<?php
declare(strict_types=1);

namespace App\Service;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

final class MailService
{
    private PHPMailer $mailer;

    public function __construct()
    {
        $this->mailer = new PHPMailer(true);

        // Config SMTP
        $this->mailer->isSMTP();
        $this->mailer->Host       = 'smtp.example.com';
        $this->mailer->SMTPAuth   = true;
        $this->mailer->Username   = 'user@example.com';
        $this->mailer->Password   = 'password';
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port       = 587;

        // Expéditeur
        $this->mailer->setFrom('contact@vitegourmand.fr', 'Vite & Gourmand');
        $this->mailer->isHTML(false);
    }

    public function send(string $to, string $subject, string $body): bool
    {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($to);
            $this->mailer->Subject = $subject;
            $this->mailer->Body    = $body;

            return $this->mailer->send();
        } catch (Exception $e) {
            error_log("Erreur mail: " . $this->mailer->ErrorInfo);
            return false;
        }
    }
}