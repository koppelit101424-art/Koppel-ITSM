<!-- Sidebar -->
    <?php
    include 'db/db.php';
    include 'auth/auth.php';

    $currentPage = basename($_SERVER['PHP_SELF']);
    $currentDir  = basename(dirname($_SERVER['PHP_SELF']));

    // Detect sections
    $isTicketSection = in_array($currentPage, [
        'tickets.php',
        'assigned_tickets.php',
        'sla.php',
        'sla_settings.php'
    ]);

    function isSupervisor() {
        return isset($_SESSION['position']) && $_SESSION['position'] === 'IT Supervisor';
    }

    // ===== INVENTORY ACTIVE LOGIC =====
    // Inventory parent is active if any of these pages/directories are current
    $inventoryActive = $currentPage === 'inventory.php' || in_array($currentDir, ['desktop','laptop','printer','ip_phone','biometrics']);
    ?>

<style>
.sidebar {
    height: 100vh;
    width: 250px;
    background-color: #212529;
    /* font-family:  'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; */
    overflow-y: auto;     /* enable vertical scroll */
    overflow-x: hidden;   /* prevent horizontal scroll */

    /* Hide scrollbar - Firefox */
    scrollbar-width: none;

    /* Hide scrollbar - IE/Edge */
    -ms-overflow-style: none;
}

/* Hide scrollbar - Chrome, Safari */
.sidebar::-webkit-scrollbar {
    display: none;
}
.sidebar a.active{
    color: white !important;
    padding-left: 17px !important;
    transition: all 0.2s ease;
    
}
.sidebar a {
    position: relative;
    display: flex;
    align-items: center;
    padding: 12px 20px !important;
    text-decoration: none;
    transition: all 0.2s ease;
    border-left: 3px solid transparent;
    width: 100%;
    box-sizing: border-box;
}

.sidebar a i {
    width: 20px;
    margin-right: 10px;
    text-align: center;
}

.submenu-container {
    overflow: hidden;
    transition: max-height 0.35s ease-in-out, opacity 0.25s ease;
    opacity: 1;
    /* max-height: 500px; */
    background: #b9d5fa;
}
.submenu-container.collapsed {
    max-height: 0 !important;
    opacity: 0;
    padding-top: 0 !important;
    padding-bottom: 0 !important;
    margin-top: 0 !important;
    margin-bottom: 0 !important;
}
.submenu-container.open {
    max-height: 1000px; /* enough to fit all items */
    padding-left: 1rem;
    
}
.submenu-container a {
    display: block;
    padding-left: 2.5rem !important;
    font-size: 0.92rem !important;
    opacity: 0.9;
    margin: 0 !important;
    
}
.submenu-container a:hover {
    opacity: 1 !important;

}
.submenu-item {
    display: flex;
    align-items: center;
    padding: 8px 20px;
    font-size: 0.92rem;
    opacity: 0.9;
    text-decoration: none;
    transition: background 0.2s, opacity 0.2s;

}
.submenu-item.active, .submenu-item:hover {
    background-color: #0d6efd;
    color: white;
    opacity: 1;
    
}
.toggle-icon {
    transition: transform 0.3s ease;
    margin-left: auto !important;
}
.toggle-icon.open {
    transform: rotate(180deg);
}

.sidebar-content {
    flex-grow: 1; /* Pushes footer to bottom */
}
</style>

<div class="sidebar" id="sidebar">
    <br>
    <center> 
        <img src="asset/img/Koppel.png" class="img-fluid" width="200" height="60 " style="margin-bottom:15px;" >
    </center> 
    <!--  -->
    <a href="index.php" class="<?= $currentPage === 'index.php' ? 'active' : '' ?>">
        <i class="fas fa-server"></i>
        <span class="sidebar-text">Dashboard</span>
    </a>
    <!-- ================= INVENTORY ================= <?= $inventoryActive ? 'active' : '' ?> -->
    <a href="javascript:void(0);" id="inventory-toggle"
       class="d-flex align-items-center "
       style="cursor:pointer;">
        <i class="fas fa-boxes"></i>
        <span class="sidebar-text flex-grow-1">Inventory</span>
        <i class="fas fa-chevron-down toggle-icon <?= $inventoryActive ? 'open' : '' ?>" 
           id="inventory-icon"></i>
    </a>

    <div id="inventory-submenu" class="submenu-container <?= $inventoryActive ? '' : 'collapsed' ?>">
        <a href="inventory.php" class="<?= $currentPage === 'inventory.php' ? 'active' : '' ?>" style="padding-left:2.5rem !important;">
            <i class="fas fa-list"></i>
            <span class="sidebar-text">Items</span>
        </a>
        <a href="inv/desktop/desktop.php" class="<?= $currentDir === 'desktop' ? 'active' : '' ?>">
            <i class="fas fa-desktop"></i>
            <span class="sidebar-text">Desktops</span>
        </a>
        <a href="inv/laptop/laptop.php" class="<?= $currentDir === 'laptop' ? 'active' : '' ?>">
            <i class="fas fa-laptop"></i>
            <span class="sidebar-text">Laptops</span>
        </a>
        <a href="inv/printer/printer.php" class="<?= $currentDir === 'printer' ? 'active' : '' ?>">
            <i class="fas fa-print"></i>
            <span class="sidebar-text">Printers</span>
        </a>
        <a href="inv/ip_phone/ip_phone.php" class="<?= $currentDir === 'ip_phone' ? 'active' : '' ?>">
            <i class="fas fa-phone"></i>
            <span class="sidebar-text">IP Phones</span>
        </a>
        <a href="inv/biometrics/biometrics.php" class="<?= $currentDir === 'biometrics' ? 'active' : '' ?>">
            <i class="fas fa-fingerprint"></i>
            <span class="sidebar-text">Biometrics</span>
        </a>
        <a href="inv/network/network.php" class="<?= $currentDir === 'network' ? 'active' : '' ?>">
            <i class="fas fa-network-wired"></i>
            <span class="sidebar-text">Networks</span>
        </a>

        <a href="inv/server/server.php" class="<?= $currentDir === 'server' ? 'active' : '' ?>">
            <i class="fas fa-server"></i>
            <span class="sidebar-text">Servers</span>
        </a>

        <a href="inv/credential/credential.php" class="<?= $currentDir === 'credential' ? 'active' : '' ?>">
            <i class="fas fa-key"></i>
            <span class="sidebar-text">Credentials</span>
        </a>

    </div>

    <!-- ================= USERS ================= -->
    <a href="users.php" class="<?= $currentPage === 'users.php' ? 'active' : '' ?>">
        <i class="fas fa-user-tie"></i>
        <span class="sidebar-text">Users</span>
    </a>

    <!-- ================= TICKETS ================= <?= $isTicketSection ? 'active' : '' ?>-->
    <a href="javascript:void(0);" id="tickets-toggle"
       class="d-flex align-items-center "
       style="cursor:pointer;">
        <i class="fas fa-ticket"></i>
            <span class="sidebar-text flex-grow-1">Tickets</span>
            <i class="fas fa-chevron-down toggle-icon <?= $isTicketSection ? 'open' : '' ?>" 
            id="tickets-icon"></i>
        </a>

    <div id="tickets-submenu" class="submenu-container <?= $isTicketSection ? '' : 'collapsed' ?>">
        <a href="tickets.php" class="<?= $currentPage === 'tickets.php' ? 'active' : '' ?> " style="padding-left:2.5rem !important;">
            <i class="fas fa-list"></i>
            <span class="sidebar-text">All Tickets</span>
        </a>
        <a href="ticket/assigned_tickets.php" class="<?= $currentPage === 'assigned_tickets.php' && $currentDir === 'ticket' ? 'active' : '' ?>">
            <i class="fas fa-user-check"></i>
            <span class="sidebar-text">Assigned Tickets</span>
        </a>
        <a href="ticket/sla.php" class="<?= $currentPage === 'sla.php' && $currentDir === 'ticket' ? 'active' : '' ?>">
            <i class="fas fa-clock"></i>
            <span class="sidebar-text">SLA</span>
        </a>
        <a href="ticket/sla_settings.php" class="<?= $currentPage === 'sla_settings.php' && $currentDir === 'ticket' ? 'active' : '' ?>">
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

      <!-- Footer at bottom -->
    <p style="text-align: center; margin-bottom: 10px; padding-top:310px; color: black; font-size: 0.8rem;">
        Developed by Aljon Cardeño<strong></strong>
    </p>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
    const currentPage = '<?= $currentPage ?>';
    const currentDir = '<?= $currentDir ?>';

    function setupToggle(toggleId, submenuId, iconId) {
        const toggle = document.getElementById(toggleId);
        const submenu = document.getElementById(submenuId);
        const icon = document.getElementById(iconId);
        if (!toggle || !submenu || !icon) return;

        if (!submenu.classList.contains('collapsed')) {
            icon.classList.add('open');
        }

        toggle.addEventListener("click", function (e) {
            e.preventDefault();
            submenu.classList.toggle('collapsed');
            icon.classList.toggle('open');

            document.querySelectorAll('.submenu-container').forEach(container => {
                if (container !== submenu && !container.classList.contains('collapsed')) {
                    container.classList.add('collapsed');
                    const relatedIcon = container.previousElementSibling?.querySelector('.toggle-icon');
                    if (relatedIcon) relatedIcon.classList.remove('open');
                }
            });
        });
    }

    setupToggle("inventory-toggle", "inventory-submenu", "inventory-icon");
    setupToggle("tickets-toggle", "tickets-submenu", "tickets-icon");
});
</script>
