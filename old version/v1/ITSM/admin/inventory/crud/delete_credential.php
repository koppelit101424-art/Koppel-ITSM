<?php
// ============================================
// DELETE CREDENTIAL
// ============================================

include __DIR__ . '/../../../includes/auth.php';
include __DIR__ . '/../../../includes/db.php';

// Check if cred_id exists
if (!isset($_GET['cred_id']) || empty($_GET['cred_id'])) {
    header("Location: ?page=inventory/credentials");
    exit;
}

$cred_id = intval($_GET['cred_id']);

// Prepare delete statement
$stmt = $conn->prepare("DELETE FROM credential_tb WHERE cred_id = ?");
$stmt->bind_param("i", $cred_id);

if ($stmt->execute()) {

    // Success
    // header("Location: ?page=inventory/credentials&msg=deleted");
           echo "<script>
            window.location.href='?page=inventory/credentials&msg=" . urlencode("Credential deleted successfully! Code: $item_code") . "';
        </script>";
    exit;

} else {

    // Error
    header("Location: ?page=inventory/credentials&msg=error");
    exit;

}

$stmt->close();
$conn->close();
?>