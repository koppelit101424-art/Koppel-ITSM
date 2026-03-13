<?php
include '../../auth/auth.php';
include '../../db/db.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('Invalid desktop ID');
}

$desktop_id = (int)$_GET['id'];

// Fetch desktop data
$sql = "SELECT * FROM desktop_tb WHERE desktop_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $desktop_id);
$stmt->execute();
$result = $stmt->get_result();

if (!$result || $result->num_rows === 0) {
    die('Desktop not found');
}

$desktop = $result->fetch_assoc();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update logic (same as add_desktop.php but with UPDATE)
    $cpu = $conn->real_escape_string(trim($_POST['cpu'] ?? ''));
    $ram = $conn->real_escape_string(trim($_POST['ram'] ?? ''));
    $rom_w_serial = $conn->real_escape_string(trim($_POST['rom_w_serial'] ?? ''));
    $motherboard = $conn->real_escape_string(trim($_POST['motherboard'] ?? ''));
    $monitor_w_serial = $conn->real_escape_string(trim($_POST['monitor_w_serial'] ?? ''));
    $avr = $conn->real_escape_string(trim($_POST['avr'] ?? ''));
    $mouse = $conn->real_escape_string(trim($_POST['mouse'] ?? ''));
    $keyboard = $conn->real_escape_string(trim($_POST['keyboard'] ?? ''));
    $ip_address = $conn->real_escape_string(trim($_POST['ip_address'] ?? ''));
    $mac_address = $conn->real_escape_string(trim($_POST['mac_address'] ?? ''));
    $computer_name = $conn->real_escape_string(trim($_POST['computer_name'] ?? ''));
    $windows_key = $conn->real_escape_string(trim($_POST['windows_key'] ?? ''));
    $antivirus = $conn->real_escape_string(trim($_POST['antivirus'] ?? ''));
    $tag_number = $conn->real_escape_string(trim($_POST['tag_number'] ?? ''));
    $remarks = $conn->real_escape_string(trim($_POST['remarks'] ?? ''));

    if (empty($cpu) || empty($ram) || empty($computer_name)) {
        $message = "CPU, RAM, and Computer Name are required.";
    } else {
        $updateSql = "
            UPDATE desktop_tb SET
                cpu = '$cpu',
                ram = '$ram',
                rom_w_serial = '$rom_w_serial',
                motherboard = '$motherboard',
                monitor_w_serial = '$monitor_w_serial',
                avr = '$avr',
                mouse = '$mouse',
                keyboard = '$keyboard',
                ip_address = '$ip_address',
                mac_address = '$mac_address',
                computer_name = '$computer_name',
                windows_key = '$windows_key',
                antivirus = '$antivirus',
                tag_number = '$tag_number',
                remarks = '$remarks'
            WHERE desktop_id = $desktop_id
        ";

        if ($conn->query($updateSql)) {
            $message = "Desktop updated successfully!";
            // Refresh data
            $desktop = $_POST;
        } else {
            $message = "Error: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Desktop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../asset/css/main.css">
    <style>
        .form-section {
            background-color: #f8f9fa;
            padding-left: 10px;
            padding-right: 10px;
            padding-bottom: 3px;
            border-radius: 6px;
            /* margin-bottom: 5px; */
        }
        .form-section h6 {
            margin-bottom: 10px;
            color: #333;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar col-md-2" id="sidebar" style="height: 100vh; width: 250px;">
        <h4 class="text-white px-3 sidebar-title">Asset Management</h4>
        <!-- Inventory (toggle parent) -->
        <a href="#" id="inventory-toggle" style="cursor: pointer;">
            <i class="fas fa-boxes"></i>
            <span class="sidebar-text">Inventory</span>
            <i class="fas fa-chevron-down ms-auto" id="inventory-icon" style="font-size: 0.75rem; margin-left: auto;"></i>
        </a>

        <!-- Submenu: Desktops (initially hidden) -->
        <div id="inventory-submenu" style="display: none;">
            <a href="desktop.php" style="padding-left: 2.5rem; font-size: 0.92rem; opacity: 0.9;" class="active">
                <i class="fas fa-desktop" style="width: 1.25rem;"></i>
                <span class="sidebar-text">Desktops</span>
            </a>
                    <a href="../laptop/laptop.php" style="padding-left: 2.5rem; font-size: 0.92rem; opacity: 0.9;">
                <i class="fas fa-laptop" style="width: 1.25rem;"></i>
                <span class="sidebar-text">Laptops</span>
            </a>
            <!-- Add more sub-items here later if needed, e.g., Laptops, Printers -->
        </div>

        <a href="../../users.php">
            <i class="fas fa-user-tie"></i>
            <span class="sidebar-text">Users</span>
        </a>
        <a href="../../requests.php">
            <i class="fas fa-file-alt"></i>
            <span class="sidebar-text">Requests</span>
        </a>
        <a href="../../transactions.php">
            <i class="fas fa-file-invoice-dollar"></i>
            <span class="sidebar-text">Transactions</span>
        </a>
        <a href="../../reports.php">
            <i class="fas fa-chart-line"></i>
            <span class="sidebar-text">Reports</span>
        </a>
        <a href="../../settings.php">
            <i class="fas fa-cog"></i>
            <span class="sidebar-text">Settings</span>
        </a>
        <!-- <a href="logout.php" onclick="return confirm('Are you sure you want to log out?');">
            <i class="fas fa-right-from-bracket"></i>
            <span class="sidebar-text">Logout</span>
        </a> -->
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const toggle = document.getElementById('inventory-toggle');
        const submenu = document.getElementById('inventory-submenu');
        const icon = document.getElementById('inventory-icon');

        // Optional: auto-expand if on desktop.php
        const currentPage = window.location.pathname;
        if (currentPage.includes('desktop.php')) {
            submenu.style.display = 'block';
            icon.classList.replace('fa-chevron-down', 'fa-chevron-up');
        }

        toggle.addEventListener('click', function (e) {
            e.preventDefault();
            const isVisible = submenu.style.display === 'block';

            if (isVisible) {
                submenu.style.display = 'none';
                icon.classList.replace('fa-chevron-up', 'fa-chevron-down');
            } else {
                submenu.style.display = 'block';
                icon.classList.replace('fa-chevron-down', 'fa-chevron-up');
            }
        });
    });
    </script>
    <div class="main-content" style="margin-left: 250px; padding: 20px;">
        <h4 class="mb-4"><i class="fas fa-edit me-2"></i> Edit Desktop </h4>

        <?php if ($message): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <form method="POST">
            <!-- System Info -->
            <div class="form-section">
                <h6><i class="fas fa-microchip me-2"></i> System Specifications</h6>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">CPU *</label>
                        <input type="text" name="cpu" class="form-control" value="<?= htmlspecialchars($desktop['cpu'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">RAM *</label>
                        <input type="text" name="ram" class="form-control" value="<?= htmlspecialchars($desktop['ram'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">ROM w/ Serial</label>
                        <textarea name="rom_w_serial" class="form-control" rows="2"><?= htmlspecialchars($desktop['rom_w_serial'] ?? '') ?></textarea>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Motherboard</label>
                        <input type="text" name="motherboard" class="form-control" value="<?= htmlspecialchars($desktop['motherboard'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <!-- Peripherals -->
            <div class="form-section">
                <h6><i class="fas fa-tv me-2"></i> Peripherals</h6>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Monitor w/ Serial</label>
                        <textarea name="monitor_w_serial" class="form-control" rows="2"><?= htmlspecialchars($desktop['monitor_w_serial'] ?? '') ?></textarea>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">AVR</label>
                        <input type="text" name="avr" class="form-control" value="<?= htmlspecialchars($desktop['avr'] ?? '') ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Mouse</label>
                        <input type="text" name="mouse" class="form-control" value="<?= htmlspecialchars($desktop['mouse'] ?? '') ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Keyboard</label>
                        <input type="text" name="keyboard" class="form-control" value="<?= htmlspecialchars($desktop['keyboard'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <!-- Network & Software -->
            <div class="form-section">
                <h6><i class="fas fa-network-wired me-2"></i> Network & Software</h6>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">IP Address</label>
                        <input type="text" name="ip_address" class="form-control" value="<?= htmlspecialchars($desktop['ip_address'] ?? '') ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">MAC Address</label>
                        <input type="text" name="mac_address" class="form-control" value="<?= htmlspecialchars($desktop['mac_address'] ?? '') ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Computer Name *</label>
                        <input type="text" name="computer_name" class="form-control" value="<?= htmlspecialchars($desktop['computer_name'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Windows Key</label>
                        <input type="text" name="windows_key" class="form-control" value="<?= htmlspecialchars($desktop['windows_key'] ?? '') ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Antivirus</label>
                        <select name="antivirus" class="form-control">
                            <option value="YES" <?= ($desktop['antivirus'] ?? '') === 'YES' ? 'selected' : '' ?>>YES</option>
                            <option value="NO" <?= ($desktop['antivirus'] ?? '') === 'NO' ? 'selected' : '' ?>>NO</option>
                            <option value="NONE" <?= ($desktop['antivirus'] ?? '') === 'NONE' ? 'selected' : '' ?>>NONE</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tag Number</label>
                        <input type="text" name="tag_number" class="form-control" value="<?= htmlspecialchars($desktop['tag_number'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <!-- Remarks -->
            <div class="form-section">
                <h6><i class="fas fa-sticky-note me-2"></i> Remarks</h6>
                <div class="mb-3">
                    <textarea name="remarks" class="form-control" rows="3"><?= htmlspecialchars($desktop['remarks'] ?? '') ?></textarea>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> Update Desktop
                </button>
                <a href="desktop.php" class="btn btn-secondary">
                    <i class="fas fa-times me-1"></i> Cancel
                </a>
            </div>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>