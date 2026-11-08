<?php
/**
 * BACK-END CONTROLLER
 *
 * Logout — POST only (with CSRF token) so a link on another site
 * cannot silently log the user out.
 */
require_once __DIR__ . '/../core/init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('index.php');
verify_csrf();

// Wipe all session data and switch to a brand-new session ID,
// so the old (logged-in) session cookie becomes useless.
$_SESSION = [];
session_regenerate_id(true);

flash('info', 'You have been logged out.');
redirect('index.php');
