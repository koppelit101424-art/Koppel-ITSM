<?php 
  // Fetch items with type name
  $sql = "
      SELECT i.*, t.type_name 
      FROM item_tb i
      LEFT JOIN item_type t ON i.type_id = t.type_id
      
      ORDER BY i.item_id DESC
  ";
//   $sql = "SELECT name, quantity, type_id FROM item_tb WHERE quantity > 0 ORDER BY quantity DESC LIMIT 10";
  $result = $conn->query($sql);
  
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