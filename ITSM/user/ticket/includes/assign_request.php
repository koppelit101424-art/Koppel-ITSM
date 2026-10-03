<?php

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';

header('Content-Type: application/json; charset=utf-8');

$response = [
    'success' => false,
    'message' => ''
];

try {

    if (!isset($_SESSION['user_id'])) {
        throw new Exception('You are not logged in.');
    }

    $current_user_id = (int)$_SESSION['user_id'];

    // =====================================================
    // POST DATA
    // =====================================================

    $request_id = (int)($_POST['request_id'] ?? 0);

    $status = strtolower(
        trim($_POST['status'] ?? '')
    );

    $purchaser_id = (int)(
        $_POST['purchaser_id'] ?? 1
    );

    $priority = strtolower(
        trim($_POST['priority'] ?? '')
    );

    $category_id = (int)(
        $_POST['category_id'] ?? 0
    );

    $order_status = strtolower(
        trim($_POST['order_status'] ?? 'n/a')
    );

    $po_no = trim(
        $_POST['po_no'] ?? ''
    );

    // =====================================================
    // BASIC VALIDATION
    // =====================================================

    if ($request_id <= 0) {
        throw new Exception('Invalid request ID.');
    }

    $allowed_priorities = [
        'urgent',
        'high',
        'medium'
    ];

    if (!in_array($priority, $allowed_priorities, true)) {
        throw new Exception('Invalid priority.');
    }

    $allowed_statuses = [
        'pending',
        'checking requirements',
        'canvassing',
        'negotiation',
        'draft po under discussion',
        'draft po approved',
        'final po approved',
        'rejected',
        'closed'
    ];

    if (!in_array($status, $allowed_statuses, true)) {
        throw new Exception('Invalid status.');
    }

    $allowed_order_statuses = [
        'n/a',
        'order acknowledged',
        'goods received',
        'payment processing',
        'payment issued',
        'closed'
    ];

    // =====================================================
    // FINAL PO APPROVED RULE
    // =====================================================

    if ($status === 'final po approved') {

        if ($po_no === '') {
            throw new Exception(
                'PO Number is required when Status is Final PO Approved.'
            );
        }

        if (!in_array(
            $order_status,
            $allowed_order_statuses,
            true
        )) {
            throw new Exception(
                'Invalid order status.'
            );
        }

    } else {

        // Not Final PO Approved
        $po_no = '';
        $order_status = 'n/a';
    }

    // =====================================================
    // GET CURRENT USER
    // =====================================================

    $userQuery = $conn->prepare("
        SELECT department
        FROM user_tb
        WHERE user_id = ?
        LIMIT 1
    ");

    if (!$userQuery) {
        throw new Exception(
            'User query failed: ' . $conn->error
        );
    }

    $userQuery->bind_param(
        "i",
        $current_user_id
    );

    $userQuery->execute();

    $userResult = $userQuery->get_result();
    $currentUser = $userResult->fetch_assoc();

    $userQuery->close();

    if (!$currentUser) {
        throw new Exception(
            'User account not found.'
        );
    }

    $department = trim(
        $currentUser['department'] ?? ''
    );

    $sessionUserType = trim(
        $_SESSION['user_type'] ?? ''
    );

    $isPurchasing =
        strcasecmp($department, 'Purchasing') === 0;

    $isAdmin =
        strcasecmp($sessionUserType, 'admin') === 0;

    if (!$isPurchasing && !$isAdmin) {
        throw new Exception(
            'You are not authorized to update purchasing requests.'
        );
    }

    // =====================================================
    // CHECK REQUEST
    // =====================================================

    $check = $conn->prepare("
        SELECT request_id
        FROM purch_request_tb
        WHERE request_id = ?
        LIMIT 1
    ");

    $check->bind_param(
        "i",
        $request_id
    );

    $check->execute();

    $result = $check->get_result();

    if ($result->num_rows === 0) {
        $check->close();

        throw new Exception(
            'Request not found.'
        );
    }

    $check->close();

    // =====================================================
    // VALIDATE CATEGORY
    // =====================================================

    if ($category_id > 0) {

        $categoryCheck = $conn->prepare("
            SELECT category_id
            FROM request_category_tb
            WHERE category_id = ?
              AND status = 1
            LIMIT 1
        ");

        if (!$categoryCheck) {
            throw new Exception(
                'Category validation failed: ' .
                $conn->error
            );
        }

        $categoryCheck->bind_param(
            "i",
            $category_id
        );

        $categoryCheck->execute();

        $categoryResult =
            $categoryCheck->get_result();

        if ($categoryResult->num_rows === 0) {

            $categoryCheck->close();

            throw new Exception(
                'Invalid category.'
            );
        }

        $categoryCheck->close();
    }

    // =====================================================
    // VALIDATE PURCHASER
    // =====================================================

    if ($purchaser_id > 1) {

        $purchaserCheck = $conn->prepare("
            SELECT user_id
            FROM user_tb
            WHERE user_id = ?
              AND LOWER(TRIM(department)) = 'purchasing'
            LIMIT 1
        ");

        $purchaserCheck->bind_param(
            "i",
            $purchaser_id
        );

        $purchaserCheck->execute();

        $purchaserResult =
            $purchaserCheck->get_result();

        if ($purchaserResult->num_rows === 0) {

            $purchaserCheck->close();

            throw new Exception(
                'Selected purchaser is not a valid Purchasing user.'
            );
        }

        $purchaserCheck->close();
    }

    // =====================================================
    // UPDATE
    // =====================================================

    $update = $conn->prepare("
        UPDATE purch_request_tb
        SET
            status = ?,
            purchaser_id = ?,
            priority = ?,
            category_id = ?,
            order_status = ?,
            po_no = ?
        WHERE request_id = ?
    ");

    if (!$update) {
        throw new Exception(
            'Update prepare failed: ' .
            $conn->error
        );
    }

    $update->bind_param(
        "sisissi",
        $status,
        $purchaser_id,
        $priority,
        $category_id,
        $order_status,
        $po_no,
        $request_id
    );

    if (!$update->execute()) {

        throw new Exception(
            'Database update failed: ' .
            $update->error
        );
    }

    $update->close();

    // =====================================================
    // GET PURCHASER NAME
    // =====================================================

    $purchaser_name = 'Unassigned';

    if ($purchaser_id > 1) {

        $purchaserQuery = $conn->prepare("
            SELECT fullname
            FROM user_tb
            WHERE user_id = ?
            LIMIT 1
        ");

        $purchaserQuery->bind_param(
            "i",
            $purchaser_id
        );

        $purchaserQuery->execute();

        $purchaserResult =
            $purchaserQuery->get_result();

        $purchaser =
            $purchaserResult->fetch_assoc();

        $purchaserQuery->close();

        if ($purchaser) {
            $purchaser_name =
                $purchaser['fullname'];
        }
    }

    // =====================================================
    // GET CATEGORY NAME
    // =====================================================

    $category_name = 'N/A';

    if ($category_id > 0) {

        $categoryQuery = $conn->prepare("
            SELECT category_name
            FROM request_category_tb
            WHERE category_id = ?
            LIMIT 1
        ");

        $categoryQuery->bind_param(
            "i",
            $category_id
        );

        $categoryQuery->execute();

        $categoryResult =
            $categoryQuery->get_result();

        $category =
            $categoryResult->fetch_assoc();

        $categoryQuery->close();

        if ($category) {
            $category_name =
                $category['category_name'];
        }
    }

    // =====================================================
    // RESPONSE
    // =====================================================

    $response['success'] = true;
    $response['message'] =
        'Request updated successfully.';

    $response['request_id'] = $request_id;
    $response['status'] = $status;
    $response['purchaser_id'] = $purchaser_id;
    $response['priority'] = $priority;
    $response['category_id'] = $category_id;
    $response['category_name'] = $category_name;
    $response['order_status'] = $order_status;
    $response['po_no'] = $po_no;
    $response['purchaser_name'] = $purchaser_name;

} catch (Throwable $e) {

    http_response_code(400);

    $response['success'] = false;
    $response['message'] = $e->getMessage();
}

echo json_encode(
    $response,
    JSON_UNESCAPED_UNICODE
);

$conn->close();