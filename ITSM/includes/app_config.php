<?php
/*
 * Loads settings from ITSM/config.local.php (NOT committed to Git).
 * Copy ITSM/config.example.php to ITSM/config.local.php and fill in the values.
 */
if (!defined('APP_CONFIG_LOADED')) {
    define('APP_CONFIG_LOADED', true);

    $__cfgFile = __DIR__ . '/../config.local.php';
    if (!is_file($__cfgFile)) {
        http_response_code(500);
        die('Missing config file: copy ITSM/config.example.php to ITSM/config.local.php and fill in the values.');
    }
    $__cfg = require $__cfgFile;

    define('DB_HOST', $__cfg['db']['host']);
    define('DB_USER', $__cfg['db']['user']);
    define('DB_PASS', $__cfg['db']['pass']);
    define('DB_NAME', $__cfg['db']['name']);

    define('MAIL_HOST', $__cfg['mail']['host']);
    define('MAIL_PORT', (int) $__cfg['mail']['port']);
    define('MAIL_USERNAME', $__cfg['mail']['username']);
    define('MAIL_PASSWORD', $__cfg['mail']['password']);
    define('MAIL_PASSWORD_TICKETS', $__cfg['mail']['password_tickets'] ?? $__cfg['mail']['password']);

    unset($__cfgFile, $__cfg);
}
