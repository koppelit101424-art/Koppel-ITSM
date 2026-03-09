<?php
http_response_code(403);
require_once '../config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>403 - Access Denied</title>
<link rel="icon" href="<?= BASE_URL ?>/asset/img/Koppel.ico">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center vh-100">

<div class="text-center">
    <h1 class="display-4 text-danger">403</h1>
    <p class="fs-4">Access Denied</p>
    <p class="text-muted">You don’t have permission to access this page.</p>

    <a href="<?= BASE_URL ?>/user/login.php" class="btn btn-primary mt-3">
        <i class="fa fa-home"></i> Go Home
    </a>
</div>

</body>
</html>
