<?php 
  // Fetch items with type name
  $sql = "
      SELECT *
      FROM item_tb 
      Where name = 'Biometrics'
      
      ORDER BY item_id DESC
  ";
  $result = $conn->query($sql);
  
?>