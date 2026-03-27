<?php
$inventory_pages = [
'inventory/all_assets','inventory/transactions','inventory/desktops','inventory/laptops','inventory/printers',
'inventory/ip_phones','inventory/tablets','inventory/biometrics','inventory/networks','inventory/servers','inventory/credentials'
];

$tickets_pages = [
'ticket/all_tickets','ticket/assigned_tickets','ticket/requests','ticket/sla','ticket/sla_settings'
];

$organization_pages = [
'organization/company','organization/departments','organization/users'];

'announcement/announcements';

$fullname = $_SESSION['fullname'] ?? 'John Doe';

$nameParts = explode(" ", $fullname);

$initials = strtoupper(
    substr($nameParts[0],0,1) .
    (isset($nameParts[1]) ? substr($nameParts[1],0,1) : '')
);
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
<a data-title="Dashboard" class="<?= ($page=='dashboard') ? 'active' : '' ?>" href="?page=dashboard">
<i class="bi bi-speedometer2"></i>
<span class="menu-text">Dashboard</span>
</a>

<a data-title="Announcements" class="<?= ($page=='announcement/announcements') ? 'active' : '' ?>" href="?page=announcement/announcements">
<i class="fa-solid fa-bullhorn"></i>
<span class="menu-text">Announcements</span>
</a>

<!-- INVENTORY -->
<div class="menu-group">

<a data-title="Inventory" class="dropdown-btn <?= in_array($page,$inventory_pages) ? 'active' : '' ?>">
<i class="bi bi-box-seam"></i>
<span class="menu-text">Inventory</span>
<i class="bi bi-chevron-down ms-auto dropdown-arrow"></i>
</a>

<div class="submenu <?= in_array($page,$inventory_pages) ? 'show' : '' ?>">

<!-- ASSETS DROPDOWN -->
<a class="dropdown-btn nested <?= in_array($page, [
    'inventory/all_assets','inventory/desktops','inventory/laptops','inventory/printers','inventory/ip_phones','inventory/tablets','inventory/biometrics','inventory/networks','inventory/servers'
]) ? 'active' : '' ?>">
    <i class="bi bi-hdd-stack"></i>
    <span class="menu-text">Assets</span>
    <i class="bi bi-chevron-down ms-auto dropdown-arrow"></i>
</a>

<div class="submenu nested-submenu <?= in_array($page, [
    'inventory/all_assets','inventory/desktops','inventory/laptops','inventory/printers','inventory/ip_phones','inventory/tablets','inventory/biometrics','inventory/networks','inventory/servers'
]) ? 'show' : '' ?>">

<a data-title="All Assets" class="<?= ($page=='inventory/all_assets') ? 'active' : '' ?>" href="?page=inventory/all_assets">
<i class="bi bi-list"></i>
<span class="menu-text">All Assets</span>
</a>

<!-- <a data-title="Scanners" class="<?= ($page=='inventory/scanners') ? 'active' : '' ?>" href="?page=inventory/scanners">
<i class="fa-solid fa-mobile-screen"></i>
<span class="menu-text">Barcode Scanners</span>
</a> -->

<!-- <a data-title="Biometrics" class="<?= ($page=='inventory/biometrics') ? 'active' : '' ?>" href="?page=inventory/biometrics">
<i class="bi bi-fingerprint"></i>
<span class="menu-text"> Biometrics</span>
</a> -->

<a data-title="Desktops" class="<?= ($page=='inventory/desktops') ? 'active' : '' ?>" href="?page=inventory/desktops">
    <i class="bi bi-pc-display"></i>
    <span class="menu-text">Desktops</span>
</a>

<!-- <a data-title="IP Phones" class="<?= ($page=='inventory/ip_phones') ? 'active' : '' ?>" href="?page=inventory/ip_phones">
    <i class="bi bi-telephone"></i> 
    <span class="menu-text">IP Phones</span>
</a>

<a data-title="Laptops" class="<?= ($page=='inventory/laptops') ? 'active' : '' ?>" href="?page=inventory/laptops">
    <i class="bi bi-laptop"></i>
    <span class="menu-text">Laptops</span>
</a>

<a data-title="Networks" class="<?= ($page=='inventory/networks') ? 'active' : '' ?>" href="?page=inventory/networks">
    <i class="bi bi-diagram-3"></i>
    <span class="menu-text"> Networks</span>
</a>

<a data-title="Printers" class="<?= ($page=='inventory/printers') ? 'active' : '' ?>" href="?page=inventory/printers">
    <i class="bi bi-printer"></i>
    <span class="menu-text"> Printers</span>
</a>

<a data-title="Servers" class="<?= ($page=='inventory/servers') ? 'active' : '' ?>" href="?page=inventory/servers">
    <i class="bi bi-hdd-network"></i> 
    <span class="menu-text">Servers</span>
</a> -->

<!-- <a data-title="Phones" class="<?= ($page=='inventory/phones') ? 'active' : '' ?>" href="?page=inventory/phones">
    <i class="fa-thin fa-mobile-button"></i>
    <span class="menu-text">Smart Phones</span>
</a> -->

<!-- <a data-title="Tablets" class="<?= ($page=='inventory/tablets') ? 'active' : '' ?>" href="?page=inventory/tablets">
    <i class="fa-thin fa-tablet-button"></i>
    <span class="menu-text">Tablets</span>
</a> -->

</div>
<!-- OTHER MENU -->
<a data-title="Transactions" class="<?= ($page=='inventory/transactions') ? 'active' : '' ?>" href="?page=inventory/transactions">
    <i class="bi bi-receipt"></i>
    <span class="menu-text">Issuance</span>
</a>

<a data-title="Credentials" class="<?= ($page=='inventory/credentials') ? 'active' : '' ?>" href="?page=inventory/credentials">
    <i class="bi bi-key"></i>
    <span class="menu-text">Credentials</span>
</a>


</div>
</div>

<!-- TICKETS -->
<div class="menu-group">

<a data-title="Tickets" class="dropdown-btn <?= in_array($page,$tickets_pages) ? 'active' : '' ?>">
<i class="bi bi-ticket"></i>
<span class="menu-text">Tickets</span>
<i class="bi bi-chevron-down ms-auto dropdown-arrow"></i>
</a>

<div class="submenu <?= in_array($page,$tickets_pages) ? 'show' : '' ?>">

<a data-title="All Tickets" class="<?= ($page=='ticket/all_tickets') ? 'active' : '' ?>" href="?page=ticket/all_tickets">
<i class="bi bi-list"></i>
<span class="menu-text">All Tickets</span>
</a>

<a data-title="Assigned Tickets" class="<?= ($page=='ticket/assigned_tickets') ? 'active' : '' ?>" href="?page=ticket/assigned_tickets">
<i class="bi bi-person-check"></i>
<span class="menu-text">Assigned Tickets</span>
</a>

<a data-title="Requests" class="<?= ($page=='ticket/requests') ? 'active' : '' ?>" href="?page=ticket/requests">
<i class="bi bi-file-text"></i>
<span class="menu-text">Requests</span>
</a>

<a data-title="SLA" class="<?= ($page=='ticket/sla') ? 'active' : '' ?>" href="?page=ticket/sla">
<i class="bi bi-clock"></i>
<span class="menu-text">SLA</span>
</a>

<?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin'): ?>
<a data-title="SLA Settings" class="<?= ($page=='ticket/sla_settings') ? 'active' : '' ?>" href="?page=ticket/sla_settings">
<i class="bi bi-sliders"></i>
<span class="menu-text">SLA Settings</span>
</a>
<?php endif; ?>
</div>
</div> 


<!-- Org -->
<div class="menu-group">
<div class="menu-group-title">
Administration
</div>

<a data-title="Organization" class="dropdown-btn <?= in_array($page,$organization_pages) ? 'active' : '' ?>">
<i class="fa-solid fa-sitemap"></i>
<span class="menu-text">Organization</span>
<i class="bi bi-chevron-down ms-auto dropdown-arrow"></i>
</a>
<div class="submenu <?= in_array($page,$organization_pages) ? 'show' : '' ?>">

<a data-title="Company" class="<?= ($page=='organization/company') ? 'active' : '' ?>" href="?page=organization/company">
<i class="fa-solid fa-building"></i>
<span class="menu-text">Company</span>
</a>

<a data-title="Departments" class="<?= ($page=='organization/departments') ? 'active' : '' ?>" href="?page=organization/departments">
<i class="fa-regular fa-building"></i>
<span class="menu-text">Departments</span>
</a>

<a data-title="Users" class="<?= ($page=='organization/users') ? 'active' : '' ?>" href="?page=organization/users">
<i class="bi bi-person"></i>
<span class="menu-text">Users</span>
</a>
</div>
</div>

<!-- <a data-title="Users" class="<?= ($page=='user/users') ? 'active' : '' ?>" href="?page=user/users">
<i class="bi bi-person"></i>
<span class="menu-text">Users</span>
</a> -->

<?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin'): ?>
    <div class="menu-group-title">
        Settings
    </div>

    <a data-title="Settings" class="<?= ($page=='settings') ? 'active' : '' ?>" href="?page=settings">
        <i class="bi bi-gear"></i>
        <span class="menu-text">Settings</span>
    </a>
<?php endif; ?>
<a class="text-danger" href="logout.php" >
    <i class="bi bi-box-arrow-right me-2"></i> Logout
</a>
<div class="sidebar-footer text-center">
    Developed by
<strong class="footer-text">Aljon Cardeno</strong>

</div>
</div>

<script>
    $(document).on('click', function(e) {

    // ❗ Ignore clicks inside sidebar
    if ($(e.target).closest('#sidebar').length) {
        return;
    }

    $('#contextMenu').hide();
});
</script>