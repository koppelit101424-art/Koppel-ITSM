<!-- Sidebar -->
<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$currentDir  = basename(dirname($_SERVER['PHP_SELF']));

// Detect sections - WORKS FOR ALL NESTED PATHS
$isInventorySection = strpos($_SERVER['PHP_SELF'], '/inv/') !== false;
$isTicketSection = in_array($currentPage, ['tickets.php', 'assigned_tickets.php', 'sla.php', 'sla_settings.php']);
// Special handling for Tickets in /ticket/ subdirectory
if ($currentDir === 'ticket' && in_array($currentPage, ['assigned_tickets.php', 'sla.php', 'sla_settings.php'])) {
    $isTicketSection = true;
}
?>

<style>
/* ===== SMOOTH ANIMATION (WORKS FOR ALL PATH DEPTHS) ===== */
.submenu-container {
    overflow: hidden;
    transition: max-height 0.35s ease-in-out, opacity 0.25s ease;
    opacity: 1;
    max-height: 500px;
    padding-top: 0;
    padding-bottom: 0;
}
.submenu-container.collapsed {
    max-height: 0 !important;
    opacity: 0;
    padding-top: 0 !important;
    padding-bottom: 0 !important;
    margin-top: 0 !important;
    margin-bottom: 0 !important;
}
.submenu-container a {
    display: block;
    padding-left: 2.5rem !important;
    font-size: 0.92rem !important;
    opacity: 0.9;
}
.submenu-container a:hover { 
    opacity: 1 !important; 
}

/* ===== ACTIVE STATE STYLING (ZERO UI CHANGES) ===== */
.sidebar a.active, 
.sidebar a:hover {
    background-color: #0d6efd !important; /* Bootstrap blue */
    color: white !important;
    /*border-left: 3px solid #ffc107 !important;  Yellow accent */
    padding-left: 17px !important; /* Compensate for border */
    transition: all 0.2s ease;
}
.sidebar a {
    display: flex;
    align-items: center;
    padding: 12px 20px !important;
    /* color: #adb5bd !important */
    text-decoration: none;
    border-left: 3px solid transparent;
    width: 100%;
    box-sizing: border-box;
}
.sidebar a i {
    width: 20px;
    margin-right: 10px;
    text-align: center;
}
.toggle-icon {
    transition: transform 0.3s ease;
    margin-left: auto !important;
    font-size: 0.75rem !important;
}
.toggle-icon.open {
    transform: rotate(180deg);
}
</style>

<!-- Sidebar -->
<div class="sidebar col-md-2" id="sidebar" style="height: 100vh; width: 250px; background-color: #212529;">
    <br>
    <center>
        <img src="asset/img/Koppel.png" class="img-fluid" width="200" height="60">
    </center>

    <!-- ================= INVENTORY ================= -->
    <a href="javascript:void(0);" id="inventory-toggle" class="d-flex align-items-center <?= $isInventorySection ? 'active' : '' ?>">
        <i class="fas fa-boxes"></i>
        <span class="sidebar-text flex-grow-1">Inventory</span>
        <i class="fas fa-chevron-down toggle-icon <?= $isInventorySection ? 'open' : '' ?>" id="inventory-icon"></i>
    </a>

    <div id="inventory-submenu" class="submenu-container <?= $isInventorySection ? '' : 'collapsed' ?>">
        <a href="inv/desktop/desktop.php" class="<?= ($currentDir === 'desktop' && strpos($_SERVER['PHP_SELF'], '/inv/') !== false) ? 'active' : '' ?>">
            <i class="fas fa-desktop"></i>
            <span class="sidebar-text">Desktops</span>
        </a>
        <a href="inv/laptop/laptop.php" class="<?= ($currentDir === 'laptop' && strpos($_SERVER['PHP_SELF'], '/inv/') !== false) ? 'active' : '' ?>">
            <i class="fas fa-laptop"></i>
            <span class="sidebar-text">Laptops</span>
        </a>
        <a href="inv/printer/printer.php" class="<?= ($currentDir === 'printer' && strpos($_SERVER['PHP_SELF'], '/inv/') !== false) ? 'active' : '' ?>">
            <i class="fas fa-print"></i>
            <span class="sidebar-text">Printers</span>
        </a>
        <a href="inv/ip_phone/ip_phone.php" class="<?= ($currentDir === 'ip_phone' && strpos($_SERVER['PHP_SELF'], '/inv/') !== false) ? 'active' : '' ?>">
            <i class="fas fa-phone"></i>
            <span class="sidebar-text">IP Phones</span>
        </a>
        <a href="inv/biometrics/biometrics.php" class="<?= ($currentDir === 'biometrics' && strpos($_SERVER['PHP_SELF'], '/inv/') !== false) ? 'active' : '' ?>">
            <i class="fas fa-fingerprint"></i>
            <span class="sidebar-text">Biometrics</span>
        </a>
    </div>

    <!-- ================= USERS ================= -->
    <a href="users.php" class="<?= $currentPage === 'users.php' ? 'active' : '' ?>">
        <i class="fas fa-user-tie"></i>
        <span class="sidebar-text">Users</span>
    </a>

    <!-- ================= TICKETS ================= -->
    <a href="javascript:void(0);" id="tickets-toggle" class="d-flex align-items-center <?= $isTicketSection ? 'active' : '' ?>">
        <i class="fas fa-ticket"></i>
        <span class="sidebar-text flex-grow-1">Tickets</span>
        <i class="fas fa-chevron-down toggle-icon <?= $isTicketSection ? 'open' : '' ?>" id="tickets-icon"></i>
    </a>

    <div id="tickets-submenu" class="submenu-container <?= $isTicketSection ? '' : 'collapsed' ?>">
        <a href="tickets.php" class="<?= ($currentPage === 'tickets.php' && $currentDir !== 'ticket') ? 'active' : '' ?>">
            <i class="fas fa-list"></i>
            <span class="sidebar-text">All Tickets</span>
        </a>
        <a href="ticket/assigned_tickets.php" class="<?= ($currentPage === 'assigned_tickets.php' && $currentDir === 'ticket') ? 'active' : '' ?>">
            <i class="fas fa-user-check"></i>
            <span class="sidebar-text">Assigned Tickets</span>
        </a>
        <a href="ticket/sla.php" class="<?= ($currentPage === 'sla.php' && $currentDir === 'ticket') ? 'active' : '' ?>">
            <i class="fas fa-clock"></i>
            <span class="sidebar-text">SLA</span>
        </a>
        <a href="ticket/sla_settings.php" class="<?= ($currentPage === 'sla_settings.php' && $currentDir === 'ticket') ? 'active' : '' ?>">
            <i class="fas fa-sliders-h"></i>
            <span class="sidebar-text">SLA Settings</span>
        </a>
    </div>

    <!-- ================= OTHER LINKS ================= -->
    <a href="requests.php" class="<?= $currentPage === 'requests.php' ? 'active' : '' ?>">
        <i class="fas fa-file-alt"></i>
        <span class="sidebar-text">Requests</span>
    </a>
    <a href="transactions.php" class="<?= $currentPage === 'transactions.php' ? 'active' : '' ?>">
        <i class="fas fa-file-invoice-dollar"></i>
        <span class="sidebar-text">Transactions</span>
    </a>
    <a href="reports.php" class="<?= $currentPage === 'reports.php' ? 'active' : '' ?>">
        <i class="fas fa-chart-line"></i>
        <span class="sidebar-text">Reports</span>
    </a>
    <a href="settings.php" class="<?= $currentPage === 'settings.php' ? 'active' : '' ?>">
        <i class="fas fa-cog"></i>
        <span class="sidebar-text">Settings</span>
    </a>
</div>

<!-- ================= JAVASCRIPT (WORKS FOR ALL PATHS) ================= -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    // Universal toggle handler for any sidebar section
    function initToggle(toggleId, submenuId, iconId) {
        const toggle = document.getElementById(toggleId);
        const submenu = document.getElementById(submenuId);
        const icon = document.getElementById(iconId);
        
        if (!toggle || !submenu || !icon) return;
        
        toggle.addEventListener("click", function(e) {
            e.preventDefault();
            
            // Toggle current submenu
            submenu.classList.toggle("collapsed");
            icon.classList.toggle("open");
            
            // Close other open submenus (optional but recommended UX)
            document.querySelectorAll('.submenu-container').forEach(container => {
                if (container !== submenu && !container.classList.contains('collapsed')) {
                    container.classList.add('collapsed');
                    const relatedToggle = container.previousElementSibling;
                    if (relatedToggle) {
                        const relatedIcon = relatedToggle.querySelector('.toggle-icon');
                        if (relatedIcon) relatedIcon.classList.remove('open');
                    }
                }
            });
        });
    }
    
    // Initialize both toggles - works for ANY path depth
    initToggle("inventory-toggle", "inventory-submenu", "inventory-icon");
    initToggle("tickets-toggle", "tickets-submenu", "tickets-icon");
});
</script>