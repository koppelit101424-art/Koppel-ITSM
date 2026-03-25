<?php
// backup.php
ini_set('memory_limit', '1024M');
set_time_limit(0);

include __DIR__ . '/../../includes/auth.php';
include __DIR__ . '/../../includes/db.php';


// 🔒 OPTIONAL: Enable admin restriction if needed
/*
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    http_response_code(403);
    die('Access denied');
}
*/

// Get database name dynamically
$dbResult = $conn->query("SELECT DATABASE()");
$dbRow = $dbResult->fetch_row();
$database = $dbRow[0];

// Generate filename
$filename = $database . "_backup_" . date('Y-m-d_H-i-s') . ".sql";

// Force download headers
header('Content-Type: application/octet-stream');
header("Content-Disposition: attachment; filename=\"$filename\"");
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');

ob_start();

// SQL Header
echo "-- -------------------------------------------\n";
echo "-- Inventory Database Backup\n";
echo "-- Generated: " . date('Y-m-d H:i:s') . "\n";
echo "-- Database: {$database}\n";
echo "-- -------------------------------------------\n\n";

echo "SET FOREIGN_KEY_CHECKS=0;\n";
echo "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
echo "START TRANSACTION;\n\n";

// Get all tables
$tables = [];
$result = $conn->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'");
while ($row = $result->fetch_row()) {
    $tables[] = $row[0];
}

// Export each table
foreach ($tables as $table) {

    echo "\n-- -------------------------------------------\n";
    echo "-- Table structure for `$table`\n";
    echo "-- -------------------------------------------\n\n";

    // Drop table first
    echo "DROP TABLE IF EXISTS `$table`;\n";

    // Create table structure
    $createResult = $conn->query("SHOW CREATE TABLE `$table`");
    $createRow = $createResult->fetch_row();
    echo $createRow[1] . ";\n\n";

    // Export data
    $dataResult = $conn->query("SELECT * FROM `$table`");

    if ($dataResult->num_rows > 0) {
        echo "-- Dumping data for `$table`\n";

        while ($row = $dataResult->fetch_assoc()) {

            $columns = array_map(function($col) {
                return "`$col`";
            }, array_keys($row));

            $values = array_map(function($value) use ($conn) {
                if (is_null($value)) {
                    return "NULL";
                }
                return "'" . $conn->real_escape_string($value) . "'";
            }, array_values($row));

            echo "INSERT INTO `$table` (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $values) . ");\n";
        }
        echo "\n";
    }
}

echo "COMMIT;\n";
echo "SET FOREIGN_KEY_CHECKS=1;\n";
echo "\n-- Backup completed successfully\n";

// Output file
$output = ob_get_clean();
echo $output;

$conn->close();
exit;
?>
