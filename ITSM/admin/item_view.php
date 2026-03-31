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
    s.comp_name
FROM qr_tb q
JOIN item_tb i ON i.item_id = q.item_id
LEFT JOIN item_condition_tb c ON i.condition_id = c.condition_id
LEFT JOIN laptop_pc_specs s ON s.item_id = i.item_id
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
</div>

<hr>

</body>
</html>