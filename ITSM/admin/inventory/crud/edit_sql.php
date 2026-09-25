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

// Fetch specs
$spec_sql = "SELECT * FROM laptop_pc_specs WHERE item_id = ?";
$spec_stmt = $conn->prepare($spec_sql);
$spec_stmt->bind_param("i", $item_id);
$spec_stmt->execute();
$specs = $spec_stmt->get_result()->fetch_assoc();
$spec_stmt->close();

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
    $condition_id = $_POST['condition'];

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
                   SET item_code=?, name=?, brand=?, model=?, serial_number=?, description=?, quantity=?, date_received=?, type_id=?, condition_id=? 
                   WHERE item_id=?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("ssssssissii", $item_code, $name, $brand, $model, $serial, $description, $quantity, $date_received, $type_id, $condition_id, $item_id);

    // Update specs if exist
    $cpu = $_POST['cpu'] ?? '';
    $ram = $_POST['ram'] ?? '';
    $rom = $_POST['rom'] ?? '';
    $motherboard = $_POST['motherboard'] ?? '';
    $os = $_POST['os'] ?? '';
    $key = $_POST['key'] ?? '';
    $antivirus = $_POST['antivirus'] ?? '';
    $comp_name = $_POST['comp_name'] ?? '';

    if ($cpu || $ram || $rom) {

        // Check if specs already exist
        $check = $conn->prepare("SELECT item_id FROM laptop_pc_specs WHERE item_id=?");
        $check->bind_param("i",$item_id);
        $check->execute();
        $check->store_result();

        if($check->num_rows > 0){

            $updateSpec = $conn->prepare("
            UPDATE laptop_pc_specs
            SET cpu=?, ram=?, rom=?, motherboard=?, os=?, `key`=?, antivirus=?, comp_name=?
            WHERE item_id=?");

            $updateSpec->bind_param(
                "ssssssssi",
                $cpu,$ram,$rom,$motherboard,$os,$key,$antivirus,$comp_name,$item_id
            );

            $updateSpec->execute();
            $updateSpec->close();

        } else {

            $insertSpec = $conn->prepare("
            INSERT INTO laptop_pc_specs
            (item_id,cpu,ram,rom,motherboard,os,`key`,antivirus,comp_name)
            VALUES (?,?,?,?,?,?,?,?,?)");

            $insertSpec->bind_param(
                "issssssss",
                $item_id,$cpu,$ram,$rom,$motherboard,$os,$key,$antivirus,$comp_name
            );

            $insertSpec->execute();
            $insertSpec->close();
        }
    }

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

    $conditionsArr = [];
    $condQuery = $conn->query("SELECT condition_id, condition_name FROM item_condition_tb ORDER BY condition_name DESC");

    while($c = $condQuery->fetch_assoc()){
        $conditionsArr[] = $c;
    }
?>