<?php

include __DIR__ . '/../../../includes/auth.php';
include __DIR__ . '/../../../includes/db.php';


// =====================================================
// GENERATE BASE URL
// =====================================================

$protocol = (
    isset($_SERVER['HTTPS'])
    && $_SERVER['HTTPS'] === 'on'
) ? 'https' : 'http';

$host = $_SERVER['HTTP_HOST'];

$scriptPath = dirname($_SERVER['SCRIPT_NAME']);

$basePath = rtrim(
    $scriptPath,
    '/\\'
);

$baseUrl = "$protocol://$host$basePath";


// =====================================================
// INITIAL VALUES
// =====================================================

$categoryName = '';
$categoryDescription = '';
$purchaserId = '';

$errors = [];


// =====================================================
// FETCH PURCHASERS ONLY
// =====================================================

$purchasers = [];

$purchaserSql = "
    SELECT
        user_id,
        fullname
    FROM user_tb
    WHERE department = 'Purchasing'
    ORDER BY fullname ASC
";

$purchaserResult = $conn->query($purchaserSql);

if ($purchaserResult) {

    while ($purchaser = $purchaserResult->fetch_assoc()) {

        $purchasers[] = $purchaser;

    }

}


// =====================================================
// PROCESS FORM
// =====================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {


    // -------------------------------------------------
    // GET FORM VALUES
    // -------------------------------------------------

    $categoryName = trim(
        $_POST['category_name'] ?? ''
    );

    $categoryDescription = trim(
        $_POST['category_description'] ?? ''
    );

    $purchaserId = trim(
        $_POST['purchaser_id'] ?? ''
    );


    // -------------------------------------------------
    // VALIDATION
    // -------------------------------------------------

    if ($categoryName === '') {

        $errors[] = 'Category name is required.';

    }


    if (mb_strlen($categoryName) > 255) {

        $errors[] =
            'Category name must not exceed 255 characters.';

    }


    if ($categoryDescription === '') {

        $errors[] =
            'Category description is required.';

    }


    if ($purchaserId === '') {

        $errors[] =
            'Purchaser is required.';

    }


    // -------------------------------------------------
    // VALIDATE PURCHASER
    // -------------------------------------------------

    if ($purchaserId !== '') {

        if (!ctype_digit($purchaserId)) {

            $errors[] =
                'Invalid purchaser selected.';

        } else {

            $checkPurchaser = $conn->prepare("
                SELECT user_id
                FROM user_tb
                WHERE user_id = ?
                  AND department = 'Purchasing'
                LIMIT 1
            ");

            $purchaserIdInt = (int)$purchaserId;

            $checkPurchaser->bind_param(
                'i',
                $purchaserIdInt
            );

            $checkPurchaser->execute();

            $purchaserCheckResult =
                $checkPurchaser->get_result();

            if ($purchaserCheckResult->num_rows === 0) {

                $errors[] =
                    'The selected user is not a valid purchaser.';

            }

            $checkPurchaser->close();

        }

    }


    // -------------------------------------------------
    // CHECK DUPLICATE CATEGORY
    // -------------------------------------------------

    if ($categoryName !== '') {

        $duplicateCheck = $conn->prepare("
            SELECT category_id
            FROM request_category_tb
            WHERE LOWER(TRIM(category_name))
                = LOWER(TRIM(?))
            LIMIT 1
        ");

        $duplicateCheck->bind_param(
            's',
            $categoryName
        );

        $duplicateCheck->execute();

        $duplicateResult =
            $duplicateCheck->get_result();

        if ($duplicateResult->num_rows > 0) {

            $errors[] =
                'A request category with this name already exists.';

        }

        $duplicateCheck->close();

    }


    // =================================================
    // INSERT CATEGORY
    // =================================================

    if (empty($errors)) {


        // -------------------------------------------------
        // STATUS
        // -------------------------------------------------

        $status = 0;


        // -------------------------------------------------
        // INSERT
        // -------------------------------------------------

        $insertSql = "
            INSERT INTO request_category_tb
            (
                purchaser_id,
                category_name,
                category_description,
                status,
                date_created
            )
            VALUES
            (
                ?,
                ?,
                ?,
                ?,
                NOW()
            )
        ";


        $stmt = $conn->prepare($insertSql);


        if (!$stmt) {

            $errors[] =
                'Unable to prepare the database request: '
                . $conn->error;

        } else {


            $purchaserIdInt = (int)$purchaserId;


            $stmt->bind_param(
                'isss',
                $purchaserIdInt,
                $categoryName,
                $categoryDescription,
                $status
            );


            if ($stmt->execute()) {


                $stmt->close();


                // -------------------------------------------------
                // REDIRECT TO CATEGORY LIST
                // -------------------------------------------------
            echo '<script>
                window.location.href = "?page=ticket/lmr_category";
            </script>';

            exit;


                exit;


            } else {

                $errors[] =
                    'Unable to save the request category: '
                    . $stmt->error;

                $stmt->close();

            }

        }

    }

}

?>

<style>

.add-category-card {
    max-width: 900px;
    margin: 0 auto;
}

.form-label {
    font-weight: 600;
}

.required {
    color: #dc3545;
}

.category-description-input {
    min-height: 150px;
    resize: vertical;
}

</style>


<!-- =====================================================
     MAIN CARD
===================================================== -->

<div class="card add-category-card">

    <div class="card-header text-white d-flex justify-content-between align-items-center">

        <span>
            <i class="fas fa-folder-plus me-1"></i>
            Add Request Category
        </span>

        <a
            href="?page=ticket/lmr_category"
            class="btn btn-light btn-sm">

            <i class="fas fa-arrow-left me-1"></i>
            Back

        </a>

    </div>


    <div class="card-body">


        <!-- =================================================
             ERROR MESSAGE
        ================================================= -->

        <?php if (!empty($errors)): ?>

            <div
                class="alert alert-danger"
                role="alert">

                <div class="fw-bold mb-1">
                    Please correct the following:
                </div>

                <ul class="mb-0">

                    <?php foreach ($errors as $error): ?>

                        <li>
                            <?= htmlspecialchars($error) ?>
                        </li>

                    <?php endforeach; ?>

                </ul>

            </div>

        <?php endif; ?>


        <!-- =================================================
             FORM
        ================================================= -->

        <form
            method="POST"
            action=""
            id="addCategoryForm">


            <!-- =================================================
                 PURCHASER
            ================================================= -->

            <div class="mb-3">

                <label
                    for="purchaser_id"
                    class="form-label">

                    Purchaser
                    <span class="required">*</span>

                </label>


                <select
                    name="purchaser_id"
                    id="purchaser_id"
                    class="form-select"
                    required>

                    <option value="">
                        -- Select Purchaser --
                    </option>


                    <?php foreach ($purchasers as $purchaser): ?>

                        <option
                            value="<?= (int)$purchaser['user_id'] ?>"
                            <?= (
                                (string)$purchaserId
                                ===
                                (string)$purchaser['user_id']
                            )
                                ? 'selected'
                                : ''
                            ?>>

                            <?= htmlspecialchars(
                                $purchaser['fullname']
                            ) ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            </div>


            <!-- =================================================
                 CATEGORY NAME
            ================================================= -->

            <div class="mb-3">

                <label
                    for="category_name"
                    class="form-label">

                    Category Name
                    <span class="required">*</span>

                </label>


                <input
                    type="text"
                    name="category_name"
                    id="category_name"
                    class="form-control"
                    maxlength="255"
                    value="<?= htmlspecialchars(
                        $categoryName,
                        ENT_QUOTES
                    ) ?>"
                    placeholder="Enter category name"
                    required>


                <div class="form-text">
                    Enter a unique name for this request category.
                </div>

            </div>


            <!-- =================================================
                 DESCRIPTION
            ================================================= -->

            <div class="mb-3">

                <label
                    for="category_description"
                    class="form-label">

                    Category Description
                    <span class="required">*</span>

                </label>


                <textarea
                    name="category_description"
                    id="category_description"
                    class="form-control category-description-input"
                    placeholder="Enter category description"
                    required><?= htmlspecialchars(
                        $categoryDescription,
                        ENT_QUOTES
                    ) ?></textarea>

            </div>


            <!-- =================================================
                 STATUS
            ================================================= -->

            <!-- <div class="mb-3">

                <label class="form-label">
                    Status
                </label>

                <input
                    type="text"
                    class="form-control"
                    value="0"
                    readonly>

                <div class="form-text">
                    New request categories are saved with status N/A.
                </div>

            </div> -->


            <!-- =================================================
                 BUTTONS
            ================================================= -->

            <div class="d-flex justify-content-end gap-2 mt-4">

                <a
                    href="?page=ticket/lmr_category"
                    class="btn btn-secondary">

                    <i class="fas fa-times me-1"></i>
                    Cancel

                </a>


                <button
                    type="submit"
                    class="btn btn-primary">

                    <i class="fas fa-save me-1"></i>
                    Save Category

                </button>

            </div>


        </form>

    </div>

</div>


<script>

$(document).ready(function () {

    $('#addCategoryForm').on('submit', function () {

        const button =
            $(this).find('button[type="submit"]');

        button
            .prop('disabled', true)
            .html(
                '<i class="fas fa-spinner fa-spin me-1"></i>' +
                ' Saving...'
            );

    });

});

</script>


<?php

$conn->close();

?>
