<?php
/**
 * Database connection using PDO.
 * A single shared connection is created on first use.
 */

function db(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // throw on SQL errors
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,                  // real prepared statements
            ]);
        } catch (PDOException $ex) {
            // Never show raw connection details to visitors
            http_response_code(500);
            error_log('DB connection failed: ' . $ex->getMessage());
            exit('<h2 style="font-family:sans-serif">Database connection failed.</h2>'
               . '<p style="font-family:sans-serif">Check that MySQL is running in XAMPP and that '
               . '<code>db_structure.sql</code> has been imported. Settings are in <code>config/config.php</code>.</p>');
        }
    }
    return $pdo;
}
