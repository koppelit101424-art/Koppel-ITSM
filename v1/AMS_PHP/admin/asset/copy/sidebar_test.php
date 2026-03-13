<div class="sidebar col-md-2" id="sidebar" style="height: 100vh; width: 250px;">
    <h4 class="text-white px-3 sidebar-title">Asset Management</h4>

    <ul class="nav flex-column" style="list-style: none; padding-left: 0;">
        <!-- Inventory Toggle (Parent) -->
        <li>
            <a class="nav-link <?php echo isCurrentPage('index.php') || 
                                    isCurrentPage('inv/desktop/desktop.php') || 
                                    isCurrentPage('inv/laptop/laptop.php') ? 'active' : ''; ?>" 
               href="#" id="inventory-toggle" style="display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <i class="fas fa-boxes"></i>
                    <span>&emsp13; Inventory</span>
                </div>
                <i class="fas fa-chevron-down ms-auto" id="inventory-icon" style="font-size: 0.75rem;"></i>
            </a>

            <!-- Submenu -->
            <ul id="inventory-submenu" class="nav flex-column" style="display: none; margin-left: 1.5rem;">
                <li>
                    <a class="nav-link <?php echo isCurrentPage('inv/desktop/desktop.php') ? 'active' : ''; ?>" 
                       href="inv/desktop/desktop.php">
                        <i class="fas fa-desktop"></i><span>&emsp13; Desktops</span>
                    </a>
                </li>
                <li>
                    <a class="nav-link <?php echo isCurrentPage('inv/laptop/laptop.php') ? 'active' : ''; ?>" 
                       href="inv/laptop/laptop.php">
                        <i class="fas fa-laptop"></i><span>&emsp13; Laptops</span>
                    </a>
                </li>
            </ul>
        </li>

        <li>
            <a class="nav-link <?php echo isCurrentPage('users.php') ? 'active' : ''; ?>" href="users.php">
                <i class="fas fa-user-tie"></i><span>&emsp13; Users</span>
            </a>
        </li>
        <li>
            <a class="nav-link <?php echo isCurrentPage('requests.php') ? 'active' : ''; ?>" href="requests.php">
                <i class="fas fa-file-alt"></i><span>&emsp13; Requests</span>
            </a>
        </li>
        <li>
            <a class="nav-link <?php echo isCurrentPage('transactions.php') ? 'active' : ''; ?>" href="transactions.php">
                <i class="fas fa-file-invoice-dollar"></i><span>&emsp13; Transactions</span>
            </a>
        </li>
        <li>
            <a class="nav-link <?php echo isCurrentPage('reports.php') ? 'active' : ''; ?>" href="reports.php">
                <i class="fas fa-chart-line"></i><span>&emsp13; Reports</span>
            </a>
        </li>
        <li>
            <a class="nav-link <?php echo isCurrentPage('settings.php') ? 'active' : ''; ?>" href="settings.php">
                <i class="fas fa-cog"></i><span>&emsp13; Settings</span>
            </a>
        </li>
        <!--
        <li>
            <a class="nav-link" href="logout.php" onclick="return confirm('Are you sure you want to log out?');">
                <i class="fas fa-right-from-bracket"></i><span>&emsp13; Logout</span>
            </a>
        </li>
        -->
    </ul>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const toggle = document.getElementById('inventory-toggle');
    const submenu = document.getElementById('inventory-submenu');
    const icon = document.getElementById('inventory-icon');

    // Auto-expand if on desktop.php or laptop.php
    const currentPage = window.location.pathname;
    if (currentPage.includes('desktop.php') || currentPage.includes('laptop.php')) {
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