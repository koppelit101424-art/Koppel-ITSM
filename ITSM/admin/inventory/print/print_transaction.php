<?php
include __DIR__ . '/../../../includes/auth.php';
include __DIR__ . '/../../../includes/db.php';

if (!isset($_GET['id'])) {
    die("Invalid request.");
}

$transaction_id = intval($_GET['id']);

$sql = "SELECT 
        t.transaction_id, t.action, t.quantity, t.action_date, t.remarks,
        i.name AS item_name, i.item_code, i.brand, i.model, i.serial_number, i.description,
        s.cpu, s.ram, s.rom, s.motherboard, s.os, s.`key`, s.antivirus, s.comp_name,
        u.fullname, u.emp_id, u.department, u.position, u.area, u.company
        FROM transaction_tb t
        JOIN item_tb i ON t.item_id = i.item_id
        LEFT JOIN laptop_pc_specs s ON i.item_id = s.item_id
        JOIN user_tb u ON t.user_id = u.user_id
        WHERE t.transaction_id = $transaction_id";

$result = $conn->query($sql);

if ($result->num_rows === 0) {
    die("Transaction not found.");
}

$row = $result->fetch_assoc();

/* Detect if Laptop or System Unit */
$itemName = strtolower($row['item_name']);
$isComputer = (strpos($itemName, 'laptop') !== false || strpos($itemName, 'system unit') !== false);
?>

<style>
body {
    padding: -10px;
    font-size: 10px;
    font-family: Arial, Helvetica, sans-serif;
}

.header {
    text-align: center;
}

.header p {
    font-size: 10px;
    color: #555;
    margin: 0;
}

footer.sticky-footer {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    width: 100%;
    background: #fff;
    z-index: 1000;
    padding: 7px 0;
}

footer.sticky-footer table {
    width: 100%;
    text-align: center;
    font-size: 10px;
    border-collapse: collapse;
    border: 2px solid #000;
}

footer.sticky-footer td {
    padding: 10px;
}

body {
    margin: 0;
    padding-bottom: 50px;
}

@media print {
    body { padding-bottom: 0; }
    footer.sticky-footer { position: fixed; bottom: 0; }
}
</style>

<body onload="window.print()">

<?php
/* Select company logo */
$company = strtoupper(trim($row['company'] ?? ''));

switch ($company) {
    case 'KOPPEL INC.':
        $logo = '../assets/img/Koppel.jpg';
        break;
    case 'HEEC':
        $logo = '../assets/img/HEEC.jpg';
        break;
    case 'HIMC':
        $logo = '../assets/img/HIMC.png';
        break;
    case 'HI-AIRE':
        $logo = '../assets/img/HIAIRE.png';
        break;
    default:
        $logo = '../assets/img/Koppel.jpg';
}
?>

<!-- Header -->
<div class="header">
    <img src="<?= $logo ?>" height="80" style="margin-bottom:5px;">
    <p>Hyatt Centre Ortigas Ave., Brgy. Wack Wack, Mandaluyong City, Metro Manila</p>
    <hr style="border:none;border-top:2px dashed black;">
    <h1><strong>ISSUANCE FORM</strong></h1>
    <hr style="border:none;border-top:2px dashed black;">
</div>

<!-- Employee Info -->
<table border="1" cellpadding="10" cellspacing="0" style="width:100%; border-collapse:collapse; margin:20px auto; border:2px solid #000;">

<tr>
<td style="width:50%; border:2px solid #000; text-align:left; padding:6px; font-size:10px;">
<strong>Name: <?= htmlspecialchars($row['fullname']) ?></strong>
</td>

<td style="width:50%; border:2px solid #000;text-align:left; padding:6px; font-size:10px;">
<strong>Position: <?= htmlspecialchars($row['position']) ?></strong>
</td>
</tr>

<tr>
<td style="border:2px solid #000; text-align:left; padding:6px; font-size:10px;">
<strong>Employee No.: <?= htmlspecialchars($row['emp_id']) ?></strong>
</td>

<td style="border:2px solid #000; text-align:left; padding:6px; font-size:10px;">
<strong>Area: <?= htmlspecialchars($row['area']) ?></strong>
</td>
</tr>

</table>

<!-- Item Table -->
<table border="1" cellpadding="6" cellspacing="0"
style="width:100%; border-collapse:collapse; text-align:center; border:2px solid #000; font-size:10px;">

<tr>
<th style="border:none;">No</th>
<th style="border:none;">Item Code</th>
<th style="border:none;">Unit Type</th>
<th style="border:none;">Brand</th>
<th style="border:none;">Model</th>
<th style="border:none;">Serial</th>
<th style="border:none;">Qty</th>
<th style="border:none;">Date Issued</th>
</tr>

<tr>
<td style="border:none;">1</td>
<td style="border:none;"><?= $row['item_code'] ?></td>
<td style="border:none;"><?= $row['item_name'] ?></td>
<td style="border:none;"><?= $row['brand'] ?></td>
<td style="border:none;"><?= $row['model'] ?></td>
<td style="border:none;"><?= $row['serial_number'] ?></td>
<td style="border:none;"><?= $row['quantity'] ?></td>
<td style="border:none;"><?= date('m-d-Y', strtotime($row['action_date'])) ?></td>
</tr>

<!-- COMPUTER SPECS -->
<?php 
$hasSpecs = !empty(trim($row['cpu'] ?? '')) ||
            !empty(trim($row['ram'] ?? '')) ||
            !empty(trim($row['rom'] ?? '')) ||
            !empty(trim($row['motherboard'] ?? '')) ||
            !empty(trim($row['os'] ?? '')) ||
            !empty(trim($row['key'] ?? '')) ||
            !empty(trim($row['antivirus'] ?? '')) ||
            !empty(trim($row['comp_name'] ?? ''));

if ($isComputer && $hasSpecs):
?>

<tr>
<td colspan="8" style="border:none; text-align:left; padding-top:8px;">
<strong>Specifications:</strong>

<table style="width:100%; margin-top:5px; font-size:10px;">
<td>

<?php if(!empty(trim($row['cpu'] ?? ''))): ?>
<strong>CPU:</strong> <?= htmlspecialchars($row['cpu']) ?><br>
<?php endif; ?>

<?php if(!empty(trim($row['ram'] ?? ''))): ?>
<strong>RAM:</strong> <?= htmlspecialchars($row['ram']) ?><br>
<?php endif; ?>

<?php if(!empty(trim($row['rom'] ?? ''))): ?>
<strong>ROM:</strong> <?= htmlspecialchars($row['rom']) ?><br>
<?php endif; ?>

<?php if(!empty(trim($row['motherboard'] ?? ''))): ?>
<strong>Motherboard:</strong> <?= htmlspecialchars($row['motherboard']) ?><br>
<?php endif; ?>

<?php if(!empty(trim($row['os'] ?? ''))): ?>
<strong>Operating System:</strong> <?= htmlspecialchars($row['os']) ?><br>
<?php endif; ?>

<?php if(!empty(trim($row['key'] ?? ''))): ?>
<strong>OS Key:</strong> <?= htmlspecialchars($row['key']) ?><br>
<?php endif; ?>

<?php if(!empty(trim($row['antivirus'] ?? ''))): ?>
<strong>Antivirus:</strong> <?= htmlspecialchars($row['antivirus']) ?><br>
<?php endif; ?>

<?php if(!empty(trim($row['comp_name'] ?? ''))): ?>
<strong>Computer Name:</strong> <?= htmlspecialchars($row['comp_name']) ?><br>
<?php endif; ?>

</td>
</table>

</td>
</tr>

<?php endif; ?>

<!-- Description -->
<tr>
<td colspan="8" style="border:none; text-align:left; padding-top: 5px; padding-bottom:10px;">

<strong>Description:</strong>

<pre style="margin:0; font-family:inherit; white-space: pre-wrap;">
<?= htmlspecialchars($row['description']) ?>
</pre>

</td>
</tr>

</table>

<br>

<!-- Remarks -->
<p>
<strong>Reason of Issuance / Remarks:</strong><br>
<pre style="margin:0; font-family:inherit; white-space: pre-wrap;">
<?= htmlspecialchars($row['remarks']) ?>
</pre>
</p>

<!-- Footer -->
<footer class="sticky-footer">
<table>

<tr>

<td>
Prepared by:<br><br><br>
<strong>IT Staff/Assistant</strong>
</td>

<td>
Checked by:<br><br><br>
<strong>IT Supervisor</strong>
</td>

<td>
Noted by:<br><br><br>
<strong>HR Department</strong>
</td>

<td>
Noted by:<br><br><br>
<strong>IT Director</strong>
</td>

<td>
Received by:<br><br>
<strong>
<?= htmlspecialchars($row['fullname']) ?><br>
<?= htmlspecialchars($row['position']) ?>
</strong>
</td>

</tr>

</table>
</footer>