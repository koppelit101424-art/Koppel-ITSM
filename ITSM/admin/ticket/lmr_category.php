<?php
include __DIR__ . '/../../includes/auth.php';
include __DIR__ . '/../../includes/db.php';

// Generate base URL dynamically
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$scriptPath = dirname($_SERVER['SCRIPT_NAME']);
$basePath = rtrim($scriptPath, '/\\');
$baseUrl = "$protocol://$host$basePath";


// =====================================================
// FETCH REQUEST CATEGORIES
// =====================================================

$sql = "SELECT
            rc.category_id,
            rc.purchaser_id,
            u.fullname AS purchaser_name,
            rc.category_name,
            rc.category_description,
            rc.status,
            rc.date_created
        FROM request_category_tb rc
        LEFT JOIN user_tb u
            ON u.user_id = rc.purchaser_id
        ORDER BY rc.category_id DESC";


$result = $conn->query($sql);

?>

<style>

/* =====================================================
   STATUS COLORS
===================================================== */

.status-blue {
    background-color: #0d6efd !important;
    color: #fff !important;
    border-color: #0d6efd !important;
}

.status-green {
    background-color: #198754 !important;
    color: #fff !important;
    border-color: #198754 !important;
}

.status-yellow {
    background-color: #ffc107 !important;
    color: #fff !important;
    border-color: #ffc107 !important;
}

.status-grey {
    background-color: #6c757d !important;
    color: #fff !important;
    border-color: #6c757d !important;
}

.status-red {
    background-color: #dc3545 !important;
    color: #fff !important;
    border-color: #dc3545 !important;
}


/* =====================================================
   CONTEXT MENU
===================================================== */

.custom-menu {
    display: none;
    position: absolute;
    background: white;
    border: 1px solid #ddd;
    box-shadow: 0 4px 10px rgba(0,0,0,0.15);
    z-index: 10000;
    min-width: 180px;
    border-radius: 4px;
    padding: 5px 0;
}

.custom-menu a {
    display: block;
    padding: 8px 16px;
    color: #333;
    text-decoration: none;
}

.custom-menu a:hover {
    background-color: #f0f8ff;
}


/* =====================================================
   FILTER BUTTON
===================================================== */

.btn-outline-blue {
    color: #1E3A8A;
    border-color: #1E3A8A;
}

.btn-outline-blue:hover,
.btn-outline-blue.active {
    background-color: #1E3A8A;
    color: white;
}


/* =====================================================
   TABLE
===================================================== */

#categoryTable tbody tr {
    cursor: pointer;
}

#categoryTable tbody tr:hover {
    background-color: #f5f9ff;
}

#categoryTable td {
    vertical-align: middle;
}

.category-description {
    width: 500px;
    min-width: 500px;
    max-width: 500px;
    white-space: normal;
    word-wrap: break-word;
}

#categoryTable {
    width: 100%;
}

#categoryTable th,
#categoryTable td {
    vertical-align: middle;
}

#categoryTable th:nth-child(2),
#categoryTable td:nth-child(2) {
    width: 70px;
    min-width: 70px;
    max-width: 70px;
}

#categoryTable th:nth-child(5),
#categoryTable td:nth-child(5) {
    width: 500px;
    min-width: 500px;
}

/* =====================================================
   STATUS BADGE
===================================================== */

.category-status {
    min-width: 120px;
    cursor: pointer;
}

</style>


<!-- =====================================================
     MAIN CARD
===================================================== -->

<div class="card">

    <div class="card-header d-flex justify-content-between text-white">

        <span>
            Request Category Management
        </span>

        <span>


            <!-- ADD CATEGORY -->
            <a
                href="?page=ticket/crud/add_request_category"
                class="btn btn-primary">

                <i class="fas fa-plus me-1"></i>
                Add Category

            </a>

        </span>

    </div>


    <div class="card-body">



        <!-- =====================================================
             TABLE
        ===================================================== -->

        <div class="table-responsive">

            <table
                id="categoryTable"
                class="table table-hover">

                <thead class="table-header-blue">

                    <tr>

                        <!-- Hidden ID -->
                        <th style="display:none;">
                            ID
                        </th>

                        <th>
                             ID
                        </th>

                        <th>
                            Purchaser ID
                        </th>

                        <th>
                            Category Name
                        </th>

                        <th>
                            Description
                        </th>

                        <!-- <th>
                            Status
                        </th> -->

                        <th>
                            Created
                        </th>
                        <th>
                            Action
                        </th>

                    </tr>

                </thead>


                <tbody>

                <?php while ($row = $result->fetch_assoc()): ?>

                    <?php

                    $categoryId = (int)$row['category_id'];

                    $status = strtolower(
                        trim($row['status'] ?? '')
                    );

                    ?>

                    <tr
                        data-category-id="<?= $categoryId ?>"
                        data-category-name="<?= htmlspecialchars(
                            $row['category_name'],
                            ENT_QUOTES
                        ) ?>"
                        data-status="<?= htmlspecialchars($status) ?>"
                    >


                        <!-- Hidden ID -->

                        <td style="display:none;">

                            <?= $categoryId ?>

                        </td>


                        <!-- CATEGORY ID -->

                        <td>

                            <?= $categoryId ?>

                        </td>


                        <!-- PURCHASER ID -->

                            <td>
                                <?= htmlspecialchars(
                                    $row['purchaser_name']
                                    ?? $row['purchaser_id']
                                ) ?>
                            </td>



                        <!-- CATEGORY NAME -->

                        <td>

                            <?= htmlspecialchars(
                                $row['category_name']
                            ) ?>

                        </td>


                        <!-- DESCRIPTION -->

                        <td class="category-description">

                            <?= htmlspecialchars(
                                $row['category_description']
                            ) ?>

                        </td>


                        <!-- STATUS -->

                        <!-- <td
                            onclick="event.stopPropagation();"
                            style="width:160px;padding:0;">

                            <?php

                            $statusColors = [

                                'active'   => 'success',

                                'inactive' => 'secondary',

                                'pending'  => 'warning',

                                'canceled' => 'danger',

                            ];

                            $color =
                                $statusColors[$status]
                                ?? 'secondary';

                            ?>

                            <span
                                class="badge bg-<?= $color ?>
                                       category-status
                                       w-100
                                       h-100
                                       d-flex
                                       align-items-center
                                       justify-content-center"

                                data-category-id="<?= $categoryId ?>"

                                data-current="<?= htmlspecialchars(
                                    $status
                                ) ?>"
                            >

                                <?= ucwords($status) ?>

                            </span>

                        </td> -->


                        <!-- DATE CREATED -->

                        <td>

                            <?= !empty($row['date_created'])
                                ? date(
                                    'm-d-Y',
                                    strtotime(
                                        $row['date_created']
                                    )
                                )
                                : ''
                            ?>

                        </td>
 <!-- =================================================
                             ACTIONS
                        ================================================= -->

                        <td
                            class="action-buttons"
                            onclick="event.stopPropagation();"
                        >

                            <!-- EDIT -->

                            <a
                                href="?page=ticket/crud/edit_request_category&id=<?= $categoryId ?>"
                                class="btn btn-sm btn-primary"
                                title="Edit Category"
                            >

                                <i class="fas fa-edit"></i>

                            </a>


                            <!-- DELETE -->

                            <button
                                type="button"
                                class="btn btn-sm btn-danger"
                                title="Delete Category"
                                onclick="deleteCategory(<?= $categoryId ?>)"
                            >

                                <i class="fas fa-trash"></i>

                            </button>

                        </td>

                    </tr>

                <?php endwhile; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>


<!-- =====================================================
     CONTEXT MENU
===================================================== -->

<div
    id="contextMenu"
    class="custom-menu">
</div>


<!-- =====================================================
     BASE URL
===================================================== -->

<script>

const BASE_URL = '<?= $baseUrl ?>';

</script>


<!-- =====================================================
     JAVASCRIPT LIBRARIES
===================================================== -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>

<script src="https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap5.min.js"></script>


<!-- =====================================================
     DATATABLE
===================================================== -->

<script>

$(document).ready(function () {


    const table = $('#categoryTable').DataTable({

        pageLength: 25,

        lengthMenu: [
            10,
            25,
            50,
            100,
            250,
            500,
            1000,
            3000,
            5000
        ],

        order: [
            [0, "desc"]
        ],

        columnDefs: [

            {
                orderable: false,
                targets: [4, 5]
            }

        ]

    });


    // =================================================
    // GLOBAL SEARCH
    // =================================================

    $('#globalSearch').on('keyup', function () {

        table
            .search(this.value)
            .draw();

    });

 

});


function deleteCategory(categoryId) {

    if (!categoryId) {

        alert('Invalid category ID.');

        return;

    }


    const confirmed = confirm(
        'Are you sure you want to delete this request category?'
    );


    if (!confirmed) {

        return;

    }


    $.ajax({

        url: 'ticket/crud/delete_request_category.php',


        type: 'POST',

        dataType: 'json',

        data: {
            category_id: categoryId
        },


        success: function (response) {


            if (response.success) {

                alert(response.message);

                // Reload the page
                window.location.href =
                    '?page=ticket/lmr_category';

            } else {

                alert(
                    response.message
                    || 'Unable to delete request category.'
                );

            }

        },


        error: function (xhr, status, error) {

            console.error(xhr.responseText);

            alert(
                'An error occurred while deleting the category.'
            );

        }

    });

}

</script>



<?php

$conn->close();

?>
