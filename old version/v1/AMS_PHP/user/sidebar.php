<?php


$currentPage = basename($_SERVER['PHP_SELF']);
$currentDir  = basename(dirname($_SERVER['PHP_SELF']));
?>
<!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"> -->
 <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<!-- Sidebar -->
<div class="sidebar col-md-2" id="sidebar" style="height:100vh;width:250px;">
    <br>
    <center>
        <img src="../asset/img/Koppel.png" class="img-fluid" width="200" height="60">
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
