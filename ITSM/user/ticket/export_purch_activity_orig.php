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
| GET LMR INFORMATION
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        request_id,
        lmr_no,
        item,
        date_created
    FROM purch_request_tb
    WHERE request_id = ?
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

$lmr_no = trim($request['lmr_no'] ?? '');
$creationDate = $request['date_created'] ?? '';

/*
|--------------------------------------------------------------------------
| GET ACTIVITY LOGS
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
        u.fullname,
        r.item,
        r.lmr_no
    FROM purch_request_history_tb h
    LEFT JOIN user_tb u
        ON h.changed_by = u.user_id
    LEFT JOIN purch_request_tb r
        ON h.request_id = r.request_id
    WHERE r.lmr_no = ?
    ORDER BY
        h.date_created ASC,
        h.history_id ASC
");

if (!$stmt) {
    die('Activity query failed: ' . $conn->error);
}

$stmt->bind_param("s", $lmr_no);
$stmt->execute();

$result = $stmt->get_result();

/*
|--------------------------------------------------------------------------
| STATUS COLUMNS
|--------------------------------------------------------------------------
*/

$statusColumns = [
    'pending'                   => 'Pending',
    'checking requirements'     => 'Checking Requirements',
    'canvassing'                => 'Canvassing',
    'negotiation'               => 'Negotiation',
    'draft po under discussion' => 'Draft PO Under Discussion',
    'draft po approved'         => 'Draft PO Approved',
    'final po approved'         => 'Final PO Approved',
    'rejected'                  => 'Rejected',
    'closed'                    => 'Closed'
];

/*
|--------------------------------------------------------------------------
| WORKING DAYS CALCULATOR
|--------------------------------------------------------------------------
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
        $dayOfWeek = (int)$current->format('N');

        if ($dayOfWeek <= 5) {
            $workingDays++;
        }

        $current->modify('+1 day');
    }

    return $workingDays;
}

/*
|--------------------------------------------------------------------------
| PREPARE EXPORT DATA
|--------------------------------------------------------------------------
*/

$exportData = [];

/*
|--------------------------------------------------------------------------
| INITIALIZE REQUEST
|--------------------------------------------------------------------------
|
| These are export fields, not database column names.
| Fields without a source in the existing code remain blank.
|
*/

$exportData[$request_id] = [
    'request_id' => $request_id,
    'requestor' => '',
    'changed_by' => [],
    'department' => '',
    'lmr_no' => $lmr_no,
    'category' => '',
    'creation_date' => $creationDate,
    'item_code' => '',
    'item' => $request['item'] ?? '',
    'qty' => '',
    'po_number' => '',
    'goods_received_date' => '',
    'statuses' => [],
    'status_events' => []
];

/*
|--------------------------------------------------------------------------
| PROCESS ACTIVITY LOGS
|--------------------------------------------------------------------------
*/

while ($row = $result->fetch_assoc()) {

    $requestId = (int)$row['request_id'];

    if (!isset($exportData[$requestId])) {
        continue;
    }

    /*
    |--------------------------------------------------------------------------
    | CHANGED BY
    |--------------------------------------------------------------------------
    */

    $changedBy = trim($row['fullname'] ?? '');

    if ($changedBy === '') {
        $changedBy = 'Unknown User';
    }

    if (!in_array(
        $changedBy,
        $exportData[$requestId]['changed_by'],
        true
    )) {
        $exportData[$requestId]['changed_by'][] = $changedBy;
    }

    /*
    |--------------------------------------------------------------------------
    | GET CHANGES JSON
    |--------------------------------------------------------------------------
    */

    if (empty($row['changes_json'])) {
        continue;
    }

    $changes = json_decode($row['changes_json'], true);

    if (!is_array($changes) || !isset($changes['status'])) {
        continue;
    }

    /*
    |--------------------------------------------------------------------------
    | GET NEW STATUS
    |--------------------------------------------------------------------------
    */

    $statusChange = $changes['status'];

    if (is_array($statusChange)) {
        $newStatus = $statusChange['new'] ?? '';
    } else {
        $newStatus = $statusChange;
    }

    $newStatus = strtolower(trim((string)$newStatus));

    if (!isset($statusColumns[$newStatus])) {
        continue;
    }

    $statusDate = $row['date_created'];

    /*
    |--------------------------------------------------------------------------
    | SAVE ALL STATUS OCCURRENCES
    |--------------------------------------------------------------------------
    */

    if (!isset(
        $exportData[$requestId]['statuses'][$newStatus]
    )) {
        $exportData[$requestId]['statuses'][$newStatus] = [];
    }

    $exportData[$requestId]['statuses'][$newStatus][] = [
        'date' => $statusDate,
        'changed_by' => $changedBy
    ];

    /*
    |--------------------------------------------------------------------------
    | SAVE STATUS EVENT FOR COMPLETION CALCULATIONS
    |--------------------------------------------------------------------------
    */

    $exportData[$requestId]['status_events'][] = [
        'status' => $newStatus,
        'date' => $statusDate,
        'history_id' => (int)$row['history_id'],
        'changed_by' => $changedBy
    ];
}

/*
|--------------------------------------------------------------------------
| SORT EVENTS CHRONOLOGICALLY
|--------------------------------------------------------------------------
*/

foreach ($exportData as &$data) {
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
}
unset($data);

/*
|--------------------------------------------------------------------------
| CALCULATE COMPLETION TIMES
|--------------------------------------------------------------------------
*/

foreach ($exportData as &$data) {

    $finalPoApprovedDate = null;
    $closedDate = null;

    foreach ($data['status_events'] as $event) {

        if (
            $event['status'] === 'final po approved'
            && $finalPoApprovedDate === null
        ) {
            $finalPoApprovedDate = $event['date'];
        }

        if (
            $event['status'] === 'closed'
            && $closedDate === null
        ) {
            $closedDate = $event['date'];
        }
    }

    /*
    |--------------------------------------------------------------------------
    | CREATION TO FINAL PO APPROVED
    |--------------------------------------------------------------------------
    */

    $data['creation_to_po_approved'] =
        $finalPoApprovedDate !== null
            ? calculateWorkingDays(
                $data['creation_date'],
                $finalPoApprovedDate
            )
            : '';

    /*
    |--------------------------------------------------------------------------
    | FINAL PO APPROVED TO CLOSED
    |--------------------------------------------------------------------------
    */

    $data['po_approved_to_closed'] =
        (
            $finalPoApprovedDate !== null
            && $closedDate !== null
        )
            ? calculateWorkingDays(
                $finalPoApprovedDate,
                $closedDate
            )
            : '';

    /*
    |--------------------------------------------------------------------------
    | CREATION TO CLOSED
    |--------------------------------------------------------------------------
    */

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

$safeLmr = preg_replace('/[^A-Za-z0-9_-]/', '_', $lmr_no);

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

fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

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
|
| These labels are only for the exported Excel/CSV file.
| They do not need to match database column names.
|
*/

$headers = [
    'Request ID',
    'Requestor',
    'Changed by',
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
| STATUS COLUMNS IN THE REQUIRED EXPORT ORDER
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
| FORMAT WORKING DAYS
|--------------------------------------------------------------------------
*/

function formatWorkingDays($days)
{
    if ($days === '') {
        return '';
    }

    return $days . ' working day' . ($days != 1 ? 's' : '');
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
| WRITE EXPORT DATA
|--------------------------------------------------------------------------
*/

foreach ($exportData as $data) {

    /*
    |--------------------------------------------------------------------------
    | BASIC INFORMATION
    |--------------------------------------------------------------------------
    |
    | Unknown fields remain blank because no database source was
    | specified for them in the original code.
    |
    */

    $rowData = [
        $data['request_id'],                         // Request ID
        $data['requestor'],                          // Requestor
        implode(' / ', $data['changed_by']),         // Changed by
        $data['department'],                         // Department
        $data['lmr_no'],                             // LMR#
        $data['category'],                           // Category
        formatExportDate(
            $data['creation_date'],
            'm-d-Y'
        ),                                           // Date Created
        $data['item_code'] !== ''
            ? $data['item_code']
            : $data['item'],                         // Item Code/item code
        $data['qty']                                 // Qty
    ];

    /*
    |--------------------------------------------------------------------------
    | STATUS COLUMNS
    |--------------------------------------------------------------------------
    |
    | All occurrences of each status are written into one cell,
    | separated by line breaks.
    |
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
    | GOODS RECEIVED DATE
    |--------------------------------------------------------------------------
    */

    $rowData[] = formatExportDate(
        $data['goods_received_date'],
        'm-d-Y'
    );

    /*
    |--------------------------------------------------------------------------
    | CLOSED
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
    | WRITE ROW
    |--------------------------------------------------------------------------
    */

    fputcsv($output, $rowData);
}

/*
|--------------------------------------------------------------------------
| CLOSE
|--------------------------------------------------------------------------
*/

fclose($output);

$stmt->close();
$conn->close();

exit;