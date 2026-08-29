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
// Set these to real values on your server or in your environment.
const PAYMENT_SMS_API_URL = '';
const PAYMENT_SMS_API_TOKEN = '';
const PAYMENT_SMS_SENDER = 'KC_SMA';
const PAYMENT_WHATSAPP_API_URL = '';
const PAYMENT_WHATSAPP_API_TOKEN = '';
