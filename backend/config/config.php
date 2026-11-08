<?php
/**
 * Application configuration.
 * Change the DB_* values to match your XAMPP / WAMP MySQL setup.
 */

define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'coral_bay_villa');
define('DB_USER', 'root');
define('DB_PASS', '');            // XAMPP default root password is empty

// Base URL path of the project (no trailing slash), e.g. '/coral-bay-villa'.
// Detected automatically from the folder name inside htdocs; set it by hand
// if you use a virtual host or an unusual setup.
$__root = str_replace('\\', '/', realpath(__DIR__ . '/../..'));
$__doc  = !empty($_SERVER['DOCUMENT_ROOT'])
    ? str_replace('\\', '/', (string) realpath($_SERVER['DOCUMENT_ROOT']))
    : '';
define('BASE_URL', ($__doc !== '' && stripos($__root, $__doc) === 0)
    ? rtrim(substr($__root, strlen($__doc)), '/')
    : '/coral-bay-villa');
unset($__root, $__doc);

define('SITE_NAME', 'Coral Bay Villa');
define('CURRENCY', 'LKR');

// Booking rules
define('MAX_NIGHTS', 30);              // longest stay allowed per booking
define('LONG_STAY_NIGHTS', 7);         // nights needed for the long-stay discount
define('LONG_STAY_DISCOUNT', 0.10);    // 10% off for long stays

// Uploads (admin room images)
define('UPLOAD_DIR', __DIR__ . '/../../frontend/assets/img/');
define('MAX_UPLOAD_BYTES', 2 * 1024 * 1024);   // 2 MB

date_default_timezone_set('Asia/Colombo');
