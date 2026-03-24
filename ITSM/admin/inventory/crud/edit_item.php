<?php
include __DIR__ . '/../../../includes/auth.php';
include __DIR__ . '/../../../includes/db.php';
include 'edit_sql.php';
?>

<div class="main-content" id="mainContent">

<div class="card">
<div class="card-header d-flex justify-content-between align-items-center text-white">
<h2>Edit Item</h2>
</div>

<div class="card-body">

<?php if ($message): ?>
<div class="alert alert-info"><?= $message ?></div>
<?php endif; ?>

<form method="POST" class="mt-3">

<!-- BASIC INFO -->

<div class="row mb-3">

<div class="col-md-4">
<label class="form-label">Item Code</label>
<input type="text" id="item_code" name="item_code" class="form-control"
value="<?= htmlspecialchars($item['item_code']) ?>">
</div>

<div class="col-md-4">
<label class="form-label">Item</label>
<input type="text" name="name" class="form-control"
value="<?= htmlspecialchars($item['name']) ?>" required>
</div>

<div class="col-md-4">
<label class="form-label">Brand</label>
<input type="text" name="brand" class="form-control"
value="<?= htmlspecialchars($item['brand']) ?>" required>
</div>

</div>


<div class="row mb-3">

<div class="col-md-6">
<label class="form-label">Model</label>
<input type="text" name="model" class="form-control"
value="<?= htmlspecialchars($item['model']) ?>" required>
</div>

<div class="col-md-6">
<label class="form-label">Serial Number</label>
<input type="text" name="serial_number" class="form-control"
value="<?= htmlspecialchars($item['serial_number']) ?>">
</div>

</div>


<div class="mb-3">
<label class="form-label">Description</label>
<textarea name="description" class="form-control" rows="5"><?= htmlspecialchars($item['description']) ?></textarea>
</div>


<div class="row mb-3">

<div class="col-md-3">
<label class="form-label">Quantity</label>
<input type="number" name="quantity" class="form-control"
value="<?= htmlspecialchars($item['quantity']) ?>" required>
</div>

<div class="col-md-3">
<label class="form-label">Date Received</label>
<input type="date" name="date_received" class="form-control"
value="<?= htmlspecialchars($item['date_received']) ?>" required>
</div>

<div class="col-md-3">
<label class="form-label">Item Type</label>

<select name="type_id" class="form-control" required>

<option value="">Select Type</option>

<?php while($type = $type_result->fetch_assoc()): ?>

<option value="<?= $type['type_id'] ?>"
<?= ($type['type_id'] == $item['type_id']) ? 'selected' : '' ?>>

<?= htmlspecialchars($type['type_name']) ?>

</option>

<?php endwhile; ?>

</select>

</div>
<div class="col-md-3">
    <label class="form-label">Condition</label>
    <select name="condition" class="form-control" required>
        <option value="">Select Condition</option>

        <?php foreach($conditionsArr as $cond): ?>
            <option value="<?= $cond['condition_id'] ?>"
                <?= ($item['condition_id'] == $cond['condition_id']) ? 'selected' : '' ?>>
                
                <?= htmlspecialchars($cond['condition_name']) ?>
            </option>
        <?php endforeach; ?>

    </select>
</div>
</div>


<!-- COMPUTER SPECS -->

<?php 
$item_lower = strtolower($item['name']);
if (strpos($item_lower, 'laptop') !== false || strpos($item_lower, 'system unit') !== false):
?>

<hr>
<h5 class="mb-3 text-primary">Computer Specifications</h5>

<div class="row mb-3">

<div class="col-md-4">
<label class="form-label">CPU</label>
<input type="text" name="cpu" class="form-control"
value="<?= htmlspecialchars($specs['cpu'] ?? '') ?>">
</div>  

<div class="col-md-4">
<label class="form-label">RAM</label>
<input type="text" name="ram" class="form-control"
value="<?= htmlspecialchars($specs['ram'] ?? '') ?>">
</div>

<div class="col-md-4">
<label class="form-label">Storage</label>
<input type="text" name="rom" class="form-control"
value="<?= htmlspecialchars($specs['rom'] ?? '') ?>">
</div>

</div>


<div class="row mb-3">

<div class="col-md-6">
<label class="form-label">Motherboard</label>
<input type="text" name="motherboard" class="form-control"
value="<?= htmlspecialchars($specs['motherboard'] ?? '') ?>">
</div>

<div class="col-md-6">
<label class="form-label">Operating System</label>
<input type="text" name="os" class="form-control"
value="<?= htmlspecialchars($specs['os'] ?? '') ?>">
</div>

</div>


<div class="row mb-3">

<div class="col-md-4">
<label class="form-label">Product Key</label>
<input type="text" name="key" class="form-control"
value="<?= htmlspecialchars($specs['key'] ?? '') ?>">
</div>

<div class="col-md-4">
<label class="form-label">Antivirus</label>
<input type="text" name="antivirus" class="form-control"
value="<?= htmlspecialchars($specs['antivirus'] ?? '') ?>">
</div>

<div class="col-md-4">
<label class="form-label">Computer Name</label>
<input type="text" name="comp_name" class="form-control"
value="<?= htmlspecialchars($specs['comp_name'] ?? '') ?>">
</div>

</div>

<?php endif; ?>


<button type="submit" class="btn btn-primary">Update Item</button>

<a href="" onclick="window.history.back(); return false;" class="btn btn-secondary">
Back
</a>

</form>

</div>
</div>
</div>