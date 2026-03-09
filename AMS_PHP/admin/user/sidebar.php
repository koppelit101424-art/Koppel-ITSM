

    <!-- Sidebar -->
<div class="sidebar col-md-2" id="sidebar" style="height: 100vh; width: 250px;">
    <!-- <h4 class="text-white px-3 sidebar-title">Asset Management</h4> -->
         <br><center><img src="../asset/img/Koppel.png" class="img-fluid" alt="" width="200px;" height="60px"></center>

    <!-- Inventory (toggle parent) -->
    <a href="index.php" id="inventory-toggle" style="cursor: pointer;">
        <i class="fas fa-boxes"></i>
        <span class="sidebar-text">Inventory</span>
        <i class="fas fa-chevron-down ms-auto" id="inventory-icon" style="font-size: 0.75rem; margin-left: auto;"></i>
    </a>

    <!-- Submenu: Desktops (initially hidden) -->
    <div id="inventory-submenu" style="display: none;">
        <a href="../inv/desktop/desktop.php" style="padding-left: 2.5rem; font-size: 0.92rem; opacity: 0.9;">
            <i class="fas fa-desktop" style="width: 1.25rem;"></i>
            <span class="sidebar-text">Desktops</span>
        </a>
        <a href="../inv/laptop/laptop.php" style="padding-left: 2.5rem; font-size: 0.92rem; opacity: 0.9;">
            <i class="fas fa-laptop" style="width: 1.25rem;"></i>
            <span class="sidebar-text">Laptops</span>
        </a>
         <a href="../inv/printer/printer.php" style="padding-left: 2.5rem; font-size: 0.92rem; opacity: 0.9;" >
                <i class="fas fa-print" style="width: 1.25rem;"></i>
                <span class="sidebar-text">Printers</span>
            </a>
            <a href="../inv/ip_phone/ip_phone.php" style="padding-left: 2.5rem; font-size: 0.92rem; opacity: 0.9;">
                <i class="fas fa-phone" style="width: 1.25rem;"></i>
                <span class="sidebar-text">IP Phones</span>
            </a>
           <a href="../inv/biometrics/biometrics.php" style="padding-left: 2.5rem; font-size: 0.92rem; opacity: 0.9;">
                <i class="fas fa-fingerprint" style="width: 1.25rem;"></i>
                <span class="sidebar-text">Biometrics</span>
            </a>
        <!-- Add more sub-items here later if needed, e.g., Laptops, Printers -->
    </div>

    <a href="../users.php" class="active">
        <i class="fas fa-user-tie"></i>
        <span class="sidebar-text">Users</span>
    </a>
    <a href="../tickets.php">
        <i class="fas fa-ticket"></i>
        <span class="sidebar-text">Tickets</span>
    </a>
    <a href="../requests.php">
        <i class="fas fa-file-alt"></i>
        <span class="sidebar-text">Requests</span>
    </a>
    <a href="../transactions.php">
        <i class="fas fa-file-invoice-dollar"></i>
        <span class="sidebar-text">Transactions</span>
    </a>
    <a href="../reports.php">
        <i class="fas fa-chart-line"></i>
        <span class="sidebar-text">Reports</span>
    </a>
    <a href="../settings.php">
        <i class="fas fa-cog"></i>
        <span class="sidebar-text">Settings</span>
    </a>
    <!-- <a href="logout.php" onclick="return confirm('Are you sure you want to log out?');">
        <i class="fas fa-right-from-bracket"></i>
        <span class="sidebar-text">Logout</span>
    </a> -->
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const toggle = document.getElementById('inventory-toggle');
    const submenu = document.getElementById('inventory-submenu');
    const icon = document.getElementById('inventory-icon');

    // Optional: auto-expand if on desktop.php
    const currentPage = window.location.pathname;
    if (currentPage.includes('desktop.php')) {
        submenu.style.display = 'block';
        icon.classList.replace('fa-chevron-down', 'fa-chevron-up');
    }

    toggle.addEventListener('click', function (e) {
        e.preventDefault();
        const isVisible = submenu.style.display === 'block';

        if (isVisible) {
            submenu.style.display = 'none';
            icon.classList.replace('fa-chevron-up', 'fa-chevron-down');
        } else {
            submenu.style.display = 'block';
            icon.classList.replace('fa-chevron-down', 'fa-chevron-up');
        }
    });
});
</script>

