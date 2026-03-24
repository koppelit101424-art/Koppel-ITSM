<?php
  include '../../auth/auth.php';
  include '../../db/db.php';

// Fetch all items
// Fetch all items, sorted by ID (newest first)
$sql = "SELECT * FROM item_tb Where name = 'Network' ORDER BY item_id DESC";
$result = $conn->query($sql);

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Print Inventory</title>
  <style>
    body { 
        font-family: Arial, sans-serif; 
        font-size: 11px; /* smaller font */
    }
    table { 
        border-collapse: collapse; 
        width: 100%; 
        table-layout: fixed; /* fixed layout for wrapping */
    }
    th, td { 
        border: 1px solid #000; 
        padding: 4px; 
        text-align: left; 
        word-wrap: break-word; /* wrap long text */
        overflow-wrap: break-word;
    }
    th { 
        background-color: #eee; 
    }
    th.code { width: 5%; }
    th.name { width: 7%; }
    th.brand { width: 7%; }
    th.model { width: 10%; }
    th.serial { width: 10%; }
    th.description { width: 20%; }
    th.quantity { width: 2%; }
    th.date { width: 7%; }
    @media print {
        button { display: none; }
    }
  </style>
</head>
<body>

<h2>Network</h2>

<table>
  <thead>
    <tr>
       <th class="code">Code</th>
      <th class="name">Item</th>
      <th class="brand">Brand</th>
      <th class="model">Model</th>
      <th class="serial">Serial No.</th>
      <th class="description">Description</th>
      <th class="quantity">Qty</th>
      <th class="date">Date Received</th>
    </tr>
  </thead>
  <tbody>
    <?php if ($result->num_rows > 0): ?>
      <?php while($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?= $row['item_code'] ?></td>
          <td><?= $row['name'] ?></td>
          <td><?= $row['brand'] ?></td>
          <td><?= $row['model'] ?></td>
          <td><?= $row['serial_number'] ?></td>
          <td><?= $row['description'] ?></td>
          <td><?= $row['quantity'] ?></td>
          <td><?= date('m-d-Y', strtotime($row['date_received'])) ?></td>
        </tr>
      <?php endwhile; ?>
    <?php else: ?>
      <tr><td colspan="8">No items found</td></tr>
    <?php endif; ?>
  </tbody>
</table>

<script>
  // Auto print on page load
  window.onload = function() {
    window.print();
  };
</script>

</body>
</html>
<?php $conn->close(); ?>
