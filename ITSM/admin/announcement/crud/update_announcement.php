<?php
if(isset($_POST['update_announcement'])){
    include "../../includes/db.php";

    $id = $_POST['id'];
    $title = $_POST['title'];
    $desc = $_POST['description'];
    $remarks = $_POST['remarks'];

    // Update announcement text
    mysqli_query($conn, "UPDATE announcement_tb 
        SET title='$title', description='$desc', remarks='$remarks'
        WHERE announcement_id='$id'");

    // Remove selected attachments
    if(!empty($_POST['remove_files'])){
        $remove_files = json_decode($_POST['remove_files'], true); // array of file_ids
        foreach($remove_files as $file_id){
            // Get file path
            $res = mysqli_query($conn, "SELECT file_path FROM announcement_attachments WHERE attachment_id='$file_id'");
            if($row = mysqli_fetch_assoc($res)){
                @unlink($row['file_path']); // delete file from server
                mysqli_query($conn, "DELETE FROM announcement_attachments WHERE attachment_id='$file_id'");
            }
        }
    }

    // Add new attachments
    if(!empty($_FILES['files']['name'][0])){
        $upload_dir = "../announcement/uploads/";
        if(!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

        foreach($_FILES['files']['tmp_name'] as $key => $tmp){
            $name = $_FILES['files']['name'][$key];
            $filename = time() . "_" . preg_replace("/[^a-zA-Z0-9_\.-]/", "", $name);
            $path = $upload_dir . $filename;

            if(move_uploaded_file($tmp, $path)){
                mysqli_query($conn, "INSERT INTO announcement_attachments 
                    (announcement_id, file_name, file_path) VALUES ('$id','$name','$path')");
            }
        }
    }

    echo "<script>location.href='index.php?page=announcement/announcements'</script>";
}
?>