<?php

include '../includes/auth.php';
include '../includes/db.php';

/*
|--------------------------------------------------------------------------
| GET REQUEST ID
|--------------------------------------------------------------------------
*/

$request_id = (int)($_GET['request_id'] ?? 0);

if ($request_id <= 0) {
    die('Invalid request ID.');
}

/*
|--------------------------------------------------------------------------
| GET REQUEST INFORMATION
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        r.request_id,
        r.requestor AS requestor_name,
        r.department,
        r.lmr_no,
        r.item,
        r.quantity,
        r.date_created,
        r.po_no,
        r.category_id,
        r.order_status,
        u.fullname AS fullname,
        u.department AS user_department,
        c.category_name
    FROM purch_request_tb r
    LEFT JOIN user_tb u
        ON r.requestor = u.user_id
    LEFT JOIN request_category_tb c
        ON r.category_id = c.category_id
    WHERE r.request_id = ?
    LIMIT 1
");

if (!$stmt) {
    die('Request query failed: ' . $conn->error);
}

$stmt->bind_param("i", $request_id);
$stmt->execute();

$result = $stmt->get_result();
$request = $result->fetch_assoc();

$stmt->close();

if (!$request) {
    die('Request not found.');
}

/*
|--------------------------------------------------------------------------
| BASIC REQUEST DETAILS
|--------------------------------------------------------------------------
*/

$lmr_no = trim($request['lmr_no'] ?? '');
$creationDate = $request['date_created'] ?? '';

$requestorName = trim($request['requestor_name'] ?? '');

$department = trim($request['user_department'] ?? '');

if ($department === '') {
    $department = trim($request['department'] ?? '');
}

$categoryName = trim($request['category_name'] ?? '');

$poNumber = trim((string)($request['po_no'] ?? ''));
$quantity = $request['quantity'] ?? '';

/*
|--------------------------------------------------------------------------
| CURRENT ORDER STATUS
|--------------------------------------------------------------------------
*/

$currentOrderStatus = strtolower(
    trim((string)($request['order_status'] ?? ''))
);

/*
|--------------------------------------------------------------------------
| WORKING DAYS CALCULATOR
|--------------------------------------------------------------------------
| Excludes the starting date.
| Counts Monday through Friday only.
|--------------------------------------------------------------------------
*/

function calculateWorkingDays($startDate, $endDate)
{
    if (empty($startDate) || empty($endDate)) {
        return '';
    }

    $startTimestamp = strtotime($startDate);
    $endTimestamp = strtotime($endDate);

    if (
        $startTimestamp === false ||
        $endTimestamp === false
    ) {
        return '';
    }

    $start = new DateTime(date('Y-m-d', $startTimestamp));
    $end = new DateTime(date('Y-m-d', $endTimestamp));

    if ($end <= $start) {
        return 0;
    }

    $current = clone $start;
    $current->modify('+1 day');

    $workingDays = 0;

    while ($current <= $end) {
        if ((int)$current->format('N') <= 5) {
            $workingDays++;
        }

        $current->modify('+1 day');
    }

    return $workingDays;
}

/*
|--------------------------------------------------------------------------
| FORMAT DATE
|--------------------------------------------------------------------------
*/

function formatExportDate($date, $format = 'm-d-Y H:i')
{
    if (empty($date)) {
        return '';
    }

    $timestamp = strtotime($date);

    if ($timestamp === false) {
        return '';
    }

    return date($format, $timestamp);
}

/*
|--------------------------------------------------------------------------
| FORMAT WORKING DAYS
|--------------------------------------------------------------------------
*/

function formatWorkingDays($days)
{
    if ($days === '' || $days === null) {
        return '';
    }

    return $days .
        ' working day' .
        ($days != 1 ? 's' : '');
}

/*
|--------------------------------------------------------------------------
| INITIALIZE EXPORT DATA
|--------------------------------------------------------------------------
*/

$exportData = [];

$exportData[$request_id] = [
    'request_id' => $request_id,
    'requestor' => $requestorName,
    'purchaser' => [],
    'department' => $department,
    'lmr_no' => $lmr_no,
    'category' => $categoryName,
    'creation_date' => $creationDate,
    'item_code' => '',
    'item' => $request['item'] ?? '',
    'qty' => $quantity,
    'po_number' => $poNumber,
    'goods_received_date' => '',
    'statuses' => [],
    'status_events' => []
];

/*
|--------------------------------------------------------------------------
| GET ACTIVITY HISTORY
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        h.history_id,
        h.request_id,
        h.changed_by,
        h.comment,
        h.changes_json,
        h.date_created,
        u.fullname AS purchaser_name
    FROM purch_request_history_tb h
    LEFT JOIN user_tb u
        ON h.changed_by = u.user_id
    WHERE h.request_id = ?
    ORDER BY
        h.date_created ASC,
        h.history_id ASC
");

if (!$stmt) {
    die('Activity query failed: ' . $conn->error);
}

$stmt->bind_param("i", $request_id);
$stmt->execute();

$result = $stmt->get_result();

/*
|--------------------------------------------------------------------------
| PROCESS ACTIVITY HISTORY
|--------------------------------------------------------------------------
*/

while ($row = $result->fetch_assoc()) {

    $requestId = (int)$row['request_id'];

    if (!isset($exportData[$requestId])) {
        continue;
    }

    /*
    |--------------------------------------------------------------------------
    | PURCHASER
    |--------------------------------------------------------------------------
    */

    $purchaser = trim($row['purchaser_name'] ?? '');

    if ($purchaser === '') {
        $purchaser = 'Unknown User';
    }

    if (!in_array(
        $purchaser,
        $exportData[$requestId]['purchaser'],
        true
    )) {
        $exportData[$requestId]['purchaser'][] = $purchaser;
    }

    /*
    |--------------------------------------------------------------------------
    | DECODE CHANGES JSON
    |--------------------------------------------------------------------------
    */

    if (empty($row['changes_json'])) {
        continue;
    }

    $changes = json_decode(
        $row['changes_json'],
        true
    );

    if (!is_array($changes)) {
        continue;
    }

    /*
    |--------------------------------------------------------------------------
    | GET STATUS CHANGE
    |--------------------------------------------------------------------------
    */

    if (!isset($changes['status'])) {
        continue;
    }

    $statusChange = $changes['status'];

    if (is_array($statusChange)) {
        $newStatus = $statusChange['new'] ?? '';
    } else {
        $newStatus = $statusChange;
    }

    $newStatus = strtolower(trim((string)$newStatus));

    $statusDate = $row['date_created'];

    /*
    |--------------------------------------------------------------------------
    | GET GOODS RECEIVED DATE FROM HISTORY
    |--------------------------------------------------------------------------
    */

    if ($newStatus === 'goods received') {
        $exportData[$requestId]['goods_received_date'] = $statusDate;
    }

    /*
    |--------------------------------------------------------------------------
    | SAVE PENDING DATE
    |--------------------------------------------------------------------------
    | The first Pending status date becomes the creation date.
    |--------------------------------------------------------------------------
    */

    if (
        $newStatus === 'pending' &&
        empty($exportData[$requestId]['pending_date'])
    ) {
        $exportData[$requestId]['pending_date'] = $statusDate;
    }

    /*
    |--------------------------------------------------------------------------
    | ACCEPT KNOWN STATUS VALUES
    |--------------------------------------------------------------------------
    */

    $statusColumns = [
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

    if (!in_array($newStatus, $statusColumns, true)) {
        continue;
    }

    /*
    |--------------------------------------------------------------------------
    | SAVE STATUS OCCURRENCE
    |--------------------------------------------------------------------------
    */

    if (!isset(
        $exportData[$requestId]['statuses'][$newStatus]
    )) {
        $exportData[$requestId]['statuses'][$newStatus] = [];
    }

    $exportData[$requestId]['statuses'][$newStatus][] = [
        'date' => $statusDate,
        'purchaser' => $purchaser
    ];

    /*
    |--------------------------------------------------------------------------
    | SAVE STATUS EVENT
    |--------------------------------------------------------------------------
    */

    $exportData[$requestId]['status_events'][] = [
        'status' => $newStatus,
        'date' => $statusDate,
        'history_id' => (int)$row['history_id'],
        'purchaser' => $purchaser
    ];
}

$stmt->close();


/*
|--------------------------------------------------------------------------
| USE ORDER_STATUS AS A FALLBACK
|--------------------------------------------------------------------------
| Use history dates when available.
| If the current order status matches but its history date is missing,
| leave the date blank rather than using the request creation date.
|--------------------------------------------------------------------------
*/

if (
    $currentOrderStatus === 'goods received' &&
    empty($exportData[$request_id]['goods_received_date'])
) {
    $exportData[$request_id]['goods_received_date'] = '';
}

if (
    $currentOrderStatus === 'closed' &&
    empty($exportData[$request_id]['statuses']['closed'])
) {
    $exportData[$request_id]['statuses']['closed'] = [];
}

/*
|--------------------------------------------------------------------------
| SET DATE CREATED EQUAL TO PENDING DATE
|--------------------------------------------------------------------------
| If a Pending event exists, use its earliest recorded date.
| Otherwise, retain purch_request_tb.date_created.
|--------------------------------------------------------------------------
*/

foreach ($exportData as &$data) {

    if (!empty($data['pending_date'])) {
        $data['creation_date'] = $data['pending_date'];
    } else {
        $data['pending_date'] = $data['creation_date'];
    }

    /*
    |--------------------------------------------------------------------------
    | SORT STATUS EVENTS
    |--------------------------------------------------------------------------
    */

    usort(
        $data['status_events'],
        function ($a, $b) {

            $comparison = strcmp($a['date'], $b['date']);

            if ($comparison !== 0) {
                return $comparison;
            }

            return $a['history_id'] <=> $b['history_id'];
        }
    );

    /*
    |--------------------------------------------------------------------------
    | ENSURE PENDING COLUMN MATCHES DATE CREATED
    |--------------------------------------------------------------------------
    */

    $data['statuses']['pending'] = [
        [
            'date' => $data['creation_date'],
            'purchaser' => ''
        ]
    ];

    /*
    |--------------------------------------------------------------------------
    | FIND COMPLETION DATES
    |--------------------------------------------------------------------------
    */

    $finalPoApprovedDate = null;
    $closedDate = null;

    foreach ($data['status_events'] as $event) {

        if (
            $event['status'] === 'final po approved' &&
            $finalPoApprovedDate === null
        ) {
            $finalPoApprovedDate = $event['date'];
        }

        if (
            $event['status'] === 'closed' &&
            $closedDate === null
        ) {
            $closedDate = $event['date'];
        }
    }

    /*
    |--------------------------------------------------------------------------
    | IF CLOSED EXISTS ONLY IN ORDER_STATUS
    |--------------------------------------------------------------------------
    */

    if (
        $closedDate === null &&
        $currentOrderStatus === 'closed' &&
        !empty($data['statuses']['closed'])
    ) {
        $closedDate = $data['statuses']['closed'][0]['date'];
    }

    /*
    |--------------------------------------------------------------------------
    | CALCULATE COMPLETION TIMES
    |--------------------------------------------------------------------------
    */

    $data['creation_to_po_approved'] =
        $finalPoApprovedDate !== null
            ? calculateWorkingDays(
                $data['creation_date'],
                $finalPoApprovedDate
            )
            : '';

    $data['po_approved_to_closed'] =
        (
            $finalPoApprovedDate !== null &&
            $closedDate !== null
        )
            ? calculateWorkingDays(
                $finalPoApprovedDate,
                $closedDate
            )
            : '';

    $data['creation_to_closed'] =
        $closedDate !== null
            ? calculateWorkingDays(
                $data['creation_date'],
                $closedDate
            )
            : '';
}

unset($data);

/*
|--------------------------------------------------------------------------
| CSV FILENAME
|--------------------------------------------------------------------------
*/

$safeLmr = preg_replace(
    '/[^A-Za-z0-9_-]/',
    '_',
    $lmr_no
);

$filename =
    'Purchasing_Status_History_' .
    $safeLmr .
    '_' .
    date('Y-m-d_H-i-s') .
    '.csv';

/*
|--------------------------------------------------------------------------
| CSV HEADERS
|--------------------------------------------------------------------------
*/

header('Content-Type: text/csv; charset=UTF-8');

header(
    'Content-Disposition: attachment; filename="' .
    $filename .
    '"'
);

header('Pragma: no-cache');
header('Expires: 0');

/*
|--------------------------------------------------------------------------
| OPEN CSV OUTPUT
|--------------------------------------------------------------------------
*/

$output = fopen('php://output', 'w');

/*
|--------------------------------------------------------------------------
| UTF-8 BOM FOR EXCEL
|--------------------------------------------------------------------------
*/

fprintf(
    $output,
    chr(0xEF) . chr(0xBB) . chr(0xBF)
);

/*
|--------------------------------------------------------------------------
| REPORT TITLE
|--------------------------------------------------------------------------
*/

fputcsv($output, ['PURCHASING REQUEST STATUS HISTORY']);
fputcsv($output, ['LMR#', $lmr_no]);
fputcsv($output, ['Generated Date', date('Y-m-d H:i:s')]);
fputcsv($output, []);

/*
|--------------------------------------------------------------------------
| EXPORT COLUMN HEADERS
|--------------------------------------------------------------------------
*/

$headers = [
    'Request ID',
    'Requestor',
    'Purchaser',
    'Department',
    'LMR#',
    'Category',
    'Date Created',
    'Item Code/item code',
    'Qty',
    'Pending',
    'Checking Requirements',
    'Canvassing',
    'Negotiation',
    'Draft PO Under Discussion',
    'Draft PO Approved',
    'Final PO Approved',
    'PO Number',
    'Goods received date',
    'Closed',
    'Creation to Final PO Approved',
    'Final PO Approved to Closed',
    'Creation to Closed'
];

fputcsv($output, $headers);

/*
|--------------------------------------------------------------------------
| STATUS COLUMNS IN EXPORT ORDER
|--------------------------------------------------------------------------
*/

$exportStatusKeys = [
    'pending',
    'checking requirements',
    'canvassing',
    'negotiation',
    'draft po under discussion',
    'draft po approved',
    'final po approved'
];

/*
|--------------------------------------------------------------------------
| WRITE EXPORT DATA
|--------------------------------------------------------------------------
*/

foreach ($exportData as $data) {

    /*
    |--------------------------------------------------------------------------
    | BASIC INFORMATION
    |--------------------------------------------------------------------------
    */

    $rowData = [
        $data['request_id'],
        $data['requestor'],
        implode(' / ', $data['purchaser']),
        $data['department'],
        $data['lmr_no'],
        $data['category'],
        formatExportDate($data['creation_date'], 'm-d-Y'),
        $data['item_code'] !== ''
            ? $data['item_code']
            : $data['item'],
        $data['qty']
    ];

    /*
    |--------------------------------------------------------------------------
    | STATUS DATE COLUMNS
    |--------------------------------------------------------------------------
    */

    foreach ($exportStatusKeys as $statusKey) {

        $occurrences = $data['statuses'][$statusKey] ?? [];
        $dates = [];

        foreach ($occurrences as $event) {

            $formattedDate = formatExportDate(
                $event['date']
            );

            if ($formattedDate !== '') {
                $dates[] = $formattedDate;
            }
        }

        $rowData[] = implode("\n", $dates);
    }

    /*
    |--------------------------------------------------------------------------
    | PO NUMBER
    |--------------------------------------------------------------------------
    */

    $rowData[] = $data['po_number'];

    /*
    |--------------------------------------------------------------------------
    | GOODS RECEIVED DATE
    |--------------------------------------------------------------------------
    */

    $rowData[] = formatExportDate(
        $data['goods_received_date'],
        'm-d-Y'
    );

    /*
    |--------------------------------------------------------------------------
    | CLOSED DATES
    |--------------------------------------------------------------------------
    */

    $closedOccurrences = $data['statuses']['closed'] ?? [];
    $closedDates = [];

    foreach ($closedOccurrences as $event) {

        $formattedDate = formatExportDate($event['date']);

        if ($formattedDate !== '') {
            $closedDates[] = $formattedDate;
        }
    }

    $rowData[] = implode("\n", $closedDates);

    /*
    |--------------------------------------------------------------------------
    | COMPLETION TIME COLUMNS
    |--------------------------------------------------------------------------
    */

    $rowData[] = formatWorkingDays(
        $data['creation_to_po_approved']
    );

    $rowData[] = formatWorkingDays(
        $data['po_approved_to_closed']
    );

    $rowData[] = formatWorkingDays(
        $data['creation_to_closed']
    );

    /*
    |--------------------------------------------------------------------------
    | WRITE CSV ROW
    |--------------------------------------------------------------------------
    */

    fputcsv($output, $rowData);
}

/*
|--------------------------------------------------------------------------
| CLOSE OUTPUT AND DATABASE
|--------------------------------------------------------------------------
*/

fclose($output);

$conn->close();

exit;