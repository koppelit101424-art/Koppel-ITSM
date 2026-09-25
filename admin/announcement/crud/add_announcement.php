<?php
if(isset($_POST['add_announcement'])){
    include "../../includes/db.php";

    $title = $_POST['title'];
    $desc = $_POST['description'];
    $remarks = $_POST['remarks'];
    $user_id = $_SESSION['user_id'];

    // Insert announcement
    mysqli_query($conn, "INSERT INTO announcement_tb (title,description,remarks,created_by)
                         VALUES ('$title','$desc','$remarks','$user_id')");

    $announcement_id = mysqli_insert_id($conn);

    // UPLOAD DIRECTORY (SERVER PATH)
    // $upload_dir = __DIR__ . "/../../../uploads/announcements/";
    $upload_dir = __DIR__ . "/../uploads/announcements/";
    // WEB PATH (FOR DISPLAY)
    $web_path = "announcement/uploads/announcements/";

    // CREATE FOLDER IF NOT EXIST
    if(!is_dir($upload_dir)){
        mkdir($upload_dir, 0777, true);
    }

    // FILE UPLOAD
    if(!empty($_FILES['files']['name'][0])){
        if(count($_FILES['files']['name']) > 10){
            echo "<script>alert('You can upload a maximum of 10 files.');</script>";
        } else {
            foreach($_FILES['files']['tmp_name'] as $key => $tmp){
                $name = $_FILES['files']['name'][$key];
                $filename = time() . "_" . $name;

                $server_path = $upload_dir . $filename;
                $db_path = $web_path . $filename;

                if(move_uploaded_file($tmp, $server_path)){
                    mysqli_query($conn, "INSERT INTO announcement_attachments
                        (announcement_id,file_name,file_path)
                        VALUES ('$announcement_id','$name','$db_path')");
                }
            }
        }
    }

    // Redirect after saving
    echo "<script>location.href='index.php?page=announcement/announcements'</script>";
}
?>