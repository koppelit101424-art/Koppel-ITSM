<?php
include __DIR__ . '/../../../includes/auth.php';
include __DIR__ . '/../../../includes/db.php';

if (!isset($_GET['user_id'])) {
    echo "<script>window.location.href='?page=organization/users';</script>";
    exit;
}

$user_id = intval($_GET['user_id']);

/* 🔍 Stronger check for ANY unreturned items */
$check = $conn->prepare("
    SELECT transaction_id 
    FROM transaction_tb 
    WHERE user_id = ?
    AND (date_returned IS NULL OR date_returned = '' OR date_returned = '0000-00-00')
    LIMIT 1
");
$check->bind_param("i", $user_id);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    // ❌ BLOCK DELETE
    echo "<script>
        alert('Cannot delete user. There are items still assigned to this user.');
        window.location.href='?page=organization/users';
    </script>";
    exit;
}
$check->close();

/* ✅ Proceed delete */
$stmt = $conn->prepare("DELETE FROM user_tb WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->close();

echo "<script>
    alert('User deleted successfully.');
    window.location.href='?page=organization/users';
</script>";

$conn->close();
?>