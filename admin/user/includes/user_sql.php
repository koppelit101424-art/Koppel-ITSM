<?php 
// Handle resignation FIRST
$resign_success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Resignation
    if (isset($_POST['date_resigned']) && isset($_POST['user_id'])) {
        $user_required = (int)$_POST['user_id'];
        $date_resigned = $_POST['date_resigned'];

        if ($user_required > 0 && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_resigned)) {
            $stmt = $conn->prepare("UPDATE user_tb SET date_resigned = ? WHERE user_id = ?");
            $stmt->bind_param("si", $date_resigned, $user_required);
            if ($stmt->execute()) {
                $resign_success = true;
            }
            $stmt->close();
        }
    }

    // Enable / Disable Account
    if (isset($_POST['toggle_status_user_id']) && isset($_POST['new_status'])) {
        $user_id = (int)$_POST['toggle_status_user_id'];
        $new_status = $_POST['new_status'] === '1' ? 1 : 0; // 1=Active, 0=Disabled
        $stmt = $conn->prepare("UPDATE user_tb SET is_active = ? WHERE user_id = ?");
        $stmt->bind_param("ii", $new_status, $user_id);
        $stmt->execute();
        $stmt->close();
    }
}

// Fetch users
$sql = "SELECT * FROM user_tb ORDER BY created_at DESC";
$result = $conn->query($sql);
?>