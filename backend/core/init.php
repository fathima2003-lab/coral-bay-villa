<?php
/**
 * Bootstrap file — included at the top of every page.
 * Loads configuration, opens the database connection, starts the session
 * and makes the helper functions available.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/../../frontend/views/view_helpers.php';

// Secure session cookie settings (must be set before session_start)
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,       // JavaScript cannot read the session cookie
        'samesite' => 'Lax',      // basic CSRF protection
    ]);
    session_start();
}

$pdo = db();
