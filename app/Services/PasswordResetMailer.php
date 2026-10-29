<?php

namespace App\Services;

use PHPMailer\PHPMailer\Exception as MailException;
use PHPMailer\PHPMailer\PHPMailer;

class PasswordResetMailer
{
    public static function isConfigured(): bool
    {
        $publicUrl = parse_url(APP_PUBLIC_URL);
        $hasValidPublicUrl = is_array($publicUrl)
            && in_array($publicUrl['scheme'] ?? '', ['http', 'https'], true)
            && !empty($publicUrl['host'])
            && !isset($publicUrl['user'])
            && !isset($publicUrl['pass']);
        $hasValidCredentials = (MAIL_SMTP_USERNAME === '') === (MAIL_SMTP_PASSWORD === '');

        return class_exists(PHPMailer::class)
            && $hasValidPublicUrl
            && MAIL_SMTP_HOST !== ''
            && MAIL_SMTP_PORT >= 1
            && MAIL_SMTP_PORT <= 65535
            && filter_var(MAIL_FROM_ADDRESS, FILTER_VALIDATE_EMAIL)
            && in_array(MAIL_SMTP_ENCRYPTION, ['tls', 'ssl', 'none'], true)
            && $hasValidCredentials;
    }

    public static function sendResetLink(string $recipient, string $name, string $token): void
    {
        if (!self::isConfigured()) {
            throw new \RuntimeException('Password reset email is not configured.');
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
            $mailer->Subject = 'Réinitialisation de votre mot de passe';

            $resetUrl = APP_PUBLIC_URL . '/reset-password?token=' . rawurlencode($token);
            $mailer->Body = "Bonjour {$name},\n\n"
                . "Une demande de réinitialisation du mot de passe de votre compte KC_SMA a été effectuée.\n"
                . "Pour choisir un nouveau mot de passe, ouvrez ce lien dans les 30 prochaines minutes :\n"
                . $resetUrl . "\n\n"
                . "Si vous n'êtes pas à l'origine de cette demande, ignorez ce message. Votre mot de passe ne changera pas.\n";
            $mailer->send();
        } catch (MailException $exception) {
            throw new \RuntimeException('SMTP delivery failed.', 0, $exception);
        }
    }
}
