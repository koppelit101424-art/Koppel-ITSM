<?php
http_response_code(404);
require_once '../config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>404 - Page Not Found</title>
<link rel="icon" href="<?= BASE_URL ?>/asset/img/Koppel.ico">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center vh-100">

<div class="text-center">
    <h1 class="display-4 text-warning">404</h1>
    <p class="fs-4">Page Not Found</p>
    <p class="text-muted">The page you’re looking for doesn’t exist.</p>

    <a href="<?= BASE_URL ?>" class="btn btn-primary mt-3">
        <i class="fa fa-home"></i> Go Home
    </a>
</div>

</body>
</html>
