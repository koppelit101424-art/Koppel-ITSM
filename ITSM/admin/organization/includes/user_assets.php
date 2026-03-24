<?php
include __DIR__ . '/../../../includes/auth.php';
include __DIR__ . '/../../../includes/db.php';

$user_id = $_GET['user_id'] ?? 0;

// Check if admin
if ($_SESSION['user_type'] !== 'admin') {
    die('Unauthorized');
}

// Fetch user info
$userStmt = $conn->prepare("SELECT fullname, emp_id FROM user_tb WHERE user_id = ?");
$userStmt->bind_param("i", $user_id);
$userStmt->execute();
$userInfo = $userStmt->get_result()->fetch_assoc();

// Fetch user transactions
$transactionSql = "
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
$stmt = $conn->prepare($transactionSql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$transactions = $stmt->get_result();

// Fetch desktops assigned to user
$desktopSql = "
SELECT 
    d.*,
    ud.date_created AS assigned_date
FROM user_desktop_tb ud
INNER JOIN desktop_tb d ON ud.desktop_id = d.desktop_id
WHERE ud.user_id = ?
";
$desktopStmt = $conn->prepare($desktopSql);
$desktopStmt->bind_param("i", $user_id);
$desktopStmt->execute();
$desktops = $desktopStmt->get_result();
?>

<h4>User Assets: <?= htmlspecialchars($userInfo['fullname']) ?> (<?= htmlspecialchars($userInfo['emp_id']) ?>)</h4>

<style>
.table-hover tbody tr:hover { background-color: #f1f1f1; }
.badge-issue { background-color: #0d6efd; }
.badge-borrow { background-color: #6f42c1; }
.badge-return { background-color: #198754; }
.asset-section-title {
    font-weight:600;
    font-size:14px;
    color:#1E3A8A;
    border-bottom:1px solid #e5e7eb;
    margin-bottom:8px;
    padding-bottom:4px;
}
.asset-label { font-size:12px; color:#6b7280; }
.asset-value { font-weight:500; white-space: normal; word-wrap: break-word; display:block; }
.asset-box { background:#f8fafc; border:1px solid #e5e7eb; border-radius:8px; }
.img-fluid { background-color:none; border:none; }
</style>

<!-- Issued / Borrowed Assets -->
<div class="card">
    <div class="card-header text-white">🕒 Issued / Borrowed Assets History</div>
    <div class="card-body">
        <?php if ($transactions->num_rows > 0): ?>
            <div class="row g-3">
                <?php while ($row = $transactions->fetch_assoc()): ?>
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
                                <div class="text-end text-muted"><?= date('m-d-Y', strtotime($row['action_date'])) ?></div>
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
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="text-center text-muted py-4">No assets have been issued to this user.</div>
        <?php endif; ?>
    </div>
</div>

<!-- Assigned Desktop -->
<div class="card mt-3">
    <div class="card-header text-white">💻 Assigned Desktop</div>
    <div class="card-body">
        <?php if ($desktops->num_rows > 0): ?>
            <?php while ($d = $desktops->fetch_assoc()): ?>
                <div class="asset-box p-3 mb-4">
                    <div class="row g-4">
                        <div class="col-md text-center">
                            <img src="../assets/img/desktop.png" class="img-fluid rounded shadow-sm" style="height: 320px;">
                        </div>
                        <div class="col-md-4">
                            <div class="asset-section-title">Device Info</div>
                            <div class="mb-2"><div class="asset-label">Computer Name</div><div class="asset-value"><?= htmlspecialchars($d['computer_name']) ?></div></div>
                            <div class="mb-2"><div class="asset-label">CPU</div><div class="asset-value"><?= htmlspecialchars($d['cpu']) ?></div></div>
                            <div class="mb-2"><div class="asset-label">RAM</div><div class="asset-value"><?= htmlspecialchars($d['ram']) ?></div></div>
                            <div class="mb-2"><div class="asset-label">Storage</div><div class="asset-value"><?= htmlspecialchars($d['rom_w_serial']) ?></div></div>
                            <div class="mb-2"><div class="asset-label">Motherboard</div><div class="asset-value"><?= htmlspecialchars($d['motherboard']) ?></div></div>
                        </div>
                        <div class="col-md-3">
                            <div class="asset-section-title">Network</div>
                            <div class="mb-2"><div class="asset-label">IP Address</div><div class="asset-value"><?= htmlspecialchars($d['ip_address']) ?></div></div>
                            <div class="mb-2"><div class="asset-label">MAC Address</div><div class="asset-value"><?= htmlspecialchars($d['mac_address']) ?></div></div>
                            <div class="mb-2"><div class="asset-label">Windows Key</div><div class="asset-value"><?= htmlspecialchars($d['windows_key']) ?></div></div>
                            <div class="mb-2"><div class="asset-label">Antivirus</div><div class="asset-value"><?= htmlspecialchars($d['antivirus']) ?></div></div>
                            <div class="mb-2"><div class="asset-label">Tag Number</div><div class="asset-value"><?= htmlspecialchars($d['tag_number']) ?></div></div>
                            <div class="mb-2"><div class="asset-label">Date Issued</div><div class="asset-value"><?= date('m-d-Y', strtotime($d['assigned_date'])) ?></div></div>
                        </div>
                        <div class="col-md-3">
                            <div class="asset-section-title">Peripherals</div>
                            <div class="mb-2"><div class="asset-label">Monitor</div><div class="asset-value"><?= htmlspecialchars($d['monitor_w_serial']) ?></div></div>
                            <div class="mb-2"><div class="asset-label">Keyboard</div><div class="asset-value"><?= htmlspecialchars($d['keyboard']) ?></div></div>
                            <div class="mb-2"><div class="asset-label">Mouse</div><div class="asset-value"><?= htmlspecialchars($d['mouse']) ?></div></div>
                            <div class="mb-2"><div class="asset-label">AVR</div><div class="asset-value"><?= htmlspecialchars($d['avr']) ?></div></div>
                            <div class="mb-2"><div class="asset-section-title">Remarks</div><div class="asset-value"><?= htmlspecialchars($d['remarks'] ?? '-') ?></div></div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="text-center text-muted">No desktop assigned.</div>
        <?php endif; ?>
    </div>
</div>