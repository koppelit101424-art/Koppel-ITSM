<?php
include __DIR__ . '/../includes/auth.php';
include __DIR__ . '/../includes/db.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'update_settings') {
        // Update system settings
        $low_stock_threshold = (int)$_POST['low_stock_threshold'];
        $auto_archive_days = (int)$_POST['auto_archive_days'];
        $default_item_type = (int)$_POST['default_item_type'];
        $borrow_limit = (int)$_POST['borrow_limit'];
        $require_approval = isset($_POST['require_approval']) ? 1 : 0;
        
        // Save to settings table (create if not exists)
        $stmt = $conn->prepare("
            INSERT INTO system_settings 
            (setting_key, setting_value) 
            VALUES 
            ('low_stock_threshold', ?),
            ('auto_archive_days', ?),
            ('default_item_type', ?),
            ('borrow_limit', ?),
            ('require_approval', ?)
            ON DUPLICATE KEY UPDATE 
            setting_value = VALUES(setting_value)
        ");
        $stmt->bind_param("iiiii", 
            $low_stock_threshold,
            $auto_archive_days,
            $default_item_type,
            $borrow_limit,
            $require_approval
        );
        $stmt->execute();
        $stmt->close();
        
        $success = "Settings updated successfully!";
    }
    
    if (isset($_POST['action']) && $_POST['action'] === 'update_email') {
        // Update email templates
        $borrow_subject = $conn->real_escape_string($_POST['borrow_subject']);
        $borrow_body = $conn->real_escape_string($_POST['borrow_body']);
        $return_subject = $conn->real_escape_string($_POST['return_subject']);
        $return_body = $conn->real_escape_string($_POST['return_body']);
        
        $stmt = $conn->prepare("
            INSERT INTO system_settings (setting_key, setting_value) VALUES 
            ('email_borrow_subject', ?),
            ('email_borrow_body', ?),
            ('email_return_subject', ?),
            ('email_return_body', ?)
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
        ");
        $stmt->bind_param("ssss", 
            $borrow_subject,
            $borrow_body,
            $return_subject,
            $return_body
        );
        $stmt->execute();
        $stmt->close();
        
        $success = "Email templates updated!";
    }
}

// Fetch current settings
$settings = [];
$result = $conn->query("SELECT setting_key, setting_value FROM system_settings");
while ($row = $result->fetch_assoc()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

// Fetch item types for dropdown
$itemTypes = $conn->query("SELECT type_id, type_name FROM item_type ORDER BY type_name");
?>

<style>
    .settings-card {
        /* border-radius: 12px; */
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        margin-bottom: 24px;
        border: none;
    }
    .settings-header {
        background: linear-gradient(135deg, #1E3A8A 0%, #1E3A8A 100%);
        color: white;
        padding: 16px 20px;
        border-radius: 12px 12px 0 0 !important;
    }
    .form-label {
        font-weight: 600;
        color: #495057;
    }
    .switch {
        position: relative;
        display: inline-block;
        width: 50px;
        height: 24px;
    }
    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        transition: .4s;
        border-radius: 24px;
    }
    .slider:before {
        position: absolute;
        content: "";
        height: 16px;
        width: 16px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
    }
    input:checked + .slider {
        background-color: #33A1E0;
    }
    input:checked + .slider:before {
        transform: translateX(26px);
    }
</style>

    <div class="main-content" id="mainContent">
   
        <div class="d-flex justify-content-between align-items-center ">
            <!-- <h2 class="text-primary">System Settings</h2> -->
        </div>
        <?php if (isset($success)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($success) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- System Configuration -->
        <div class="card settings-card">
            <div class="card-header text-white settings-header">
                <h5 class="mb-0"><i class="fas fa-cogs me-2"></i>System Configuration</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="update_settings">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Low Stock Threshold</label>
                            <input type="number" class="form-control" name="low_stock_threshold" 
                                   value="<?= htmlspecialchars($settings['low_stock_threshold'] ?? '5') ?>" 
                                   min="0" required>
                            <div class="form-text">Items below this quantity will show low stock alerts</div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Auto-Archive Inactive Items (Days)</label>
                            <input type="number" class="form-control" name="auto_archive_days" 
                                   value="<?= htmlspecialchars($settings['auto_archive_days'] ?? '365') ?>" 
                                   min="30" required>
                            <div class="form-text">Items not transacted in X days will be archived</div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Default Item Type</label>
                            <select class="form-select" name="default_item_type" required>
                                <?php while($type = $itemTypes->fetch_assoc()): ?>
                                    <option value="<?= $type['type_id'] ?>" 
                                        <?= (isset($settings['default_item_type']) && $settings['default_item_type'] == $type['type_id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($type['type_name']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Max Items Per User</label>
                            <input type="number" class="form-control" name="borrow_limit" 
                                   value="<?= htmlspecialchars($settings['borrow_limit'] ?? '5') ?>" 
                                   min="1" required>
                            <div class="form-text">Maximum items a user can borrow simultaneously</div>
                        </div>
                        
                        <div class="col-12 mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <label class="form-label mb-0">Require Approval for Borrowing</label>
                                    <div class="form-text">New borrow requests need admin approval</div>
                                </div>
                                <label class="switch">
                                    <input type="checkbox" name="require_approval" 
                                           <?= (isset($settings['require_approval']) && $settings['require_approval']) ? 'checked' : '' ?>>
                                    <span class="slider"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-blue">
                        <i class="fas fa-save me-1"></i> Save System Settings
                    </button>
                </form>
            </div>
        </div>

        <!-- Email Templates -->
        
            <div class="card-header settings-header">
                <h5 class="mb-0"><i class="fas fa-envelope me-2"></i>Email Notifications</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="update_email">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Borrow Request Subject</label>
                            <input type="text" class="form-control" name="borrow_subject" 
                                   value="<?= htmlspecialchars($settings['email_borrow_subject'] ?? 'Borrow Request Approved') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Return Reminder Subject</label>
                            <input type="text" class="form-control" name="return_subject" 
                                   value="<?= htmlspecialchars($settings['email_return_subject'] ?? 'Item Return Reminder') ?>">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Borrow Approval Email Body</label>
                        <textarea class="form-control" name="borrow_body" rows="4"><?= 
                            htmlspecialchars($settings['email_borrow_body'] ?? "Your request for [ITEM] has been approved. Please collect it by [DATE].") 
                        ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Return Reminder Email Body</label>
                        <textarea class="form-control" name="return_body" rows="4"><?= 
                            htmlspecialchars($settings['email_return_body'] ?? "This is a reminder to return [ITEM] by [DUE_DATE]. Late returns may incur penalties.") 
                        ?></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-blue">
                        <i class="fas fa-save me-1"></i> Save Email Templates
                    </button>
                </form>
            </div>
        </div>

        <!-- Database Maintenance -->
        <div class="card settings-card">
            <div class="card-header settings-header">
                <h5 class="mb-0"><i class="fas fa-database me-2"></i>Database Maintenance</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Backup Database</h6>
                        <p class="text-muted">Download a full backup of your system data</p>
                        <a href="backup.php" class="btn btn-outline-primary">
                            <i class="fas fa-download me-1"></i> Create Backup
                        </a>
                    </div>
                    <div class="col-md-6">
                        <h6>Clear Audit Logs</h6>
                        <p class="text-muted">Remove transaction history older than 1 year</p>
                        <!-- <button class="btn btn-outline-danger" onclick="confirmClearLogs()">
                            <i class="fas fa-trash me-1"></i> Clear Old Logs
                        </button> -->
                    </div>
                </div>
            </div>
        </div>
    </div>

<!-- sla -->
<?php
    /* ==============================
    AUTO CREATE TABLES (SAFE)
    ============================== */
    $conn->query("
    CREATE TABLE IF NOT EXISTS business_hours (
        id INT PRIMARY KEY DEFAULT 1,
        start_time TIME NOT NULL DEFAULT '07:30:00',
        end_time TIME NOT NULL DEFAULT '18:00:00'
    )");

    $conn->query("
    INSERT IGNORE INTO business_hours (id,start_time,end_time)
    VALUES (1,'07:30:00','18:00:00')
    ");

    $conn->query("
    CREATE TABLE IF NOT EXISTS holidays (
        id INT AUTO_INCREMENT PRIMARY KEY,
        holiday_date DATE NOT NULL UNIQUE,
        description VARCHAR(255) NULL
    )");

    /* ==============================
    SAVE SLA SETTINGS
    ============================== */
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        /* ---- Update SLA Matrix ---- */
        if (!empty($_POST['response'])) {
            foreach ($_POST['response'] as $priority => $response) {

                $resolution = $_POST['resolution'][$priority];

                $stmt = $conn->prepare("
                    UPDATE sla_settings 
                    SET response_minutes = ?, resolution_minutes = ?
                    WHERE priority = ?
                ");
                $stmt->bind_param("iis", $response, $resolution, $priority);
                $stmt->execute();
            }
        }

        /* ---- Update Business Hours ---- */
        if (!empty($_POST['business_start'])) {
            $start = $_POST['business_start'];
            $end   = $_POST['business_end'];

            $stmt = $conn->prepare("
                UPDATE business_hours 
                SET start_time=?, end_time=? 
                WHERE id=1
            ");
            $stmt->bind_param("ss", $start, $end);
            $stmt->execute();
        }

        /* ---- Add Holiday ---- */
        if (!empty($_POST['holiday_date'])) {

            $date = $_POST['holiday_date'];
            $desc = $_POST['holiday_desc'];

            $stmt = $conn->prepare("
                INSERT IGNORE INTO holidays (holiday_date, description)
                VALUES (?,?)
            ");
            $stmt->bind_param("ss", $date, $desc);
            $stmt->execute();
        }

        /* ---- Delete Holiday ---- */
        if (!empty($_POST['delete_holiday'])) {
            $stmt = $conn->prepare("DELETE FROM holidays WHERE id=?");
            $stmt->bind_param("i", $_POST['delete_holiday']);
            $stmt->execute();
        }

        $success = "Settings updated successfully!";
    }

    /* ==============================
    FETCH DATA
    ============================== */

    $slaResult = $conn->query("
    SELECT * FROM sla_settings 
    ORDER BY FIELD(priority,'highest','high','medium','low')
    ");

    $business = $conn->query("SELECT * FROM business_hours WHERE id=1")->fetch_assoc();

    $holidayResult = $conn->query("SELECT * FROM holidays ORDER BY holiday_date ASC");

    ?>

    <div class="main-content d-flex">
        <div class="content flex-grow-1">
            <div class="card shadow-sm">
            <div class="card-header settings-header">
            <h5>⚙ SLA Configuration</h5>
            </div>

            <div class="card-body">

            <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>

            <form method="POST">

            <!-- ================= SLA MATRIX ================= -->
            <h6 class="mb-3">📊 SLA Targets</h6>

            <div class="table-responsive">
            <table class="table table-bordered align-middle">
            <thead class="table-light">
            <tr>
            <th>Priority</th>
            <th>Response Target (Minutes)</th>
            <th>Resolution Target (Minutes)</th>
            </tr>
            </thead>
            <tbody>

            <?php while ($row = $slaResult->fetch_assoc()): ?>
            <tr>
            <td><strong><?= ucfirst($row['priority']) ?></strong></td>
            <td>
            <input type="number"
            class="form-control"
            name="response[<?= $row['priority'] ?>]"
            value="<?= $row['response_minutes'] ?>"
            required>
            </td>
            <td>
            <input type="number"
            class="form-control"
            name="resolution[<?= $row['priority'] ?>]"
            value="<?= $row['resolution_minutes'] ?>"
            required>
            </td>
            </tr>
            <?php endwhile; ?>

            </tbody>
            </table>
            </div>

            <hr>

            <!-- ================= BUSINESS HOURS ================= -->
            <h6 class="mb-3">🕒 Business Hours</h6>

            <div class="row mb-4">
            <div class="col-md-4">
            <label>Start Time</label>
            <input type="time" name="business_start"
            class="form-control"
            value="<?= $business['start_time'] ?>">
            </div>

            <div class="col-md-4">
            <label>End Time</label>
            <input type="time" name="business_end"
            class="form-control"
            value="<?= $business['end_time'] ?>">
            </div>
            </div>

            <hr>

            <!-- ================= HOLIDAY SECTION ================= -->
            <h6 class="mb-3">🎉 Holiday Exclusion</h6>

            <div class="row mb-3">
            <div class="col-md-3">
            <input type="date" name="holiday_date" class="form-control">
            </div>
            <div class="col-md-5">
            <input type="text" name="holiday_desc" class="form-control" placeholder="Holiday description">
            </div>
            <div class="col-md-2">
            <button class="btn btn-success">Add Holiday</button>
            </div>
            </div>

            <div class="table-responsive">
            <table class="table table-sm table-bordered">
            <thead class="table-light">
            <tr>
            <th>Date</th>
            <th>Description</th>
            <th>Action</th>
            </tr>
            </thead>
            <tbody>

            <?php while($holiday = $holidayResult->fetch_assoc()): ?>
            <tr>
            <td><?= $holiday['holiday_date'] ?></td>
            <td><?= htmlspecialchars($holiday['description']) ?></td>
            <td>
            <button name="delete_holiday"
            value="<?= $holiday['id'] ?>"
            class="btn btn-danger btn-sm">
            Delete
            </button>
            </td>
            </tr>
            <?php endwhile; ?>

            </tbody>
            </table>
            </div>
            <hr>
            <button class="btn btn-primary">💾 Save All Changes</button>
            </form>
            </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function confirmClearLogs() {
        if (confirm('Are you sure? This will permanently delete transaction logs older than 1 year.')) {
            // In real implementation, call clear_logs.php
            alert('Logs cleared successfully!');
        }
    }
    </script>
<?php $conn->close(); ?>