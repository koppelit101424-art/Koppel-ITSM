<?php


require_once __DIR__ . '/../../includes/db.php';

require_once __DIR__ . '/../../includes/auth.php';


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
        r.description,
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

$currentOrderStatus = strtolower(
    trim((string)($request['order_status'] ?? ''))
);

/*
|--------------------------------------------------------------------------
| WORKING DAYS CALCULATOR
|--------------------------------------------------------------------------
| Excludes the starting date.
| Counts Monday through Friday only.
*/

function calculateWorkingDays($startDate, $endDate)
{
    if (empty($startDate) || empty($endDate)) {
        return '';
    }

    $startTimestamp = strtotime($startDate);
    $endTimestamp = strtotime($endDate);

    if ($startTimestamp === false || $endTimestamp === false) {
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

    return $days . ' working day' . ($days != 1 ? 's' : '');
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
    'description' => $request['description'] ?? '',
    'qty' => $quantity,
    'po_number' => $poNumber,
    'order_statuses' => [],
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

    $changes = json_decode($row['changes_json'], true);

    if (!is_array($changes)) {
        continue;
    }

    $statusDate = $row['date_created'];

    /*
    |--------------------------------------------------------------------------
    | REGULAR STATUS CHANGES
    |--------------------------------------------------------------------------
    */

    if (isset($changes['status'])) {

        $statusChange = $changes['status'];

        $newStatus = is_array($statusChange)
            ? ($statusChange['new'] ?? '')
            : $statusChange;

        $newStatus = strtolower(trim((string)$newStatus));

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

        if (
            $newStatus === 'pending' &&
            empty($exportData[$requestId]['pending_date'])
        ) {
            $exportData[$requestId]['pending_date'] = $statusDate;
        }

        if (in_array($newStatus, $statusColumns, true)) {

            if (!isset(
                $exportData[$requestId]['statuses'][$newStatus]
            )) {
                $exportData[$requestId]['statuses'][$newStatus] = [];
            }

            $exportData[$requestId]['statuses'][$newStatus][] = [
                'date' => $statusDate,
                'purchaser' => $purchaser
            ];

            $exportData[$requestId]['status_events'][] = [
                'status' => $newStatus,
                'date' => $statusDate,
                'history_id' => (int)$row['history_id'],
                'purchaser' => $purchaser
            ];
        }
    }

    /*
    |--------------------------------------------------------------------------
    | ORDER STATUS CHANGES
    |--------------------------------------------------------------------------
    */

    if (isset($changes['order_status'])) {

        $orderStatusChange = $changes['order_status'];

        $newOrderStatus = is_array($orderStatusChange)
            ? ($orderStatusChange['new'] ?? '')
            : $orderStatusChange;

        $newOrderStatus = strtolower(
            trim((string)$newOrderStatus)
        );

        $orderStatusColumns = [
            'order acknowledged',
            'goods received',
            'payment processing',
            'payment issued',
            'closed'
        ];

        if (in_array($newOrderStatus, $orderStatusColumns, true)) {

            if (!isset(
                $exportData[$requestId]['order_statuses'][$newOrderStatus]
            )) {
                $exportData[$requestId]['order_statuses'][$newOrderStatus] = [];
            }

            $exportData[$requestId]['order_statuses'][$newOrderStatus][] = [
                'date' => $statusDate,
                'purchaser' => $purchaser
            ];
        }
    }
}

$stmt->close();

/*
|--------------------------------------------------------------------------
| PREPARE CREATION DATE AND STATUS EVENTS
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
    | SORT REGULAR STATUS EVENTS CHRONOLOGICALLY
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
    | FIND FIRST FINAL PO APPROVED DATE
    |--------------------------------------------------------------------------
    */

    $finalPoApprovedDate = null;

    foreach ($data['status_events'] as $event) {

        if (
            $event['status'] === 'final po approved' &&
            $finalPoApprovedDate === null
        ) {
            $finalPoApprovedDate = $event['date'];
        }
    }

    /*
    |--------------------------------------------------------------------------
    | FIND FIRST CLOSED DATE
    |--------------------------------------------------------------------------
    | Check BOTH regular Status and Order Status history.
    | Use whichever applicable Closed event occurred first.
    |--------------------------------------------------------------------------
    */

    $closedDates = [];

    // Closed events from regular Status history.
    foreach (($data['statuses']['closed'] ?? []) as $event) {

        if (!empty($event['date'])) {
            $closedDates[] = $event['date'];
        }
    }

    // Closed events from Order Status history.
    foreach (($data['order_statuses']['closed'] ?? []) as $event) {

        if (!empty($event['date'])) {
            $closedDates[] = $event['date'];
        }
    }

    $closedDate = null;

    if (!empty($closedDates)) {

        usort($closedDates, function ($a, $b) {
            return strtotime($a) <=> strtotime($b);
        });

        $closedDate = $closedDates[0];
    }

    /*
    |--------------------------------------------------------------------------
    | CALCULATE COMPLETION TIMES
    |--------------------------------------------------------------------------
    |
    | 1. Creation to Final PO Approved
    | 2. Final PO Approved to Closed
    | 3. Creation to Closed
    |
    | Uses working days, Monday through Friday.
    | Excludes the starting date.
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
    'Description',
    'Qty',
    'Pending',
    'Checking Requirements',
    'Canvassing',
    'Negotiation',
    'Draft PO Under Discussion',
    'Draft PO Approved',
    'Final PO Approved',
    'PO Number',
    'Order Acknowledged',
    'Goods Received',
    'Payment Processing',
    'Payment Issued',
    'Closed',
    'Closed',
    'Creation to Final PO Approved',
    'Final PO Approved to Closed',
    'Creation to Closed'
];

fputcsv($output, $headers);

/*
|--------------------------------------------------------------------------
| REGULAR STATUS COLUMNS IN EXPORT ORDER
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
| ORDER STATUS COLUMNS IN EXPORT ORDER
|--------------------------------------------------------------------------
| Excludes N/A.
|--------------------------------------------------------------------------
*/

$orderStatusKeys = [
    'order acknowledged',
    'goods received',
    'payment processing',
    'payment issued',
    'closed'
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
        $data['description'],
        $data['qty']
    ];

    /*
    |--------------------------------------------------------------------------
    | REGULAR STATUS DATE COLUMNS
    |--------------------------------------------------------------------------
    */

    foreach ($exportStatusKeys as $statusKey) {

        $occurrences = $data['statuses'][$statusKey] ?? [];
        $dates = [];

        foreach ($occurrences as $event) {

            $formattedDate = formatExportDate($event['date']);

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
    | ORDER STATUS DATE COLUMNS
    |--------------------------------------------------------------------------
    */

    foreach ($orderStatusKeys as $orderStatusKey) {

        $orderDates = [];

        $occurrences =
            $data['order_statuses'][$orderStatusKey] ?? [];

        foreach ($occurrences as $event) {

            $formattedDate = formatExportDate($event['date']);

            if ($formattedDate !== '') {
                $orderDates[] = $formattedDate;
            }
        }

        $rowData[] = implode("\n", $orderDates);
    }

    /*
    |--------------------------------------------------------------------------
    | REGULAR STATUS CLOSED DATE COLUMN
    |--------------------------------------------------------------------------
    | This is separate from the Order Status Closed column.
    |--------------------------------------------------------------------------
    */

    $closedOccurrences = $data['statuses']['closed'] ?? [];
    $closedStatusDates = [];

    foreach ($closedOccurrences as $event) {

        $formattedDate = formatExportDate($event['date']);

        if ($formattedDate !== '') {
            $closedStatusDates[] = $formattedDate;
        }
    }

    $rowData[] = implode("\n", $closedStatusDates);

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