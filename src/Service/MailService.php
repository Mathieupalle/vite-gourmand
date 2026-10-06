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
        $this->mailer->Timeout = 10;

        // Expéditeur
        $this->mailer->setFrom(getenv('SMTP_FROM') ?: 'no-reply@example.com', 'Vite & Gourmand');
        $this->mailer->isHTML(false);
        $this->mailer->CharSet = PHPMailer::CHARSET_UTF8;
    }

    public function send(string $to, string $subject, string $body, ?string $replyTo = null): bool
    {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->clearReplyTos();
            $this->mailer->addAddress($to);
            if ($replyTo !== null && $replyTo !== '') {
                $this->mailer->addReplyTo($replyTo);
            }
            $this->mailer->Subject = $subject;
            $this->mailer->Body    = $body;

            return $this->mailer->send();
        } catch (Exception $e) {
            error_log("Erreur mail: " . $this->mailer->ErrorInfo);
            return false;
        }
    }
}