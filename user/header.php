<?php
$fullname = $_SESSION['fullname'] ?? 'John Doe';

$nameParts = explode(" ", $fullname);

$initials = strtoupper(
    substr($nameParts[0],0,1) .
    (isset($nameParts[1]) ? substr($nameParts[1],0,1) : '')
);
?>

<div class="header d-flex align-items-center">



<button class="btn btn-light me-3" onclick="toggleSidebar()">
<i class="bi bi-list"></i>
</button>


<!-- LOGO -->
<img src="../assets/img/Koppel.png" class="sidebar-logo" alt="Koppel Inc">

<div class="ms-auto d-flex align-items-center gap-2">

<!-- NOTIFICATION -->
<!-- <button class="btn btn-light position-relative">
<i class="bi bi-bell"></i>
<span class="notification-badge">3</span>
</button> -->

<!-- USER DROPDOWN -->
<div class="dropdown">

<button class="btn user-dropdown d-flex align-items-center gap-2" data-bs-toggle="dropdown">

<div class="avatar-circle">
<?= $initials ?>
</div>

<span class="user-name">
<?= $fullname ?>
</span>

</button>

</div>
</div>
</div>


<!-- Drawer overlay -->
<!-- <div class="sidebar-overlay"></div> -->