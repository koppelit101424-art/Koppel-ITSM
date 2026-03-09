<?php
    include '../../auth/auth.php';
    include '../../db/db.php';
    include 'inv_sql.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biometrics</title>
    <link rel="icon" type="image/x-icon" href="asset/img/Koppel.ico">
     <link rel="icon" type="image/png" sizes="32x32" href="asset/img/Koppel.jpg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../asset/css/main.css">
    <link rel="stylesheet" href="../../asset/css/menu.css">
    <link rel="stylesheet" href="../sidebar.css">

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar col-md-2" id="sidebar" style="height: 100vh; width: 250px;">
        <br><center><img src="../../asset/img/Koppel.png" class="img-fluid" alt="" width="200px;" height="60px" style="margin-bottom:15px;"></center>
        <!-- <h4 class="text-white px-3 sidebar-title">Asset Management</h4> -->
    <a href="../../index.php" class="<?= $currentDir === 'index.php' ? 'active' : '' ?>">
        <i class="fas fa-server"></i>
        <span class="sidebar-text">Dashboard</span>
    </a>
        <!-- Inventory (toggle parent) -->
        <a href="#" id="inventory-toggle" style="cursor: pointer;">
            <i class="fas fa-boxes"></i>
            <span class="sidebar-text">Inventory</span>
            <i class="fas fa-chevron-up ms-auto" id="inventory-icon" style="font-size: 0.75rem; margin-left: auto;"></i>
        </a>

        <!-- Submenu: Desktops (initially hidden) -->
        <div id="inventory-submenu" class="submenu-container">
                <a href="../../inventory.php" style="padding-left:2.5rem;">
                <i class="fas fa-list"></i>
                <span class="sidebar-text">Items</span>
                </a>
            <a href="../desktop/desktop.php" style="padding-left: 2.5rem; font-size: 0.92rem; opacity: 0.9;">
                <i class="fas fa-desktop" style="width: 1.25rem;"></i>
                <span class="sidebar-text">Desktops</span>
            </a>
            <a href="../laptop/laptop.php" style="padding-left: 2.5rem; font-size: 0.92rem; opacity: 0.9;" >
                <i class="fas fa-laptop" style="width: 1.25rem;"></i>
                <span class="sidebar-text">Laptops</span>
            </a>
            <a href="../printer/printer.php" style="padding-left: 2.5rem; font-size: 0.92rem; opacity: 0.9;" >
                <i class="fas fa-print" style="width: 1.25rem;"></i>
                <span class="sidebar-text">Printers</span>
            </a>
            <a href="../ip_phone/ip_phone.php" style="padding-left: 2.5rem; font-size: 0.92rem; opacity: 0.9;">
                <i class="fas fa-phone" style="width: 1.25rem;"></i>
                <span class="sidebar-text">IP Phones</span>
            </a>
            <a href="biometrics.php" style="padding-left: 2.5rem; font-size: 0.92rem; opacity: 0.9;" class="active">
                <i class="fas fa-fingerprint" style="width: 1.25rem;"></i>
                <span class="sidebar-text">Biometrics</span>
            </a>
            <a href="../network/network.php" style="padding-left: 2.5rem; font-size: 0.92rem; opacity: 0.9;">
                <i class="fas fa-network-wired" style="width: 1.25rem;" ></i>
                <span class="sidebar-text">Networks</span>
            </a>

            <a href="../server/server.php" style="padding-left: 2.5rem; font-size: 0.92rem; opacity: 0.9;">
                <i class="fas fa-server" style="width: 1.25rem;"></i>
                <span class="sidebar-text">Servers</span>
            </a>

            <a href="../credential/credential.php" style="padding-left: 2.5rem; font-size: 0.92rem; opacity: 0.9;" >
                <i class="fas fa-key" style="width: 1.25rem;"></i>
                <span class="sidebar-text">Credentials</span>
            </a>
            <!-- Add more sub-items here later if needed, e.g., Laptops, Printers -->
        </div>

        <a href="../../users.php">
            <i class="fas fa-user-tie"></i>
            <span class="sidebar-text">Users</span>
        </a>
        <a href="../../tickets.php">
    <i class="fas fa-ticket"></i>
        <span class="sidebar-text flex-grow-1">Tickets</span>
        <i class="fas fa-chevron-down toggle-icon <?= $isTicketSection ? 'open' : '' ?>" 
        id="tickets-icon"></i>
    </a>
        </a>
        <a href="../../requests.php">
            <i class="fas fa-file-alt"></i>
            <span class="sidebar-text">Requests</span>
        </a>
        <a href="../../transactions.php">
            <i class="fas fa-file-invoice-dollar"></i>
            <span class="sidebar-text">Transactions</span>
        </a>
        <a href="../../reports.php">
            <i class="fas fa-chart-line"></i>
            <span class="sidebar-text">Reports</span>
        </a>
        <a href="../../settings.php">
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
        if (currentPage.includes('laptop.php')) {
            submenu.style.display = 'block';
            icon.classList.replace('fa-chevron-up', 'fa-chevron-down');
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
    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <!-- Header -->
            <?php include '../header.php'; ?>
            <?php include 'stats_card.php'; ?>
            <!-- Table Container (Hidden by default) -->
            <div id="inventoryTableContainer">
                <?php include 'inv_table.php'; ?>
                            
            </div>
        <div id="contextMenu" class="custom-menu">
            <a href="#" id="editLink">
            <i class="fas fa-edit text-primary me-1"></i> Edit
            </a>

            <a href="#" id="borrowLink">
            <i class="fas fa-hand-holding text-success me-1"></i> Borrow
            </a>

            <a href="#" id="issueLink">
            <i class="fas fa-exclamation-circle text-danger me-1"></i> Issue
            </a>
        </div>
        <?php include '../inv_modal.php'; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <!-- <script src="inv.js"></script> -->
    <script>     
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        const toggleBtn = document.getElementById('toggleSidebar');
        let isCollapsed = false;
        
        toggleBtn.addEventListener('click', function() {
            if (window.innerWidth <= 768) {
                if (sidebar.style.width === '0px' || sidebar.style.width === '') {
                    sidebar.style.width = '250px';
                    sidebar.style.opacity = '1';
                    sidebar.style.pointerEvents = 'auto';
                    mainContent.classList.remove('sidebar-collapsed');
                } else {
                    sidebar.style.width = '0px';
                    sidebar.style.opacity = '0';
                    sidebar.style.pointerEvents = 'none';
                    mainContent.classList.add('sidebar-collapsed');
                }
            } else {
                if (isCollapsed) {
                    sidebar.classList.remove('collapsed');
                    mainContent.classList.remove('sidebar-collapsed');
                    toggleBtn.innerHTML = '<i class="fas fa-chevron-left"></i>';
                    isCollapsed = false;
                } else {
                    sidebar.classList.add('collapsed');
                    mainContent.classList.add('sidebar-collapsed');
                    toggleBtn.innerHTML = '<i class="fas fa-chevron-right"></i>';
                    isCollapsed = true;
                }
            }
        });
        
        window.addEventListener('resize', function() {
            if (window.innerWidth <= 768) {
                sidebar.classList.remove('collapsed');
                mainContent.classList.remove('sidebar-collapsed');
                toggleBtn.innerHTML = '<i class="fas fa-chevron-left"></i>';
                isCollapsed = false;
            }
        });
    });

    $(document).ready(function() {
        // Initialize DataTable
        var table = $('#inventoryTable').DataTable({
            "pageLength": 10,
            "lengthMenu": [5, 10, 25, 50],
            "columnDefs": [
                { "visible": false, "targets": [0, 6, 10] },
                { "orderable": false, "targets": [9] }
            ],
            "order": [[8, 'desc']],
            "autoWidth": false,
            "language": {
                "search": "Search within table:"
            }
        });

        // === Global Search ===
        $('#globalSearch').on('keyup', function() {
            table.search(this.value).draw();
        });

        // === Filter by Item Name ===
        $('#filterByName').on('change', function() {
            var val = $(this).val();
            table.column(2).search(val ? '^' + $.fn.dataTable.util.escapeRegex(val) + '$' : '', true, false).draw();
        });

    // Type filter (buttons)
        $('.type-filter').on('click', function() {
        // Reset all buttons to outline
        $('.type-filter')
            .removeClass('btn-blue active')
            .addClass('btn-outline-blue');

        // Set only the clicked one to blue
        $(this)
            .removeClass('btn-blue')
            .addClass('btn-blue active');

            const type = $(this).data('type');
            if (type) {
            table.column(10).search('^' + type + '$', true, false).draw(); 
            } else {
            table.column(10).search('').draw();
            }
        });

        // === MODAL on CLICK ===
        $('#inventoryTable tbody').on('click', 'tr', function(e) {
            // Prevent triggering on context menu or links (not needed here, but safe)
            var $row = $(this);
            
            $('#modalItemCode').text($row.data('code') || '—');
            $('#modalItemName').text($row.data('name') || '—');
            $('#modalItemBrand').text($row.data('brand') || '—');
            $('#modalItemModel').text($row.data('model') || '—');
            $('#modalItemSerial').text($row.data('serial') || '—');
            $('#modalItemDesc').text($row.data('desc') || '—');
            $('#modalItemQty').text($row.data('qty') || '0');
            $('#modalItemReceived').text($row.data('received') || '—');
            $('#modalItemType').text($row.data('type') || '—');

            // Status
            var statusHtml = (parseInt($row.data('qty')) > 0)
                ? '<span class="status-active">In Stock</span>'
                : '<span class="status-inactive">In Use</span>';
            $('#modalItemStatus').html(statusHtml);

            var modal = new bootstrap.Modal(document.getElementById('itemDetailsModal'));
            modal.show();
        });

        // === CONTEXT MENU (Right-click) ===
        $('#inventoryTable tbody').on('contextmenu', 'tr', function(e) {
            e.preventDefault();
            var $row = $(this);
            var itemId = $row.data('item-id');
            var qty = parseInt($row.data('qty'));

            $('#editLink').attr('href', '../edit_item.php?item_id=' + encodeURIComponent(itemId));
            
            if (qty > 0) {
                $('#borrowLink').attr('href', '../borrow_item.php?item_id=' + encodeURIComponent(itemId)).show();
                $('#issueLink').attr('href', '../issue_item.php?item_id=' + encodeURIComponent(itemId)).show();
            } else {
                $('#borrowLink, #issueLink').hide();
            }

            $('#contextMenu')
                .css({
                    display: 'block',
                    left: e.pageX + 'px',
                    top: e.pageY + 'px'
                });
        });

        $(document).on('click', function() {
            $('#contextMenu').hide();
        });

        $('#contextMenu').on('click', function(e) {
            e.stopPropagation();
        });
    });
    </script>
</body>
</html>