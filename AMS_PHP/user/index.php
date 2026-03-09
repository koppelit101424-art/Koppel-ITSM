<?php
require_once '../config/config.php';
require_once 'db/db.php';
require_once 'auth/auth.php';

// Example: auth.php sets $isLoggedIn
// if (!isset($isLoggedIn) || !$isLoggedIn) {
//     http_response_code(403);
//     require '../errors/403.php';
//     exit;
// }

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ITSM - Admin Login</title>
    <link rel="icon" type="image/x-icon" href="asset/img/Koppel_bip.ico">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="asset/css/main.css">
<link rel="stylesheet" href="asset/css/menu.css">
</head>

<body>

    <div class="main-content d-flex" id="mainContent">
        <div class="sidebar col-md-2" id="sidebar" style="height:100vh;width:250px;">
            <br>
            <center>
                <img src="asset/img/Koppel.png" class="img-fluid" width="200" height="60">
            </center> <br>

            <a href="<?= BASE_URL ?>/user/ticket/tickets.php"
            class="<?= ($currentPage === 'tickets.php' && $currentDir === 'ticket') ? 'active' : '' ?>">
                <i class="fas fa-ticket"></i>
                <span class="sidebar-text">Tickets</span>
            </a>

            <a href="<?= BASE_URL ?>/user/request/requests.php"
            class="<?= ($currentPage === 'requests.php' && $currentDir === 'request') ? 'active' : '' ?>">
                <i class="fas fa-file-alt"></i>
                <span class="sidebar-text">Requests</span>
            </a>

            <a href="<?= BASE_URL ?>/user/transaction/transactions.php"
            class="<?= $currentPage === 'transactions.php' ? 'active' : '' ?>">
                <i class="fas fa-file-invoice-dollar"></i>
                <span class="sidebar-text">Transactions</span>
            </a>
        </div>

    <div class="content flex-grow-1">
        <?php include 'header.php'; ?>
        <!-- PAGE CONTENT -->
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
