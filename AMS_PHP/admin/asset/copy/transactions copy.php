<?php
  include 'auth/auth.php';
  include 'db/db.php';

  // Fetch all transactions with item + user info
  $sql = "SELECT t.transaction_id, t.action, t.quantity, t.action_date, t.date_returned, t.remarks,
                i.name AS item_name, i.brand, i.model, it.type_name,
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Transactions</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="asset/css/style.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
  <style>
    /* Custom context menu style */
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
      color: #fff ;
      border: 1px solid #33A1E0;
    }
    .btn-blue:hover {
      background-color: #1e8ac5;
      border-color: #1e8ac5;
    }
    .btn-outline-blue {
      background-color: #fff;
      color: #33A1E0 ;
      border: 1px solid #33A1E0;
    }
    .btn-outline-blue:hover {
      background-color: #33A1E0;
      color: #fff ;
    }

  </style>
</head>
<?php
// Fetch unique item names for filter
$itemNames = $conn->query("SELECT DISTINCT name FROM item_tb ORDER BY name ASC");

 ?>
<body>
<div class="container-fluid">
  <div class="row">
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>
    <!-- Main Content -->
    <div class="col-md-10 main">
      <div class="d-flex justify-content-between align-items-center ">
    <h3 style="color: #33A1E0"><i class="fas fa-file-invoice-dollar me-2" ></i> Transactions</h3>
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 ">

      </div>
    </div>
    <div class="row">
      <div class="col-7">
      <!-- Type Filter Buttons -->
        <div class="d-flex flex-wrap gap-2" style="margin-bottom: 10px;">
          <button class="btn btn-sm btn-blue type-filter active" data-type="">All</button>
          <?php while($t = $typeResult->fetch_assoc()): ?>
            <button class="btn btn-sm btn-outline-blue type-filter" 
              data-type="<?= htmlspecialchars($t['type_name']) ?>">
              <?= htmlspecialchars($t['type_name']) ?>
            </button>
          <?php endwhile; ?>
        </div>


      <div class="col-2">
      <!-- Filter by Item Name -->
        <!-- <div style="width:190px; ">
          <select id="filterByName" class="form-select form-select-sm">
            <option value="">Select Item</option>
            <?php while($n = $itemNames->fetch_assoc()): ?>
              <option value="<?= htmlspecialchars($n['name']) ?>"><?= htmlspecialchars($n['name']) ?></option>
            <?php endwhile; ?>
          </select>
        </div> -->
      </div>
    </div>
      <!-- Success message -->
      <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          <?= htmlspecialchars($_GET['msg']) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>

      <table id="transactionTable" class="table table-bordered table-striped table-sm table-hover">
       
      <thead >
        <tr>
          <th>ID</th>
          <th>User</th>
          <th>Item</th>
          <th>Brand</th>
          <th>Model</th>
          <th style="display:none;">Remarks</th>
          <th >Qty</th>
          <th>Date</th>
          <th>Returned</th>
          <th>Status</th>
          <th style="display:none;">Type</th> <!-- hidden type column -->
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr data-transaction="<?= $row['transaction_id'] ?>" 
            data-user="<?= htmlspecialchars($row['fullname']) ?>" 
            data-item="<?= htmlspecialchars($row['item_name']) ?>" 
            data-brand="<?= htmlspecialchars($row['brand']) ?>" 
            data-model="<?= htmlspecialchars($row['model']) ?>" 
            data-qty="<?= $row['quantity'] ?>" 
            data-date="<?= date('m-d-Y', strtotime($row['action_date'])) ?>" 
            data-returned-label="<?= (empty($row['date_returned']) || $row['date_returned']=='0000-00-00 00:00:00') ? 'Not Returned' : date('m-d-Y', strtotime($row['date_returned'])) ?>" 
            data-returned="<?= (empty($row['date_returned']) || $row['date_returned']=='0000-00-00 00:00:00') ? '0' : '1' ?>" 
            data-remarks="<?= htmlspecialchars($row['remarks']) ?>" 
            data-status="<?= $row['action'] ?>">
            
            <td><?= $row['transaction_id'] ?></td>
            <td><?= $row['fullname'] ?></td>
            <td><?= $row['item_name'] ?></td>
            <td><?= $row['brand'] ?></td>
            <td><?= $row['model'] ?></td>
            <td style="display:none;"><?= $row['remarks'] ?></td>
            <td ><?= $row['quantity'] ?></td>
            <td style="width: 80px;"><?= date('m-d-Y', strtotime($row['action_date'])) ?></td>
            <td style="width: 100px;">
              <?php 
                if (empty($row['date_returned']) || $row['date_returned'] == '0000-00-00 00:00:00') 
                    echo '<span class="text-danger">Not Returned</span>';
                else 
                    echo date('m-d-Y', strtotime($row['date_returned']));
              ?>
            </td>
            <td>
              <?php if ($row['action'] == 'issued'): ?>
                  <span class="badge bg-warning text-dark">Issued</span>
              <?php elseif ($row['action'] == 'borrowed'): ?>
                  <span class="badge bg-primary">Borrowed</span>
              <?php elseif ($row['action'] == 'returned'): ?>
                  <span class="badge bg-success">Returned</span>
              <?php endif; ?>
            </td>
            <td style="display:none;"><?= htmlspecialchars($row['type_name']) ?></td> <!-- hidden type -->
        </tr>
        <?php endwhile; ?>
      </tbody>

      </table>
    </div>
  </div>
</div>
  <div id="contextMenu" class="custom-menu">
      <a href="#" id="editLink">Edit</a>
      <a href="#" id="returnLink">Return</a>
      <a href="#" id="printLink" target="_blank">Print Issuance</a>
      <a href="#" id="printReturnedLink" target="_blank" style="display:none;">Print Returned</a>
  </div>
  <!-- DataTables scripts -->
  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap5.min.js"></script>
  <script>
   $(document).ready(function () {
  var table = $('#transactionTable').DataTable({
    "pageLength": 12,
    "lengthMenu": [5, 10, 25, 50, 100],
    "order": [[0, "desc"]]
  });

  // Item filter (dropdown)
  $('#filterByName').on('change', function () {
    table.column(2).search(this.value).draw(); // column 2 = Item
  });

  // Type filter (buttons)
$('.type-filter').on('click', function() {
  // Reset all buttons to outline
  $('.type-filter')
    .removeClass('btn-blue active')
    .addClass('btn-outline-blue');

  // Set only the clicked one to blue
  $(this)
    .removeClass('btn-blue')
    .addClass('btn-blue active');

    const type = $(this).data('type');
    if (type) {
      table.column(10).search('^' + type + '$', true, false).draw(); 
    } else {
      table.column(10).search('').draw();
    }
  });
});

  </script>
  <script>
      const contextMenu = document.getElementById('contextMenu');
      const returnLink = document.getElementById('returnLink');
      const printLink = document.getElementById('printLink');
      const printReturnedLink = document.getElementById('printReturnedLink');
      const editLink = document.getElementById('editLink');

      document.querySelectorAll('#transactionTable tbody tr').forEach(row => {
          row.addEventListener('contextmenu', function(e) {
              e.preventDefault();

              const transactionId = this.dataset.transaction;
              const notReturned = this.dataset.returned === '0';

              // Default Issuance print
              printLink.href = 'transaction/print_transaction.php?id=' + transactionId;

              // Show/Hide Return link
              if (notReturned) {
                  returnLink.style.display = 'block';
                  returnLink.href = 'inventory/return_item.php?id=' + transactionId;
                  printReturnedLink.style.display = 'none';
              } else {
                  returnLink.style.display = 'none';
                  printReturnedLink.style.display = 'block';
                  printReturnedLink.href = 'transaction/print_returned.php?id=' + transactionId;
              }

              // Edit always visible
              editLink.href = 'transaction/edit_transaction.php?id=' + transactionId;

              // Show menu
              contextMenu.style.top = e.pageY + 'px';
              contextMenu.style.left = e.pageX + 'px';
              contextMenu.style.display = 'block';
          });
      });

    document.addEventListener('click', function() {
        contextMenu.style.display = 'none';
    });
// Enable Bootstrap tooltip globally
const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
tooltipTriggerList.map(function (tooltipTriggerEl) {
  return new bootstrap.Tooltip(tooltipTriggerEl);
});

  // Add tooltip + click event to rows
  $('#transactionTable tbody tr').each(function () {
    const remarks = $(this).data('remarks');
    const status = $(this).data('status');
    $(this).attr('data-bs-toggle', 'tooltip');
    $(this).attr('data-bs-placement', 'top');
    $(this).attr('title', `${remarks}`);
  });

// On row left-click → show modal
$('#transactionTable tbody').on('click', 'tr', function (e) {
  if (e.button !== 0) return; // only left click

  const row = $(this);

  $('#modalTransId').text(row.data('transaction'));
  $('#modalUser').text(row.data('user'));
  $('#modalItem').text(row.data('item'));
  $('#modalBrand').text(row.data('brand'));
  $('#modalModel').text(row.data('model'));
  $('#modalQty').text(row.data('qty'));
  $('#modalDate').text(row.data('date'));
  $('#modalReturned').text(row.data('returned-label')); // use label version
  $('#modalRemarks').text(row.data('remarks'));
  $('#modalStatus').text(row.data('status'));

  const modal = new bootstrap.Modal(document.getElementById('transactionModal'));
  modal.show();
});




  </script>
  <!-- Transaction Details Modal -->
    <div class="modal fade" id="transactionModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content shadow-sm rounded-3">
          <div class="modal-header bg-dark text-white py-2">
            <h6 class="modal-title">Transaction Details</h6>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body small">
            <div class="row g-2">
              <div class="col-6"><strong>ID:</strong><br><span id="modalTransId"></span></div>
              <div class="col-6"><strong>User:</strong><br><span id="modalUser"></span></div>
              <div class="col-6"><strong>Item:</strong><br><span id="modalItem"></span></div>
              <div class="col-6"><strong>Brand:</strong><br><span id="modalBrand"></span></div>
              <div class="col-6"><strong>Model:</strong><br><span id="modalModel"></span></div>
              <div class="col-6"><strong>Qty:</strong><br><span id="modalQty"></span></div>
              <div class="col-6"><strong>Date:</strong><br><span id="modalDate"></span></div>
              <div class="col-6"><strong>Returned:</strong><br><span id="modalReturned"></span></div>
              <div class="col-12"><strong>Remarks:</strong><br><span id="modalRemarks"></span></div>
              <div class="col-12"><strong>Status:</strong><br><span id="modalStatus"></span></div>
            </div>
          </div>
        </div>
      </div>
    </div>

  </body>
</html>
<?php $conn->close(); ?>
