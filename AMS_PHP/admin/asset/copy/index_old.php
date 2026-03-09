<?php
  include 'auth/auth.php';
  include 'db/db.php';
  // Fetch items with type name
  $sql = "
      SELECT i.*, t.type_name 
      FROM item_tb i
      LEFT JOIN item_type t ON i.type_id = t.type_id
      ORDER BY i.date_received DESC
  ";
  $result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Inventory System</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="asset/css/style.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
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
    .btn-blue {
      background-color: #33A1E0;
      color: #fff !important;
      border: 1px solid #33A1E0;
    }
    .btn-blue:hover {
      background-color: #1e8ac5;
      border-color: #1e8ac5;
    }

    .btn-outline-blue {
      background-color: #fff;
      color: #33A1E0 !important;
      border: 1px solid #33A1E0;
    }
    .btn-outline-blue:hover {
      background-color: #33A1E0;
      color: #fff !important;
    }

  </style>
</head>
<body>
  <?php $itemNames = $conn->query("SELECT DISTINCT name FROM item_tb ORDER BY name ASC"); ?>
    <div class="container-fluid">
      <div class="row">
        <?php include 'sidebar.php'; ?>
        <div class="col-md-10 main">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <h3 class="mb-0" style="color:#33A1E0; ">
              <i class="fas fa-boxes me-2" ></i> Inventory List
            </h3>
          <div class="d-flex align-items-center gap-2">
            <!-- Buttons -->
            <a href="inventory/add_item.php" class="btn btn-primary btn-sm" style="background-color: #33A1E0"> Add New Item</a>
            <a href="transaction/print_inventory.php" target="_blank" class="btn btn-secondary btn-sm">🖨 Print All</a>
          </div>
        </div><br>
        <?php
          // Fetch item types
          $typeResult = $conn->query("SELECT type_id, type_name, description FROM item_type ORDER BY type_name ASC");
        ?>
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">

        <!-- Type Filter Buttons -->
        <div class="d-flex flex-wrap gap-2">
          <button class="btn btn-sm btn-outline-blue type-filter active" data-type="">All</button>
          <?php while($t = $typeResult->fetch_assoc()): ?>
            <button class="btn btn-sm btn-outline-blue type-filter" data-type="<?= htmlspecialchars($t['type_name']) ?>">
              <?= htmlspecialchars($t['type_name']) ?>
            </button>
          <?php endwhile; ?>
        </div>

        <!-- Filter by Item Name -->
        <div style="width: 188px;">
          <select id="filterByName" class="form-select form-select-sm">
            <option value="">Select Item</option>
            <?php while($n = $itemNames->fetch_assoc()): ?>
              <option value="<?= htmlspecialchars($n['name']) ?>"><?= htmlspecialchars($n['name']) ?></option>
            <?php endwhile; ?>
          </select>
        </div>
      </div>
          <table id="inventoryTable" class="table table-bordered table-striped table-sm table-hover table-responsive">
            <thead >
              <tr>
                <th style="display:none;">ID</th>
                <th>Code</th>
                <th>Item</th>
                <th>Brand</th>
                <th>Model</th>
                <th>Serial No.</th>
                <th style="width: 350px; display: none">Description</th>
                <th>Qty</th>
                <th>Received</th>
                <th>Actions</th>
                <th style="display:none;">Type</th> <!-- Hidden column -->
              </tr>
            </thead>
            <tbody>
              <?php if ($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                  <tr>
                    <td style="display:none;"><?= $row['item_id'] ?></td>
                    <td><?= $row['item_code'] ?></td>
                    <td><?= $row['name'] ?></td>
                    <td><?= $row['brand'] ?></td>
                    <td><?= $row['model'] ?></td>
                    <td><?= $row['serial_number'] ?></td>
                    <td style="white-space: pre-wrap; width:250px; display:none"><?= htmlspecialchars($row['description']) ?></td>
                    <td><?= $row['quantity'] ?></td>
                    <td data-order="<?= date('Y-m-d', strtotime($row['date_received'])) ?>">
                      <?= date('m-d-Y', strtotime($row['date_received'])) ?>
                    </td>
                    <td>
                      <?php if ($row['quantity'] > 0): ?>
                        <span class="badge bg-success">Available</span>
                      <?php else: ?>
                          <span class="badge bg-danger">Out of Stock</span>
                      <?php endif; ?>
                    </td>
                    <td style="display:none;"><?= htmlspecialchars($row['type_name']) ?></td>
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
    <a href="#" id="editLink">Edit</a>
    <a href="#" id="borrowLink">Borrow</a>
    <a href="#" id="issueLink">Issue</a>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

  <script>
    $(document).ready(function() {
      const table = $('#inventoryTable').DataTable({
        "pageLength": 12,
        "lengthMenu": [5, 10, 25, 50, 100],
        "order": [[8, "desc"]] // Default sort by ID
      });

    // Filter by item name
    $('#filterByName').on('change', function() {
      const val = $(this).val();
      if (val) {
        table.column(2).search('^' + val + '$', true, false).draw(); 
      } else {
        table.column(2).search('').draw(); // Reset
      }
    });

    // Filter by item type via buttons
    $('.type-filter').on('click', function() {
      // Reset all buttons to outline-blue
      $('.type-filter')
        .removeClass('btn-blue active')
        .addClass('btn-outline-blue');

      // Set the clicked one to solid blue
      $(this)
        .removeClass('btn-blue')
        .addClass('btn-outline-blue active');

      const type = $(this).data('type');
      if (type) {
        table.column(10).search('^' + type + '$', true, false).draw();
      } else {
        table.column(10).search('').draw();
      }
    });

    });
    const contextMenu = document.getElementById('contextMenu');

    // Right-click on table row
    document.querySelectorAll('#inventoryTable tbody tr').forEach(row => {
        row.addEventListener('contextmenu', function(e) {
            e.preventDefault();

            // Get the quantity from the row (7th cell, index 6)
            const quantity = parseInt(this.querySelectorAll('td')[7].innerText);

            // Only show menu if quantity > 0
            if (quantity > 0) {
                const itemId = this.querySelector('td:first-child').innerText;

                // Set the links dynamically
                document.getElementById('borrowLink').href = 'inventory/borrow_item.php?item_id=' + itemId;
                document.getElementById('issueLink').href = 'inventory/issue_item.php?item_id=' + itemId;

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
    
    // Right-click on table row
    document.querySelectorAll('#inventoryTable tbody tr').forEach(row => {
          row.addEventListener('contextmenu', function(e) {
              e.preventDefault();

              const quantity = parseInt(this.querySelectorAll('td')[7].innerText);
              const itemId = this.querySelector('td:first-child').innerText;

              if (quantity >= 0) { // allow edit even if out of stock
                  // Set the links dynamically
                  document.getElementById('borrowLink').href = 'inventory/borrow_item.php?item_id=' + itemId;
                  document.getElementById('issueLink').href = 'inventory/issue_item.php?item_id=' + itemId;
                  document.getElementById('editLink').href   = 'inventory/edit_item.php?item_id=' + itemId; // ✅ Edit link

                  // Show menu at mouse position
                  contextMenu.style.display = 'block';
                  contextMenu.style.top = e.pageY + 'px';
                  contextMenu.style.left = e.pageX + 'px';
              }
          });
      });

    $(document).ready(function () {
      // Enable Bootstrap tooltips
      const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
      tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
      });

      // Add tooltip (hover description) for each row
      $('#inventoryTable tbody tr').each(function () {
        const desc = $(this).find('td:eq(6)').text(); // description column
        $(this).attr('data-bs-toggle', 'tooltip');
        $(this).attr('data-bs-placement', 'top');
        $(this).attr('title', desc);
      });

      // Row click → open modal with details
      $('#inventoryTable tbody').on('click', 'tr', function () {
        const tds = $(this).find('td');

        $('#modalItemCode').text(tds.eq(1).text());
        $('#modalItemName').text(tds.eq(2).text());
        $('#modalItemBrand').text(tds.eq(3).text());
        $('#modalItemModel').text(tds.eq(4).text());
        $('#modalItemSerial').text(tds.eq(5).text());
        $('#modalItemDesc').text(tds.eq(6).text());
        $('#modalItemQty').text(tds.eq(7).text());
        $('#modalItemReceived').text(tds.eq(8).text());
        $('#modalItemStatus').text(tds.eq(9).text());
        $('#modalItemType').text(tds.eq(10).text());

        const modal = new bootstrap.Modal(document.getElementById('itemDetailsModal'));
        modal.show();
      });
    });
  </script>
    <!-- Item Details Modal -->
    <div class="modal fade" id="itemDetailsModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow-sm rounded-3">
          <div class="modal-header bg-dark text-white py-2">
            <h6 class="modal-title">Item Details</h6>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <ul class="list-group list-group-flush small">
              <div class="row">
                <div class="col-6">
                  <li class="list-group-item"><strong>Code:</strong> <span id="modalItemCode"></span></li>
                  <li class="list-group-item"><strong>Item:</strong> <span id="modalItemName"></span></li>
                  <li class="list-group-item"><strong>Brand:</strong> <span id="modalItemBrand"></span></li>
                  <li class="list-group-item"><strong>Description:</strong><br><span id="modalItemDesc"  style="white-space: pre-wrap;"></span></li>
                </div>
                <div class="col-6">
                  <li class="list-group-item"><strong>Model:</strong> <span id="modalItemModel"></span></li>
                  <li class="list-group-item"><strong>Serial:</strong> <span id="modalItemSerial"></span></li>
                  <li class="list-group-item"><strong>Qty:</strong> <span id="modalItemQty"></span></li>
                  <li class="list-group-item"><strong>Received:</strong> <span id="modalItemReceived"></span></li>
                  <li class="list-group-item"><strong>Status:</strong> <span id="modalItemStatus"></span></li>
                  <li class="list-group-item"><strong>Type:</strong> <span id="modalItemType"></span></li>
                </div>
              </div>
            </ul>
          </div>
        </div>
      </div>
    </div>
</body>
</html>
<?php $conn->close(); ?>
