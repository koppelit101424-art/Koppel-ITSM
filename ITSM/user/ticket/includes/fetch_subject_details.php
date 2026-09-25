<?php
include 'includes/db.php';

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
        <input class='form-check-input' type='radio'
            name='subject_details_id'
            value='{$row['subject_details_id']}'
            required>
        <label class='form-check-label'>
            <strong>{$row['name']}</strong><br>
            <small class='text-muted'>{$row['description']}</small>
        </label>
    </div>
    ";
}
