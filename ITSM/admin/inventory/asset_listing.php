<?php
include __DIR__ . '/../../includes/auth.php';
include __DIR__ . '/../../includes/db.php';

// ============================
// GET FILTERS
// ============================
$itemName       = $_GET['type'] ?? 'Laptop'; // default item name
$filterBrand    = $_GET['brand'] ?? '';
$filterModel    = $_GET['model'] ?? '';
$filterStatus   = $_GET['status'] ?? '';
$filterCondition= $_GET['condition'] ?? '';
$dateFrom       = $_GET['date_from'] ?? '';
$dateTo         = $_GET['date_to'] ?? '';
$isLaptop       = strtolower($itemName) === 'laptop';

// ============================
// FETCH BRANDS AND MODELS FOR SELECT
// ============================
// only fetch brands/models that exist for this item type
$brandsArr = [];
$brandQuery = $conn->prepare("SELECT DISTINCT brand FROM item_tb WHERE name=? ORDER BY brand ASC");
$brandQuery->bind_param("s", $itemName);
$brandQuery->execute();
$brandResult = $brandQuery->get_result();
while ($b = $brandResult->fetch_assoc()) $brandsArr[] = $b['brand'];

$modelsArr = [];

if ($filterBrand) {
    // fetch only models under the selected brand
    $modelQuery = $conn->prepare("SELECT DISTINCT model FROM item_tb WHERE name=? AND brand=? ORDER BY model ASC");
    $modelQuery->bind_param("ss", $itemName, $filterBrand);
} else {
    // fetch all models for the item type
    $modelQuery = $conn->prepare("SELECT DISTINCT model FROM item_tb WHERE name=? ORDER BY model ASC");
    $modelQuery->bind_param("s", $itemName);
}

$modelQuery->execute();
$modelResult = $modelQuery->get_result();
while ($m = $modelResult->fetch_assoc()) $modelsArr[] = $m['model'];

// fetch all types (optional, could be used for type dropdown)
$typesArr = [];
$typeQuery = $conn->query("SELECT type_id, type_name FROM item_type ORDER BY type_name ASC");
while ($t = $typeQuery->fetch_assoc()) $typesArr[] = $t;

// ============================
// BUILD SQL DYNAMICALLY
// ============================
$sql = "SELECT 
            i.*, 
            t.type_name,
            c.condition_name,
            q.qr_code_path";

if ($isLaptop) {
    $sql .= ", s.cpu, s.ram, s.rom, s.motherboard, s.os, s.`key`, s.antivirus, s.comp_name";
}

$sql .= " FROM item_tb i
          LEFT JOIN item_type t ON i.type_id = t.type_id
          LEFT JOIN qr_tb q ON i.item_id = q.item_id
          LEFT JOIN item_condition_tb c ON i.condition_id = c.condition_id";

if ($isLaptop) {
    $sql .= " LEFT JOIN laptop_pc_specs s ON i.item_id = s.item_id";
}

// ============================
// BUILD FILTER WHERE CLAUSE
// ============================
$where = ["i.name = ?"];
$params = [$itemName];
$types = "s"; // for bind_param

if ($filterBrand) {
    $where[] = "i.brand = ?";
    $params[] = $filterBrand;
    $types .= "s";
}

if ($filterModel) {
    $where[] = "i.model = ?";
    $params[] = $filterModel;
    $types .= "s";
}

if ($filterStatus) {
    if ($filterStatus === 'stock') $where[] = "i.quantity > 0";
    elseif ($filterStatus === 'use') $where[] = "i.quantity = 0 AND i.type_id <> 8";
    elseif ($filterStatus === 'consumed') $where[] = "i.type_id = 8";
}

if ($filterCondition) {
    $where[] = "i.condition_id = ?";
    $params[] = $filterCondition;
    $types .= "i";
}

if ($dateFrom) {
    $where[] = "i.date_received >= ?";
    $params[] = $dateFrom;
    $types .= "s";
}

if ($dateTo) {
    $where[] = "i.date_received <= ?";
    $params[] = $dateTo;
    $types .= "s";
}

$sql .= " WHERE " . implode(" AND ", $where) . " ORDER BY i.item_id DESC";

$stmt = $conn->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// ============================
// FETCH CONDITIONS
// ============================
$conditionsArr = [];
$condQuery = $conn->query("SELECT condition_id, condition_name FROM item_condition_tb ORDER BY condition_name ASC");
while($c = $condQuery->fetch_assoc()) $conditionsArr[] = $c;

?>

<!-- FILTER CARD -->
<div class="card shadow-sm mb-2">
  <div class="card-header bg-white d-flex justify-content-between align-items-center">
    <h6 class="mb-0 text-primary fw-semibold"><i class="fas fa-filter me-1"></i> Inventory Filter</h6>
    <div class="d-flex gap-2">
      <a href="?page=inventory/asset_listing&type=<?= urlencode($itemName) ?>" class="btn btn-secondary btn-sm">
        <i class="fas fa-undo me-1"></i> Reset
      </a>
      <button type="submit" form="filterForm" class="btn btn-primary btn-sm">
        <i class="fas fa-search me-1"></i> Filter
      </button>
      <button type="button" onclick="printInventory()" class="btn btn-success btn-sm">
        <i class="fas fa-print me-1"></i> Print
      </button>
      <button type="button" onclick="printQRStickers()" class="btn btn-dark btn-sm">
        <i class="fas fa-qrcode me-1"></i> Print QR Codes
      </button>
    </div>
  </div>

  <div class="card-body">
    <form id="filterForm" method="GET" class="row g-3 align-items-end">
      <input type="hidden" name="page" value="inventory/asset_listing">
      <input type="hidden" name="type" value="<?= htmlspecialchars($itemName) ?>">

      <!-- Brand -->
      <div class="col-md">
        <label class="form-label">Brand</label>
        <select name="brand" class="form-select">
          <option value="">All Brands</option>
          <?php foreach($brandsArr as $brand): ?>
            <option value="<?= htmlspecialchars($brand) ?>" <?= $filterBrand==$brand?'selected':'' ?>><?= htmlspecialchars($brand) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Model -->
      <div class="col-md">
        <label class="form-label">Model</label>
        <select name="model" class="form-select">
          <option value="">All Models</option>
          <?php foreach($modelsArr as $model): ?>
            <option value="<?= htmlspecialchars($model) ?>" <?= $filterModel==$model?'selected':'' ?>><?= htmlspecialchars($model) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Status -->
      <div class="col-md">
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
          <option value="">All Status</option>
          <option value="stock" <?= $filterStatus=='stock'?'selected':'' ?>>In Stock</option>
          <option value="use" <?= $filterStatus=='use'?'selected':'' ?>>In Use</option>
          <option value="consumed" <?= $filterStatus=='consumed'?'selected':'' ?>>Consumed</option>
        </select>
      </div>

      <!-- Condition -->
      <div class="col-md">
        <label class="form-label">Condition</label>
        <select name="condition" class="form-select">
          <option value="">All Conditions</option>
          <?php foreach($conditionsArr as $cond): ?>
            <option value="<?= $cond['condition_id'] ?>" <?= $filterCondition==$cond['condition_id']?'selected':'' ?>>
              <?= $cond['condition_name'] ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Date From -->
      <div class="col-md">
        <label class="form-label">Date From</label>
        <input type="date" name="date_from" class="form-control" value="<?= $dateFrom ?>">
      </div>

      <!-- Date To -->
      <div class="col-md">
        <label class="form-label">Date To</label>
        <input type="date" name="date_to" class="form-control" value="<?= $dateTo ?>">
      </div>
    </form>
  </div>
</div>
<div class="card shadow-sm rounded-3 mb-4">
    <div class="card-header d-flex justify-content-between align-items-center bg-white border-bottom-0">
        <h5 class="mb-0 text-primary fw-semibold"><?= htmlspecialchars($itemName) ?></h5>
        <div>
            <a href="?page=inventory/crud/add_item" class="btn btn-sm btn-primary me-2">
                <i class="fas fa-plus me-1"></i> Add Item
            </a>
        </div>
    </div>

    <div class="card-body">
        <div class="table-responsive">
<table id="inventoryTable" class="table table-hover align-middle">
    <thead class="table-light">
        <tr>
            <th style="display:none;">ID</th>
            <th>QR</th>
            <th>Code</th>
            <th>Item</th>
            <th>Brand</th>
            <th>Model</th>
            <th>Serial No.</th>
            <th style="display:none;">Description</th>
            <th>Qty</th>
            <th>Received</th>
            <th style="width: 50px;">Status</th>
            <th style="display:none;">Type</th>
            <th style="width: 30px;">Condition</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr
                data-item-id="<?= (int)$row['item_id'] ?>"
                data-code="<?= htmlspecialchars($row['item_code']) ?>"
                data-name="<?= htmlspecialchars($row['name']) ?>"
                data-brand="<?= htmlspecialchars($row['brand']) ?>"
                data-model="<?= htmlspecialchars($row['model']) ?>"
                data-serial="<?= htmlspecialchars($row['serial_number']) ?>"
                data-desc="<?= htmlspecialchars($row['description']) ?>"
                data-qty="<?= (int)$row['quantity'] ?>"
                data-received="<?= date('m-d-Y', strtotime($row['date_received'])) ?>"
                data-type="<?= htmlspecialchars($row['type_name']) ?>"
                data-qr="<?= htmlspecialchars($row['qr_code_path'] ?? '') ?>"
                <?php if ($isLaptop): ?>
                    data-cpu="<?= htmlspecialchars($row['cpu'] ?? '') ?>"
                    data-ram="<?= htmlspecialchars($row['ram'] ?? '') ?>"
                    data-rom="<?= htmlspecialchars($row['rom'] ?? '') ?>"
                    data-motherboard="<?= htmlspecialchars($row['motherboard'] ?? '') ?>"
                    data-os="<?= htmlspecialchars($row['os'] ?? '') ?>"
                    data-key="<?= htmlspecialchars($row['key'] ?? '') ?>"
                    data-antivirus="<?= htmlspecialchars($row['antivirus'] ?? '') ?>"
                    data-compname="<?= htmlspecialchars($row['comp_name'] ?? '') ?>"
                <?php endif; ?>
            >
                <td style="display:none;"><?= $row['item_id'] ?></td>
                
                <!-- QR Column -->
                <td>
                    <?php if (!empty($row['qr_code_path'])): ?>
                        <a href="inventory/<?= htmlspecialchars($row['qr_code_path']) ?>" target="_blank">
                            <img src="inventory/<?= htmlspecialchars($row['qr_code_path']) ?>" width="50">
                        </a>
                    <?php else: ?>
                        <span class="text-muted">No QR</span>
                    <?php endif; ?>
                </td>
                
                <td><?= $row['item_code'] ?></td>
                <td><?= $row['name'] ?></td>
                <td><?= $row['brand'] ?></td>
                <td><?= $row['model'] ?></td>
                <td><?= $row['serial_number'] ?></td>
                <td style="display:none;"><?= htmlspecialchars($row['description']) ?></td>
                <td><?= $row['quantity'] ?></td>
                <td data-order="<?= date('Y-m-d', strtotime($row['date_received'])) ?>">
                    <?= date('m-d-Y', strtotime($row['date_received'])) ?>
                </td>
                <td class="text-center">
                    <?php
                    if ($row['quantity'] > 0) {
                        echo '<span class="badge bg-success">In Stock</span>';
                    } elseif ($row['type_id'] == 8) {
                        echo '<span class="badge bg-danger">Consumed</span>';
                    } else {
                        echo '<span class="badge bg-danger">In Use</span>';
                    }
                    ?>
                </td>
                <td style="display:none;"><?= htmlspecialchars($row['type_name']) ?></td>
                <td style="text-align:center;">
                    <?php
                    $condRaw = $row['condition_name'] ?? 'N/A';
                    $cond = strtolower(trim($condRaw));
                    $badgeClass = match($cond) {
                        'good' => 'bg-success',
                        'damaged' => 'bg-warning',
                        'defective' => 'bg-danger',
                        'disposed' => 'bg-secondary',
                        default => 'bg-secondary'
                    };
                    ?>
                    <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($condRaw) ?></span>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="13" class="text-center text-muted">No items found</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
        </div>
    </div>
</div>

<?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin'): ?>
    <?php include "inventory/includes/context_menu.php"; ?>
<?php endif; ?>

<?php include __DIR__ . '/includes/inv_modal.php'; ?>

<script>
function printInventory() {

    const rows = document.querySelectorAll("#inventoryTable tbody tr");

    // helper to mimic nl2br(htmlspecialchars())
    function formatDescription(text) {
        if (!text) return "";

        const div = document.createElement("div");
        div.innerText = text; // escapes HTML like htmlspecialchars

        return div.innerHTML.replace(/\n/g, "<br>"); // nl2br
    }

    let html = `
    <h2 style="text-align:center;">Inventory Report</h2>

    <table border="1" cellspacing="0" cellpadding="6" width="100%">
    <thead>
        <tr>
            <th>Code</th>
            <th>Item</th>
            <th>Brand</th>
            <th>Model</th>
            <th>Serial</th>
            <th>Description / Specifications</th>
            <th>Qty</th>
            <th>Date Received</th>
      
        </tr>
    </thead>
    <tbody>
    `;

    rows.forEach(row => {

        const code = row.dataset.code || "";
        const item = row.dataset.name || "";
        const brand = row.dataset.brand || "";
        const model = row.dataset.model || "";
        const serial = row.dataset.serial || "";
        const desc = row.dataset.desc || "";
        const qty = row.dataset.qty || "";
        const received = row.dataset.received || "";

        const cpu = row.dataset.cpu || "";
        const ram = row.dataset.ram || "";
        const rom = row.dataset.rom || "";
        const motherboard = row.dataset.motherboard || "";
        const os = row.dataset.os || "";
        const key = row.dataset.key || "";
        const antivirus = row.dataset.antivirus || "";
        const compname = row.dataset.compname || "";

        const status = row.querySelector("td:nth-child(10)")?.innerText || "";

        // format description like PHP nl2br(htmlspecialchars())
        let description = formatDescription(desc);

        if (cpu || ram || rom || motherboard || os || key || antivirus || compname) {
            description += `
            <br><b>Specifications:</b>
            ${cpu ? `<br>CPU: ${cpu}` : ""}
            ${ram ? `<br>RAM: ${ram}` : ""}
            ${rom ? `<br>ROM: ${rom}` : ""}
            ${motherboard ? `<br>Motherboard: ${motherboard}` : ""}
            ${os ? `<br>OS: ${os}` : ""}
            ${key ? `<br>Key: ${key}` : ""}
            ${antivirus ? `<br>Antivirus: ${antivirus}` : ""}
            ${compname ? `<br>Computer Name: ${compname}` : ""}
            `;
        }

        html += `
        <tr>
            <td>${code}</td>
            <td>${item}</td>
            <td>${brand}</td>
            <td>${model}</td>
            <td>${serial}</td>
            <td>${description}</td>
            <td>${qty}</td>
            <td>${received}</td>
        </tr>
        `;
    });

    html += "</tbody></table>";

    const win = window.open("", "", "width=1200,height=700");

    win.document.write(`
    <html>
    <head>
        <title>Inventory Report</title>
        <style>
            body{font-family:Arial;padding:20px}
            h2{text-align:center;margin-bottom:20px}
            table{border-collapse:collapse;width:100%;font-size:12px}
            th,td{
                border:1px solid #000;
                padding:6px;
                text-align:left;
                vertical-align:top;
            }
            th{background:#eee}
        </style>
    </head>
    <body>
        ${html}
    </body>
    </html>
    `);

    win.document.close();
    win.print();
}
</script>
<!-- print qr -->
 <script>
function printQRStickers() {

    const rows = document.querySelectorAll("#inventoryTable tbody tr");

    let content = "";

    rows.forEach(row => {

        // skip hidden rows (important if using filters/datatables)
        if (row.offsetParent === null) return;

        const qr = row.dataset.qr;
        const code = row.dataset.code || "";
        const name = row.dataset.name || "";

        if (!qr) return;

        content += `
        <div class="sticker">
            <img src="inventory/${qr}" class="qr">
            <div class="label">
                <strong>${code}</strong><br>
                ${name}
            </div>
        </div>
        `;
    });

    const win = window.open("", "", "width=800,height=600");

    win.document.write(`
    <html>
    <head>
        <title>QR Stickers</title>
        <style>
            @page {
                size: 4in 4in;
                margin: 0;
            }

            body {
                margin: 0;
                padding: 0;
                font-family: Arial;
            }

            .sticker {
                width: 4in;
                height: 4in;
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                page-break-after: always;
                text-align: center;
            }

            .qr {
                width: 3in;
                height: 3in;
                object-fit: contain;
            }

            .label {
                margin-top: 10px;
                font-size: 14px;
            }
        </style>
    </head>
    <body>
        ${content}
    </body>
    </html>
    `);

    win.document.close();
    win.print();
}
</script>
<!-- ajax -->
<script>
document.addEventListener('DOMContentLoaded', function() {

    const brandSelect = document.querySelector('select[name="brand"]');
    const modelSelect = document.querySelector('select[name="model"]');
const type = "<?= addslashes($itemName) ?>"; // item type (Laptop, etc.)

brandSelect.addEventListener('change', function() {
    const brand = this.value;

    fetch(`?ajax=get_models&type=${encodeURIComponent(type)}&brand=${encodeURIComponent(brand)}`)
        .then(res => res.json())
        .then(data => {
            // clear current model options
            modelSelect.innerHTML = '<option value="">All Models</option>';
            data.forEach(model => {
                const option = document.createElement('option');
                option.value = model;
                option.textContent = model;
                modelSelect.appendChild(option);
            });
        })
        .catch(err => console.error('Error fetching models:', err));
});

});
</script>