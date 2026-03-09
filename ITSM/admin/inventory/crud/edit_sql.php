<?php 
$message = "";

// Get item id
if (!isset($_GET['item_id'])) {
    die("❌ Item ID is missing.");
}
$item_id = intval($_GET['item_id']);

// Fetch item types
$type_sql = "SELECT type_id, type_name FROM item_type ORDER BY type_name ASC";
$type_result = $conn->query($type_sql);

// Fetch existing item
$item_sql = "SELECT * FROM item_tb WHERE item_id = ?";
$stmt = $conn->prepare($item_sql);
$stmt->bind_param("i", $item_id);
$stmt->execute();
$item = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$item) {
    die("❌ Item not found.");
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $item_code         = trim($_POST['item_code']);
    $name         = trim($_POST['name']);
    $brand        = $_POST['brand'];
    $model        = $_POST['model'];
    $serial       = strtoupper(trim($_POST['serial_number']));
    $description  = $_POST['description'];
    $quantity     = $_POST['quantity'];
    $date_received= $_POST['date_received'];
    $type_id      = $_POST['type_id'];

    // ===== Auto-generate Item Code (same logic as add_item.php) =====
    $prefix = strtoupper(substr($name, 0, 3));
    $last4  = ($serial === "" || $serial === "N/A") ? "0000" : substr($serial, -4);

    $count_sql = "SELECT COUNT(*) as total FROM item_tb WHERE LOWER(name) = LOWER(?)";
    $stmt_count = $conn->prepare($count_sql);
    $stmt_count->bind_param("s", $name);
    $stmt_count->execute();
    $res = $stmt_count->get_result();
    $row = $res->fetch_assoc();
    $count = $row['total']; // don’t +1 when editing
    $stmt_count->close();



    // Update
    $update_sql = "UPDATE item_tb 
                   SET item_code=?, name=?, brand=?, model=?, serial_number=?, description=?, quantity=?, date_received=?, type_id=?
                   WHERE item_id=?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("ssssssissi", $item_code, $name, $brand, $model, $serial, $description, $quantity, $date_received, $type_id, $item_id);

if ($stmt->execute()) {

    echo "<script>
        window.location.href='?page=inventory/all_assets&msg=" . urlencode("Item updated successfully! Code: $item_code") . "';
    </script>";
    exit();
    } else {
        $message = "❌ Error: " . $stmt->error;
    }
    $stmt->close();
}
?>