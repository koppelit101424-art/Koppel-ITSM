<?php
  include '../auth/auth.php';
  include '../db/db.php';

// Fetch all transactions with item + user info, sorted by action_date (newest first)
$sql = "SELECT t.transaction_id, t.action, t.quantity, t.action_date, t.date_returned, t.remarks,
                i.name AS item_name, i.brand, i.model, i.serial_number,
                u.fullname
          FROM transaction_tb t
          JOIN item_tb i ON t.item_id = i.item_id
          JOIN user_tb u ON t.user_id = u.user_id
          ORDER BY t.action_date DESC";
$result = $conn->query($sql);

// Group transactions by month
$transactionsByMonth = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $monthYear = date('F Y', strtotime($row['action_date'])); // Format: "November 2025"
        $transactionsByMonth[$monthYear][] = $row;
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
    <title>ITSM - Print Transactions</title>
  <link rel="icon" href="../asset/img/Koppel_bip.ico">
  <style>
    body { 
        font-family: Arial, sans-serif; 
        font-size: 11px; /* smaller font */
    }
    table { 
        border-collapse: collapse; 
        width: 100%; 
        table-layout: fixed; /* fixed layout for wrapping */
        margin-bottom: 20px;
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
    th.id { width: 2%; }
    th.user { width: 8%; }
    th.item { width: 8%; }
    th.brand { width: 7%; }
    th.model { width: 8%; }
    th.serial { width: 8%; }
    th.quantity { width: 3%; }
    th.date { width: 7%; }
    th.returned { width: 7%; }
    th.status { width: 5%; }
    th.remarks { width: 15%; }
    .month-header {
        background-color: #ddd;
        padding: 8px;
        font-weight: bold;
        font-size: 13px;
        margin-top: 15px;
        border: 1px solid #000;
    }
    @media print {
        button { display: none; }
    }
  </style>
</head>
<body>

<h2>Transaction List</h2>

<?php if (!empty($transactionsByMonth)): ?>
    <?php foreach ($transactionsByMonth as $monthYear => $transactions): ?>
        <div class="month-header"><?php echo $monthYear; ?></div>
        <table>
            <thead>
                <tr>
                    <th class="id">ID</th>
                    <th class="user">User</th>
                    <th class="item">Item</th>
                    <th class="brand">Brand</th>
                    <th class="model">Model</th>
                    <th class="serial">Serial</th>
                    <th class="quantity">Qty</th>
                    <th class="date">Date</th>
                    <th class="returned">Returned</th>
                    <th class="status">Status</th>
                    <th class="remarks">Remarks</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($transactions as $row): ?>
                    <tr>
                        <td><?= $row['transaction_id'] ?></td>
                        <td><?= $row['fullname'] ?></td>
                        <td><?= $row['item_name'] ?></td>
                        <td><?= $row['brand'] ?></td>
                        <td><?= $row['model'] ?></td>
                        <td><?= $row['serial_number'] ?></td>
                        <td><?= $row['quantity'] ?></td>
                        <td><?= date('m-d-Y', strtotime($row['action_date'])) ?></td>
                        <td>
                            <?php 
                                if (empty($row['date_returned']) || $row['date_returned'] == '0000-00-00 00:00:00') 
                                    echo 'Not Returned';
                                else 
                                    echo date('m-d-Y', strtotime($row['date_returned']));
                            ?>
                        </td>
                        <td>
                            <?php 
                                if ($row['action'] == 'issued'): 
                                    echo 'Issued';
                                elseif ($row['action'] == 'borrowed'): 
                                    echo 'Borrowed';
                                elseif ($row['action'] == 'returned'): 
                                    echo 'Returned';
                                endif; 
                            ?>
                        </td>
                        <td><?= $row['remarks'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endforeach; ?>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th class="id">ID</th>
                <th class="user">User</th>
                <th class="item">Item</th>
                <th class="brand">Brand</th>
                <th class="model">Model</th>
                <th class="serial">Serial</th>
                <th class="quantity">Qty</th>
                <th class="date">Date</th>
                <th class="returned">Returned</th>
                <th class="status">Status</th>
                <th class="remarks">Remarks</th>
            </tr>
        </thead>
        <tbody>
            <tr><td colspan="11">No transactions found</td></tr>
        </tbody>
    </table>
<?php endif; ?>

<script>
  // Auto print on page load
  window.onload = function() {
    window.print();
  };
</script>

</body>
</html>
<?php $conn->close(); ?>