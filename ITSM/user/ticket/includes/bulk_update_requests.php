<?php

ob_start();

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';

header('Content-Type: application/json; charset=utf-8');

function returnJson($response, $statusCode = 200)
{
    if (ob_get_length()) {
        ob_clean();
    }

    http_response_code($statusCode);

    echo json_encode(
        $response,
        JSON_UNESCAPED_UNICODE
    );

    exit;
}

try {

    // =====================================================
    // LOGIN
    // =====================================================

    if (!isset($_SESSION['user_id'])) {

        returnJson([
            'success' => false,
            'message' => 'Unauthorized.'
        ], 401);
    }

    $userId = (int)$_SESSION['user_id'];

    // =====================================================
    // CHECK USER
    // =====================================================

    $userQuery = $conn->prepare("
        SELECT department
        FROM user_tb
        WHERE user_id = ?
        LIMIT 1
    ");

    $userQuery->bind_param(
        "i",
        $userId
    );

    $userQuery->execute();

    $userResult =
        $userQuery->get_result();

    $user =
        $userResult->fetch_assoc();

    $userQuery->close();

    if (!$user) {
        throw new Exception(
            'User account not found.'
        );
    }

    $department =
        trim($user['department'] ?? '');

    $isPurchasing =
        strcasecmp(
            $department,
            'Purchasing'
        ) === 0;

    $isAdmin =
        isset($_SESSION['user_type']) &&
        strcasecmp(
            trim($_SESSION['user_type']),
            'admin'
        ) === 0;

    if (!$isPurchasing && !$isAdmin) {
        throw new Exception(
            'You are not authorized to perform bulk updates.'
        );
    }

    // =====================================================
    // POST DATA
    // =====================================================

    $requestIds =
        $_POST['request_ids'] ?? [];

    $status =
        strtolower(
            trim($_POST['status'] ?? '')
        );

    $priority =
        strtolower(
            trim($_POST['priority'] ?? '')
        );

    $purchaserId =
        trim($_POST['purchaser_id'] ?? '');

    $categoryId =
        trim($_POST['category_id'] ?? '');

    $orderStatus =
        strtolower(
            trim($_POST['order_status'] ?? 'n/a')
        );

    $poNo =
        trim($_POST['po_no'] ?? '');

    // =====================================================
    // REQUEST IDS
    // =====================================================

    if (
        !is_array($requestIds) ||
        count($requestIds) === 0
    ) {
        throw new Exception(
            'No requests were selected.'
        );
    }

    $requestIds = array_map(
        'intval',
        $requestIds
    );

    $requestIds = array_values(
        array_filter(
            $requestIds,
            function ($id) {
                return $id > 0;
            }
        )
    );

    if (count($requestIds) === 0) {
        throw new Exception(
            'Invalid request IDs.'
        );
    }

    // =====================================================
    // STATUS
    // =====================================================

    $allowedStatuses = [
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

    if (
        $status !== '' &&
        !in_array(
            $status,
            $allowedStatuses,
            true
        )
    ) {
        throw new Exception(
            'Invalid status.'
        );
    }

    // =====================================================
    // PRIORITY
    // =====================================================

    $allowedPriorities = [
        'urgent',
        'high',
        'medium'
    ];

    if (
        $priority !== '' &&
        !in_array(
            $priority,
            $allowedPriorities,
            true
        )
    ) {
        throw new Exception(
            'Invalid priority.'
        );
    }

    // =====================================================
    // CATEGORY
    // =====================================================

    if ($categoryId !== '') {

        if (!ctype_digit($categoryId)) {
            throw new Exception(
                'Invalid category.'
            );
        }

        $categoryId = (int)$categoryId;

        $categoryCheck = $conn->prepare("
            SELECT category_id
            FROM request_category_tb
            WHERE category_id = ?
              AND status = 1
            LIMIT 1
        ");

        $categoryCheck->bind_param(
            "i",
            $categoryId
        );

        $categoryCheck->execute();

        $categoryResult =
            $categoryCheck->get_result();

        if ($categoryResult->num_rows === 0) {

            $categoryCheck->close();

            throw new Exception(
                'Selected category is invalid.'
            );
        }

        $categoryCheck->close();
    }

    // =====================================================
    // PURCHASER
    // =====================================================

    if ($purchaserId !== '') {

        if (!ctype_digit($purchaserId)) {
            throw new Exception(
                'Invalid purchaser.'
            );
        }

        $purchaserId = (int)$purchaserId;

        if ($purchaserId > 1) {

            $purchaserCheck = $conn->prepare("
                SELECT user_id
                FROM user_tb
                WHERE user_id = ?
                  AND LOWER(TRIM(department)) = 'purchasing'
                LIMIT 1
            ");

            $purchaserCheck->bind_param(
                "i",
                $purchaserId
            );

            $purchaserCheck->execute();

            $purchaserResult =
                $purchaserCheck->get_result();

            if ($purchaserResult->num_rows === 0) {

                $purchaserCheck->close();

                throw new Exception(
                    'Selected purchaser is not valid.'
                );
            }

            $purchaserCheck->close();
        }
    }

    // =====================================================
    // ORDER STATUS
    // =====================================================

    $allowedOrderStatuses = [
        'n/a',
        'order acknowledged',
        'goods received',
        'payment processing',
        'payment issued',
        'closed'
    ];

    if (
        $orderStatus !== '' &&
        !in_array(
            $orderStatus,
            $allowedOrderStatuses,
            true
        )
    ) {
        throw new Exception(
            'Invalid order status.'
        );
    }

    // =====================================================
    // FINAL PO RULE
    // =====================================================

        if ($status === 'final po approved') {

            if ($poNo === '') {
                throw new Exception(
                    'PO Number is required when Status is Final PO Approved.'
                );
            }

            if ($orderStatus === '') {
                $orderStatus = 'n/a';
            }

        } else {

            // When moving away from Final PO Approved,
            // automatically clear PO Number and reset Order Status.
            $poNo = null;
            $orderStatus = 'n/a';
        }

    // =====================================================
    // BUILD UPDATE
    // =====================================================

    $fields = [];
    $types = '';
    $values = [];

    // STATUS
    if ($status !== '') {

        $fields[] = "status = ?";
        $types .= "s";
        $values[] = $status;
    }

    // PRIORITY
    if ($priority !== '') {

        $fields[] = "priority = ?";
        $types .= "s";
        $values[] = $priority;
    }

    // PURCHASER
    if ($purchaserId !== '') {

        $fields[] = "purchaser_id = ?";
        $types .= "i";
        $values[] = $purchaserId;
    }

    // CATEGORY
    if ($categoryId !== '') {

        $fields[] = "category_id = ?";
        $types .= "i";
        $values[] = $categoryId;
    }

    // ORDER STATUS
    if ($orderStatus !== '') {

        $fields[] = "order_status = ?";
        $types .= "s";
        $values[] = $orderStatus;
    }

    // PO NUMBER
    if ($poNo !== '') {

        $fields[] = "po_no = ?";
        $types .= "s";
        $values[] = $poNo;
    }

    // =====================================================
    // NOTHING TO UPDATE
    // =====================================================

    if (count($fields) === 0) {

        throw new Exception(
            'No changes were specified.'
        );
    }

    // =====================================================
    // IDS
    // =====================================================

    $placeholders = implode(
        ',',
        array_fill(
            0,
            count($requestIds),
            '?'
        )
    );

    foreach ($requestIds as $id) {

        $types .= "i";
        $values[] = $id;
    }

    // =====================================================
    // SQL
    // =====================================================

    $sql = "
        UPDATE purch_request_tb
        SET " . implode(', ', $fields) . "
        WHERE request_id IN ($placeholders)
    ";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {

        throw new Exception(
            'Failed to prepare update: ' .
            $conn->error
        );
    }

    $stmt->bind_param(
        $types,
        ...$values
    );

    if (!$stmt->execute()) {

        $error = $stmt->error;

        $stmt->close();

        throw new Exception(
            'Database update failed: ' . $error
        );
    }

    $affectedRows =
        $stmt->affected_rows;

    $stmt->close();

    returnJson([
        'success' => true,
        'message' =>
            count($requestIds) .
            ' request(s) updated successfully.',
        'affected_rows' => $affectedRows
    ]);

} catch (Throwable $e) {

    returnJson([
        'success' => false,
        'message' => $e->getMessage()
    ], 400);
}