<?php
    // Database connection - credentials come from ITSM/config.local.php
    require_once __DIR__ . '/../../includes/app_config.php';

    $host = DB_HOST;
    $user = DB_USER;
    $pass = DB_PASS;
    $db   = DB_NAME;

    $conn = new mysqli($host, $user, $pass, $db);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
?>
