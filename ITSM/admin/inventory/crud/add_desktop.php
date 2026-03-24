<?php
  include __DIR__ . '/../../../includes/auth.php';
  include __DIR__ . '/../../../includes/db.php';
$message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input
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

    // Basic validation
    if (empty($cpu) || empty($ram) || empty($computer_name)) {
        $message = "CPU, RAM, and Computer Name are required.";
    } else {
        $desktop_area_id = $conn->real_escape_string(trim($_POST['desktop_area_id'] ?? ''));
        // Then update the INSERT:
        $sql = "INSERT INTO desktop_tb (
            cpu, ram, rom_w_serial, motherboard, monitor_w_serial,
            avr, mouse, keyboard, ip_address, mac_address,
            computer_name, windows_key, antivirus, tag_number, desktop_area_id, remarks,
             date_created
        ) VALUES (
            '$cpu', '$ram', '$rom_w_serial', '$motherboard', '$monitor_w_serial',
            '$avr', '$mouse', '$keyboard', '$ip_address', '$mac_address',
            '$computer_name', '$windows_key', '$antivirus', '$tag_number', '$desktop_area_id','$remarks',
             NOW()
        )";

        if ($conn->query($sql)) {
            $success = true;
            $message = "Desktop added successfully!";
            // Optional: redirect after success
            // header("Location: desktop.php?added=1");
            // exit();
        } else {
            $message = "Error: " . $conn->error;
        }
    }
}
// Fetch all areas for dropdown
$areas = [];
$sql_areas = "SELECT desktop_area_id, area FROM desktop_area_tb ORDER BY area";
$result_areas = $conn->query($sql_areas);

if ($result_areas && $result_areas->num_rows > 0) {
    while ($row = $result_areas->fetch_assoc()) {
        $areas[] = $row;
    }
}
?>

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
        <div class="card">

        <div class="card-header d-flex justify-content-between align-items-center text-white"><h4>Add New Desktop</h4>                 
        <a href="#" onclick="window.history.back(); return false;" class="btn btn-secondary">Back</a>
        </div>
        
        <div class="card-body">

        <?php if ($message): ?>
            <div class="alert alert-<?= $success ? 'success' : 'danger' ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <form method="POST">
            <!-- System Info -->
            <div class="form-section">
                <h6><i class="fas fa-microchip me-2"></i> System Specifications</h6>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">CPU *</label>
                        <input type="text" name="cpu" class="form-control" value="<?= htmlspecialchars($_POST['cpu'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">RAM *</label>
                        <input type="text" name="ram" class="form-control" value="<?= htmlspecialchars($_POST['ram'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">ROM w/ Serial</label>
                        <input name="rom_w_serial" class="form-control" ><?= htmlspecialchars($_POST['rom_w_serial'] ?? '') ?></textarea>
                        <!-- <small class="form-text text-muted">e.g., "512GB SSD Samsung,SN: XYZ;1TB HDD WD,SN: ABC"</small> -->
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Motherboard</label>
                        <input type="text" name="motherboard" class="form-control" value="<?= htmlspecialchars($_POST['motherboard'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <!-- Peripherals -->
            <div class="form-section">
                <h6><i class="fas fa-tv me-2"></i> Peripherals</h6>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Monitor w/ Serial</label>
                        <input name="monitor_w_serial" class="form-control" ><?= htmlspecialchars($_POST['monitor_w_serial'] ?? '') ?></textarea>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Mouse</label>
                        <input type="text" name="mouse" class="form-control" value="<?= htmlspecialchars($_POST['mouse'] ?? '') ?>">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Keyboard</label>
                        <input type="text" name="keyboard" class="form-control" value="<?= htmlspecialchars($_POST['keyboard'] ?? '') ?>">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">AVR</label>
                        <input type="text" name="avr" class="form-control" value="<?= htmlspecialchars($_POST['avr'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <!-- Network & Software -->
            <div class="form-section">
                <h6><i class="fas fa-network-wired me-2"></i> Network & Software</h6>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">IP Address</label>
                        <input type="text" name="ip_address" class="form-control" value="<?= htmlspecialchars($_POST['ip_address'] ?? '') ?>">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">MAC Address</label>
                        <input type="text" name="mac_address" class="form-control" value="<?= htmlspecialchars($_POST['mac_address'] ?? '') ?>">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Windows Key</label>
                        <input type="text" name="windows_key" class="form-control" value="<?= htmlspecialchars($_POST['windows_key'] ?? '') ?>">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Computer Name *</label>
                        <input type="text" name="computer_name" class="form-control" value="<?= htmlspecialchars($_POST['computer_name'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Antivirus</label>
                        <select name="antivirus" class="form-control">
                            <option value="YES" <?= (($_POST['antivirus'] ?? '') === 'YES') ? 'selected' : '' ?>>YES</option>
                            <!-- <option value="NO" <?= (($_POST['antivirus'] ?? '') === 'NO') ? 'selected' : '' ?>>NO</option> -->
                            <option value="NONE" <?= (($_POST['antivirus'] ?? '') === 'NONE') ? 'selected' : '' ?>>NONE</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Tag Number</label>
                        <input type="text" name="tag_number" class="form-control" value="<?= htmlspecialchars($_POST['tag_number'] ?? '') ?>">
                    </div>
                     <div class="col-md-4 mb-3">
                        <label class="form-label">Area *</label>
                        <select name="desktop_area_id" class="form-control" required>
                            <option value="">Select Area</option>
                            <?php foreach ($areas as $area): ?>
                                <option value="<?= htmlspecialchars($area['desktop_area_id']) ?>"
                                    <?= (isset($_POST['desktop_area_id']) && $_POST['desktop_area_id'] == $area['desktop_area_id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($area['area']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Remarks -->
            <div class="form-section">
                <h6><i class="fas fa-sticky-note me-2"></i> Remarks</h6>
                <div class="mb-3">
                    <textarea name="remarks" class="form-control" rows="3" placeholder="Defects, notes, etc."><?= htmlspecialchars($_POST['remarks'] ?? '') ?></textarea>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Save Desktop
                </button>  
                <a href="?page=inventory/desktops" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div> 
</div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<?php $conn->close(); ?>