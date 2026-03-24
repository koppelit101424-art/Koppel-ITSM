<?php
include '../auth/auth.php';
include '../db/db.php';
include '../../config/config.php';

/* ============================
   AJAX: LOAD SUBJECT DETAILS
============================ */
if (isset($_GET['ajax']) && $_GET['ajax'] === 'subject_details') {

    $subject_id = (int)$_GET['subject_id'];

    $stmt = $conn->prepare("
        SELECT subject_details_id, name, description
        FROM subject_details
        WHERE subject_id = ?
    ");
    $stmt->bind_param("i", $subject_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo "<p class='text-muted'>No details available.</p>";
        exit;
    }

    while ($row = $result->fetch_assoc()) {
        echo "
        <div class='form-check mb-2'>
            <input class='form-check-input'
                   type='radio'
                   name='subject_details_id'
                   value='{$row['subject_details_id']}'
                   required>
            <label class='form-check-label'>
                <strong>{$row['name']}</strong><br>
                <small class='text-muted'>{$row['description']}</small>
            </label>
        </div>";
    }
    exit;
}

/* ============================
   USER DETAILS
============================ */
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT fullname, email, company, department
    FROM user_tb WHERE user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

/* ============================
   TICKET NUMBER
============================ */
$res = $conn->query("SELECT MAX(ticket_id) AS max_id FROM ticket_tb");
$row = $res->fetch_assoc();
$ticket_number = 'TICKET-' . str_pad(($row['max_id'] ?? 0) + 1, 5, '0', STR_PAD_LEFT);

/* ============================
   CATEGORY
============================ */
$ticket_category = $_GET['type'] ?? 'incident';

/* ============================
   SAVE TICKET
============================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $status = 'waiting for support';
    $assigned_to = 1;

    /* SUBJECT NAME */
    $stmt = $conn->prepare("SELECT subject FROM ticket_subject WHERE subject_id = ?");
    $stmt->bind_param("i", $_POST['subject_id']);
    $stmt->execute();
    $subject = $stmt->get_result()->fetch_assoc()['subject'];

    /* SUBJECT DETAIL NAME */
    $stmt = $conn->prepare("SELECT name FROM subject_details WHERE subject_details_id = ?");
    $stmt->bind_param("i", $_POST['subject_details_id']);
    $stmt->execute();
    $subject_details = $stmt->get_result()->fetch_assoc()['name'];

    $stmt = $conn->prepare("
        INSERT INTO ticket_tb (
            ticket_number, user_id, fullname, email, company, department,
            priority, urgency, impact, ticket_category,
            subject, subject_details,
            issue, status, assigned_to
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "sissssssssssssi",
        $ticket_number,
        $user_id,
        $user['fullname'],
        $user['email'],
        $user['company'],
        $user['department'],
        $_POST['priority'],
        $_POST['urgency'],
        $_POST['impact'],
        $_POST['ticket_category'],
        $subject,
        $subject_details,
        $_POST['issue'],
        $status,
        $assigned_to
    );

if ($stmt->execute()) {

    // GET THE CREATED TICKET ID
    $ticket_id = $stmt->insert_id;

    /* ============================
       HANDLE FILE ATTACHMENTS
    ============================ */

    if (!empty($_FILES['attachments']['name'][0])) {

        $uploadDir = "../../uploads/tickets/";

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $files = $_FILES['attachments'];
        $fileCount = min(count($files['name']), 3); // MAX 3 FILES

        for ($i = 0; $i < $fileCount; $i++) {

            if ($files['error'][$i] === 0) {

                $originalName = basename($files['name'][$i]);
                $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

                $allowed = ['jpg','jpeg','png','pdf','doc','docx'];

                if (in_array($ext, $allowed)) {

                    $newName = time().'_'.$i.'.'.$ext;
                    $filePath = $uploadDir . $newName;

                    move_uploaded_file($files['tmp_name'][$i], $filePath);

                    $stmtAttach = $conn->prepare("
                        INSERT INTO ticket_attachments (ticket_id, file_name, file_path)
                        VALUES (?, ?, ?)
                    ");

                    $stmtAttach->bind_param(
                        "iss",
                        $ticket_id,
                        $originalName,
                        $filePath
                    );

                    $stmtAttach->execute();
                }
            }
        }
    }

    // REDIRECT AFTER EVERYTHING IS SAVED
    header("Location: tickets.php?success=1");
    exit;

} else {
    die("Insert failed: " . $stmt->error);
}
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Create Ticket</title>
<link rel="icon" href="../asset/img/Koppel_bip.ico">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<link href="../asset/css/main.css" rel="stylesheet">
<link href="../asset/css/menu.css" rel="stylesheet">
</head>

<body>

<div class="main-content d-flex">
<?php include '../sidebar.php'; ?>

<div class="content flex-grow-1">
<?php include '../header.php'; ?>

<div class="card mt-4">
<div class="card-header">Create New Ticket</div>

<div class="card-body">
<form method="POST" enctype="multipart/form-data">

<!-- <div class="mb-3">
  <label class="form-label">Ticket Number</label>
  <input class="form-control" value="Hidden" readonly> -->
  <input type="hidden" name="ticket_number" value="<?= htmlspecialchars($ticket_number) ?>">
<!--</div>-->

<div class="row">

<div class="col-md-6 mb-3">
<label class="form-label">Full Name</label>
<input class="form-control" value="<?= htmlspecialchars($user['fullname']) ?>" readonly>
</div>

<div class="col-md-6 mb-3">
<label class="form-label">Company Email</label>
<input class="form-control" value="<?= htmlspecialchars($user['email']) ?>" readonly>
</div>

<div class="col-md-6 mb-3">
<label class="form-label">Company</label>
<input class="form-control" value="<?= htmlspecialchars($user['company']) ?>" readonly>
</div>

<div class="col-md-6 mb-3">
<label class="form-label">Department</label>
<input class="form-control" value="<?= htmlspecialchars($user['department']) ?>" readonly>
</div>

<!--<div class="mb-3">
 <label class="form-label">Ticket Category</label>
 <select name="ticket_category" class="form-select" required>
<option value="incident" <?= $ticket_category=='incident'?'selected':'' ?>>Incident</option>
<option value="service" <?= $ticket_category=='service'?'selected':'' ?>>Service</option>
<option value="change" <?= $ticket_category=='change'?'selected':'' ?>>Change</option> 
</select></div>-->
<input type="hidden" name="ticket_category" value="<?= htmlspecialchars($ticket_category) ?>">

<div class="col-md-4 mb-3">
<label class="form-label">Contact us about</label>
<select name="subject_id" id="subjectSelect" class="form-select" required>
<option value="">Select Subject</option>
<?php
$res = $conn->query("SELECT subject_id, subject FROM ticket_subject");
while ($row = $res->fetch_assoc()) {
    echo "<option value='{$row['subject_id']}'>{$row['subject']}</option>";
}
?>
</select>
</div>

<div class="col-md-4 mb-3">
<label class="form-label">Priority</label>
<select name="priority" class="form-select" required>
<option value="">Select Priority</option>
<option value="highest">Highest</option>
<option value="high">High</option>
<option value="medium">Medium</option>
<option value="low">Low</option>
<option value="lowest">Lowest</option>
</select>
</div>

<div class="col-md-4 mb-3">
 <label class="form-label">Impact</label>
<select name="impact" class="form-select">
<option value="">Select Impact</option>
<option value="Individual" >Individual</option>
<option value="Department" >Department</option>
<option value="Organization" >Organization</option>
</select>

</div>

<div class="mb-3">
<label class="form-label">What can we help you with?</label>
<div id="subjectDetailsBox" class="border rounded p-3 text-muted">
Select a subject to see options
</div>
</div>

<div class="mb-3">
<label class="form-label">Description</label>
<textarea name="issue" rows="5" class="form-control" required></textarea>
</div>

<input type="hidden" name="urgency" value="None">

<div class="mb-3">
<label class="form-label">Attachments (Max 3 files)</label>
<input type="file" name="attachments[]" class="form-control" multiple accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
<small class="text-muted">Allowed: JPG, PNG, PDF, DOC, DOCX</small>
</div>
<!-- <input type="hidden" name="impact" value="Individual"> -->

</div>

<button class="btn btn-primary">
Submit Ticket
</button>

</form>
</div>
</div>

</div>
</div>

<script>
document.getElementById('subjectSelect').addEventListener('change', function () {
    const subjectId = this.value;
    const box = document.getElementById('subjectDetailsBox');

    if (!subjectId) {
        box.innerHTML = 'Select a subject to see options';
        return;
    }

    fetch('?ajax=subject_details&subject_id=' + subjectId)
        .then(res => res.text())
        .then(html => box.innerHTML = html);
});
</script>
<?php 

?>
</body>
</html>
