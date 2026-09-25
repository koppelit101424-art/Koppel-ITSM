<?php
  // Fetch all transactions with item + user info
  $sql = "SELECT t.transaction_id, t.action, t.quantity, t.action_date, t.date_returned, t.remarks,
                i.name AS item_name, i.item_code, i.brand, i.model, i.serial_number, it.type_name,
                u.fullname
          FROM transaction_tb t
          JOIN item_tb i ON t.item_id = i.item_id
          JOIN item_type it ON i.type_id = it.type_id
          JOIN user_tb u ON t.user_id = u.user_id
          ORDER BY t.action_date DESC"; // newest first
  $result = $conn->query($sql);

  // Fetch item types for filter
  $typeResult = $conn->query("SELECT type_id, type_name, description FROM item_type ORDER BY type_name ASC");

  // Fetch unique item names for dropdown filter
  $itemNames = $conn->query("SELECT DISTINCT name FROM item_tb ORDER BY name ASC");

  // Fetch unique item names for filter
  $itemNames = $conn->query("SELECT DISTINCT name FROM item_tb ORDER BY name ASC");
?>