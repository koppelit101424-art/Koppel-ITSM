<?php
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.gc_maxlifetime', 28800);
    $env = (strpos(__DIR__, 'staging') !== false) ? 'STG' : 'PRD';
    session_name("ITSM_{$env}_ADMIN");        // cookie name: ITSM_STG_ADMIN
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}


$timeout = 19000000; // 15 minutes

$currentPage = $_GET['page'] ?? '';

/* ALLOW LOGIN PAGE */
if ($currentPage == 'login') {
    return;
}

/* USER NOT LOGGED IN */
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?page=login");
    exit;
}

/* CHECK INACTIVITY */
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout) {

    session_unset();
    session_destroy();

    header("Location: index.php?page=login&msg=timeout");
    exit;
}

/* UPDATE ACTIVITY */
$_SESSION['LAST_ACTIVITY'] = time();

/* ROLE CHECK */
if (!in_array($_SESSION['user_type'] ?? '', ['admin','agent','manager'], true)) {

    //session_unset();
    //session_destroy();

    header("Location: index.php?page=login");
    exit;
}