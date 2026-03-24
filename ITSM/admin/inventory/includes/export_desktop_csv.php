<?php
// Auth and DB connection
include '../../auth/auth.php'; // Optional: remove if no auth needed for export
include '../../db/db.php';

// Fetch all desktop records
$sql = "SELECT 
    desktop_id,
    cpu,
    ram,
    rom_w_serial,
    motherboard,
    monitor_w_serial,
    avr,
    mouse,
    keyboard,
    ip_address,
    mac_address,
    computer_name,
    windows_key,
    antivirus,
    tag_number,
    remarks,
    date_created
FROM desktop_tb 
ORDER BY desktop_id DESC";

$result = $conn->query($sql);

if (!$result) {
    die('Query failed: ' . $conn->error);
}

// Set headers to force download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="desktop_inventory_' . date('Y-m-d') . '.csv"');

// Open output stream
$output = fopen('php://output', 'w');

// Write UTF-8 BOM (optional, helps Excel display special chars correctly)
fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

// Write CSV header row
fputcsv($output, [
    'Desktop ID',
    'CPU',
    'RAM',
    'ROM w/ Serial',
    'Motherboard',
    'Monitor w/ Serial',
    'AVR',
    'Mouse',
    'Keyboard',
    'IP Address',
    'MAC Address',
    'Computer Name',
    'Windows Key',
    'Antivirus',
    'Tag Number',
    'Remarks',
    'Date Created'
]);

// Write data rows
while ($row = $result->fetch_assoc()) {
    // Clean and escape each field (fputcsv handles quoting)
    fputcsv($output, [
        $row['desktop_id'] ?? '',
        $row['cpu'] ?? '',
        $row['ram'] ?? '',
        $row['rom_w_serial'] ?? '',
        $row['motherboard'] ?? '',
        $row['monitor_w_serial'] ?? '',
        $row['avr'] ?? '',
        $row['mouse'] ?? '',
        $row['keyboard'] ?? '',
        $row['ip_address'] ?? '',
        $row['mac_address'] ?? '',
        $row['computer_name'] ?? '',
        $row['windows_key'] ?? '',
        $row['antivirus'] ?? '',
        $row['tag_number'] ?? '',
        $row['remarks'] ?? '',
        $row['date_created'] ?? ''
    ]);
}

fclose($output);
$conn->close();
exit();
?>