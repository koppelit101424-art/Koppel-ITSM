<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
/* Sidebar submenu smooth transition */
.submenu-container {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.35s ease-in-out, padding 0.25s ease;
    padding-left: 0;
}
.submenu-container.open {
    max-height: 1000px; /* enough to fit all items */
    padding-left: 1rem;
}
.submenu-item {
    display: flex;
    align-items: center;
    /* padding: 8px 20px; */
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
.sidebar-parent {
    display: flex;
    align-items: center;
    padding: 12px 20px;
    cursor: pointer;
    text-decoration: none;
    border-left: 3px solid transparent;
    /* background-color: red; */
    
}
.sidebar-parent.active {
    color: white;

}
.toggle-icon {
    transition: transform 0.3s;
}
.toggle-icon.open {
    transform: rotate(180deg);
}
</style>

<!-- Sidebar -->
<div class="sidebar col-md-2" id="sidebar" style="height: 100vh; width: 250px; overflow-y: auto;">

    <br>
    <center>
        <img src="../asset/img/Koppel.png" class="img-fluid" alt="" width="200px" height="60px" style="margin-bottom: 15px;">
    </center>
    <a href="../index.php" class="<?= $currentPage === 'index.php' ? 'active' : '' ?>">
        <i class="fas fa-server"></i>
        <span class="sidebar-text">Dashboard</span>
    </a>
    <!-- ===== INVENTORY ===== -->
    <a href="../inventory.php" id="inventory-toggle" class="sidebar-parent">
        <i class="fas fa-boxes"></i>
        <span class="sidebar-text">Inventory</span>
        <i class="fas fa-chevron-down ms-auto" id="inventory-icon"></i>
    </a>

    <div id="inventory-submenu" class="submenu-container">
                <a href="../inventory.php" class="submenu-item" >
            <i class="fas fa-list"></i>
            <span class="sidebar-text">Items</span>
        </a>
        <a href="../inv/desktop/desktop.php" class="submenu-item">
            <i class="fas fa-desktop"></i>
            <span class="sidebar-text">Desktops</span>
        </a>
        <a href="../inv/laptop/laptop.php" class="submenu-item">
            <i class="fas fa-laptop"></i>
            <span class="sidebar-text">Laptops</span>
        </a>
        <a href="../inv/printer/printer.php" class="submenu-item">
            <i class="fas fa-print"></i>
            <span class="sidebar-text">Printers</span>
        </a>
        <a href="../inv/ip_phone/ip_phone.php" class="submenu-item">
            <i class="fas fa-phone"></i>
            <span class="sidebar-text">IP Phones</span>
        </a>
        <a href="../inv/biometrics/biometrics.php" class="submenu-item">
            <i class="fas fa-fingerprint"></i>
            <span class="sidebar-text">Biometrics</span>
        </a>
    </div>

    <!-- ===== USERS ===== -->
    <a href="../users.php">
        <i class="fas fa-user-tie"></i>
        <span class="sidebar-text">Users</span>
    </a>

    <!-- ===== TICKETS ===== -->
    <a href="javascript:void(0);" id="tickets-toggle" class="sidebar-parent">
        <i class="fas fa-ticket"></i>
        <span class="sidebar-text">Tickets</span>
        <i class="fas fa-chevron-up ms-auto" id="tickets-icon"></i>
    </a>

    <div id="tickets-submenu" class="submenu-container">
        <a href="../tickets.php" class="submenu-item">
            <i class="fas fa-list"></i>
            <span class="sidebar-text">All Tickets</span>
        </a>
        <a href="assigned_tickets.php" class="submenu-item">
            <i class="fas fa-user-check"></i>
            <span class="sidebar-text">Assigned Tickets</span>
        </a>
        <a href="sla.php" class="submenu-item">
            <i class="fas fa-clock"></i>
            <span class="sidebar-text">SLA</span>
        </a>
        <a href="sla_settings.php" class="submenu-item">
            <i class="fas fa-sliders-h"></i>
            <span class="sidebar-text">SLA Settings</span>
        </a>
    </div>

    <!-- ===== OTHER LINKS ===== -->
    <a href="../requests.php"><i class="fas fa-file-alt"></i> Requests</a>
    <a href="../transactions.php"><i class="fas fa-file-invoice-dollar"></i> Transactions</a>
    <a href="../reports.php"><i class="fas fa-chart-line"></i> Reports</a>
    <a href="../settings.php"><i class="fas fa-cog"></i> Settings</a>
</div>


<script>
document.addEventListener('DOMContentLoaded', function () {
    function setupToggle(toggleId, submenuId, iconId) {
        const toggle = document.getElementById(toggleId);
        const submenu = document.getElementById(submenuId);
        const icon = document.getElementById(iconId);

        const currentPage = window.location.pathname.split('/').pop(); // only filename

        // Open if active page is inside submenu
        if ([...submenu.querySelectorAll('a')].some(a => currentPage === a.getAttribute('href').split('/').pop())) {
            submenu.classList.add('open');
            // toggle.classList.add('active');
            icon.classList.add('open');
        }

        toggle.addEventListener('click', function () {
            submenu.classList.toggle('open');
            toggle.classList.toggle('active');
            icon.classList.toggle('open');
        });
    }

    setupToggle('inventory-toggle', 'inventory-submenu', 'inventory-icon');
    setupToggle('tickets-toggle', 'tickets-submenu', 'tickets-icon');

    // Highlight active submenu item
    const currentPage = window.location.pathname.split('/').pop(); // get filename only
    document.querySelectorAll('.submenu-item').forEach(item => {
        const hrefPage = item.getAttribute('href').split('/').pop(); // get href filename only
        if (currentPage === hrefPage) {
            item.classList.add('active');
        }
    });
});
</script>

