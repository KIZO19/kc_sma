<?php

require_once dirname(__DIR__) . '/app/Config/config.php';

$appScope = rtrim(dirname(BASE_URL), '/');
$appScope = ($appScope === '' || $appScope === '.') ? '/' : $appScope . '/';
$assetBase = BASE_URL . '/assets/icons/';

header('Content-Type: application/manifest+json; charset=utf-8');

echo json_encode([
    'id' => $appScope,
    'name' => 'KC_SMA - Gestion scolaire',
    'short_name' => 'KC_SMA',
    'description' => 'Plateforme de gestion scolaire KC_SMA.',
    'lang' => 'fr',
    'start_url' => $appScope,
    'scope' => $appScope,
    'display' => 'standalone',
    'background_color' => '#f4f7fb',
    'theme_color' => '#3c8dbc',
    'icons' => [
        [
            'src' => $assetBase . 'kc-sma-192.png',
            'sizes' => '192x192',
            'type' => 'image/png',
            'purpose' => 'any',
        ],
        [
            'src' => $assetBase . 'kc-sma-512.png',
            'sizes' => '512x512',
            'type' => 'image/png',
            'purpose' => 'any',
        ],
        [
            'src' => $assetBase . 'kc-sma-maskable-512.png',
            'sizes' => '512x512',
            'type' => 'image/png',
            'purpose' => 'maskable',
        ],
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
