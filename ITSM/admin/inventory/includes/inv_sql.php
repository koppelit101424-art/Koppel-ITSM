<?php 

  
//   / FILTER VALUES
$filterItem = $_GET['item'] ?? '';
$filterBrand = $_GET['brand'] ?? '';
$filterStatus = $_GET['status'] ?? '';
$filterType = $_GET['type'] ?? '';
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';

// Fetch items
$itemsArr = [];
$itemQuery = $conn->query("SELECT DISTINCT name FROM item_tb ORDER BY name ASC");
while($i = $itemQuery->fetch_assoc()){
    $itemsArr[] = $i['name'];
}

// Fetch brands
$brandsArr = [];
$brandQuery = $conn->query("SELECT DISTINCT brand FROM item_tb ORDER BY brand ASC");
while($b = $brandQuery->fetch_assoc()){
    $brandsArr[] = $b['brand'];
}

// Fetch types
$typesArr = [];
$typeQuery = $conn->query("SELECT type_id, type_name FROM item_type ORDER BY type_name ASC");
while($t = $typeQuery->fetch_assoc()){
    $typesArr[] = $t;
}

$sql = "SELECT 
            i.*, 
            t.type_name,

            s.cpu,
            s.ram,
            s.rom,
            s.motherboard,
            s.os,
            s.`key`,
            s.antivirus,
            s.comp_name,
            c.condition_name,
            q.qr_code_path

        FROM item_tb i

        LEFT JOIN item_type t 
            ON i.type_id = t.type_id

        LEFT JOIN laptop_pc_specs s 
            ON i.item_id = s.item_id

        LEFT JOIN item_condition_tb c 
             ON i.condition_id = c.condition_id
            
        LEFT JOIN qr_tb q ON i.item_id = q.item_id

        WHERE 1=1";

$params = [];
$types = "";

if($filterItem){
    $sql .= " AND i.name = ?";
    $params[] = $filterItem;
    $types .= "s";
}

if($filterBrand){
    $sql .= " AND i.brand = ?";
    $params[] = $filterBrand;
    $types .= "s";
}

if($filterType){
    $sql .= " AND i.type_id = ?";
    $params[] = $filterType;
    $types .= "i";
}

if($filterStatus){
    if($filterStatus == "stock"){
        $sql .= " AND i.quantity > 0";
    }
    elseif($filterStatus == "use"){
        $sql .= " AND i.quantity = 0 AND i.type_id != 8";
    }
    elseif($filterStatus == "consumed"){
        $sql .= " AND i.type_id = 8";
    }
}

if($dateFrom && $dateTo){
    $sql .= " AND i.date_received BETWEEN ? AND ?";
    $params[] = $dateFrom;
    $params[] = $dateTo;
    $types .= "ss";
}

$sql .= " ORDER BY i.date_received DESC";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("SQL Error: " . $conn->error);
}

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute(); // ✅ REQUIRED

$result = $stmt->get_result(); // ✅ NOW VALID

  
  // Fetch item types ORDER BY i.date_received DESC 
  $typeResult = $conn->query("SELECT type_id, type_name, description FROM item_type ORDER BY type_name ASC");
  
  // Fetch item names for filter
  $itemNames = $conn->query("SELECT DISTINCT name FROM item_tb ORDER BY name ASC");

    // Fetch data for charts
  // 1. Asset count by category with available and issued counts
  $catQuery = "
      SELECT 
          t.type_name,
          COUNT(*) as total,
          SUM(CASE WHEN i.quantity > 0 THEN 1 ELSE 0 END) as available,
          SUM(CASE WHEN i.quantity = 0 THEN 1 ELSE 0 END) as issued
      FROM item_tb i
      LEFT JOIN item_type t ON i.type_id = t.type_id
      GROUP BY t.type_name
      ORDER BY total DESC
  ";
  $catResult = $conn->query($catQuery);

  // 2. Asset status counts
  $statusQuery = "
      SELECT 
          SUM(CASE WHEN quantity > 0 THEN 1 ELSE 0 END) as available,
          SUM(CASE WHEN quantity = 0 THEN 1 ELSE 0 END) as out_of_stock
      FROM item_tb
  ";
  $statusResult = $conn->query($statusQuery);
  $statusRow = $statusResult->fetch_assoc();
  $available = $statusRow['available'] ?? 0;
  $outOfStock = $statusRow['out_of_stock'] ?? 0;

  // 3. Monthly asset additions (last 6 months)
  $monthQuery = "
      SELECT 
          DATE_FORMAT(date_received, '%b %Y') as month,
          COUNT(*) as count
      FROM item_tb 
      WHERE date_received >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
      GROUP BY DATE_FORMAT(date_received, '%Y-%m')
      ORDER BY date_received
  ";
  $monthResult = $conn->query($monthQuery);

  // --- NEW: Query for available stock per item (excluding zero stock & duplicates) ---
  $stockPerItemSql = "
      SELECT 
          name, 
          SUM(quantity) AS total_quantity,
          type_id
      FROM item_tb 
      WHERE quantity > 0 
        AND type_id != 5
      GROUP BY name
      ORDER BY total_quantity DESC
  ";
  $stockPerItemResult = $conn->query($stockPerItemSql);

  // Prepare data for the new chart
  $itemLabels = [];
  $itemStocks = [];
  $itemType = [];
  if ($stockPerItemResult) {
      while ($row = $stockPerItemResult->fetch_assoc()) {
          $itemLabels[] = $row['name'];
          $itemStocks[] = (int)$row['total_quantity']; // Use aggregated total
          $itemType[] = (int)$row['type_id'];
      }
  }
?>
 
