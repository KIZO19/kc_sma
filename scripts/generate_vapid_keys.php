<?php

require dirname(__DIR__) . '/vendor/autoload.php';

$keys = \Minishlink\WebPush\VAPID::createVapidKeys();
echo 'PUSH_VAPID_PUBLIC_KEY=' . $keys['publicKey'] . PHP_EOL;
echo 'PUSH_VAPID_PRIVATE_KEY=' . $keys['privateKey'] . PHP_EOL;
echo 'PUSH_VAPID_SUBJECT=mailto:admin@example.com' . PHP_EOL;
