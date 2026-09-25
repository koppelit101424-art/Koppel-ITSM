<?php
include '../auth/auth.php';
include '../db/db.php';

// Your desired type order
$desiredTypeOrder = [6, 7, 1, 2, 4, 8, 5];

// Fetch all non-zero items with their type names
$sql = "
    SELECT 
        i.*, 
        it.type_name 
    FROM 
        item_tb i
    JOIN 
        item_type it ON i.type_id = it.type_id
    WHERE 
        i.quantity != 0
    ORDER BY 
        FIELD(i.type_id, " . implode(',', $desiredTypeOrder) . "), 
        i.item_id DESC
";
$result = $conn->query($sql);

// Group items by type_id
$itemsByType = [];
while ($row = $result->fetch_assoc()) {
    $tid = (int)$row['type_id'];
    if (!isset($itemsByType[$tid])) {
        $itemsByType[$tid] = [
            'type_name' => $row['type_name'],
            'items' => []
        ];
    }
    $itemsByType[$tid]['items'][] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Print Inventory by Type</title>
  <style>
    body { 
        font-family: Arial, sans-serif; 
        font-size: 11px;
        margin: 10px;
    }
    h3 {
        margin-top: 20px;
        margin-bottom: 8px;
        page-break-after: avoid;
    }
    table { 
        border-collapse: collapse; 
        width: 100%; 
        table-layout: fixed;
        margin-bottom: 16px;
        page-break-inside: avoid;
    }
    th, td { 
        border: 1px solid #000; 
        padding: 4px; 
        text-align: left; 
        word-wrap: break-word;
        overflow-wrap: break-word;
    }
    th { 
        background-color: #eee; 
    }
    th.code { width: 6%; }
    th.name { width: 10%; }
    th.brand { width: 8%; }
    th.model { width: 10%; }
    th.serial { width: 12%; }
    th.description { width: 22%; }
    th.quantity { width: 2%; }
    th.date { width: 8%; }
    @media print {
        button, .no-print { display: none; }
        body { margin: 0; }
    }
  </style>
</head>
<body>

<h2>Inventory List </h2>

<?php
foreach ($desiredTypeOrder as $typeId) {
    if (isset($itemsByType[$typeId]) && !empty($itemsByType[$typeId]['items'])) {
        $typeName = htmlspecialchars($itemsByType[$typeId]['type_name']);
        echo "<h3>{$typeName}</h3>\n";
        echo "<table>\n";
        echo "  <thead>\n";
        echo "    <tr>\n";
        echo "      <th class='code'>Code</th>\n";
        echo "      <th class='name'>Item</th>\n";
        echo "      <th class='brand'>Brand</th>\n";
        echo "      <th class='model'>Model</th>\n";
        echo "      <th class='serial'>Serial No.</th>\n";
        echo "      <th class='description'>Description</th>\n";
        echo "      <th class='quantity'>Qty</th>\n";
        echo "      <th class='date'>Date Received</th>\n";
        echo "    </tr>\n";
        echo "  </thead>\n";
        echo "  <tbody>\n";

        foreach ($itemsByType[$typeId]['items'] as $row) {
            echo "    <tr>\n";
            echo "      <td>" . htmlspecialchars($row['item_code']) . "</td>\n";
            echo "      <td>" . htmlspecialchars($row['name']) . "</td>\n";
            echo "      <td>" . htmlspecialchars($row['brand']) . "</td>\n";
            echo "      <td>" . htmlspecialchars($row['model']) . "</td>\n";
            echo "      <td>" . htmlspecialchars($row['serial_number']) . "</td>\n";
            echo "      <td>" . nl2br(htmlspecialchars($row['description'])) . "</td>\n";
            echo "      <td>" . (int)$row['quantity'] . "</td>\n";
            echo "      <td>" . date('m-d-Y', strtotime($row['date_received'])) . "</td>\n";
            echo "    </tr>\n";
        }

        echo "  </tbody>\n";
        echo "</table>\n";
    }
}
?>

<script>          
  // Auto print on page load
  window.onload = function() {
    window.print();
  };
</script>

</body>
</html>
<?php $conn->close(); ?>