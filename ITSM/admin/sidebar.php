<?php
$inventory_pages = [
'inventory/all_assets',
'inventory/asset_listing',
'inventory/desktops',
'inventory/transactions',
'inventory/credentials'
];

$tickets_pages = [
'ticket/all_tickets','ticket/assigned_tickets','ticket/sla','ticket/sla_settings'
];

$organization_pages = [
'organization/company','organization/departments','organization/users'
];

$fullname = $_SESSION['fullname'] ?? 'John Doe';

$nameParts = explode(" ", $fullname);

$initials = strtoupper(
    substr($nameParts[0],0,1) .
    (isset($nameParts[1]) ? substr($nameParts[1],0,1) : '')
);

// SAFE GET TYPE
$type = $_GET['type'] ?? '';
?>

<div class="sidebar <?= $sidebar_collapsed ? 'collapsed' : 'open' ?>" id="sidebar">

<!-- HEADER -->
<div class="sidebar-header">
<span class="logo-full">IT SYSTEM MANAGEMENT</span>
<span class="logo-circle">ITSM</span>
</div>

<div class="menu-group-title">System</div>

<a class="<?= ($page=='dashboard') ? 'active' : '' ?>" href="?page=dashboard">
<i class="bi bi-speedometer2"></i>
<span class="menu-text">Dashboard</span>
</a>

<a class="<?= ($page=='announcement/announcements') ? 'active' : '' ?>" href="?page=announcement/announcements">
<i class="fa-solid fa-bullhorn"></i>
<span class="menu-text">Announcements</span>
</a>

<!-- INVENTORY -->
<div class="menu-group">

<a class="dropdown-btn <?= in_array($page,$inventory_pages) ? 'active' : '' ?>">
<i class="bi bi-box-seam"></i>
<span class="menu-text">Inventory</span>
<i class="bi bi-chevron-down ms-auto dropdown-arrow"></i>
</a>

<div class="submenu <?= in_array($page,$inventory_pages) ? 'show' : '' ?>">

<!-- ASSETS -->
<a class="dropdown-btn nested <?= ($page=='inventory/asset_listing' || $page=='inventory/all_assets') ? 'active' : '' ?>">
    <i class="bi bi-hdd-stack"></i>
    <span class="menu-text">Assets</span>
    <i class="bi bi-chevron-down ms-auto dropdown-arrow"></i>
</a>

<div class="submenu nested-submenu <?= ($page=='inventory/asset_listing' || $page=='inventory/all_assets') ? 'show' : '' ?>">

<a class="<?= ($page=='inventory/all_assets') ? 'active' : '' ?>" href="?page=inventory/all_assets">
<i class="bi bi-list"></i>
<span class="menu-text">All Assets</span>
</a>

<a class="<?= ($page=='inventory/asset_listing' && $type=='Scanner') ? 'active' : '' ?>" href="?page=inventory/asset_listing&type=Scanner">
<i class="fa-solid fa-mobile-screen"></i>
<span class="menu-text">Barcode Scanners</span>
</a>

<a class="<?= ($page=='inventory/asset_listing' && $type=='Biometrics') ? 'active' : '' ?>" href="?page=inventory/asset_listing&type=Biometrics">
<i class="bi bi-fingerprint"></i>
<span class="menu-text">Biometrics</span>
</a>

<a class="<?= ($page=='inventory/asset_listing' && $type=='IP Phone') ? 'active' : '' ?>" href="?page=inventory/asset_listing&type=IP Phone">
<i class="bi bi-telephone"></i>
<span class="menu-text">IP Phones</span>
</a>

<a class="<?= ($page=='inventory/asset_listing' && $type=='Laptop') ? 'active' : '' ?>" href="?page=inventory/asset_listing&type=Laptop">
<i class="bi bi-laptop"></i>
<span class="menu-text">Laptops</span>
</a>

<a class="<?= ($page=='inventory/asset_listing' && $type=='Printer') ? 'active' : '' ?>" href="?page=inventory/asset_listing&type=Printer">
<i class="bi bi-printer"></i>
<span class="menu-text">Printers</span>
</a>

<a class="<?= ($page=='inventory/asset_listing' && $type=='Server') ? 'active' : '' ?>" href="?page=inventory/asset_listing&type=Server">
<i class="bi bi-hdd-network"></i>
<span class="menu-text">Servers</span>
</a>

<a class="<?= ($page=='inventory/asset_listing' && $type=='Switch') ? 'active' : '' ?>" href="?page=inventory/asset_listing&type=Switch">
<i class="bi bi-diagram-3"></i>
<span class="menu-text">Switches</span>
</a>

<!-- <a class="<?= ($page=='inventory/asset_listing' && $type=='Phones') ? 'active' : '' ?>" href="?page=inventory/asset_listing&type=Phones">
<i class="fa-thin fa-mobile-button"></i>
<span class="menu-text">Smart Phones</span>
</a> -->

<a class="<?= ($page=='inventory/asset_listing' && $type=='Tablet') ? 'active' : '' ?>" href="?page=inventory/asset_listing&type=Tablet">
<i class="fa-thin fa-tablet-button"></i>
<span class="menu-text">Tablets</span>
</a>

</div>

<!-- OTHER -->
 <a class="<?= ($page=='inventory/desktops') ? 'active' : '' ?>" href="?page=inventory/desktops">
    <i class="bi bi-pc-display"></i>
    <span class="menu-text">Desktops</span>
</a>

<a class="<?= ($page=='inventory/transactions') ? 'active' : '' ?>" href="?page=inventory/transactions">
    <i class="bi bi-receipt"></i>
    <span class="menu-text">Issuance</span>
</a>

<a class="<?= ($page=='inventory/credentials') ? 'active' : '' ?>" href="?page=inventory/credentials">
    <i class="bi bi-key"></i>
    <span class="menu-text">Credentials</span>
</a>

</div>
</div>

<!-- TICKETS -->
<div class="menu-group">

<a class="dropdown-btn <?= in_array($page,$tickets_pages) ? 'active' : '' ?>">
<i class="bi bi-ticket"></i>
<span class="menu-text">Tickets</span>
<i class="bi bi-chevron-down ms-auto dropdown-arrow"></i>
</a>

<div class="submenu <?= in_array($page,$tickets_pages) ? 'show' : '' ?>">

<a class="<?= ($page=='ticket/all_tickets') ? 'active' : '' ?>" href="?page=ticket/all_tickets">
<i class="bi bi-list"></i>
<span class="menu-text">All Tickets</span>
</a>

<a class="<?= ($page=='ticket/assigned_tickets') ? 'active' : '' ?>" href="?page=ticket/assigned_tickets">
<i class="bi bi-person-check"></i>
<span class="menu-text">Assigned Tickets</span>
</a>

<a class="<?= ($page=='ticket/sla') ? 'active' : '' ?>" href="?page=ticket/sla">
<i class="bi bi-clock"></i>
<span class="menu-text">SLA</span>
</a>

<?php if ($_SESSION['user_type'] === 'admin'): ?>
<a class="<?= ($page=='ticket/sla_settings') ? 'active' : '' ?>" href="?page=ticket/sla_settings">
<i class="bi bi-sliders"></i>
<span class="menu-text">SLA Settings</span>
</a>
<?php endif; ?>

</div>
</div>

<a class="<?= ($page=='ticket/requests') ? 'active' : '' ?>" href="?page=ticket/requests">
<i class="bi bi-file-text"></i>
<span class="menu-text">Requests</span>
</a>
<!-- ORGANIZATION -->
<div class="menu-group">
<div class="menu-group-title">Administration</div>

<a class="dropdown-btn <?= in_array($page,$organization_pages) ? 'active' : '' ?>">
<i class="fa-solid fa-sitemap"></i>
<span class="menu-text">Organization</span>
<i class="bi bi-chevron-down ms-auto dropdown-arrow"></i>
</a>

<div class="submenu <?= in_array($page,$organization_pages) ? 'show' : '' ?>">

<a class="<?= ($page=='organization/company') ? 'active' : '' ?>" href="?page=organization/company">
<i class="fa-solid fa-building"></i>
<span class="menu-text">Company</span>
</a>

<a class="<?= ($page=='organization/departments') ? 'active' : '' ?>" href="?page=organization/departments">
<i class="fa-regular fa-building"></i>
<span class="menu-text">Departments</span>
</a>

<a class="<?= ($page=='organization/users') ? 'active' : '' ?>" href="?page=organization/users">
<i class="bi bi-person"></i>
<span class="menu-text">Users</span>
</a>

</div>
</div>

<?php if ($_SESSION['user_type'] === 'admin'): ?>
<div class="menu-group-title">Settings</div>

<a class="<?= ($page=='settings') ? 'active' : '' ?>" href="?page=settings">
<i class="bi bi-gear"></i>
<span class="menu-text">Settings</span>
</a>
<?php endif; ?>

<a class="text-danger" href="logout.php">
<i class="bi bi-box-arrow-right me-2"></i> Logout
</a>

<div class="sidebar-footer text-center">
Developed by <strong class="footer-text"></strong>
</div>

</div>

<script>
$(document).on('click', function(e) {
    if ($(e.target).closest('#sidebar').length) return;
    $('#contextMenu').hide();
});
</script>