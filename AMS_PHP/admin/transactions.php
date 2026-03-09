
<?php
  include 'auth/auth.php';
  include 'db/db.php';
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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ITSM - Transactions</title>
    <link rel="icon" href="asset/img/Koppel_bip.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="asset/css/main.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
</head>
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
<body>
<!-- Sidebar -->
<?php include 'sidebar.php'; ?>
    <!-- Main Content -->
    <div class="main-content" id="mainContent">
    <?php include 'header.php'; ?>
        <?php include 'transaction/trans_table.php'; ?>
    </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
        <!-- <script src="asset/js/trans.js"></script> -->
         <script>
          $(document).ready(function () {

          var table = $('#transactionTable').DataTable({
            pageLength: 10,
            lengthMenu: [5, 10, 25, 50, 100],
            order: [[0, "desc"]],
            language: {
              search: "Search within table:"
            }
          });

          /* ===============================
            GLOBAL SEARCH
          =============================== */
          $('#globalSearch').on('keyup', function () {
            table.search(this.value).draw();
          });

          /* ===============================
            ITEM FILTER (Dropdown)
          =============================== */
          $('#filterByName').on('change', function () {
            table.column(2).search(this.value).draw();
          });

          /* ===============================
            TYPE FILTER (Buttons)
          =============================== */
          $('.type-filter').on('click', function () {

            $('.type-filter')
              .removeClass('btn-blue active')
              .addClass('btn-outline-blue');

            $(this)
              .removeClass('btn-outline-blue')
              .addClass('btn-blue active');

            const type = $(this).data('type');

            if (type) {
              table.column(11).search('^' + type + '$', true, false).draw();
            } else {
              table.column(11).search('').draw();
            }
          });

          /* ===============================
            AFTER TABLE DRAW (IMPORTANT)
            Fix context menu + tooltip
          =============================== */
          table.on('draw', function () {

            // Remove old tooltips
            $('[data-bs-toggle="tooltip"]').tooltip('dispose');

            // Reapply tooltip
            $('#transactionTable tbody tr').each(function () {
              const remarks = $(this).data('remarks');
              $(this).attr('data-bs-toggle', 'tooltip');
              $(this).attr('data-bs-placement', 'top');
              $(this).attr('title', remarks);
            });

            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
              return new bootstrap.Tooltip(tooltipTriggerEl);
            });

          });

          /* ===============================
            ROW CLICK → MODAL
          =============================== */
          $('#transactionTable tbody').on('click', 'tr', function (e) {

            if (e.button !== 0) return;

            $('#transactionTable tbody tr').removeClass('table-primary');
            $(this).addClass('table-primary');

            const row = $(this);

            $('#modalTransId').text(row.data('transaction'));
            $('#modalUser').text(row.data('user'));
            $('#modalItem').text(row.data('item'));
            $('#modalBrand').text(row.data('brand'));
            $('#modalModel').text(row.data('model'));
            $('#modalQty').text(row.data('qty'));
            $('#modalDate').text(row.data('date'));
            $('#modalReturned').text(row.data('returned-label'));
            $('#modalRemarks').text(row.data('remarks'));
            $('#modalStatus').text(row.data('status'));

            const modal = new bootstrap.Modal(document.getElementById('transactionModal'));
            modal.show();
          });

          /* ===============================
            CONTEXT MENU (RIGHT CLICK)
          =============================== */
          const contextMenu = document.getElementById('contextMenu');
          const returnLink = document.getElementById('returnLink');
          const printLink = document.getElementById('printLink');
          const printReturnedLink = document.getElementById('printReturnedLink');
          const editLink = document.getElementById('editLink');

          $('#transactionTable tbody').on('contextmenu', 'tr', function (e) {

            e.preventDefault();

            const transactionId = this.dataset.transaction;
            const notReturned = this.dataset.returned === '0';

            printLink.href = 'transaction/print_transaction.php?id=' + transactionId;

            if (notReturned) {
              returnLink.style.display = 'block';
              returnLink.href = 'transaction/return_item.php?id=' + transactionId;
              printReturnedLink.style.display = 'none';
            } else {
              returnLink.style.display = 'none';
              printReturnedLink.style.display = 'block';
              printReturnedLink.href = 'transaction/print_returned.php?id=' + transactionId;
            }

            editLink.href = 'transaction/edit_transaction.php?id=' + transactionId;

            contextMenu.style.top = e.pageY + 'px';
            contextMenu.style.left = e.pageX + 'px';
            contextMenu.style.display = 'block';
          });

          $(document).on('click', function () {
            contextMenu.style.display = 'none';
          });

        });
         </script>

        <?php include 'transaction/trans_modal.php'; ?>
</body>
</html>