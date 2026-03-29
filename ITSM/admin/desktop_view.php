<?php
include __DIR__ . '/../includes/db.php';

// Get desktop ID from QR
$desktop_id = $_GET['id'] ?? null;

if (!$desktop_id) {
    die("Invalid request.");
}

// Query desktop + QR
$sql = "
SELECT 
    d.*,
    q.qr_code_path,
    a.area_name
FROM qr_desktop_tb q
JOIN desktop_tb d ON d.desktop_id = q.desktop_id
LEFT JOIN desktop_area_tb a ON d.desktop_area_id = a.desktop_area_id
WHERE q.desktop_id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $desktop_id);
$stmt->execute();

$result = $stmt->get_result();
$desktop = $result->fetch_assoc();

if (!$desktop) {
    die("Desktop not found.");
}

// OUTPUT (PLAIN TEXT STYLE)
echo "<h2>Desktop Details</h2>";

echo "<strong>Tag Number:</strong> " . htmlspecialchars($desktop['tag_number']) . "<br>";
echo "<strong>Area:</strong> " . htmlspecialchars($desktop['area_name'] ?? 'N/A') . "<br>";
echo "<strong>Computer Name:</strong> " . htmlspecialchars($desktop['computer_name']) . "<br>";
echo "<strong>IP Address:</strong> " . htmlspecialchars($desktop['ip_address']) . "<br>";
echo "<strong>MAC Address:</strong> " . htmlspecialchars($desktop['mac_address']) . "<br><br>";

echo "<h3>Hardware</h3>";
echo "<strong>CPU:</strong> " . htmlspecialchars($desktop['cpu']) . "<br>";
echo "<strong>RAM:</strong> " . htmlspecialchars($desktop['ram']) . "<br>";
echo "<strong>Storage:</strong> " . htmlspecialchars($desktop['rom_w_serial']) . "<br>";
echo "<strong>Motherboard:</strong> " . htmlspecialchars($desktop['motherboard']) . "<br>";
echo "<strong>Monitor:</strong> " . htmlspecialchars($desktop['monitor_w_serial']) . "<br><br>";

echo "<h3>Peripherals</h3>";
echo "<strong>Keyboard:</strong> " . htmlspecialchars($desktop['keyboard']) . "<br>";
echo "<strong>Mouse:</strong> " . htmlspecialchars($desktop['mouse']) . "<br>";
echo "<strong>AVR:</strong> " . htmlspecialchars($desktop['avr']) . "<br><br>";

echo "<h3>Software</h3>";
echo "<strong>Windows Key:</strong> " . htmlspecialchars($desktop['windows_key']) . "<br>";
echo "<strong>Antivirus:</strong> " . htmlspecialchars($desktop['antivirus']) . "<br><br>";

echo "<h3>Remarks</h3>";
echo nl2br(htmlspecialchars($desktop['remarks']));
?>