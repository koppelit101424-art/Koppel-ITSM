<?php
include __DIR__ . '/../../includes/auth.php';
include __DIR__ . '/../../includes/db.php';
include __DIR__ . '/includes/inv_sql.php';
?>
<!-- <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script> -->
<!-- FILTER -->
<div class="card shadow-sm mb-2">
  <div class="card-header bg-white d-flex justify-content-between align-items-center">
    <h6 class="mb-0 text-primary fw-semibold">
      <i class="fas fa-filter me-1"></i> Inventory Filter
    </h6>
    <div class="d-flex gap-2">
      <a href="?page=inventory/all_assets" class="btn btn-secondary btn-sm">
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
      <input type="hidden" name="page" value="inventory/all_assets">

      <!-- Item -->
      <div class="col-md-2">
        <label class="form-label">Item</label>
        <select name="item" class="form-select searchable">
          <option value="">All Items</option>
          <?php foreach($itemsArr as $item): ?>
            <option value="<?= $item ?>" <?= $filterItem == $item ? 'selected' : '' ?>>
              <?= $item ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Brand -->
      <div class="col-md-2">
        <label class="form-label">Brand</label>
        <select name="brand" class="form-select searchable">
          <option value="">All Brands</option>
          <?php foreach($brandsArr as $brand): ?>
            <option value="<?= $brand ?>" <?= $filterBrand == $brand ? 'selected' : '' ?>>
              <?= $brand ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Type -->
      <div class="col-md">
        <label class="form-label">Type</label>
        <select name="type" class="form-select">
          <option value="">All Types</option>
          <?php foreach($typesArr as $type): ?>
            <option value="<?= $type['type_id'] ?>" <?= $filterType == $type['type_id'] ? 'selected' : '' ?>>
              <?= $type['type_name'] ?>
            </option>
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
            <?php

            $conditionsArr = [];
            $condQuery = $conn->query("SELECT condition_id, condition_name FROM item_condition_tb ORDER BY condition_name ASC");
            while($c = $condQuery->fetch_assoc()){
                $conditionsArr[] = $c;
            }
            foreach($conditionsArr as $cond): ?>
                <option value="<?= $cond['condition_id'] ?>" <?= ($filterCondition == $cond['condition_id']) ? 'selected' : '' ?>>
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

<!-- Inventory -->
<div class="card shadow-sm rounded-3 mb-4">
    <div class="card-header d-flex justify-content-between align-items-center bg-white border-bottom-0">
        <h5 class="mb-0 text-primary fw-semibold">Inventory Management</h5>
        <div>
            <a href="?page=inventory/crud/add_item" class="btn btn-sm btn-primary me-2">
                <i class="fas fa-plus me-1"></i> Add Item
            </a>
            <!-- <a href="inv/print_inventory.php" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-file-csv me-1"></i> Export CSV
            </a> -->
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

                      data-cpu="<?= htmlspecialchars($row['cpu'] ?? '') ?>"
                      data-ram="<?= htmlspecialchars($row['ram'] ?? '') ?>"
                      data-rom="<?= htmlspecialchars($row['rom'] ?? '') ?>"
                      data-motherboard="<?= htmlspecialchars($row['motherboard'] ?? '') ?>"
                      data-os="<?= htmlspecialchars($row['os'] ?? '') ?>"
                      data-key="<?= htmlspecialchars($row['key'] ?? '') ?>"
                      data-antivirus="<?= htmlspecialchars($row['antivirus'] ?? '') ?>"
                      data-compname="<?= htmlspecialchars($row['comp_name'] ?? '') ?>"
                      data-qr="<?= htmlspecialchars($row['qr_code_path'] ?? '') ?>"
                      >
                            <td style="display:none;"><?= $row['item_id'] ?></td>
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
                            <td style="width: 90px; align-items: center; text-align: center;">
                                <?php
                                if ($row['quantity'] > 0) {
                                    echo '<span class="badge bg-success" style="width: 100%;">In Stock</span>';
                                } elseif ($row['type_id'] == 8) {
                                    echo '<span class="badge bg-danger" style="width: 100%;">Consumed</span>';
                                } else {
                                    echo '<span class="badge bg-danger" style="width: 100%;">In Use</span>';
                                }
                                ?>
                            </td>
                            <td style="display:none;"><?= htmlspecialchars($row['type_name']) ?></td>
                            <td style="width: 50px; align-items: center; text-align: center;">
                                <?php
                                $condRaw = $row['condition_name'] ?? '';
                                $cond = strtolower(trim($condRaw));

                                $badgeClass = 'bg-success';
                                if ($cond == 'good') $badgeClass = 'bg-success';
                                elseif ($cond == 'damaged') $badgeClass = 'bg-warning';
                                elseif ($cond == 'defective') $badgeClass = 'bg-danger';
                                elseif ($cond == 'disposed') $badgeClass = 'bg-secondary';
                                ?>
                                <span style="width: 100%;" class="badge <?= $badgeClass ?>">
                                    <?= htmlspecialchars($condRaw ?: 'N/A') ?>
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" class="text-center text-muted">No items found</td>
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
<?php include __DIR__ . '/includes/inv_js.php'; ?>
<!--// $(document).ready(function() {

// // SELECT2
// $('.searchable').select2({
//     placeholder: "Select option",
//     allowClear: true,
//     width: '100%'
// }); -->
<!-- <script>
function printInventory() {

    let table = document.getElementById("inventoryTable").outerHTML;

    let printWindow = window.open('', '', 'width=1000,height=700');

    printWindow.document.write(`
        <html>
        <head>
            <title>Inventory Report</title>
            <style>
                body{
                    font-family: Arial;
                    padding:20px;
                }
                h2{
                    text-align:center;
                    margin-bottom:20px;
                }
                table{
                    width:100%;
                    border-collapse:collapse;
                }
                th, td{
                    border:1px solid #000;
                    padding:8px;
                    text-align:left;
                }
                th{
                    background:#f2f2f2;
                }
            </style>
        </head>
        <body>

        <h2>Inventory Report</h2>

        ${table}

        </body>
        </html>
    `);

    printWindow.document.close();
    printWindow.print();
}
</script> -->
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