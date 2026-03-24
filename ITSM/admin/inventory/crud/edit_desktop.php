<?php
include __DIR__ . '/../../../includes/auth.php';
include __DIR__ . '/../../../includes/db.php';

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
    <style>
        .form-section {
            /* background-color: #f8f9fa; */
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

<div class="main-content" id="mainContent">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center text-white">
 
        <h4 class="mb-2"><i class="fas fa-edit me-2"></i> Edit Desktop </h4></div>

        <?php if ($message): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <form method="POST">
            <!-- System Info -->
            <div class="form-section">
                <h6><i class="fas fa-microchip mt-2"></i> System Specifications</h6>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">CPU *</label>
                        <input type="text" name="cpu" class="form-control" value="<?= htmlspecialchars($desktop['cpu'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">RAM *</label>
                        <input type="text" name="ram" class="form-control" value="<?= htmlspecialchars($desktop['ram'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">ROM w/ Serial</label>
                        <textarea name="rom_w_serial" class="form-control" rows="2"><?= htmlspecialchars($desktop['rom_w_serial'] ?? '') ?></textarea>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Motherboard</label>
                        <input type="text" name="motherboard" class="form-control" value="<?= htmlspecialchars($desktop['motherboard'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <!-- Peripherals -->
            <div class="form-section">
                <h6><i class="fas fa-tv me-2"></i> Peripherals</h6>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Monitor w/ Serial</label>
                        <textarea name="monitor_w_serial" class="form-control" rows="2"><?= htmlspecialchars($desktop['monitor_w_serial'] ?? '') ?></textarea>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">AVR</label>
                        <input type="text" name="avr" class="form-control" value="<?= htmlspecialchars($desktop['avr'] ?? '') ?>">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Mouse</label>
                        <input type="text" name="mouse" class="form-control" value="<?= htmlspecialchars($desktop['mouse'] ?? '') ?>">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Keyboard</label>
                        <input type="text" name="keyboard" class="form-control" value="<?= htmlspecialchars($desktop['keyboard'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <!-- Network & Software -->
            <div class="form-section">
                <h6><i class="fas fa-network-wired me-2"></i> Network & Software</h6>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">IP Address</label>
                        <input type="text" name="ip_address" class="form-control" value="<?= htmlspecialchars($desktop['ip_address'] ?? '') ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">MAC Address</label>
                        <input type="text" name="mac_address" class="form-control" value="<?= htmlspecialchars($desktop['mac_address'] ?? '') ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Computer Name *</label>
                        <input type="text" name="computer_name" class="form-control" value="<?= htmlspecialchars($desktop['computer_name'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Windows Key</label>
                        <input type="text" name="windows_key" class="form-control" value="<?= htmlspecialchars($desktop['windows_key'] ?? '') ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Antivirus</label>
                        <select name="antivirus" class="form-control">
                            <option value="YES" <?= ($desktop['antivirus'] ?? '') === 'YES' ? 'selected' : '' ?>>YES</option>
                            <option value="NO" <?= ($desktop['antivirus'] ?? '') === 'NO' ? 'selected' : '' ?>>NO</option>
                            <option value="NONE" <?= ($desktop['antivirus'] ?? '') === 'NONE' ? 'selected' : '' ?>>NONE</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
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
                <button type="submit" class="btn btn-primary"> Update Desktop
                </button>
                <a href="#" onclick="window.history.back(); return false;" class="btn btn-secondary">Back</a>
            </div>
        </form>
    </div>   </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<?php $conn->close(); ?>