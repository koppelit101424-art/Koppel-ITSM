<?php 
  // Fetch items with type name
  $sql = "
      SELECT *
      FROM credential_tb 
      ORDER BY cred_id ASC
      
  ";
  $result = $conn->query($sql);
  
?>