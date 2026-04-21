<?php
session_start();

$page = $_GET['page'] ?? 'announcements';

include "includes/auth.php";
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
<link rel="stylesheet" href="../assets/css/ticket.css">
<link rel="stylesheet" href="../assets/css/modal.css">
<link rel="stylesheet" href="../assets/css/chat_bot.css">
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
        case "fetch_subject_details":
            include "ticket/includes/fetch_subject_details.php";
            exit;
        case "fetch_tickets":
            include "ticket/includes/fetch_tickets.php";
            exit;
        case "sla_ticket_logs":
            include "ticket/sla_ticket_logs.php";
            exit;
        case "send_message":
            include "ticket/includes/send_message.php";
            exit;
        case "submit_rating":
            include "ticket/includes/submit_rating.php";
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
                    // Tickets
                    case "ticket/all_tickets":
                        include "ticket/all_tickets.php";
                        break;
                    case "ticket/view":
                        include "ticket/view.php";
                        break;
                    case "ticket/includes/add":
                        include "ticket/includes/add.php";
                        break;
                    case "ticket/requests":
                        include "ticket/requests.php";
                        break;
                    case "ticket/includes/add_request":
                        include "ticket/includes/add_request.php";
                        break;
                    case "transaction/transactions":
                        include "transaction/transactions.php";
                        break;
                    case "chat_bot":
                        include "chat_bot.php";
                        break;
    
                }
            ?>
        </div>
<?php if($page != "login"): ?>
</div>
<?php endif; ?>

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