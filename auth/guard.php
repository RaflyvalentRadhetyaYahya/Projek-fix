<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once __DIR__ . '/helpers.php';

function require_role(string $role): void
{
    if (class_exists(\App\Auth\Guard::class)) {
        \App\Auth\Guard::requireRole($role);
        return;
    }

    // Fallback (seharusnya tidak terjadi jika Composer autoload aktif)
    $loginUrl = app_base_url() . '/login.php?role=' . urlencode(normalize_role($role));
    redirect_to($loginUrl);
}
