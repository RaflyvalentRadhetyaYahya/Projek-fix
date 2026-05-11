<?php
session_start();
require_once __DIR__ . '/../config.php';

if (!class_exists(\App\Auth\GoogleOAuth::class)) {
    http_response_code(500);
    echo 'Autoload belum aktif. Jalankan: composer dump-autoload';
    exit;
}

global $pdo, $GOOGLE_OAUTH;
(new \App\Auth\GoogleOAuth($pdo, $GOOGLE_OAUTH))->handleCallback();
