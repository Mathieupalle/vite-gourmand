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
        $this->mailer->Host       = getenv('SMTP_HOST') ?: '';
        $this->mailer->SMTPAuth   = true;
        $this->mailer->Username   = getenv('SMTP_USER') ?: '';
        $this->mailer->Password   = getenv('SMTP_PASS') ?: '';
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port       = (int)(getenv('SMTP_PORT') ?: 587);

        // Expéditeur
        $this->mailer->setFrom(getenv('SMTP_FROM') ?: 'no-reply@example.com', 'Vite & Gourmand');
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