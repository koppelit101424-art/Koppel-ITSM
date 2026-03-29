<?php
include __DIR__ . '/../../../includes/db.php';
include __DIR__ . '/../../../phpqrcode/qrlib.php';

// Folder to save QR images
$qrFolder = __DIR__ . '/../qrcodes/desktop_qrcodes/';

// Create folder if not exists
if (!is_dir($qrFolder)) {
    mkdir($qrFolder, 0777, true);
}

// Get desktops WITHOUT QR
$sql = "
SELECT d.desktop_id
FROM desktop_tb d
LEFT JOIN qr_desktop_tb q ON d.desktop_id = q.desktop_id
WHERE q.desktop_id IS NULL
";

$result = $conn->query($sql);

if (!$result) {
    die("Query error: " . $conn->error);
}

$count = 0;

while ($row = $result->fetch_assoc()) {

    $desktop_id = $row['desktop_id'];

    // 🔗 URL that QR will open
    $qrValue = "http://115.88.1.63/koppel-itsm/ITSM/admin/desktop_view.php?id=" . $desktop_id;

    // File name
    $fileName = "desktop_" . $desktop_id . ".png";
    $filePath = $qrFolder . $fileName;
    

    // Path to store in DB (relative)
    $qrCodePath = "qrCodePath/" . $fileName;

    // Generate QR
    QRcode::png($qrValue, $filePath, QR_ECLEVEL_L, 5);

    // Save to database
    $stmt = $conn->prepare("
        INSERT INTO qr_desktop_tb (desktop_id, qr_code_path, qr_value, created_at)
        VALUES (?, ?, ?, NOW())
    ");

    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("iss", $desktop_id, $qrCodePath, $qrValue);
    $stmt->execute();

    $count++;
}

echo "✅ Generated QR for $count desktop(s).";
?>