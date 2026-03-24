<?php
include __DIR__ . '/../../../includes/auth.php';
include __DIR__ . '/../../../includes/db.php';
$fullname = $_SESSION['fullname'];
// ✅ Get LMR No from URL (not request_id)
$lmr_no = trim($_GET['lmr_no'] ?? '');

if (empty($lmr_no)) {
    die('LMR No is required.');
}

// ✅ Fetch ALL rows with this LMR No
$sql = "SELECT * FROM request_tb WHERE lmr_no = ? ORDER BY request_id ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $lmr_no);
$stmt->execute();
$result = $stmt->get_result();

$items = [];
$shared = null;

while ($row = $result->fetch_assoc()) {
    if ($shared === null) {
        // Capture shared info from first row
        $shared = [
            'lmr_no' => htmlspecialchars($row['lmr_no']),
            'requestor' => htmlspecialchars($row['requestor']),
            'department' => htmlspecialchars($row['department']),
            'date_created' => !empty($row['date_created']) && $row['date_created'] !== '0000-00-00'
                ? date('F j, Y', strtotime($row['date_created']))
                : ''
        ];
    }
    $items[] = $row;
}

if (empty($items)) {
    die('No items found for LMR No: ' . htmlspecialchars($lmr_no));
}

$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
      <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
  <title>Print Request</title>
  <style>
    body { 
        font-family: 'Times New Roman', Times, serif; 
        font-size: 8px; /* smaller font */
  
    }
    .container{
      padding-top: 55px;
    }
    table { 
      border: 0.1px solid black;
      font-weight: 100;
      width: 100%; 
      text-align: center;
      align-items: center;
    }
    th{
      text-align: center;
    }
    th, td {
      border: 0.1px solid black;
    }
    td{
      height: 20px;
    }
    h5{
      font-size: small;
      font-weight: bold;
    }
    th.no { width: 5%; }
    th.model { width: 7%; }
    th.desc { width: 25%; }
    th.qty { width: 7%; }
    th.oum { width: 7%; }
    th.price { width: 7%; }
    th.date { width: 7%; }
    th.remarks { width: 18%; }
    th.empty { width: 25%; border: none;}

    p{
      margin-left:23px;
    }

    td.prepared_by{width: 11.2%; height: 65px;}
    td.checked_by, td.recom_by, td.noted_by, td.approved_by{width: 9.5%;}
    td.distribution{border-right: none; width: 12%;}
    td.distributions{border-right: none; width: 15.5%;}
    td.canvassed{width: 2%;}
    .f{
      align-items: center;
      text-align: center;
    }
    .l{
      text-align: left;
    }
    .caps{
      text-transform: uppercase;
    }
  </style>
</head>
<body>
  <div class="">
    <div class="container"></div>
    <div class="row">
        <div class="col-4">
          <img src="../asset/img/Koppel.jpg" alt="" width="100px">
          <p>Koppel, Inc</p>
        </div>
        <div class="col-4">
          <h5>LOCAL MATERIALS REQUISITION</h5>
        </div>
    </div>
    <div class="row">
      <div class="col-8">
        <p class="caps">REQUISITIONING DEPT: &emsp;&emsp; <u><?= $shared['department'] ?></u></p>
        <p class="caps">PROJECT/CUSTOMER: &emsp;&emsp; &emsp;<u><?= $shared['requestor'] ?></u></p>
      </div>
      <div class="col-4">
        <p class="caps">LMR NO: &emsp;&emsp;<u><?= $shared['lmr_no'] ?></u></p>
        <p class="caps">DATE: &emsp;&emsp;&emsp;<u><?= $shared['date_created'] ?></u></p>
        <p>P.0. NO: &emsp;&emsp;   ____________________________</p>
      </div>
    </div>
 
    <table>
      <thead>
        <tr>
          <th class="no">ITEM NO</th>
          <th class="model">ITEM</th>
          <th class="desc">DESCRIPTION</th>
          <th class="qty">QTY</th>
          <th class="oum">UoM</th>
          <th class="price">PRICE</th>
          <th class="date">DATE NEEDED</th>
          <th class="remarks">REMARKS</th>
          <th class="empty"></th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($items as $index => $item): ?>
        <tr>
          <td><?= $index + 1 ?></td>
          <td><?= $item['item'] ?></td>
          <td><?= $item['description'] ?></td>
          <td><?= $item['quantity'] ?></td>
          <td><?= $item['UoM'] ?></td>
          <td></td>
          <td><?= $item['date_needed'] ?></td>
          <td><?= $item['remarks'] ?></td>
        </tr>
        <?php endforeach; ?>
        <!-- First row: populated with real data -->
         <!-- 19 blank rows for printing -->
        <?php for ($i = 2; $i <= 15; $i++): ?>
        <tr>
          <td></td>
          <td></td><td></td><td></td><td></td><td></td><td></td><td></td>
        </tr>
        <?php endfor; ?>
      </tbody>
        
    </table>
    <table style="border-top: none;">
      <tr class="f">
            <td colspan="2">PREPARED BY: </td>
            <td >CHECKED BY: </td>
            <td> RECOMMENDED BY:</td>
            <td> NOTED BY:</td>
            <td> APPROVED BY:</td>
            <td> CANVASSED BY:</td>
            <td class="canvassed"> CANVASSED APPROVED BY:</td>
            <td class="distribution"> DISTRIBUTION</td>
        </tr>
        <tr>
            <td colspan="2" class="prepared_by"><?= $fullname ?></td>
          <?php
          $sql = "SELECT fullname FROM user_tb WHERE position = 'IT Supervisor' LIMIT 1";
          $result = $conn->query($sql);

          $supervisorName = '';

          if ($result && $result->num_rows > 0) {
              $row = $result->fetch_assoc();
              $supervisorName = $row['fullname'];
          }
          ?>

          <td class="checked_by"><?= htmlspecialchars($supervisorName); ?></td>
            <td class="recom_by"></td>
            <td class="noted_by"></td>
            <td class="approved_by"></td>
            <td class="distribution"></td>
            <td class="distribution"></td>
            <td class="distributions">Original Copy (Purchasing Dept.) <br> Duplicate Copy (Requititioning Dept) </td>
        </tr>      
        <tr class="l">
            <td colspan="2"> Date:<?= $shared['date_created'] ?></td>
            <td>  Date</td>
            <td>  Date</td>
            <td>  Date</td>
            <td class="distribution">  Date</td>
            <td class="distribution">  Date</td>
            <td class="distribution">  Date</td>
            <td class="distribution"></td>
        </tr>
    </table>

  </div>

<script>
  // Auto print on page load
  window.onload = function() {
    window.print();
  };
</script>
</body>
</html>
<?php $conn->close(); ?>
