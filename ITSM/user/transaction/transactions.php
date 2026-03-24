<?php
include 'includes/auth.php';
include 'includes/db.php';

$user_id = $_SESSION['user_id'];

// Fetch assets issued/borrowed to the logged-in user
    $sql = "
        SELECT 
            t.transaction_id,
            t.action,
            t.quantity,
            t.action_date,
            t.date_returned,
            t.remarks,
            i.item_code,
            i.name,
            i.brand,
            i.model,
            i.serial_number
        FROM transaction_tb t
        INNER JOIN item_tb i ON t.item_id = i.item_id
        WHERE t.user_id = ?
        ORDER BY t.action_date DESC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();


/* ============================
FETCH DESKTOP ASSIGNED TO USER
============================ */

$desktopSql = "
SELECT 
    d.desktop_id,
    d.cpu,
    d.ram,
    d.rom_w_serial,
    d.motherboard,
    d.monitor_w_serial,
    d.avr,
    d.mouse,
    d.keyboard,
    d.ip_address,
    d.mac_address,
    d.computer_name,
    d.windows_key,
    d.antivirus,
    d.tag_number,
    d.desktop_area_id,
    d.remarks,
    d.date_created,
    ud.date_created AS assigned_date
FROM user_desktop_tb ud
INNER JOIN desktop_tb d ON ud.desktop_id = d.desktop_id
WHERE ud.user_id = ?
";

$desktopStmt = $conn->prepare($desktopSql);
$desktopStmt->bind_param("i", $user_id);
$desktopStmt->execute();
$desktopResult = $desktopStmt->get_result();
    $laptopSql = "
    SELECT 
        i.item_code,
        i.name,
        i.brand,
        i.model,
        i.serial_number,
        t.quantity,
        t.action_date
    FROM transaction_tb t
    INNER JOIN item_tb i ON t.item_id = i.item_id
    WHERE t.user_id = ?
    AND LOWER(i.name) LIKE '%laptop%'
    AND t.action = 'issue'
    ";

    $laptopStmt = $conn->prepare($laptopSql);
    $laptopStmt->bind_param("i", $user_id);
    $laptopStmt->execute();
    $laptopResult = $laptopStmt->get_result();
?>
<style>
    .table-hover tbody tr:hover {
        background-color: #f1f1f1;
    }
    .badge-issue { background-color: #0d6efd; }
    .badge-borrow { background-color: #6f42c1; }
    .badge-return { background-color: #198754; }
    .asset-section-title{
    font-weight:600;
    font-size:14px;
    color:#1E3A8A;
    border-bottom:1px solid #e5e7eb;
    margin-bottom:8px;
    padding-bottom:4px;
}

.asset-label{
    font-size:12px;
    color:#6b7280;
}

.asset-value{
    font-weight:500;
}

.asset-box{
    background:#f8fafc;
    border:1px solid #e5e7eb;
    border-radius:8px;
}
.img-fluid{
    background-color: none;
    border: none;
}
.asset-value {
white-space: normal;      /* Allow text to wrap */
word-wrap: break-word;    /* Break long words if necessary */
display: block;           /* Ensure it behaves like a block element */
            }
</style>
<div class="card">
    <div class="card-header text-white">
        🕒 Issued / Borrowed Assets History
    </div>
        <?php if ($result->num_rows > 0): ?>
            <div class="row g-3">
                <?php $i = 1; while ($row = $result->fetch_assoc()): ?>
                    <?php
                        // Determine status
                        if (!empty($row['date_returned']) && $row['date_returned'] != '0000-00-00 00:00:00') {
                            $status = 'Returned';
                            $badgeClass = 'bg-success';
                        } else {
                            $status = ($row['action'] == 'issued' || $row['action'] == 'borrowed') ? 'In Use' : 'Unknown';
                            $badgeClass = 'bg-danger text-white';
                        }
                    ?>
                    <div class="col-12">
                        <div class="history-item border rounded shadow-sm p-3 h-100">
                            <div class="d-flex justify-content-between mb-2">
                                <div>
                                    <strong><?= htmlspecialchars($row['name']) ?></strong>
                                    <span class="text-muted ms-2">(<?= htmlspecialchars($row['item_code']) ?>)</span>
                                </div>
                                <div class="text-end text-muted">
                                    <?= date('m-d-Y', strtotime($row['action_date'])) ?>
                                </div>
                            </div>

                            <div class="row g-2">
                                <div class="col-4"><strong>Brand / Model:</strong> <?= htmlspecialchars($row['brand'] . ' ' . $row['model']) ?></div>
                                <div class="col-4"><strong>Quantity:</strong> <?= $row['quantity'] ?></div>

                                <div class="col-4"><strong>Returned:</strong> 
                                    <?= !empty($row['date_returned']) && $row['date_returned'] != '0000-00-00 00:00:00' 
                                        ? date('m-d-Y', strtotime($row['date_returned'])) 
                                        : '<span class="text-danger">Not Returned</span>' ?>
                                </div>
                                <div class="col-4"><strong>Serial No.:</strong> <?= htmlspecialchars($row['serial_number']) ?></div>
                                <div class="col-8"><strong>Remarks:</strong> <?= htmlspecialchars($row['remarks'] ?? '-') ?></div>
                            </div>
<!-- 
                            <div class="mt-2">
                                <span class="badge <?= $badgeClass ?>"><?= $status ?></span>
                            </div> -->
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="text-center text-muted py-4">
                No assets have been issued to this user.
            </div>
        <?php endif; ?>
</div>
<!-- DESKTOP -->
<div class="card mt-3">
  <div class="card-header text-white">
    💻 Assigned Desktop
  </div>

  <div class="card-body">

    <?php if ($desktopResult->num_rows > 0): ?>
      <?php while ($d = $desktopResult->fetch_assoc()): ?>

        <div class="asset-box p-3 mb-4">

          <div class="row g-4">

            <!-- DESKTOP IMAGE -->
            <div class="col-md text-center">
              <img src="../assets/img/desktop.png" class="img-fluid rounded shadow-sm" style="height: 320px;">
            </div>
        
            <!-- HARDWARE -->
            <div class="col-md-4">
              <div class="asset-section-title">Device Info</div>

              <div class="mb-2">
                <div class="asset-label">Computer Name</div>
                <div class="asset-value"><?= htmlspecialchars($d['computer_name']) ?></div>
              </div>

              <div class="mb-2">
                <div class="asset-label">CPU</div>
                <div class="asset-value"><?= htmlspecialchars($d['cpu']) ?></div>
              </div>

              <div class="mb-2">
                <div class="asset-label">RAM</div>
                <div class="asset-value"><?= htmlspecialchars($d['ram']) ?></div>
              </div>

            <div class="mb-2">
            <div class="asset-label">Storage</div>
            <div class="asset-value"><?= htmlspecialchars($d['rom_w_serial']) ?></div>
            </div>

              <div class="mb-2">
                <div class="asset-label">Motherboard</div>
                <div class="asset-value"><?= htmlspecialchars($d['motherboard']) ?></div>
              </div>
            </div>

            <!-- NETWORK -->
            <div class="col-md-3">
              <div class="asset-section-title">Network</div>

              <div class="mb-2">
                <div class="asset-label">IP Address</div>
                <div class="asset-value"><?= htmlspecialchars($d['ip_address']) ?></div>
              </div>

              <div class="mb-2">
                <div class="asset-label">MAC Address</div>
                <div class="asset-value"><?= htmlspecialchars($d['mac_address']) ?></div>
              </div>

              <div class="mb-2">
                <div class="asset-label">Windows Key</div>
                <div class="asset-value"><?= htmlspecialchars($d['windows_key']) ?></div>
              </div>

              <div class="mb-2">
                <div class="asset-label">Antivirus</div>
                <div class="asset-value"><?= htmlspecialchars($d['antivirus']) ?></div>
              </div>

              <div class="mb-2">
                <div class="asset-label">Tag Number</div>
                <div class="asset-value"><?= htmlspecialchars($d['tag_number']) ?></div>
              </div>

              <div class="mb-2">
                <div class="asset-label">Date Issued</div>
                <div class="asset-value"><?= date('m-d-Y', strtotime($d['date_created'])) ?></div>
              </div>
            </div>

            <!-- PERIPHERALS & REMARKS -->
            <div class="col-md-3">
              <div class="asset-section-title">Peripherals</div>

              <div class="mb-2">
                <div class="asset-label">Monitor</div>
                <div class="asset-value"><?= htmlspecialchars($d['monitor_w_serial']) ?></div>
              </div>

              <div class="mb-2">
                <div class="asset-label">Keyboard</div>
                <div class="asset-value"><?= htmlspecialchars($d['keyboard']) ?></div>
              </div>

              <div class="mb-2">
                <div class="asset-label">Mouse</div>
                <div class="asset-value"><?= htmlspecialchars($d['mouse']) ?></div>
              </div>

              <div class="mb-2">
                <div class="asset-label">AVR</div>
                <div class="asset-value"><?= htmlspecialchars($d['avr']) ?></div>
              </div>

              <div class="mb-2">
                <div class="asset-section-title">Remarks</div>
                <div class="asset-value"><?= htmlspecialchars($d['remarks'] ?? '-') ?></div>
              </div>
            </div>

          </div> <!-- row -->

        </div> <!-- asset-box -->

      <?php endwhile; ?>

    <?php else: ?>

      <div class="text-center text-muted">
        No desktop assigned.
      </div>

    <?php endif; ?>
  </div>
</div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

