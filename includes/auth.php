<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$timeout = 9000000; // 15 minutes

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
if (!in_array($_SESSION['user_type'], ['admin','agent','manager'])) {

    session_unset();
    session_destroy();

    header("Location: index.php?page=login");
    exit;
}