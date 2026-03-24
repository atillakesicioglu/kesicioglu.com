<?php
require_once '../config.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Clear all session data
$_SESSION = [];

// Invalidate the session cookie so the browser drops it immediately
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $p['path'],
        $p['domain'],
        $p['secure'],
        $p['httponly']
    );
}

session_destroy();

redirect('login.php');
