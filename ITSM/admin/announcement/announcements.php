<?php include "../includes/db.php"; ?>

<style>
.image-grid {
    display: grid;
    gap: 2px;
}

/* 1 image → full width / big */
.image-count-1 {
    grid-template-columns: 1fr;
    grid-auto-rows: 500px;
}

/* 2 images → 2 columns */
.image-count-2 {
    grid-template-columns: repeat(2, 1fr);
    grid-auto-rows: 400px;
}

/* 3–20 images → 2x2 grid (2 per row, 2 rows) */
.image-count-3,
.image-count-4,
.image-count-5,
.image-count-6,
.image-count-7,
.image-count-8,
.image-count-9,
.image-count-10,
.image-count-11,
.image-count-12,
.image-count-13,
.image-count-14,
.image-count-15,
.image-count-16,
.image-count-17,
.image-count-18,
.image-count-19,
.image-count-20 {
    grid-template-columns: repeat(2, 1fr); /* 2 per row */
    grid-template-rows: repeat(2, 320px);  /* 2 rows of 120px each */
    gap: 2px;
}

.grid-item {
    position: relative;
    overflow: hidden;
    cursor: pointer;
}

.grid-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s;
}
/* 
.grid-item:hover img {
    transform: scale(1.05);
} */

.grid-item .overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    color: #fff;
    font-size: 2rem;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>


<div class="container" style="max-width: 1200px;">

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center my-3">
        <h4 class="text-primary fw-bold">Announcements</h4>

        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="bi bi-plus"></i> Add
        </button>
    </div>

    <?php
    $query = "SELECT a.*, u.fullname 
              FROM announcement_tb a
              LEFT JOIN user_tb u ON a.created_by = u.user_id
              ORDER BY date_created DESC";

    $result = mysqli_query($conn, $query);

    while($row = mysqli_fetch_assoc($result)):
    ?>

    <!-- POST -->
    <div class="card mb-4 shadow-sm border-0">

        <div class="card-body">

            <!-- USER INFO -->
            <div class="d-flex align-items-center mb-2">
                <div class="me-2">
                    <i class="bi bi-person-circle fs-3 text-secondary"></i>
                </div>

                <div>
                    <strong><?= $row['fullname'] ?? 'Unknown' ?></strong><br>
                    <small class="text-muted">
                        <?= date("F d, Y h:i A", strtotime($row['date_created'])) ?>
                    </small>
                </div>
            </div>

            <!-- TITLE -->
            <h5 class="mt-2"><?= $row['title'] ?></h5>

            <!-- DESCRIPTION -->
            <p style="white-space: pre-line;">
                <?= $row['description'] ?>
            </p>
        <div class="mt-3">
        <?php
        $aid = $row['announcement_id'];
        $files = mysqli_query($conn, "SELECT * FROM announcement_attachments WHERE announcement_id='$aid'");

        $images = [];
        while($f = mysqli_fetch_assoc($files)){
            if(preg_match('/(jpg|jpeg|png|gif)$/i', $f['file_name'])){
                $images[] = $f['file_path'];
            } elseif(preg_match('/(pdf)$/i', $f['file_name'])){
                echo "<div class='mb-2'>
                        <iframe src='{$f['file_path']}' width='100%' height='1000px' style='border:1px solid #ccc;'></iframe>
                    </div>";
            } else {
                echo "<div>
                        <i class='bi bi-paperclip'></i>
                        <a href='{$f['file_path']}' target='_blank'>{$f['file_name']}</a>
                    </div>";
            }
        }

        $count = count($images);

        if($count > 0){
            echo "<div class='image-grid image-count-$count'>";
            $slice = array_slice($images, 0, 4); // show max 4 thumbnails in grid
            foreach($slice as $index => $img){
                $extra = $count - 4;
                $overlay = '';
                if($index == 3 && $extra > 0){
                    $overlay = "<span class='overlay'>+$extra</span>";
                }
                echo "<div class='grid-item' data-aid='$aid' data-index='$index'>
                        <img src='$img'>
                        $overlay
                    </div>";
            }
            echo "</div>";
        }

        // Store JS array for modal
        echo "<script>var images_$aid = " . json_encode($images) . ";</script>";
        ?>
        </div>

        </div>

        <!-- ACTIONS -->
        <div class="card-footer bg-white border-0 d-flex justify-content-end">

            <button class="btn btn-sm btn-outline-warning me-2 editBtn"
                data-id="<?= $row['announcement_id'] ?>"
                data-title="<?= $row['title'] ?>"
                data-desc="<?= $row['description'] ?>"
                data-remarks="<?= $row['remarks'] ?>">
                Edit
            </button>

            <a href="index.php?page=announcement/crud/delete_announcement&id=<?= $row['announcement_id'] ?>"
            class="btn btn-sm btn-outline-danger"
            onclick="return confirm('Are you sure you want to delete this announcement?')">
            Delete
            </a>

        </div>

    </div>

    <?php endwhile; ?>

</div>
<div class="modal fade modal-lg text-white" id="addModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" enctype="multipart/form-data">
        <div class="modal-header">
          <h5 class="modal-title">Add Announcement</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="text" name="title" class="form-control" placeholder="Title" required>
          <textarea name="description" class="form-control mt-2" placeholder="Description"></textarea>
          <input type="file" name="files[]" multiple class="form-control mt-2">
        </div>
        <div class="modal-footer">
          <button type="submit" name="add_announcement" class="btn btn-primary">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php include "crud/add_announcement.php"; ?>
<?php include "crud/edit_announcement.php"; ?>

<script>
$('.editBtn').click(function(){
    let aid = $(this).data('id');
    $('#edit_id').val(aid);
    $('#edit_title').val($(this).data('title'));
    $('#edit_desc').val($(this).data('desc'));
    $('#edit_remarks').val($(this).data('remarks'));

    // Populate existing attachments
    let attachments = window['images_' + aid]; // same JS array used for modal images
    let html = '';
    attachments.forEach(file => {
        html += `<div class="position-relative" style="width:80px; height:80px;">
                    <img src="${file.path}" class="img-thumbnail" style="width:100%; height:100%; object-fit:cover;">
                    <button type="button" class="btn-close position-absolute top-0 end-0 remove-file" data-attachment-id="${file.id}"></button>
                </div>`;
    });
    $('#currentAttachments').html(html);

    $('#editModal').modal('show');
});
</script>
<script>
$(document).ready(function(){
    var currentImages = [];
    var currentIndex = 0;

    $('.grid-item').click(function(){
        var aid = $(this).data('aid');
        currentImages = window['images_' + aid];
        currentIndex = parseInt($(this).data('index'));
        $('#modal-img').attr('src', currentImages[currentIndex]);
        $('#imageModal').modal('show');
    });

    $('#prevImg').click(function(){
        currentIndex = (currentIndex - 1 + currentImages.length) % currentImages.length;
        $('#modal-img').attr('src', currentImages[currentIndex]);
    });

    $('#nextImg').click(function(){
        currentIndex = (currentIndex + 1) % currentImages.length;
        $('#modal-img').attr('src', currentImages[currentIndex]);
    });
});
</script>