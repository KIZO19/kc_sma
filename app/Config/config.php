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
// Provider supported: 'infobip', 'twilio' or 'generic'
// Channel supported: 'whatsapp', 'sms', 'both'
define('PAYMENT_PROVIDER', $paymentConfig('PAYMENT_PROVIDER', 'infobip'));
define('PAYMENT_CHANNEL', $paymentConfig('PAYMENT_CHANNEL', 'both'));
define('PAYMENT_SMS_API_URL', $paymentConfig('PAYMENT_SMS_API_URL'));
define('PAYMENT_SMS_API_TOKEN', $paymentConfig('PAYMENT_SMS_API_TOKEN'));
define('PAYMENT_SMS_SENDER', $paymentConfig('PAYMENT_SMS_SENDER'));
define('PAYMENT_WHATSAPP_API_URL', $paymentConfig('PAYMENT_WHATSAPP_API_URL'));
define('PAYMENT_WHATSAPP_API_TOKEN', $paymentConfig('PAYMENT_WHATSAPP_API_TOKEN'));

// Infobip credentials (used when PAYMENT_PROVIDER = 'infobip')
define('PAYMENT_INFOBIP_BASE_URL', rtrim($paymentConfig('PAYMENT_INFOBIP_BASE_URL'), '/'));
define('PAYMENT_INFOBIP_API_KEY', $paymentConfig('PAYMENT_INFOBIP_API_KEY'));
define('PAYMENT_INFOBIP_SMS_FROM', $paymentConfig('PAYMENT_INFOBIP_SMS_FROM'));
define('PAYMENT_INFOBIP_WHATSAPP_FROM', $paymentConfig('PAYMENT_INFOBIP_WHATSAPP_FROM'));

// Twilio credentials (used when PAYMENT_PROVIDER = 'twilio')
define('PAYMENT_TWILIO_ACCOUNT_SID', $paymentConfig('PAYMENT_TWILIO_ACCOUNT_SID'));
define('PAYMENT_TWILIO_AUTH_TOKEN', $paymentConfig('PAYMENT_TWILIO_AUTH_TOKEN'));
define('PAYMENT_TWILIO_SMS_FROM', $paymentConfig('PAYMENT_TWILIO_SMS_FROM'));
define('PAYMENT_TWILIO_WHATSAPP_FROM', $paymentConfig('PAYMENT_TWILIO_WHATSAPP_FROM'));
