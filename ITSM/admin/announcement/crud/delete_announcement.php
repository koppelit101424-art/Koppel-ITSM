<?php
include __DIR__ . "/../../../includes/db.php";

$id = intval($_GET['id']);

// GET FILES
$files = mysqli_query($conn, "SELECT * FROM announcement_attachments WHERE announcement_id='$id'");

while($f = mysqli_fetch_assoc($files)){

    // convert to server path
    $server_path = __DIR__ . "/../uploads/announcements/" . basename($f['file_path']);

    if(file_exists($server_path)){
        unlink($server_path);
    }
}

// DELETE RECORD (attachments auto-delete if FK CASCADE is set)
mysqli_query($conn, "DELETE FROM announcement_tb WHERE announcement_id='$id'");

// REDIRECT
echo "<script>window.location='index.php?page=announcement/announcements'</script>";
exit;