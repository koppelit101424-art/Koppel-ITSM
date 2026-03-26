<?php
include 'includes/auth.php';
include 'includes/db.php';

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
    // $subject = $stmt->get_result()->fetch_assoc()['subject'];
    $row = $stmt->get_result()->fetch_assoc();
    $subject = $row['subject'] ?? 'Unknown';

    /* SUBJECT DETAIL NAME */
    $stmt = $conn->prepare("SELECT name FROM subject_details WHERE subject_details_id = ?");
    $stmt->bind_param("i", $_POST['subject_details_id']);
    $stmt->execute();
    // $subject_details = $stmt->get_result()->fetch_assoc()['name'];
    $row = $stmt->get_result()->fetch_assoc();
    $subject_details = $row['subject_details'] ?? 'Unknown';

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

    include 'ticket_email.php';
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
    // header("Location: ?page=ticket/all_tickets.php&success=1");
    echo "<script>
        window.location.href='?page=ticket/all_tickets&success=1';
    </script>";
    exit;

} else {
    die("Insert failed: " . $stmt->error);
}
}

?>
<style>
 /* From Uiverse.io by boryanakrasteva */ 
@-webkit-keyframes honeycomb {
  0%,
  20%,
  80%,
  100% {
    opacity: 0;
    -webkit-transform: scale(0);
    transform: scale(0);
  }

  30%,
  70% {
    opacity: 1;
    -webkit-transform: scale(1);
    transform: scale(1);
  }
}

@keyframes honeycomb {
  0%,
  20%,
  80%,
  100% {
    opacity: 0;
    -webkit-transform: scale(0);
    transform: scale(0);
  }

  30%,
  70% {
    opacity: 1;
    -webkit-transform: scale(1);
    transform: scale(1);
  }
}

.honeycomb {
  height: 24px;
  position: relative;
  width: 24px;
}

.honeycomb div {
  -webkit-animation: honeycomb 2.1s infinite backwards;
  animation: honeycomb 2.1s infinite backwards;
  background: #5c84f0;
  height: 12px;
  margin-top: 6px;
  position: absolute;
  width: 24px;
}

.honeycomb div:after, .honeycomb div:before {
  content: '';
  border-left: 12px solid transparent;
  border-right: 12px solid transparent;
  position: absolute;
  left: 0;
  right: 0;
}

.honeycomb div:after {
  top: -6px;
  border-bottom: 6px solid #5c84f0;
}

.honeycomb div:before {
  bottom: -6px;
  border-top: 6px solid #5c84f0;
}

.honeycomb div:nth-child(1) {
  -webkit-animation-delay: 0s;
  animation-delay: 0s;
  left: -28px;
  top: 0;
}

.honeycomb div:nth-child(2) {
  -webkit-animation-delay: 0.1s;
  animation-delay: 0.1s;
  left: -14px;
  top: 22px;
}

.honeycomb div:nth-child(3) {
  -webkit-animation-delay: 0.2s;
  animation-delay: 0.2s;
  left: 14px;
  top: 22px;
}

.honeycomb div:nth-child(4) {
  -webkit-animation-delay: 0.3s;
  animation-delay: 0.3s;
  left: 28px;
  top: 0;
}

.honeycomb div:nth-child(5) {
  -webkit-animation-delay: 0.4s;
  animation-delay: 0.4s;
  left: 14px;
  top: -22px;
}

.honeycomb div:nth-child(6) {
  -webkit-animation-delay: 0.5s;
  animation-delay: 0.5s;
  left: -14px;
  top: -22px;
}

.honeycomb div:nth-child(7) {
  -webkit-animation-delay: 0.6s;
  animation-delay: 0.6s;
  left: 0;
  top: 0;
}
</style>
<style>
#ticketLoader {
    display: none; /* hidden by default */
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(255,255,255,0.7);
    z-index: 9999;
    justify-content: center;
    align-items: center;
}
</style>
<div class="card">
<div class="card-header text-white">Create New Ticket</div>

<div class="card-body position-relative" id="ticketFormWrapper">
<form method="POST" enctype="multipart/form-data" id="ticketForm">

<!-- <div class="mb-3">
  <label class="form-label">Ticket Number</label>
  <input class="form-control" value="Hidden" readonly> -->
  <input type="hidden" name="ticket_number" value="<?= htmlspecialchars($ticket_number) ?>">
<!--</div>-->

<div class="row">

<div class="col-md-6 mb-3">
<label class="form-label">Full Name</label>
<input class="form-control" value="<?= htmlspecialchars($user['fullname']) ?>" >
</div>

<div class="col-md-6 mb-3">
<label class="form-label">Company Email</label>
<input class="form-control" value="<?= htmlspecialchars($user['email']) ?>" >
</div>

<div class="col-md-6 mb-3">
<label class="form-label">Company</label>
<input class="form-control" value="<?= htmlspecialchars($user['company']) ?>" >
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

<button class="btn btn-primary btn-sm" id="ticketSubmitBtn">
Submit Ticket
</button>
<a href="#" onclick="window.history.back(); return false;" class="btn btn-secondary btn-sm">
    Back
</a>

</form>
<!-- Loader overlay -->
<div id="ticketLoader">
<!-- From Uiverse.io by boryanakrasteva --> 
<div class="honeycomb">
  <div></div>
  <div></div>
  <div></div>
  <div></div>
  <div></div>
  <div></div>
  <div></div>
</div>
</div>


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

    fetch('?ajax=fetch_subject_details&subject_id=' + subjectId)
        .then(res => res.text())
        .then(html => box.innerHTML = html);
});
</script>
<script>
const form = document.getElementById('ticketForm');
const loader = document.getElementById('ticketLoader');
const submitBtn = document.getElementById('ticketSubmitBtn');

form.addEventListener('submit', function(e) {
    e.preventDefault();

    loader.style.display = 'flex'; // Show loader overlay
    submitBtn.disabled = true;

    const formData = new FormData(form);

    fetch('', {
        method: 'POST',
        body: formData
    })
    .then(res => res.text())
    .then(html => {
        window.location.href='?page=ticket/all_tickets&success=1';
    })
    .catch(err => {
        console.error(err);
        alert('Error submitting ticket.');
    })
    .finally(() => {
        loader.style.display = 'none';
        submitBtn.disabled = false;
    });
});
</script>