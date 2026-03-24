<?php
session_start();

if(!isset($_SESSION['user_id'])){
    header("Location: ?page=login");
    exit;
}

$page = $_GET['page'] ?? 'dashboard';

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>IT System Management</title>
<link rel="icon" type="image/x-icon" href="../assets/img/Koppel_bip.ico">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

<!-- DataTables JS -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<link rel="stylesheet" href="../assets/css/main.css">
<!-- <link rel="stylesheet" href="../assets/css/login.css"> -->
<link rel="stylesheet" href="../assets/css/mobile_sidebar.css">
<link rel="stylesheet" href="../assets/css/header.css">
</head>

<body>

<?php include "sidebar.php"; ?>
<div class="main">
    <?php include "header.php"; ?>
        <div class="content">
            <?php
                switch($page){

                    // Inventory pages
                    case "inventory/all_assets":
                        include "inventory/all_assets.php";
                        break;

                    case "inventory/transactions":
                        include "inventory/transactions.php";
                        break;

                    case "inventory/desktops":
                        include "inventory/desktops.php";
                        break;

                    case "inventory/laptops":
                        include "inventory/laptops.php";
                        break;

                    case "inventory/printers":
                        include "inventory/printers.php";
                        break;

                    case "inventory/ip_phones":
                        include "inventory/ip_phones.php";
                        break;

                    case "inventory/biometrics":
                        include "inventory/biometrics.php";
                        break;

                    case "inventory/networks":
                        include "inventory/networks.php";
                        break;

                    case "inventory/servers":
                        include "inventory/servers.php";
                        break;

                    case "inventory/credentials":
                        include "inventory/credentials.php";
                        break;

                    // Crud
                    case "inventory/crud/add_item":
                        include "inventory/crud/add_item.php";
                        break;

                    case "inventory/crud/edit_item":
                        include "inventory/crud/edit_item.php";
                        break;

                    case "inventory/crud/borrow_item":
                        include "inventory/crud/borrow_item.php";
                        break;
                    case 'process_borrow':
                        include 'inventory/crud/process_borrow.php';
                        break;

                    case 'process_issue':
                        include 'inventory/crud/process_issue.php';
                        break;

                    case "inventory/crud/issue_item":
                        include "inventory/crud/issue_item.php";
                        break;

                    case 'inventory/crud/return_item':
                        include 'inventory/crud/return_item.php';
                        break;

                    case 'process_return':
                        include 'inventory/crud/process_return.php';
                        break;

                    case 'inventory/crud/edit_transaction':
                        include 'inventory/crud/edit_transaction.php';
                        break;

                    case "inventory/crud/add_credential":
                        include "inventory/crud/add_credential.php";
                        break;
                    case "inventory/crud/edit_credential":
                        include "inventory/crud/edit_credential.php";
                        break;
                    case "inventory/crud/delete_credential":
                        include "inventory/crud/delete_credential.php";
                        break;
                    case "organization/crud/change_password":
                        include "organization/crud/change_password.php";
                        break;
                        
                    case "organization/crud/add_user":
                        include "organization/crud/add_user.php";
                        break;

                    case "organization/crud/edit_user":
                        include "organization/crud/edit_user.php";
                        break;

                    case "organization/crud/delete_user":
                        include "organization/crud/delete_user.php";
                        break;
                        
                        
                    // Print
                    case 'inventory/print/print_transaction':
                        include 'inventory/print/print_transaction.php';
                        break;

                    case 'inventory/print/print_transactions':
                        include 'inventory/print/print_transactions.php';
                        break;

                    case 'inventory/print/print_returned':
                        include 'inventory/print/print_returned.php';
                        break;

                    // Tickets
                    case "ticket/all_tickets":
                        include "ticket/all_tickets.php";
                        break;

                    case "ticket/assigned_tickets":
                        include "ticket/assigned_tickets.php";
                        break;
                    
                    case "ticket/requests":
                        include "ticket/requests.php";
                        break;

                    case "ticket/sla":
                        include "ticket/sla.php";
                        break;
                    
                    case "ticket/sla_settings":
                        include "ticket/sla_settings.php";  
                        break;

                    // Organization

                    case "organization/company":
                        include "organization/company.php";
                        break;

                    case "organization/departments":
                        include "organization/departments.php";
                        break;

                    case "organization/users":
                        include "organization/users.php";
                        break;

                    // case "user/users":
                    // include "user/users.php";
                    // break;

                    case "../forgot_password":
                        include "../forgot_password.php";
                        break;
                    case "../reset_password":
                        include "../reset_password.php";
                        break;

                    case "reports":
                    include "reports.php";
                    break;

                    case "settings":
                    include "settings.php";
                    break;

                    default:
                    include "dashboard.php";
                    break;
                }
            ?>
        </div>
    </div>
<!-- <script>
    document.addEventListener("DOMContentLoaded", function(){

        const sidebar = document.getElementById("sidebar");

        // Apply saved state immediately on page load
        const savedState = localStorage.getItem("sidebarState") || "collapsed";
        sidebar.classList.add(savedState);   // add "collapsed" or "open"
        sidebar.classList.remove(savedState === "collapsed" ? "open" : "collapsed");

        // Toggle sidebar
        window.toggleSidebar = function(){
            sidebar.classList.toggle("collapsed");

            if(sidebar.classList.contains("collapsed")){
                sidebar.classList.remove("open");
                localStorage.setItem("sidebarState","collapsed");
            } else {
                sidebar.classList.add("open");
                localStorage.setItem("sidebarState","open");
            }
        }

        // Dropdowns (unchanged)
        document.querySelectorAll(".dropdown-btn").forEach(btn => {
            btn.addEventListener("click", function(e){
                e.preventDefault();

                let submenu = this.nextElementSibling;

                document.querySelectorAll(".submenu").forEach(menu => {
                    if(menu !== submenu){
                        menu.classList.remove("show");
                    }
                });

                submenu.classList.toggle("show");
            });
        });

    });
</script> -->
<script>
    document.addEventListener("DOMContentLoaded", function(){

        const sidebar = document.getElementById("sidebar");

        // On page load, apply saved state without triggering transitions
        const savedState = localStorage.getItem("sidebarState");
        if(savedState === "open"){
            sidebar.classList.remove("collapsed");
            sidebar.classList.add("open");
        } else {
            sidebar.classList.add("collapsed");
            sidebar.classList.remove("open");
        }

        // Toggle sidebar
        window.toggleSidebar = function(){
            if(sidebar.classList.contains("collapsed")){
                sidebar.classList.remove("collapsed");
                sidebar.classList.add("open");
                localStorage.setItem("sidebarState","open");
            } else {
                sidebar.classList.add("collapsed");
                sidebar.classList.remove("open");
                localStorage.setItem("sidebarState","collapsed");
            }
        }

        // Dropdowns (unchanged)
        document.querySelectorAll(".dropdown-btn").forEach(btn => {
            btn.addEventListener("click", function(e){
                e.preventDefault();

                let submenu = this.nextElementSibling;

                document.querySelectorAll(".submenu").forEach(menu => {
                    if(menu !== submenu){
                        menu.classList.remove("show");
                    }
                });

                submenu.classList.toggle("show");
            });
        });

    });
</script>
<!-- mobile toggle -->
<script>
document.addEventListener("DOMContentLoaded", function() {

    const sidebar = document.getElementById("sidebar");
    const overlay = document.querySelector(".sidebar-overlay");
    const mobileToggleButtons = document.querySelectorAll(".btn-mobile-toggle");

    // Toggle drawer
    mobileToggleButtons.forEach(btn => {
        btn.addEventListener("click", function() {
            sidebar.classList.add("drawer-open");
            overlay.classList.add("active");
        });
    });

    // Close drawer on overlay click
    overlay.addEventListener("click", function() {
        sidebar.classList.remove("drawer-open");
        overlay.classList.remove("active");
    });

});
</script>
</body>
</html>