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

<!-- <i class="bi bi-chevron-down small user-arrow"></i> -->

</button>

<!-- <ul class="dropdown-menu dropdown-menu-end shadow">

<li>
<a class="dropdown-item" href="?page=profile">
<i class="bi bi-person me-2"></i> Profile
</a>
</li>

<li><hr class="dropdown-divider"></li>

<li>
<a class="dropdown-item text-danger" href="logout.php">
<i class="bi bi-box-arrow-right me-2"></i> Logout
</a>
</li>

</ul> -->

</div>

</div>
</div>


<!-- Drawer overlay -->
<div class="sidebar-overlay"></div>