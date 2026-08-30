<?php
declare(strict_types=1);

const APP_NAME = 'KC_SMA';
const BASE_URL = '/kc_sma/public';
const DB_HOST = 'localhost';
const DB_NAME = 'school_management_db';
const DB_USER = 'root';
const DB_PASS = '';
const DB_DSN = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';

// SMS / WhatsApp notification settings
// Provider supported: 'infobip', 'twilio' or 'generic'
const PAYMENT_PROVIDER = 'infobip';
const PAYMENT_SMS_API_URL = '';
const PAYMENT_SMS_API_TOKEN = '';
const PAYMENT_SMS_SENDER = '';
const PAYMENT_WHATSAPP_API_URL = '';
const PAYMENT_WHATSAPP_API_TOKEN = '';

// Infobip credentials (used when PAYMENT_PROVIDER = 'infobip')
const PAYMENT_INFOBIP_BASE_URL = 'https://api.infobip.com';
const PAYMENT_INFOBIP_API_KEY = '44201bc9a2286e3b9a1d0dda105201bb-974f41bf-7b63-45f8-afc6-69df738b40d1';
const PAYMENT_INFOBIP_SMS_FROM = 'InfoSMS';
const PAYMENT_INFOBIP_WHATSAPP_FROM = '447860099299';

// Twilio credentials (used when PAYMENT_PROVIDER = 'twilio')
const PAYMENT_TWILIO_ACCOUNT_SID = '';
const PAYMENT_TWILIO_AUTH_TOKEN = '';
const PAYMENT_TWILIO_SMS_FROM = '';
const PAYMENT_TWILIO_WHATSAPP_FROM = 'whatsapp:+2439773';
