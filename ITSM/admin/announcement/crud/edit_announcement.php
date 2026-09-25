<?php
if(isset($_POST['update_announcement'])){
    include "../../includes/db.php";

    $id = $_POST['id'];
    $title = $_POST['title'];
    $desc = $_POST['description'];
    $remarks = $_POST['remarks'];

    // Update announcement text fields
    mysqli_query($conn, "UPDATE announcement_tb 
        SET title='$title', description='$desc', remarks='$remarks'
        WHERE announcement_id='$id'");

    // Remove selected attachments
    if(!empty($_POST['remove_files'])){
        $remove_files = json_decode($_POST['remove_files'], true);
        foreach($remove_files as $file_id){
            $res = mysqli_query($conn, "SELECT attachment_id FROM announcement_attachments WHERE attachment_id='$file_id'");
            if($row = mysqli_fetch_assoc($res)){
                @unlink($row['file_path']);
                mysqli_query($conn, "DELETE FROM announcement_attachments WHERE attachment_id='$file_id'");
            }
        }
    }

    // Add new attachments
    $upload_dir = __DIR__ . "/../uploads/announcements/";
    if(!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

    if(!empty($_FILES['files']['name'][0])){
        foreach($_FILES['files']['tmp_name'] as $key => $tmp){
            $name = $_FILES['files']['name'][$key];
            $filename = time() . "_" . preg_replace("/[^a-zA-Z0-9_\.-]/","",$name);
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

<!-- EDIT MODAL -->
<div class="modal fade modal-lg text-white" id="editModal">
<div class="modal-dialog">
<form method="POST" enctype="multipart/form-data" class="modal-content">

<input type="hidden" name="id" id="edit_id">
<input type="hidden" name="remove_files" id="remove_files" value="">

<div class="modal-header">
    <h5>Edit Announcement</h5>
</div>

<div class="modal-body">
    <input type="text" name="title" id="edit_title" class="form-control mb-2" placeholder="Title">
    <textarea name="description" id="edit_desc" class="form-control mb-2" placeholder="Description"></textarea>
    <input type="text" name="remarks" id="edit_remarks" class="form-control mb-2" placeholder="Remarks">

    <!-- Existing Attachments -->
    <div class="existing-attachments mb-2">
        <label>Existing Attachments:</label>
        <div id="currentAttachments" class="d-flex flex-wrap gap-2">
            <!-- JS will populate thumbnails here -->
        </div>
    </div>

    <!-- Add new attachments -->
    <label>Add More Attachments</label>
    <input type="file" name="files[]" multiple class="form-control">
</div>

<div class="modal-footer">
    <button type="submit" name="update_announcement" class="btn btn-success">Update</button>
</div>

</form>
</div>
</div>

<!-- JS to populate existing attachments and handle remove -->
<script>
$(document).ready(function(){
    $('.editBtn').click(function(){
        let aid = $(this).data('id');
        $('#edit_id').val(aid);
        $('#edit_title').val($(this).data('title'));
        $('#edit_desc').val($(this).data('desc'));
        $('#edit_remarks').val($(this).data('remarks'));

        // Populate existing attachments
        let attachments = window['images_' + aid]; // same JS array used for modal images
        let html = '';
        attachments.forEach((file, index)=>{
            html += `<div class="position-relative" style="width:80px; height:80px;">
                        <img src="${file}" class="img-thumbnail" style="width:100%; height:100%; object-fit:cover;">
                        <button type="button" class="btn-close position-absolute top-0 end-0 remove-file" data-index="${index}"></button>
                     </div>`;
        });
        $('#currentAttachments').html(html);

        $('#remove_files').val(''); // reset removed files
        $('#editModal').modal('show');
    });

    // Handle removing existing files
    $(document).on('click', '.remove-file', function(){
        let id = $(this).data('attachment-id'); // attachment_id
        let removed = $('#remove_files').val();
        removed = removed ? JSON.parse(removed) : [];
        removed.push(id);
        $('#remove_files').val(JSON.stringify(removed)); // send JSON array of IDs
        $(this).parent().remove();
    });
});
</script>