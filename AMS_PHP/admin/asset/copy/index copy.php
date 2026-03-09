<?php
include 'db.php';

// Fetch items with type name
$sql = "
    SELECT i.*, t.type_name 
    FROM item_tb i
    LEFT JOIN item_type t ON i.type_id = t.type_id
    ORDER BY i.created_at DESC
";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Inventory System</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="style.css" rel="stylesheet">
  <style>
.custom-menu {
    display: none;
    position: absolute;
    z-index: 1000;
    background-color: #fff;
    border: 1px solid #ccc;
    padding: 5px;
    border-radius: 5px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.2);
}
.custom-menu a {
    display: block;
    padding: 5px 10px;
    color: #000;
    text-decoration: none;
}
.custom-menu a:hover {
    background-color: #f0f0f0;
}
</style>


  <!-- ✅ DataTables CSS -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
</head>
<body>
<div class="container-fluid">
  <div class="row">
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>

    <!-- Main Content -->
    <div class="col-md-10 main">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">📦 Inventory List</h3>
          <div>
              <a href="add_item.php" class="btn btn-primary btn-sm">➕ Add New Item</a>
              <a href="print_inventory.php" target="_blank" class="btn btn-secondary btn-sm ms-2">🖨 Print All</a>

          </div>
      </div>

     <table id="inventoryTable" class="table table-bordered table-striped table-sm mt-3">

        <thead class="table-dark">
          <tr>
            <th>ID</th>
            <th>Type</th>
            <th>Item</th>
            <th>Brand</th>
            <th>Model</th>
            <th>Serial No.</th>
            <th>Description</th>
            <th>Quantity</th>
            <th>Received </th>   
            
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php if ($result->num_rows > 0): ?>
          <?php while($row = $result->fetch_assoc()): ?>
            <tr>
              <td><?= $row['item_id'] ?></td>
              <td><?= $row['type_name'] ?? 'N/A' ?></td> <!-- Display type name -->
              <td><?= $row['name'] ?></td>
              <td><?= $row['brand'] ?></td>
              <td><?= $row['model'] ?></td>
              <td><?= $row['serial_number'] ?></td>
              <td><?= $row['description'] ?></td>
              <td><?= $row['quantity'] ?></td>
              <td><?= date('m-d-Y', strtotime($row['date_received'])) ?></td>
              <td>
                <?php if ($row['quantity'] > 0): ?>
                  <span class="badge bg-success">Available</span>
                <?php else: ?>
                  <span class="badge bg-danger">Out of Stock</span>
                <?php endif; ?>
              </td>
            </tr>

          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="9" class="text-center">No items found</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<div id="contextMenu" class="custom-menu">
    <a href="#" id="borrowLink">Borrow</a>
    <a href="#" id="issueLink">Issue</a>
</div>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- ✅ jQuery + DataTables JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
  $(document).ready(function() {
      $('#inventoryTable').DataTable({
            "pageLength": 9,
            "lengthMenu": [5, 10, 25, 50, 100],
            "order": [[0, "desc"]] // Sort by first column (ID) descending
        });

  });
</script>
<script>
const contextMenu = document.getElementById('contextMenu');

// Right-click on table row
document.querySelectorAll('#inventoryTable tbody tr').forEach(row => {
    row.addEventListener('contextmenu', function(e) {
        e.preventDefault();

        // Get the quantity from the row (7th cell, index 6)
        const quantity = parseInt(this.querySelectorAll('td')[6].innerText);

        // Only show menu if quantity > 0
        if (quantity > 0) {
            const itemId = this.querySelector('td:first-child').innerText;

            // Set the links dynamically
            document.getElementById('borrowLink').href = 'borrow_item.php?item_id=' + itemId;
            document.getElementById('issueLink').href = 'issue_item.php?item_id=' + itemId;

            // Show menu at mouse position
            contextMenu.style.display = 'block';
            contextMenu.style.top = e.pageY + 'px';
            contextMenu.style.left = e.pageX + 'px';
        }
    });
});

// Hide menu on click elsewhere
document.addEventListener('click', function() {
    contextMenu.style.display = 'none';
});

//Print

</script>
</body>
</html>
<?php $conn->close(); ?>
