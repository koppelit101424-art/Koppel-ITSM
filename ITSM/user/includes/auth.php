<?php

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.gc_maxlifetime', 28800); // 8 hours, dapat mas mahaba sa $timeout
    $env = (strpos(__DIR__, 'staging') !== false) ? 'STG' : 'PRD';
    if ($env === 'STG') {
        session_save_path('/var/lib/php/session/staging');
    }
    session_name("ITSM_{$env}_USER");         // cookie name: ITSM_STG_USER
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

$timeout = 3600; // 15 minutes

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
// if ($_SESSION['user_type'] !== 'user') {

//     session_unset();
//     session_destroy();
//     header("Location: index.php?page=login");
//     exit;
// }