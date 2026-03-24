<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include __DIR__ . '/../../includes/auth.php';
include __DIR__ . '/../../includes/db.php';

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

<div class="card shadow-sm mb-4">
<div class="card-header text-white">
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


<?php $conn->close(); ?>
