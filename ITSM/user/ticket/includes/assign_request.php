<?php

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';

header('Content-Type: application/json; charset=utf-8');

$response = [
    'success' => false,
    'message' => ''
];

try {

    // =====================================================
    // CHECK LOGIN
    // =====================================================

    if (!isset($_SESSION['user_id'])) {
        throw new Exception('You are not logged in.');
    }

    $current_user_id = (int) $_SESSION['user_id'];


    // =====================================================
    // GET POST DATA
    // =====================================================

    $request_id = isset($_POST['request_id'])
        ? (int) $_POST['request_id']
        : 0;

    $status = strtolower(trim($_POST['status'] ?? ''));

    $purchaser_id = isset($_POST['purchaser_id'])
        ? (int) $_POST['purchaser_id']
        : 1;


    // =====================================================
    // VALIDATE REQUEST ID
    // =====================================================

    if ($request_id <= 0) {
        throw new Exception('Invalid request ID.');
    }


    // =====================================================
    // VALIDATE STATUS
    // =====================================================

    $allowed_statuses = [
        'pending',
        'proceed request',
        'checking request',
        'closed'
    ];

    if (!in_array($status, $allowed_statuses, true)) {
        throw new Exception('Invalid status.');
    }


    // =====================================================
    // GET CURRENT USER DEPARTMENT
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
        throw new Exception('User account not found.');
    }


    // =====================================================
    // CHECK PURCHASING / ADMIN
    // =====================================================

    $department = trim(
        $currentUser['department'] ?? ''
    );

    $sessionUserType = trim(
        $_SESSION['user_type'] ?? ''
    );

    $isPurchasing =
        strcasecmp(
            $department,
            'Purchasing'
        ) === 0;

    $isAdmin =
        strcasecmp(
            $sessionUserType,
            'admin'
        ) === 0;


    if (!$isPurchasing && !$isAdmin) {
        throw new Exception(
            'You are not authorized to update purchasing requests.'
        );
    }


    // =====================================================
    // CHECK REQUEST EXISTS
    // =====================================================

    $check = $conn->prepare("
        SELECT request_id
        FROM purch_request_tb
        WHERE request_id = ?
        LIMIT 1
    ");

    if (!$check) {
        throw new Exception(
            'Request check failed: ' . $conn->error
        );
    }

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
    // VALIDATE PURCHASER
    // =====================================================

    // 1 = Unassigned
    if ($purchaser_id > 1) {

        $purchaserCheck = $conn->prepare("
            SELECT user_id
            FROM user_tb
            WHERE user_id = ?
              AND LOWER(TRIM(department)) = 'purchasing'
            LIMIT 1
        ");

        if (!$purchaserCheck) {
            throw new Exception(
                'Purchaser validation failed: ' .
                $conn->error
            );
        }

        $purchaserCheck->bind_param(
            "i",
            $purchaser_id
        );

        $purchaserCheck->execute();

        $purchaserResult = $purchaserCheck->get_result();

        if ($purchaserResult->num_rows === 0) {
            $purchaserCheck->close();

            throw new Exception(
                'Selected purchaser is not a valid Purchasing user.'
            );
        }

        $purchaserCheck->close();
    }


    // =====================================================
    // UPDATE REQUEST
    // =====================================================

    $update = $conn->prepare("
        UPDATE purch_request_tb
        SET
            status = ?,
            purchaser_id = ?
        WHERE request_id = ?
    ");

    if (!$update) {
        throw new Exception(
            'Update prepare failed: ' .
            $conn->error
        );
    }

    $update->bind_param(
        "sii",
        $status,
        $purchaser_id,
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

        if (!$purchaserQuery) {
            throw new Exception(
                'Purchaser query failed: ' .
                $conn->error
            );
        }

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
    // SUCCESS
    // =====================================================

    $response['success'] = true;

    $response['message'] =
        'Request updated successfully.';

    $response['request_id'] =
        $request_id;

    $response['status'] =
        $status;

    $response['purchaser_id'] =
        $purchaser_id;

    $response['purchaser_name'] =
        $purchaser_name;


} catch (Throwable $e) {

    http_response_code(400);

    $response['success'] = false;

    $response['message'] =
        $e->getMessage();
}


// =========================================================
// RETURN JSON
// =========================================================

echo json_encode($response);

$conn->close();