<?php 
  // Fetch items with type name
  $sql = "
      SELECT *
      FROM item_tb 
      Where name = 'Server'
      
      ORDER BY item_id DESC
  ";
  $result = $conn->query($sql);
  
?>