<?php
include __DIR__ . '/../includes/db.php';

// Get desktop ID from QR
$desktop_id = $_GET['id'] ?? null;

if (!$desktop_id) {
    die("Invalid request.");
}

// Query
$sql = "
SELECT 
    d.*,
    q.qr_code_path,
    a.area
FROM qr_desktop_tb q
JOIN desktop_tb d ON d.desktop_id = q.desktop_id
LEFT JOIN desktop_area_tb a ON d.desktop_area_id = a.desktop_area_id
WHERE q.desktop_id = ?
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("SQL Error: " . $conn->error);
}

$stmt->bind_param("i", $desktop_id);
$stmt->execute();

$result = $stmt->get_result();
$desktop = $result->fetch_assoc();

if (!$desktop) {
    die("Desktop not found.");
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Desktop Details</title>

<style>
body {
    font-family: Arial, sans-serif;
    padding: 20px;
    font-size: 25px; /* 🔥 bigger base */
}

/* CENTER TITLE */
h2 {
    text-align: center;
    margin-bottom: 20px;
    font-size: 50px; /* 🔥 bigger title */
}

.section {
    margin-bottom: 20px;
}

.title {
    font-weight: bold;
    margin-bottom: 8px;
    border-bottom: 2px solid #ccc;
    padding-bottom: 4px;
    font-size: 35px; /* 🔥 bigger section title */
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

/* 🔥 FIX LONG TEXT (MONITOR, SERIAL, ETC) */
.wrap {
    word-wrap: break-word;
    word-break: break-word;
    white-space: pre-wrap;
}
</style>

</head>
<body>

<h2>Desktop Details</h2><br>

<div class="section">
    <div class="row">
        <div class="col">
            <div class="label">Tag Number</div>
            <div class="value"><?= htmlspecialchars($desktop['tag_number']) ?></div>
        </div>

        <div class="col">
            <div class="label">Area</div>
            <div class="value"><?= htmlspecialchars($desktop['area'] ?? 'N/A') ?></div>
        </div>
    </div>
</div>

<!-- HARDWARE -->
<div class="section">
    <div class="row">
        <div class="col">
            <div class="title">Hardware Info</div>

            <div class="label">Computer Name</div>
            <div class="value"><?= htmlspecialchars($desktop['computer_name']) ?></div>

            <div class="label">CPU</div>
            <div class="value"><?= htmlspecialchars($desktop['cpu']) ?></div>

            <div class="label">RAM</div>
            <div class="value"><?= htmlspecialchars($desktop['ram']) ?></div>

            <div class="label">ROM / Serial</div>
            <div class="value wrap"><?= htmlspecialchars($desktop['rom_w_serial']) ?></div>

            <div class="label">Motherboard</div>
            <div class="value wrap"><?= htmlspecialchars($desktop['motherboard']) ?></div>
        </div>

        <div class="col">
            <div class="title">System & Network</div>

            <div class="label">IP Address</div>
            <div class="value"><?= htmlspecialchars($desktop['ip_address']) ?></div>

            <div class="label">MAC Address</div>
            <div class="value"><?= htmlspecialchars($desktop['mac_address']) ?></div>

            <div class="label">Windows Key</div>
            <div class="value wrap"><?= htmlspecialchars($desktop['windows_key']) ?></div>

            <div class="label">Antivirus</div>
            <div class="value"><?= htmlspecialchars($desktop['antivirus']) ?></div>
        </div>
    </div>
</div>

<!-- PERIPHERALS -->
<div class="section">
    <div class="row">
        <div class="col">
            <div class="title">Peripherals</div>

            <div class="label">Monitor</div>
            <div class="value wrap"><?= htmlspecialchars($desktop['monitor_w_serial']) ?></div>

            <div class="label">Keyboard</div>
            <div class="value"><?= htmlspecialchars($desktop['keyboard']) ?></div>

            <div class="label">Mouse</div>
            <div class="value"><?= htmlspecialchars($desktop['mouse']) ?></div>

            <div class="label">AVR</div>
            <div class="value"><?= htmlspecialchars($desktop['avr']) ?></div>
        </div>

        <div class="col">
            <div class="title">Remarks</div>
            <div class="value wrap"><?= nl2br(htmlspecialchars($desktop['remarks'])) ?></div>
        </div>
    </div>
</div>

<hr>

</body>
</html>