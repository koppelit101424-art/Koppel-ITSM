<?php 
  // Fetch items with type name
  $sql = "
      SELECT i.*, t.type_name 
      FROM item_tb i
      LEFT JOIN item_type t ON i.type_id = t.type_id
      ORDER BY i.date_received DESC
  ";
  $result = $conn->query($sql);
  
  // Fetch item types
  $typeResult = $conn->query("SELECT type_id, type_name, description FROM item_type ORDER BY type_name ASC");
  
  // Fetch item names for filter
  $itemNames = $conn->query("SELECT DISTINCT name FROM item_tb ORDER BY name ASC");
?>