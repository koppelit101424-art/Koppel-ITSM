<?php

ob_start();

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';

header('Content-Type: application/json; charset=utf-8');

$response = [
    'success' => false,
    'message' => ''
];

function returnJson($response, $statusCode = 200)
{
    // Remove anything accidentally output by included PHP files
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
    // CHECK LOGIN
    // =====================================================

    if (!isset($_SESSION['user_id'])) {

        returnJson([
            'success' => false,
            'message' => 'Unauthorized.'
        ], 401);
    }

    $userId = (int) $_SESSION['user_id'];


    // =====================================================
    // CHECK PURCHASING / ADMIN
    // =====================================================

    $userQuery = $conn->prepare("
        SELECT department
        FROM user_tb
        WHERE user_id = ?
        LIMIT 1
    ");

    if (!$userQuery) {
        throw new Exception(
            'Failed to check user: ' . $conn->error
        );
    }

    $userQuery->bind_param("i", $userId);
    $userQuery->execute();

    $userResult = $userQuery->get_result();
    $user = $userResult->fetch_assoc();

    $userQuery->close();

    if (!$user) {
        throw new Exception('User account not found.');
    }

    $department = trim(
        $user['department'] ?? ''
    );

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
    // GET POST DATA
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

    $poNo =
        trim($_POST['po_no'] ?? '');


    // =====================================================
    // VALIDATE REQUEST IDS
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
    // VALIDATE STATUS
    // =====================================================

    $allowedStatuses = [
        'pending',
        'checking requirements',
        'canvassing',
        'negotiation',
        'under discussion',
        'draft',
        'final',
        'end',
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
    // VALIDATE PRIORITY
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
    // VALIDATE PURCHASER
    // =====================================================

    if ($purchaserId !== '') {

        if (!ctype_digit($purchaserId)) {
            throw new Exception(
                'Invalid purchaser.'
            );
        }

        $purchaserId = (int) $purchaserId;

        // 1 = Unassigned
        if ($purchaserId > 1) {

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
                $purchaserId
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
    // REQUEST ID PLACEHOLDERS
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


    // =====================================================
    // PREPARE
    // =====================================================

    $stmt = $conn->prepare($sql);

    if (!$stmt) {

        throw new Exception(
            'Failed to prepare update: ' .
            $conn->error
        );
    }


    // =====================================================
    // BIND
    // =====================================================

    $stmt->bind_param(
        $types,
        ...$values
    );


    // =====================================================
    // EXECUTE
    // =====================================================

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


    // =====================================================
    // SUCCESS
    // =====================================================

    $response = [
        'success' => true,
        'message' =>
            count($requestIds) .
            ' request(s) updated successfully.',
        'affected_rows' => $affectedRows
    ];

    returnJson($response, 200);


} catch (Throwable $e) {

    returnJson([
        'success' => false,
        'message' => $e->getMessage()
    ], 400);
}