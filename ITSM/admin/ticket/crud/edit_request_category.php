<?php


include __DIR__ . '/../../../includes/auth.php';
include __DIR__ . '/../../../includes/db.php';

// =====================================================
// GET CATEGORY ID
// =====================================================

$categoryId = isset($_GET['id'])
    ? (int)$_GET['id']
    : 0;


if ($categoryId <= 0) {

    die('Invalid category ID.');

}


// =====================================================
// FETCH CATEGORY
// =====================================================

$stmt = $conn->prepare("
    SELECT
        category_id,
        purchaser_id,
        category_name,
        category_description,
        status,
        date_created
    FROM request_category_tb
    WHERE category_id = ?
    LIMIT 1
");

$stmt->bind_param(
    "i",
    $categoryId
);

$stmt->execute();

$result = $stmt->get_result();

$category = $result->fetch_assoc();

$stmt->close();


if (!$category) {

    die('Request category not found.');

}


// =====================================================
// FETCH PURCHASERS
// =====================================================

$purchaserSql = "
    SELECT
        user_id,
        fullname
    FROM user_tb
    WHERE department = 'Purchasing'
    ORDER BY fullname ASC
";


$purchaserResult = $conn->query($purchaserSql);


// =====================================================
// UPDATE CATEGORY
// =====================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $purchaserId = isset($_POST['purchaser_id'])
        ? (int)$_POST['purchaser_id']
        : 0;

    $categoryName = trim(
        $_POST['category_name'] ?? ''
    );

    $categoryDescription = trim(
        $_POST['category_description'] ?? ''
    );

    $status =  0;

    // =================================================
    // VALIDATION
    // =================================================

    if (
        $purchaserId <= 0 ||
        $categoryName === ''
    ) {

        $error = 'Please complete all required fields.';

    } else {


        // =================================================
        // UPDATE
        // =================================================

        $updateStmt = $conn->prepare("
            UPDATE request_category_tb
            SET
                purchaser_id = ?,
                category_name = ?,
                category_description = ?,
                status = ?
            WHERE category_id = ?
        ");

        $updateStmt->bind_param(
            "isssi",
            $purchaserId,
            $categoryName,
            $categoryDescription,
            $status,
            $categoryId
        );


        if ($updateStmt->execute()) {

            $updateStmt->close();

            // Redirect back to category list

          echo '<script>
                window.location.href = "?page=ticket/lmr_category";
            </script>';


            exit;

        } else {

            $error =
                'Failed to update category: ' .
                $updateStmt->error;

            $updateStmt->close();

        }

    }

}

?>

<div class="card">

    <div class="card-header bg-primary text-white">

        <i class="fas fa-edit me-2"></i>

        Edit Request Category

    </div>


    <div class="card-body">

        <?php if (!empty($error)): ?>

            <div class="alert alert-danger">

                <?= htmlspecialchars(
                    $error,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>

            </div>

        <?php endif; ?>


        <form
            method="POST"
            action=""
        >


            <!-- =================================================
                 PURCHASER
            ================================================= -->

            <div class="mb-3">

                <label
                    for="purchaser_id"
                    class="form-label"
                >
                    Purchaser
                </label>

                <select
                    name="purchaser_id"
                    id="purchaser_id"
                    class="form-select"
                    required
                >

                    <option value="">
                        -- Select Purchaser --
                    </option>


                    <?php while (
                        $purchaser = $purchaserResult->fetch_assoc()
                    ): ?>

                        <option
                            value="<?= (int)$purchaser['user_id'] ?>"
                            <?= (
                                (int)$purchaser['user_id']
                                === (int)$category['purchaser_id']
                            )
                                ? 'selected'
                                : ''
                            ?>
                        >

                            <?= htmlspecialchars(
                                $purchaser['fullname'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>

                        </option>

                    <?php endwhile; ?>

                </select>

            </div>


            <!-- =================================================
                 CATEGORY NAME
            ================================================= -->

            <div class="mb-3">

                <label
                    for="category_name"
                    class="form-label"
                >
                    Category Name
                </label>

                <input
                    type="text"
                    name="category_name"
                    id="category_name"
                    class="form-control"
                    value="<?= htmlspecialchars(
                        $category['category_name'],
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                    required
                >

            </div>


            <!-- =================================================
                 DESCRIPTION
            ================================================= -->

            <div class="mb-3">

                <label
                    for="category_description"
                    class="form-label"
                >
                    Description
                </label>

                <textarea
                    name="category_description"
                    id="category_description"
                    class="form-control"
                    rows="5"
                ><?= htmlspecialchars(
                    $category['category_description'],
                    ENT_QUOTES,
                    'UTF-8'
                ) ?></textarea>

            </div>


            <!-- =================================================
                 STATUS
            ================================================= -->

            <!-- <div class="mb-3">

                <label
                    for="status"
                    class="form-label"
                >
                    Status
                </label>

                <select
                    name="status"
                    id="status"
                    class="form-select"
                >

                    <option
                        value="active"
                        <?= $category['status'] === 'active'
                            ? 'selected'
                            : '' ?>
                    >
                        Active
                    </option>

                    <option
                        value="inactive"
                        <?= $category['status'] === 'inactive'
                            ? 'selected'
                            : '' ?>
                    >
                        Inactive
                    </option>

                    <option
                        value="pending"
                        <?= $category['status'] === 'pending'
                            ? 'selected'
                            : '' ?>
                    >
                        Pending
                    </option>

                    <option
                        value="canceled"
                        <?= $category['status'] === 'canceled'
                            ? 'selected'
                            : '' ?>
                    >
                        Canceled
                    </option>

                </select>

            </div> -->


            <!-- =================================================
                 BUTTONS
            ================================================= -->

            <div class="d-flex gap-2">

                <button
                    type="submit"
                    class="btn btn-primary"
                >

                    <i class="fas fa-save me-1"></i>

                    Save Changes

                </button>


                <a
                    href="?page=ticket/lmr_category"
                    class="btn btn-secondary"
                >

                    <i class="fas fa-arrow-left me-1"></i>

                    Cancel

                </a>

            </div>

        </form>

    </div>

</div>


<?php

$conn->close();

?>
