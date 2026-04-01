<?php

$fullname = $_SESSION['fullname'] ?? 'John Doe';

$nameParts = explode(" ", $fullname);

$initials = strtoupper(
    substr($nameParts[0],0,1) .
    (isset($nameParts[1]) ? substr($nameParts[1],0,1) : '')
);

$inventory_pages = [
'inventory/all_assets','inventory/transactions','inventory/desktops','inventory/laptops','inventory/printers',
'inventory/ip_phones','inventory/tablets','inventory/biometrics','inventory/networks','inventory/servers','inventory/credentials'
];

$tickets_pages = [
'ticket/all_tickets','ticket/assigned_tickets','ticket/requests','ticket/sla','ticket/sla_settings'
];

$organization_pages = [
'organization/company','organization/departments','organization/users'];

?>
<div class="sidebar <?= $sidebar_collapsed ? 'collapsed' : 'open' ?>" id="sidebar">
<!-- <button class="btn btn-light d-md-none" onclick="toggleSidebar()">
    <i class="bi bi-list"></i>
</button> -->

    <!-- <div class="sidebar <?= ($_SESSION['sidebarState'] ?? 'collapsed') ?>" id="sidebar"> -->
<!-- <div class="sidebar collapsed" id="sidebar"> -->

<!-- SIDEBAR HEADER -->
<div class="sidebar-header">
<span class="logo-full">IT SYSTEM MANAGEMENT</span>
<span class="logo-circle">ITSM</span>
</div>

<div class="menu-group-title">
System
</div>
<a data-title="Announcements" class="<?= ($page=='announcement/announcements') ? 'active' : '' ?>" href="?page=announcement/announcements">
<i class="fa-solid fa-bullhorn"></i>
<span class="menu-text">Announcements</span>
</a>
<a data-title="Transactions" class="<?= ($page=='transaction/transactions') ? 'active' : '' ?>" href="?page=transaction/transactions">
<i class="fas fa-receipt"></i>
<span class="menu-text">Issued Asset</span>
</a>

<!-- TICKETS -->

<a data-title="All Tickets" class="<?= ($page=='ticket/all_tickets') ? 'active' : '' ?>" href="?page=ticket/all_tickets">
<i class="fas fa-ticket"></i>
<span class="menu-text">Tickets</span>
</a>


<a data-title="Requests" class="<?= ($page=='ticket/requests') ? 'active' : '' ?>" href="?page=ticket/requests">
<i class="fas fa-file-text"></i>
<span class="menu-text">Requests</span>
</a>

<div class="sidebar-footer">
<a class="text-danger" href="logout.php" >
    <i class="bi bi-box-arrow-right me-2"></i> Logout
</a>
</div>
</div>

