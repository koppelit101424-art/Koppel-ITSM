<?php
session_start();

$page = $_GET['page'] ?? 'dashboard';

include "../includes/auth.php";

/* PRINT PAGES (NO SIDEBAR / HEADER) */
$print_pages = [
    "inventory/print/print_transaction",
    "inventory/print/print_transactions",
    "inventory/print/print_returned",
    "ticket/print/print_request"
];

if (in_array($page, $print_pages)) {
    include $page . ".php";
    exit;
}
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
<link rel="stylesheet" href="../assets/css/mobile_sidebar.css">
<link rel="stylesheet" href="../assets/css/header.css">
<link rel="stylesheet" href="../assets/css/ticket.css">
<link rel="stylesheet" href="../assets/css/modal.css">
</head>

<body>
<?php 
if(isset($_GET['ajax'])){
    switch($_GET['ajax']){
        case "fetch_ticket_logs":
            include "ticket/includes/fetch_ticket_logs.php";
            exit;
        case "fetch_ticket_chat":
            include "ticket/includes/fetch_ticket_chat.php";
            exit;
        case "send_message":
            include "ticket/includes/send_message.php";
            exit;
        case "fetch_subject_details":
            include "ticket/includes/fetch_subject_details.php";
            exit;
        case "fetch_ticket_table":
            include "ticket/includes/fetch_ticket_table.php";
            exit;
        case "get_desktop_details":
            include "inventory/includes/get_desktop_details.php";
            break;
        case "get_models":
            include "includes/get_models.php";
            exit;
    }
}
?>
<?php if($page != "login"): ?>
<?php include "sidebar.php"; ?>
<div class="main">
<?php include "header.php"; ?>
<?php endif; ?>
        <div class="content">
            <?php
                switch($page){
                        case "login":
                        include "login.php";
                        break;
                    case "announcement/announcements":
                        include "announcement/announcements.php";
                        break;
                    // Inventory pages
                    case "inventory/all_assets":
                        include "inventory/all_assets.php";
                        break;
                    case "inventory/includes/generate_missing_qr":
                        include "inventory/includes/generate_missing_qr.php";
                        break;
                    case "inventory/includes/generate_desktop_qr":
                        include "inventory/includes/generate_desktop_qr.php";
                        break;
                    case "inventory/transactions":
                        include "inventory/transactions.php";
                        break;
                    case "inventory/desktops":
                        include "inventory/desktops.php";
                        break;
                    case "inventory/credentials":
                        include "inventory/credentials.php";
                        break;
                    case 'inventory/asset_listing':
                        include 'inventory/asset_listing.php';
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

                    case "inventory/tablets":
                        include "inventory/tablets.php";
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

                    // Crud
                    case "inventory/crud/add_item":
                        include "inventory/crud/add_item.php";
                        break;

                    case "inventory/crud/add_desktop":
                        include "inventory/crud/add_desktop.php";
                        break;
                    case "inventory/crud/edit_desktop":
                        include "inventory/crud/edit_desktop.php";
                        break;

                    case "inventory/includes/assign_desktop":
                        include "inventory/includes/assign_desktop.php";
                        break;

                    case "inventory/crud/edit_item":
                        include "inventory/crud/edit_item.php";
                        break;

                    case "inventory/crud/delete_item":
                        include "inventory/crud/delete_item.php";
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
                    case "organization/includes/user_assets":
                        include "organization/includes/user_assets.php";
                        break;
                    case "announcement/crud/delete_announcement":
                        include "announcement/crud/delete_announcement.php";
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
                    case 'ticket/print/print_request':
                        include 'ticket/print/print_request.php';
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
                    case "sla_ticket_logs":
                        include "ticket/includes/sla_ticket_logs.php";
                        exit;

                    // case "ticket/sla_ticket_logs":
                    //     include "ticket/sla_ticket_logs.php";
                    //     break;

                    case "ticket/view_ticket":
                        include "ticket/view_ticket.php";
                        break;
                    
                    case "ticket/includes/admin_tickets":
                        include "ticket/includes/admin_tickets.php";
                        break;
                    // case "ticket/includes/send_message":
                    //     include "ticket/includes/send_message.php";
                    //     break;
                    case "ticket/includes/update_ticket_field":
                        include "ticket/includes/update_ticket_field.php";
                        break;
                    case "ticket/includes/update_ticket_status":
                        include "ticket/includes/update_ticket_status.php";
                        break;
                    case "ticket/includes/update_status":
                        include "ticket/includes/update_status.php";
                        break;
                    case "ticket/includes/reassign_ticket":
                        include "ticket/includes/reassign_ticket.php";
                        break;

                    case "ticket/includes/image_modal":
                        include "ticket/includes/image_modal.php";
                        break;

                    case "ticket/crud/add_ticket":
                        include "ticket/crud/add_ticket.php";
                        break;

                    case "ticket/crud/add_request":
                        include "ticket/crud/add_request.php";
                        break;
                    case "ticket/crud/edit_request":
                        include "ticket/crud/edit_request.php";
                        break;
                    case "ticket/crud/delete_request":
                        include "ticket/crud/delete_request.php";
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
<?php if($page != "login"): ?>
</div>
<?php endif; ?>
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
                e.stopPropagation(); // ✅ prevent bubbling
                this.classList.toggle("open");
                let submenu = this.nextElementSibling;

                // ✅ find only siblings inside same parent
                let parent = this.parentElement;

                parent.querySelectorAll(":scope > .submenu").forEach(menu => {
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