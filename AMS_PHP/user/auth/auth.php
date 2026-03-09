
<?php

// Only start session if not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

//$timeout = 900;  15 minutes = 900 seconds

// Check if logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: user/index.php");
    exit;
}

// ---- INACTIVITY CHECK ----
// if (isset($_SESSION['LAST_ACTIVITY'])) {
//     if (time() - $_SESSION['LAST_ACTIVITY'] > $timeout) {

//         // Destroy session if idle too long
//         session_unset();
//         session_destroy();
// include "../config/config.php";
//     header("Location: ".BASE_URL."login.php?msg=timeout");

//         exit;
//     }
// }


// Update activity timestamp
// $_SESSION['LAST_ACTIVITY'] = time();
// --------------------------

// Require user
if (isset($_SESSION['user_type']) && $_SESSION['user_type'] !== 'user') {
    header("Location: index.php");
    exit;
}
?>
