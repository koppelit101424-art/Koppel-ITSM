<?php
include __DIR__ . '/../../../includes/auth.php';
include __DIR__ . '/../../../includes/db.php';

if (!isset($_GET['item_id'])) {
    echo "<script>window.location.href='?page=inventory/all_assets';</script>";
    exit;
}

$item_id = intval($_GET['item_id']);

/* 🔍 Check if item is currently issued (NOT returned) */
$check = $conn->prepare("
    SELECT transaction_id 
    FROM transaction_tb 
    WHERE item_id = ?
    AND (date_returned IS NULL OR date_returned = '' OR date_returned = '0000-00-00')
    LIMIT 1
");
$check->bind_param("i", $item_id);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    // ❌ BLOCK DELETE
    echo "<script>
        alert('Cannot delete item. It is currently issued to a user.');
        window.location.href='?page=inventory/all_assets';
    </script>";
    exit;
}
$check->close();

/* ✅ Safe to delete */
$stmt = $conn->prepare("DELETE FROM item_tb WHERE item_id = ?");
$stmt->bind_param("i", $item_id);
$stmt->execute();
$stmt->close();

echo "<script>
    alert('Item deleted successfully.');
    window.location.href='?page=inventory/all_assets';
</script>";

$conn->close();
?>