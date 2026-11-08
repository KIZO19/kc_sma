<?php
declare(strict_types=1);

const APP_NAME = 'KC_SMA';
const BASE_URL = '/kc_sma/public';
const DB_HOST = 'localhost';
const DB_NAME = 'school_management_db';
const DB_USER = 'root';
const DB_PASS = '';
const DB_DSN = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';

$localEnvFile = dirname(__DIR__, 2) . '/.env.local';
if (is_file($localEnvFile) && is_readable($localEnvFile)) {
    foreach (file($localEnvFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }

        [$name, $value] = explode('=', $line, 2);
        $name = trim($name);
        $value = trim(trim($value), "\"'");
        if (!preg_match('/^[A-Z][A-Z0-9_]*$/', $name) || getenv($name) !== false) {
            continue;
        }

        putenv($name . '=' . $value);
        $_ENV[$name] = $value;
    }
}

$paymentConfig = static function (string $name, string $default = ''): string {
    $value = getenv($name);
    return $value === false ? $default : trim((string) $value);
};

// SMS / WhatsApp notification settings
// Primary SMS/WhatsApp provider: 'infobip', 'twilio' or 'generic'.
// SMS automatically falls back between Infobip and Twilio if the primary fails.
// Channel supported: 'whatsapp', 'sms', 'both'
define('PAYMENT_PROVIDER', $paymentConfig('PAYMENT_PROVIDER', 'infobip'));
define('PAYMENT_CHANNEL', $paymentConfig('PAYMENT_CHANNEL', 'both'));
define('PAYMENT_SMS_API_URL', $paymentConfig('PAYMENT_SMS_API_URL'));
define('PAYMENT_SMS_API_TOKEN', $paymentConfig('PAYMENT_SMS_API_TOKEN'));
define('PAYMENT_SMS_SENDER', $paymentConfig('PAYMENT_SMS_SENDER'));
define('PAYMENT_WHATSAPP_API_URL', $paymentConfig('PAYMENT_WHATSAPP_API_URL'));
define('PAYMENT_WHATSAPP_API_TOKEN', $paymentConfig('PAYMENT_WHATSAPP_API_TOKEN'));

// Infobip credentials
define('PAYMENT_INFOBIP_BASE_URL', rtrim($paymentConfig('PAYMENT_INFOBIP_BASE_URL'), '/'));
define('PAYMENT_INFOBIP_API_KEY', $paymentConfig('PAYMENT_INFOBIP_API_KEY'));
define('PAYMENT_INFOBIP_SMS_FROM', $paymentConfig('PAYMENT_INFOBIP_SMS_FROM'));
define('PAYMENT_INFOBIP_WHATSAPP_FROM', $paymentConfig('PAYMENT_INFOBIP_WHATSAPP_FROM'));

// Twilio credentials
define('PAYMENT_TWILIO_ACCOUNT_SID', $paymentConfig('PAYMENT_TWILIO_ACCOUNT_SID'));
define('PAYMENT_TWILIO_AUTH_TOKEN', $paymentConfig('PAYMENT_TWILIO_AUTH_TOKEN'));
define('PAYMENT_TWILIO_SMS_FROM', $paymentConfig('PAYMENT_TWILIO_SMS_FROM'));
define('PAYMENT_TWILIO_WHATSAPP_FROM', $paymentConfig('PAYMENT_TWILIO_WHATSAPP_FROM'));

// SMTP settings for account recovery emails
define('APP_PUBLIC_URL', rtrim($paymentConfig('APP_PUBLIC_URL'), '/'));
define('MAIL_SMTP_HOST', $paymentConfig('MAIL_SMTP_HOST'));
define('MAIL_SMTP_PORT', (int) $paymentConfig('MAIL_SMTP_PORT', '587'));
define('MAIL_SMTP_USERNAME', $paymentConfig('MAIL_SMTP_USERNAME'));
define('MAIL_SMTP_PASSWORD', $paymentConfig('MAIL_SMTP_PASSWORD'));
define('MAIL_SMTP_ENCRYPTION', strtolower($paymentConfig('MAIL_SMTP_ENCRYPTION', 'tls')));
define('MAIL_FROM_ADDRESS', $paymentConfig('MAIL_FROM_ADDRESS'));
define('MAIL_FROM_NAME', $paymentConfig('MAIL_FROM_NAME', APP_NAME));

// Web Push (VAPID) keys. Generate a key pair with scripts/generate_vapid_keys.php.
define('PUSH_VAPID_PUBLIC_KEY', $paymentConfig('PUSH_VAPID_PUBLIC_KEY'));
define('PUSH_VAPID_PRIVATE_KEY', $paymentConfig('PUSH_VAPID_PRIVATE_KEY'));
define(
    'PUSH_VAPID_SUBJECT',
    $paymentConfig(
        'PUSH_VAPID_SUBJECT',
        'mailto:' . (MAIL_FROM_ADDRESS !== '' ? MAIL_FROM_ADDRESS : 'admin@localhost')
    )
);

// OpenAI-compatible AI chat provider
// Enable the chat explicitly with AI_CHAT_ENABLED=true in .env.local.
define('AI_CHAT_ENABLED', filter_var($paymentConfig('AI_CHAT_ENABLED', 'false'), FILTER_VALIDATE_BOOLEAN));
define('AI_CHAT_ENDPOINT', $paymentConfig('AI_CHAT_ENDPOINT', 'https://api.openai.com/v1/chat/completions'));
define('AI_CHAT_API_KEY', $paymentConfig('AI_CHAT_API_KEY'));
define('AI_CHAT_MODEL', $paymentConfig('AI_CHAT_MODEL', 'gpt-4o-mini'));
