<?php
    include 'auth/auth.php';
    include 'db/db.php';
    include 'inv/inv_sql.php';
    include '../config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ITSM - Inventory</title>
    <link rel="icon" type="image/x-icon" href="asset/img/Koppel_bip.ico">
     <link rel="icon" type="image/png" sizes="32x32" href="asset/img/Koppel.jpg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="asset/css/main.css">
    <link rel="stylesheet" href="asset/css/menu.css">
</head>
<body>
    <?php include 'sidebar.php'; ?> 
    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <!-- Header -->
        <?php include 'header.php'; ?>  
            <div id="inventoryTableContainer">
                <?php include 'inv/inv_table.php'; ?>
        </div>
        <div id="contextMenu" class="custom-menu">
            <a href="#" id="editLink">
            <i class="fas fa-edit text-primary me-1"></i> Edit
            </a>

            <a href="#" id="borrowLink">
            <i class="fas fa-hand-holding text-success me-1"></i> Borrow
            </a>

            <a href="#" id="issueLink">
            <i class="fas fa-file-signature me-1"></i> Issue
            </a>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="asset/js/inv.js"></script>
    <?php include 'inv/inv_modal.php'; ?>

</body>
</html>