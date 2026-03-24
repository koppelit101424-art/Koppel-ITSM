<?php
include __DIR__ . '/../../../includes/auth.php';
include __DIR__ . '/../../../includes/db.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $transaction_id = intval($_POST['transaction_id']);
    $item_id = intval($_POST['item_id']);
    $quantity_returned = intval($_POST['quantity_returned']);
    $date_returned = $_POST['date_returned'];
    $remarks = $conn->real_escape_string($_POST['remarks']);

    // Update transaction
    $update = "UPDATE transaction_tb 
               SET action = 'returned', quantity = $quantity_returned, date_returned = '$date_returned', remarks = '$remarks'
               WHERE transaction_id = $transaction_id";

    if ($conn->query($update) === TRUE) {
        // Update stock
        $stockUpdate = "UPDATE item_tb 
                        SET quantity = quantity + $quantity_returned 
                        WHERE item_id = $item_id";
        $conn->query($stockUpdate);

        // header("Location: ../transactions.php?msg=Item returned successfully");
    echo "<script>
        window.location.href='?page=inventory/transactions&msg=" . urlencode("Item returned successfully!") . "';
    </script>";
        exit;
    } else {
        echo "Error updating transaction: " . $conn->error;
    }
} else {
    echo "Invalid request.";
}

$conn->close();
?>
