<?php

namespace App\Services;

use PHPMailer\PHPMailer\Exception as MailException;
use PHPMailer\PHPMailer\PHPMailer;

class SchoolEmailNotifier
{
    public static function send(string $recipient, string $name, string $subject, string $body): void
    {
        if (!self::isConfigured()) {
            throw new \RuntimeException('La configuration SMTP est incomplète.');
        }
        if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('L’adresse email du parent est invalide.');
        }

        $mailer = new PHPMailer(true);
        try {
            $mailer->isSMTP();
            $mailer->Host = MAIL_SMTP_HOST;
            $mailer->Port = MAIL_SMTP_PORT;
            $mailer->SMTPAuth = MAIL_SMTP_USERNAME !== '';
            $mailer->Username = MAIL_SMTP_USERNAME;
            $mailer->Password = MAIL_SMTP_PASSWORD;
            $mailer->SMTPAutoTLS = MAIL_SMTP_ENCRYPTION === 'tls';
            $mailer->SMTPSecure = MAIL_SMTP_ENCRYPTION === 'none'
                ? ''
                : (MAIL_SMTP_ENCRYPTION === 'ssl' ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS);
            $mailer->Timeout = 10;
            $mailer->CharSet = PHPMailer::CHARSET_UTF8;
            $mailer->setFrom(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);
            $mailer->addAddress($recipient, $name);
            $mailer->isHTML(false);
            $mailer->Subject = $subject;
            $mailer->Body = $body;
            $mailer->send();
        } catch (MailException $exception) {
            throw new \RuntimeException('L’envoi du courriel de convocation a échoué.', 0, $exception);
        }
    }

    private static function isConfigured(): bool
    {
        return class_exists(PHPMailer::class)
            && MAIL_SMTP_HOST !== ''
            && MAIL_SMTP_PORT >= 1
            && MAIL_SMTP_PORT <= 65535
            && filter_var(MAIL_FROM_ADDRESS, FILTER_VALIDATE_EMAIL)
            && in_array(MAIL_SMTP_ENCRYPTION, ['tls', 'ssl', 'none'], true)
            && ((MAIL_SMTP_USERNAME === '') === (MAIL_SMTP_PASSWORD === ''));
    }
}
