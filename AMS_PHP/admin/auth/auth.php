<?php
// Only start session if not already active
// if (session_status() === PHP_SESSION_NONE) {
//     session_start();
// }

// Require login
// if (!isset($_SESSION['user_id'])) {
//     header("Location: login.php");
//     exit;
// }

// Require admin (optional, only if this file is for admin-only pages)
// if (isset($_SESSION['user_type']) && $_SESSION['user_type'] !== 'admin') {
//     header("Location: index.php");
//     exit;
// }

?>

<?php
// Only start session if not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$isLoggedIn = false;

if (isset($_SESSION['user_id'])) {
    $isLoggedIn = true;
}
//$timeout = 900;  15 minutes = 900 seconds

// Check if logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// ---- INACTIVITY CHECK ----
// if (isset($_SESSION['LAST_ACTIVITY'])) {
//     if (time() - $_SESSION['LAST_ACTIVITY'] > $timeout) {

//         // Destroy session if idle too long
//         session_unset();
//         session_destroy();

//         header("Location: login.php?msg=timeout");
//         exit;
//     }
// }

// Update activity timestamp
// $_SESSION['LAST_ACTIVITY'] = time();
// --------------------------

// Require admin (only for admin pages)
if (isset($_SESSION['user_type']) && $_SESSION['user_type'] !== 'admin') {
    header("Location: index.php");
    exit;
}
?>
