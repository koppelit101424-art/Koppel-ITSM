<?php
include __DIR__ . '/../includes/db.php';

// Get item ID from QR
$item_id = $_GET['id'] ?? null;

if (!$item_id) {
    die("Invalid request.");
}

// Query
$sql = "
SELECT 
    i.*, 
    q.qr_code_path,
    c.condition_name,
    s.cpu,
    s.ram,
    s.rom,
    s.motherboard,
    s.os,
    s.`key`,
    s.antivirus,
    s.comp_name,
    t.action,
    t.quantity AS trans_quantity,
    t.action_date,
    t.date_returned,
    t.remarks,
    u.fullname,
    u.position,
    u.department,
    u.company

FROM qr_tb q
JOIN item_tb i ON i.item_id = q.item_id
LEFT JOIN item_condition_tb c ON i.condition_id = c.condition_id
LEFT JOIN laptop_pc_specs s ON s.item_id = i.item_id

LEFT JOIN (
    SELECT t1.*
    FROM transaction_tb t1
    INNER JOIN (
        SELECT item_id, MAX(transaction_id) AS latest_id
        FROM transaction_tb
        GROUP BY item_id
    ) t2 ON t1.transaction_id = t2.latest_id
) t ON t.item_id = i.item_id

LEFT JOIN user_tb u ON t.user_id = u.user_id

WHERE q.item_id = ?
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("SQL Error: " . $conn->error);
}

$stmt->bind_param("i", $item_id);
$stmt->execute();

$result = $stmt->get_result();
$item = $result->fetch_assoc();

if (!$item) {
    die("Item not found.");
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Item Details</title>

<style>
body {
    font-family: Arial, sans-serif;
    padding: 20px;
    font-size: 25px;
}

/* CENTER TITLE */
h2 {
    text-align: center;
    margin-bottom: 20px;
    font-size: 50px;
}

.section {
    margin-bottom: 20px;
}

.title {
    font-weight: bold;
    margin-bottom: 8px;
    border-bottom: 2px solid #ccc;
    padding-bottom: 4px;
    font-size: 35px;
}

.row {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
}

.col {
    flex: 1;
    min-width: 260px;
}

.label {
    font-size: 20px;
    color: #666;
}

.value {
    font-weight: bold;
    margin-bottom: 10px;
    font-size: 28px;
}

/* TEXT WRAP FIX */
.wrap {
    word-wrap: break-word;
    word-break: break-word;
    white-space: pre-wrap;
}
</style>

</head>
<body>

<h2>Item Details</h2>

<!-- BASIC INFO -->
<div class="section">
    <div class="row">
        <div class="col">
            <div class="label">Item Code</div>
            <div class="value"><?= htmlspecialchars($item['item_code']) ?></div>

            <div class="label">Name</div>
            <div class="value"><?= htmlspecialchars($item['name']) ?></div>

            <div class="label">Brand</div>
            <div class="value"><?= htmlspecialchars($item['brand']) ?></div>

            <div class="label">Model</div>
            <div class="value wrap"><?= htmlspecialchars($item['model']) ?></div>
        </div>

        <div class="col">
            <div class="label">Serial Number</div>
            <div class="value wrap"><?= htmlspecialchars($item['serial_number']) ?></div>

            <div class="label">Quantity</div>
            <div class="value"><?= htmlspecialchars($item['quantity']) ?></div>

            <div class="label">Date Received</div>
            <div class="value"><?= htmlspecialchars($item['date_received']) ?></div>

            <div class="label">Condition</div>
            <div class="value"><?= htmlspecialchars($item['condition_name'] ?? 'N/A') ?></div>
        </div>
    </div>
</div>

<!-- SPECS -->
<?php if (!empty($item['cpu']) || !empty($item['ram'])): ?>
<div class="section">
    <div class="title">Specifications</div>

    <div class="row">
        <div class="col">
            <div class="label">CPU</div>
            <div class="value"><?= htmlspecialchars($item['cpu']) ?></div>

            <div class="label">RAM</div>
            <div class="value"><?= htmlspecialchars($item['ram']) ?></div>

            <div class="label">Storage</div>
            <div class="value wrap"><?= htmlspecialchars($item['rom']) ?></div>

            <div class="label">Motherboard</div>
            <div class="value wrap"><?= htmlspecialchars($item['motherboard']) ?></div>
        </div>

        <div class="col">
            <div class="label">OS</div>
            <div class="value"><?= htmlspecialchars($item['os']) ?></div>

            <div class="label">OS Key</div>
            <div class="value wrap"><?= htmlspecialchars($item['key']) ?></div>

            <div class="label">Antivirus</div>
            <div class="value"><?= htmlspecialchars($item['antivirus']) ?></div>

            <div class="label">Computer Name</div>
            <div class="value"><?= htmlspecialchars($item['comp_name']) ?></div>
        </div>
    </div>
</div>
<?php endif; ?> 
<!-- DESCRIPTION -->
<div class="section">
    <div class="title">Description</div>
    <div class="value wrap"><?= nl2br(htmlspecialchars($item['description'])) ?></div>
</div> <br>
<!-- ISSUANCE -->
<div class="section">
    <div class="title">Issuance Status</div>

    <?php if (!empty($item['action'])): ?>

        <div class="label">Status</div>
        <div class="value">
            <?php
            switch(strtolower($item['action'])) {
                case 'issue':
                    echo 'Issued';
                    break;
                case 'borrow':
                    echo 'Borrowed';
                    break;
                default:
                    echo ucfirst($item['action']);
            }
            ?>
        </div>

        <div class="label">Issued To</div>
        <div class="value">
            <?= htmlspecialchars($item['fullname'] ?? 'Unknown User') ?>
        </div>

        <?php if (!empty($item['position'])): ?>
            <div class="label">Position</div>
            <div class="value"><?= htmlspecialchars($item['position']) ?></div>
        <?php endif; ?>

        <?php if (!empty($item['department'])): ?>
            <div class="label">Department</div>
            <div class="value"><?= htmlspecialchars($item['department']) ?></div>
        <?php endif; ?>

        <?php if (!empty($item['company'])): ?>
            <div class="label">Company</div>
            <div class="value"><?= htmlspecialchars($item['company']) ?></div>
        <?php endif; ?>

        <div class="label">Action Date</div>
        <div class="value">
            <?= !empty($item['action_date']) ? htmlspecialchars($item['action_date']) : 'N/A' ?>
        </div>

        <?php if (!empty($item['remarks'])): ?>
            <div class="label">Remarks</div>
            <div class="value wrap"><?= htmlspecialchars($item['remarks']) ?></div>
        <?php endif; ?>

    <?php else: ?>

        <div class="value">In Stock / Not Issued</div>

    <?php endif; ?>
</div> <br>
<hr>

</body>
</html>