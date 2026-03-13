
<?php
include __DIR__ . '/../../includes/auth.php';
include __DIR__ . '/../../includes/db.php';
include __DIR__ . '/includes/trans_sql.php';
$filterUser = $_GET['user'] ?? '';
$filterItem = $_GET['item'] ?? '';
$filterBrand = $_GET['brand'] ?? '';
$filterType = $_GET['type'] ?? '';
$filterStatus = $_GET['status'] ?? '';
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';

$usersArr = [];
$q = $conn->query("SELECT DISTINCT fullname FROM user_tb ORDER BY fullname ASC");
while($r=$q->fetch_assoc()) $usersArr[]=$r['fullname'];

$itemsArr = [];
$q = $conn->query("SELECT DISTINCT name FROM item_tb ORDER BY name ASC");
while($r=$q->fetch_assoc()) $itemsArr[]=$r['name'];

$brandsArr = [];
$q = $conn->query("SELECT DISTINCT brand FROM item_tb ORDER BY brand ASC");
while($r=$q->fetch_assoc()) $brandsArr[]=$r['brand'];

$typesArr = [];
$q = $conn->query("SELECT type_name FROM item_type ORDER BY type_name ASC");
while($r=$q->fetch_assoc()) $typesArr[]=$r;

$sql = "
SELECT t.*, u.fullname, i.name AS item_name, i.brand, i.model, i.serial_number, i.item_code, ty.type_name
FROM transaction_tb t
LEFT JOIN user_tb u ON t.user_id = u.user_id
LEFT JOIN item_tb i ON t.item_id = i.item_id
LEFT JOIN item_type ty ON i.type_id = ty.type_id
WHERE 1=1
";

$params=[];
$types="";

if($filterUser){
$sql.=" AND u.fullname=?";
$params[]=$filterUser;
$types.="s";
}

if($filterItem){
$sql.=" AND i.name=?";
$params[]=$filterItem;
$types.="s";
}

if($filterBrand){
$sql.=" AND i.brand=?";
$params[]=$filterBrand;
$types.="s";
}

if($filterType){
$sql.=" AND ty.type_name=?";
$params[]=$filterType;
$types.="s";
}

if($filterStatus){
$sql.=" AND t.action=?";
$params[]=$filterStatus;
$types.="s";
}

if($dateFrom && $dateTo){
$sql.=" AND DATE(t.action_date) BETWEEN ? AND ?";
$params[]=$dateFrom;
$params[]=$dateTo;
$types.="ss";
}
$sql.=" ORDER BY t.transaction_id DESC";

$stmt = $conn->prepare($sql);

if(!empty($params)){
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

?>

<!-- FILTER -->
<div class="card shadow-sm mb-4">
  <div class="card-header bg-white d-flex justify-content-between align-items-center">
    <h6 class="mb-0 text-primary fw-semibold">
      <i class="fas fa-filter me-1"></i> Transaction Filter
    </h6>
    <div class="d-flex gap-2">
      <a href="?page=inventory/transactions" class="btn btn-secondary btn-sm">
        <i class="fas fa-undo me-1"></i> Reset
      </a>
      <button type="submit" form="transactionFilterForm" class="btn btn-primary btn-sm">
        <i class="fas fa-search me-1"></i> Filter
      </button>
      <button type="button" onclick="window.print()" class="btn btn-success btn-sm">
        <i class="fas fa-print me-1"></i> Print
      </button>
    </div>
  </div>

  <div class="card-body">
    <form id="transactionFilterForm" method="GET" class="row g-3 align-items-end">
      <input type="hidden" name="page" value="inventory/transactions">

      <!-- Item -->
      <div class="col-md-2">
        <label class="form-label">Item</label>
        <select name="item" class="form-select searchable">
          <option value="">All Items</option>
          <?php foreach($itemsArr as $item): ?>
            <option value="<?= $item ?>" <?= ($filterItem==$item)?'selected':'' ?>>
              <?= $item ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Brand -->
      <div class="col-md-2">
        <label class="form-label">Brand</label>
        <select name="brand" class="form-select">
          <option value="">All Brands</option>
          <?php foreach($brandsArr as $brand): ?>
            <option value="<?= $brand ?>" <?= ($filterBrand==$brand)?'selected':'' ?>>
              <?= $brand ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Type -->
      <div class="col-md-2">
        <label class="form-label">Type</label>
        <select name="type" class="form-select">
          <option value="">All Types</option>
          <?php foreach($typesArr as $type): ?>
            <option value="<?= $type['type_name'] ?>" <?= ($filterType==$type['type_name'])?'selected':'' ?>>
              <?= $type['type_name'] ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Status -->
      <div class="col-md-2">
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
          <option value="">All Status</option>
          <option value="issued" <?= ($filterStatus=='issued')?'selected':'' ?>>Issued</option>
          <option value="borrowed" <?= ($filterStatus=='borrowed')?'selected':'' ?>>Borrowed</option>
          <option value="returned" <?= ($filterStatus=='returned')?'selected':'' ?>>Returned</option>
        </select>
      </div>

      <!-- Date From -->
      <div class="col-md-2">
        <label class="form-label">Date From</label>
        <input type="date" name="date_from" class="form-control" value="<?= $dateFrom ?>">
      </div>

      <!-- Date To -->
      <div class="col-md-2">
        <label class="form-label">Date To</label>
        <input type="date" name="date_to" class="form-control" value="<?= $dateTo ?>">
      </div>
    </form>
  </div>
</div>
        <!-- Inventory Table -->
    <div class="card shadow-sm rounded-3 mb-4">
      <div class="card-header d-flex justify-content-between align-items-center bg-white border-bottom-0">
          <h5 class="mb-0 text-primary fw-semibold">Issuance Record</h5> </div>
            <!-- <    <a href="inventory/add_item.php">
                    <button class="btn btn-blue btn-sm">
                        <i class="fas fa-plus me-1"></i> Add Item
                    </button>
                </a>
                a href="transaction/print_transactions.php" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-file-csv me-1"></i> Export CSV
                </a>                 -->
           <div>
            <div class="card-body">
                <!-- Filter Section -->
                <!-- <div class="d-flex align-items-center justify-content-between flex-wrap gap-2"> -->
                    <!-- Type Filter Buttons -->
                    <!-- <div class="d-flex flex-wrap gap-2">
                        <button class="btn btn-outline-blue type-filter active" data-type="">All</button>
                        <?php while($t = $typeResult->fetch_assoc()): ?>
                            <button class="btn btn-outline-blue type-filter" data-type="<?= htmlspecialchars($t['type_name']) ?>">
                                <?= htmlspecialchars($t['type_name']) ?>
                            </button>
                        <?php endwhile; ?>
                    </div> -->

                    <!-- Filter by Item Name -->
                    <!-- <div style="width: 188px;">
                        <select id="filterByName" class="form-select form-select-sm">
                            <option value="">Select Item</option>
                            <?php while($n = $itemNames->fetch_assoc()): ?>
                                <option value="<?= htmlspecialchars($n['name']) ?>"><?= htmlspecialchars($n['name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div> -->
                <!-- </div>                                 -->
                 
                <div class="table-responsive">
                    <table id="transactionTable" class="table table-hover align-middle">
                         <thead class="table-light">
                            <tr>
                            <th>ID</th>
                            <th>User</th>                         
                            <th>Item</th>
                            <th>Code</th>   
                            <th>Brand</th>
                            <th>Model</th>
                            <th>Serial</th>
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
                                 data-item_code="<?= $row['item_code'] ?>"
                                data-brand="<?= htmlspecialchars($row['brand']) ?>" 
                                data-model="<?= htmlspecialchars($row['model']) ?>" 
                                data-qty="<?= $row['serial_number'] ?>" 
                                data-qty="<?= $row['quantity'] ?>" 
                                data-date="<?= date('m-d-Y', strtotime($row['action_date'])) ?>" 
                                data-returned-label="<?= (empty($row['date_returned']) || $row['date_returned']=='0000-00-00 00:00:00') ? 'Not Returned' : date('m-d-Y', strtotime($row['date_returned'])) ?>" 
                                data-returned="<?= (empty($row['date_returned']) || $row['date_returned']=='0000-00-00 00:00:00') ? '0' : '1' ?>" 
                                data-remarks="<?= htmlspecialchars($row['remarks']) ?>" 
                                data-status="<?= $row['action'] ?>">
                                
                                <td><?= $row['transaction_id'] ?></td>
                                <td><?= $row['fullname'] ?></td>
                                <td><?= $row['item_name'] ?></td>
                                <td><?= $row['item_code'] ?></td>
                                <td><?= $row['brand'] ?></td>
                                <td ><?= $row['model'] ?></td>
                                 <td ><?= $row['serial_number'] ?></td>
                                <td style="display:none;"><?= $row['remarks'] ?></td>
                                <td ><?= $row['quantity'] ?></td>
                                <td style="width: 85px;"><?= date('m-d-Y', strtotime($row['action_date'])) ?></td>
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
                                    <span class="status-issued">Issued</span>
                                <?php elseif ($row['action'] == 'borrowed'): ?>
                                    <span class="status-borrowed">Borrowed</span>
                                <?php elseif ($row['action'] == 'returned'): ?>
                                    <span class="status-returned">Returned</span>
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
        
<?php include __DIR__.'/includes/trans_modal.php'; ?>
<?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin'): ?>
<?php include "inventory/includes/trans_menu.php"; ?>
<?php endif; ?>
<?php include __DIR__.'/includes/trans_js.php'; ?>

