<?php
include __DIR__ . '/../../includes/auth.php';
include __DIR__ . '/../../includes/db.php';

/* Get companies for dropdown */
$companies = $conn->query("
    SELECT DISTINCT company 
    FROM user_tb 
    WHERE company IS NOT NULL AND company != ''
    ORDER BY company ASC
");

$selectedCompany = $_GET['company'] ?? '';

/* Query departments */
if($selectedCompany){

    $stmt = $conn->prepare("
        SELECT DISTINCT department, company
        FROM user_tb
        WHERE company = ?
        AND department IS NOT NULL
        AND department != ''
        ORDER BY department ASC
    ");

    $stmt->bind_param("s", $selectedCompany);
    $stmt->execute();
    $result = $stmt->get_result();

}else{

    $result = $conn->query("
        SELECT DISTINCT department, company
        FROM user_tb
        WHERE department IS NOT NULL
        AND department != ''
        ORDER BY company ASC, department ASC
    ");

}
?>

<div class="container-fluid">

<!-- Company Filter -->
<div class="row mb-4">

<div class="col-md-4">

<form method="GET">

<input type="hidden" name="page" value="organization/departments">

<label class="form-label fw-semibold" >Filter by Company</label>

<select name="company" class="form-select" style="width: 72%;" onchange="this.form.submit()">

<option value=""    >All Companies</option>

<?php while($c = $companies->fetch_assoc()): ?>
<option value="<?= htmlspecialchars($c['company']) ?>"
<?= ($selectedCompany == $c['company']) ? 'selected' : '' ?>>
<?= htmlspecialchars($c['company']) ?>
</option>
<?php endwhile; ?>

</select>

</form>

</div>

</div>


<!-- Department Cards -->
<div class="row g-4">

<?php while($row = $result->fetch_assoc()): ?>

<div class="col-xl-3 col-lg-4 col-md-6">

<!-- <a href="index.php?page=users&department=<?= urlencode($row['department']) ?>&company=<?= urlencode($row['company']) ?>" style="text-decoration:none;color:inherit;"> -->

<a href="index.php?page=organization/users&company=<?= urlencode($row['company']) ?>&department=<?= urlencode($row['department']) ?>" style="text-decoration:none;color:inherit;">

<div class="card department-card h-100">

<div class="card-body">

<div class="department-icon">
<i class="bi bi-diagram-3"></i>
</div>

<h5 class="department-title">
  
        <?= htmlspecialchars($row['department']) ?>
    
</h5>

<p class="department-desc">
Department under <?= htmlspecialchars($row['company']) ?>
</p>

<hr>

<small class="text-muted">
<?= htmlspecialchars($row['company']) ?>
</small>

</div>

</div>

</div></a>

<?php endwhile; ?>

</div>

</div>

<style>
    .department-card{
border-radius:16px;
border:1px solid #e5e7eb;
background:white;
transition:all .25s ease;
padding:10px;
}

.department-card:hover{
transform:translateY(-6px);
box-shadow:0 15px 35px rgba(0,0,0,0.08);
border-color:#dbeafe;
}

.department-icon{
width:50px;
height:50px;
display:flex;
align-items:center;
justify-content:center;
border-radius:14px;
background:linear-gradient(135deg,#6366f1,#4338ca);
color:white;
font-size:20px;
margin-bottom:10px;
}

.department-title{
font-weight:600;
font-size:18px;
margin-bottom:5px;
}

.department-desc{
font-size:14px;
color:#6b7280;
}
</style>