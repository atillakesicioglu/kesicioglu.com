<?php
/**
 * Admin bootstrap — must be the first include in every admin page except login.php / logout.php.
 *
 * Guarantees:
 *   1. Database + helpers loaded (config.php).
 *   2. Security helpers loaded (lib/security.php).
 *   3. Session is active before any header/redirect.
 *   4. admin_guard() bounces unauthenticated HTML requests to login.php.
 *      For JSON API endpoints use admin_guard_api() instead.
 */

declare(strict_types=1);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../lib/security.php';

/** For regular HTML admin pages: redirect to login if not authenticated. */
function admin_guard(): void {
    if (!isLoggedIn()) {
        redirect('login.php');
    }
}

/** For JSON API endpoints: respond with 401 JSON and exit if not authenticated. */
function admin_guard_api(): void {
    if (!isLoggedIn()) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        exit;
    }
}
