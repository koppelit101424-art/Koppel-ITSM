<?php
include __DIR__ . '/../../../includes/db.php';
include __DIR__ . '/../../../phpqrcode/qrlib.php';

// Folder to save QR images
$qrFolder = __DIR__ . '/../qrcodes/';
// Create folder if not exists
if (!is_dir($qrFolder)) {
    mkdir($qrFolder, 0777, true);
}

// Get items WITHOUT QR
$sql = "SELECT i.item_id
FROM item_tb i
LEFT JOIN qr_tb q ON i.item_id = q.item_id
WHERE i.item_id = 318";

// $sql = "
// SELECT i.item_id
// FROM item_tb i
// LEFT JOIN qr_tb q ON i.item_id = q.item_id
// WHERE q.item_id IS NULL
// ";

$result = $conn->query($sql);

if (!$result) {
    die("Query error: " . $conn->error);
}

$count = 0;

while ($row = $result->fetch_assoc()) {

    $item_id = $row['item_id'];

    // 🔗 URL that QR will open
    $qrValue = "http://115.88.1.63/koppel-itsm/ITSM/admin/item_view.php?id=" . $item_id;
    $folder = "qrcodes/";
    // 📁 File name
    $fileName = "item_" . $item_id . ".png";
    $filePath = $qrFolder . $fileName;
    $qrCodePath = $folder . $fileName;
    // Generate QR
    QRcode::png($qrValue, $filePath, QR_ECLEVEL_L, 5);

    // Save to database
    $stmt = $conn->prepare("
        INSERT INTO qr_tb (item_id, qr_code_path, qr_value, created_at)
        VALUES (?, ?, ?, NOW())
    ");

    $stmt->bind_param("iss", $item_id, $fileName, $qrValue);
    $stmt->execute();

    $count++;
}

echo "✅ Generated QR for $count items.";