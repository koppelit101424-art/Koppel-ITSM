<?php
include '../auth/auth.php';
include '../db/db.php';

// Get request ID from URL
$request_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($request_id <= 0) {
    die('Invalid request ID.');
}

// Fetch the specific request
$sql = "SELECT * FROM request_tb WHERE request_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $request_id);
$stmt->execute();
$result = $stmt->get_result();
$request = $result->fetch_assoc();

if (!$request) {
    die('Request not found.');
}

// Extract values safely
$lmr_no        = htmlspecialchars($request['lmr_no'] ?? '');
$requestor     = htmlspecialchars($request['requestor'] ?? '');
$department    = htmlspecialchars($request['department'] ?? '');
$item          = htmlspecialchars($request['item'] ?? '');
$description   = htmlspecialchars($request['description'] ?? '');
$quantity      = htmlspecialchars($request['quantity'] ?? '');
$uom           = htmlspecialchars($request['UoM'] ?? '');
$date_needed   = htmlspecialchars($request['date_needed'] ?? '');
$remarks       = htmlspecialchars($request['remarks'] ?? '');
$date_created  = !empty($request['date_created']) && $request['date_created'] !== '0000-00-00'
                 ? date('F j, Y', strtotime($request['date_created']))
                 : '';

$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Print Request</title>
  <style>
    body { 
        font-family: 'Times New Roman', Times, serif; 
        font-size: 8px;
        margin: 0;
        padding: 0;
    }
    .container {
      padding-top: 70px;
      width: 100%;
      max-width: none;
    }
    table { 
      border: 0.1px solid gray;
      width: 100%; 
      border-collapse: collapse;
      table-layout: fixed;
    }
    th, td {
      border: 0.1px solid gray;
      padding: 2px;
      vertical-align: top;
    }
    th {
      text-align: center;
      font-weight: bold;
    }
    td {
      height: 20px;
      font-size: 8px;
    }
    h5 {
      font-size: small;
      font-weight: bold;
      margin: 0;
    }
    th.no      { width: 5%;  }
    th.model   { width: 7%;  }
    th.desc    { width: 25%; }
    th.qty     { width: 7%;  }
    th.oum     { width: 8%;  }
    th.price   { width: 8%;  }
    th.date    { width: 9%;  }
    th.remarks { width: 18%; }
    th.empty   { width: 23%; border: none; }

    p {
      margin: 4px 0 0 23px;
      font-size: 8px;
    }

    .header-row .col-4 {
      font-size: 8px;
    }

    td.prepared_by { width: 11.2%; height: 70px; }
    td.checked_by,
    td.recom_by,
    td.noted_by,
    td.approved_by { width: 11.5%; }
    td.distribution { border-right: none; width: 12%; }

    .f {
      text-align: center;
    }
    .l {
      text-align: left;
    }
  </style>
</head>
<body>
  <div class="container">
    <!-- Header -->
    <div class="row header-row">
      <div class="col-4">
        <img src="../asset/img/Koppel.jpg" alt="Koppel Logo" width="100px">
        <p>Koppel, Inc</p>
      </div>
      <div class="col-4">
        <h5>LOCAL MATERIALS REQUISITION</h5>
      </div>
    </div>

    <!-- Form Fields -->
    <div class="row" style="font-size: 8px;">
      <div class="col-8">
        <p>REQUISITIONING DEPT: &emsp;&emsp;&emsp; <u><?= $department ?></u></p>
        <p>PROJECT/CUSTOMER: &emsp;&emsp; &emsp; <u>____________________________</u></p>
      </div>
      <div class="col-4">
        <p>LMR NO: &emsp;&emsp; <u><?= $lmr_no ?></u></p>
        <p>DATE: &emsp;&emsp;&emsp; <u><?= $date_created ?></u></p>
        <p>P.O. NO: &emsp;&emsp; <u>____________________________</u></p>
      </div>
    </div>

    <!-- Items Table -->
    <table>
      <thead>
        <tr>
          <th class="no">ITEM NO</th>
          <th class="model">KOPPEL MODEL</th>
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
        <!-- First row: populated with real data -->
        <tr>
          <td>1</td>
          <td><?= $item ?></td>
          <td><?= $description ?></td>
          <td><?= $quantity ?></td>
          <td><?= $uom ?></td>
          <td></td> <!-- PRICE not in your schema – leave blank or add if available -->
          <td><?= $date_needed ?></td>
          <td><?= $remarks ?></td>
          <td></td>
        </tr>

        <!-- 19 blank rows for printing -->
        <?php for ($i = 2; $i <= 20; $i++): ?>
        <tr>
          <td><?= $i ?></td>
          <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
        </tr>
        <?php endfor; ?>
      </tbody>
    </table>

    <!-- Signatures -->
    <table style="border-top: none; margin-top: 5px;">
      <tr class="f">
        <td colspan="2">PREPARED BY: </td>
        <td>CHECKED BY: </td>
        <td>RECOMMENDED BY:</td>
        <td>NOTED BY:</td>
        <td>APPROVED BY:</td>
        <td>CANVASSED BY:</td>
        <td>CANVASSED APPROVED BY:</td>
        <td class="distribution">DISTRIBUTION</td>
      </tr>
      <tr>
        <td colspan="2" class="prepared_by"></td>
        <td class="checked_by"></td>
        <td class="recom_by"></td>
        <td class="noted_by"></td>
        <td class="approved_by"></td>
        <td class="distribution"></td>
        <td class="distribution"></td>
        <td class="distribution"></td>
      </tr>      
      <tr class="l">
        <td colspan="2">Date</td>
        <td>Date</td>
        <td>Date</td>
        <td>Date</td>
        <td class="distribution">Date</td>
        <td class="distribution">Date</td>
        <td class="distribution">Date</td>
        <td class="distribution"></td>
      </tr>
    </table>
  </div>

  <!-- Optional: Auto-print -->
  
  <script>
    window.onload = function() {
      window.print();
    };
  </script>
 
</body>
</html>
<?php $conn->close(); ?>