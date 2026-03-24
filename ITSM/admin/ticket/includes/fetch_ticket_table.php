<?php
// fetch_ticket_table.php
include __DIR__ . '/../../includes/db.php';

$ticket_id = $_GET['ticket_id'] ?? 0;

$stmt = $conn->prepare("SELECT * FROM ticket_messages WHERE ticket_id=? ORDER BY created_at ASC");
$stmt->bind_param("i", $ticket_id);
$stmt->execute();
$messages = $stmt->get_result();

echo "<table class='table'>";
while($msg = $messages->fetch_assoc()){
    echo "<tr><td>{$msg['message']}</td></tr>";
}
echo "</table>";
?>