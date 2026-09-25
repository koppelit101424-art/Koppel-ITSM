<?php
include __DIR__ . '/../../../includes/auth.php';
include __DIR__ . '/../../../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $item_id  = $_POST['item_id'];
    $user_id  = $_POST['user_id'];
    $quantity = $_POST['quantity'];
    $remarks  = $_POST['remarks'];

    // Insert transaction
    $sql = "INSERT INTO transaction_tb 
            (item_id, user_id, action, quantity, remarks, date_returned) 
            VALUES (?, ?, 'borrowed', ?, ?, '0000-00-00')";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiis", $item_id, $user_id, $quantity, $remarks);
    $stmt->execute();

    // Update inventory quantity
    $sql2 = "UPDATE item_tb SET quantity = quantity - ? WHERE item_id = ?";
    $stmt2 = $conn->prepare($sql2);
    $stmt2->bind_param("ii", $quantity, $item_id);
    $stmt2->execute();

    // Redirect to inventory
    // header("Location: ?page=inventory/all_assets&msg=" . urlencode("Item borrowed successfully"));

      echo "<script>
        window.location.href='?page=inventory/all_assets&msg=" . urlencode("Item borrowed successfully!") . "';
    </script>";
    exit;
}