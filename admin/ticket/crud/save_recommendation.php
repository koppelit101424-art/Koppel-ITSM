<?php

include __DIR__.'/../../../includes/auth.php';
include __DIR__.'/../../../includes/db.php';

$request_id = (int)$_POST['request_id'];
$recommendation = trim($_POST['recommendation']);
$recommended_by = $_SESSION['user_id'];

$stmt = $conn->prepare("
    INSERT INTO request_recommendations
    (
        request_id,
        recommendation,
        recommended_by
    )
    VALUES
    (
        ?, ?, ?
    )
");

$stmt->bind_param(
    "isi",
    $request_id,
    $recommendation,
    $recommended_by
);

$stmt->execute();

echo "<script>
    window.location.href='?page=ticket/view_request&request_id=" . $request_id . "';
</script>";
exit;
// header("Location: ../../index.php?page=ticket/view_request&request_id=".$request_id);
exit;