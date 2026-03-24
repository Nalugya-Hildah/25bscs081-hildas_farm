<?php
// ============================================================
//  HILDA'S POULTRY FARM — db_connect.php
//  Dedicated database connection file (Milestone: Data-Driven)
//  Keep this file outside the web root if possible.
//  Update DB_USER and DB_PASS before deploying.
// ============================================================

define('DB_HOST',    'localhost');
define('DB_NAME',    'hildas_poultry_farm');
define('DB_USER',    'root');     // ← CHANGE THIS
define('DB_PASS',    '');         // ← CHANGE THIS
define('DB_CHARSET', 'utf8mb4');

/**
 * Returns a singleton PDO connection.
 * Throws a clean error page on failure instead of leaking credentials.
 */
function connectDB(): PDO {
    static $pdo = null;

    if ($pdo === null) {
        $dsn  = 'mysql:host=' . DB_HOST
              . ';dbname='    . DB_NAME
              . ';charset='   . DB_CHARSET;

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Log the real error; never expose it to the browser
            error_log('[Hilda\'s Farm] DB Connection failed: ' . $e->getMessage());
            die('<p style="font-family:sans-serif;color:#c0392b;padding:2rem;">
                    ⚠️ Could not connect to the database. Please try again later.
                 </p>');
        }
    }

    return $pdo;
}
