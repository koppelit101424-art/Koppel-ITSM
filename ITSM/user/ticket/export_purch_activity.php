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

$stmt->bind_param("i", $request_id);
$stmt->execute();

$result = $stmt->get_result();
$request = $result->fetch_assoc();

$stmt->close();


if (!$request) {
    die('Request not found.');
}


$lmr_no = trim($request['lmr_no']);

$creationDate = $request['date_created'];


/*
|--------------------------------------------------------------------------
| GET ACTIVITY LOGS
|--------------------------------------------------------------------------
|
| Get all activity logs belonging to this LMR.
|
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
    die(
        'Activity query failed: ' .
        $conn->error
    );
}

$stmt->bind_param("s", $lmr_no);

$stmt->execute();

$result = $stmt->get_result();


/*
|--------------------------------------------------------------------------
| STATUS COLUMNS
|--------------------------------------------------------------------------
|
| These are the ONLY status columns that will appear in Excel.
|
*/

$statusColumns = [

    'pending' =>
        'Pending',

    'checking requirements' =>
        'Checking Requirements',

    'canvassing' =>
        'Canvassing',

    'negotiation' =>
        'Negotiation',

    'draft po under discussion' =>
        'Draft PO Under Discussion',

    'draft po approved' =>
        'Draft PO Approved',

    'final po approved' =>
        'Final PO Approved',

    'rejected' =>
        'Rejected',

    'closed' =>
        'Closed'

];


/*
|--------------------------------------------------------------------------
| WORKING DAYS CALCULATOR
|--------------------------------------------------------------------------
|
| Does NOT count the starting date.
|
| Example:
|
| Monday Oct 5 -> Thursday Oct 8
|
| Oct 5 = starting date, NOT counted
| Oct 6 = 1
| Oct 7 = 2
| Oct 8 = 3
|
| Saturday and Sunday are excluded.
|
*/

function calculateWorkingDays(
    $startDate,
    $endDate
) {

    if (
        empty($startDate) ||
        empty($endDate)
    ) {
        return '';
    }


    /*
    |--------------------------------------------------------------------------
    | CONVERT TO DATE ONLY
    |--------------------------------------------------------------------------
    */

    $start = new DateTime(
        date(
            'Y-m-d',
            strtotime($startDate)
        )
    );

    $end = new DateTime(
        date(
            'Y-m-d',
            strtotime($endDate)
        )
    );


    /*
    |--------------------------------------------------------------------------
    | SAME DATE OR END BEFORE START
    |--------------------------------------------------------------------------
    */

    if ($end <= $start) {
        return 0;
    }


    /*
    |--------------------------------------------------------------------------
    | START FROM THE NEXT DAY
    |--------------------------------------------------------------------------
    */

    $current = clone $start;

    $current->modify('+1 day');


    $workingDays = 0;


    /*
    |--------------------------------------------------------------------------
    | COUNT MONDAY - FRIDAY
    |--------------------------------------------------------------------------
    */

    while ($current <= $end) {

        $dayOfWeek =
            (int)$current->format('N');


        /*
        | Monday    = 1
        | Tuesday   = 2
        | Wednesday = 3
        | Thursday  = 4
        | Friday    = 5
        | Saturday  = 6
        | Sunday    = 7
        */

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
| PROCESS ACTIVITY LOGS
|--------------------------------------------------------------------------
*/

while ($row = $result->fetch_assoc()) {

    $requestId =
        (int)$row['request_id'];


    /*
    |--------------------------------------------------------------------------
    | INITIALIZE REQUEST
    |--------------------------------------------------------------------------
    */

    if (!isset(
        $exportData[$requestId]
    )) {

        $exportData[$requestId] = [

            'request_id' =>
                $requestId,

            'lmr_no' =>
                $row['lmr_no'],

            'item' =>
                $row['item'],

            'creation_date' =>
                $creationDate,

            'changed_by' =>
                [],

            'statuses' =>
                [],

            'status_events' =>
                []

        ];
    }


    /*
    |--------------------------------------------------------------------------
    | CHANGED BY
    |--------------------------------------------------------------------------
    */

    $changedBy =
        trim(
            $row['fullname'] ?? ''
        );


    if ($changedBy === '') {

        $changedBy =
            'Unknown User';
    }


    /*
    |--------------------------------------------------------------------------
    | STORE UNIQUE USERS
    |--------------------------------------------------------------------------
    */

    if (!in_array(
        $changedBy,
        $exportData[$requestId]['changed_by'],
        true
    )) {

        $exportData[$requestId]['changed_by'][] =
            $changedBy;
    }


    /*
    |--------------------------------------------------------------------------
    | GET CHANGES JSON
    |--------------------------------------------------------------------------
    */

    if (
        empty(
            $row['changes_json']
        )
    ) {

        continue;
    }


    $changes =
        json_decode(
            $row['changes_json'],
            true
        );


    if (!is_array($changes)) {

        continue;
    }


    /*
    |--------------------------------------------------------------------------
    | CHECK STATUS CHANGE
    |--------------------------------------------------------------------------
    */

    if (
        !isset(
            $changes['status']
        )
    ) {

        continue;
    }


    $statusChange =
        $changes['status'];


    /*
    |--------------------------------------------------------------------------
    | GET NEW STATUS
    |--------------------------------------------------------------------------
    */

    $newStatus = '';


    if (is_array($statusChange)) {

        $newStatus =
            $statusChange['new']
            ?? '';

    } else {

        $newStatus =
            $statusChange;
    }


    /*
    |--------------------------------------------------------------------------
    | NORMALIZE STATUS
    |--------------------------------------------------------------------------
    */

    $newStatus =
        strtolower(
            trim(
                (string)$newStatus
            )
        );


    /*
    |--------------------------------------------------------------------------
    | IGNORE UNKNOWN STATUS
    |--------------------------------------------------------------------------
    */

    if (
        !isset(
            $statusColumns[$newStatus]
        )
    ) {

        continue;
    }


    /*
    |--------------------------------------------------------------------------
    | STATUS DATE
    |--------------------------------------------------------------------------
    */

    $statusDate =
        $row['date_created'];


    /*
    |--------------------------------------------------------------------------
    | INITIALIZE STATUS ARRAY
    |--------------------------------------------------------------------------
    */

    if (
        !isset(
            $exportData[$requestId]['statuses'][$newStatus]
        )
    ) {

        $exportData[$requestId]['statuses'][$newStatus] = [];
    }


    /*
    |--------------------------------------------------------------------------
    | SAVE STATUS OCCURRENCE
    |--------------------------------------------------------------------------
    |
    | IMPORTANT:
    |
    | We keep EVERY occurrence.
    |
    | Example:
    |
    | Pending:
    |
    | 10-05-2026 08:30
    | 10-08-2026 10:15
    | 10-12-2026 09:20
    |
    | All of them will appear inside the SAME
    | Pending column in Excel.
    |
    |--------------------------------------------------------------------------
    */

    $exportData[$requestId]['statuses'][$newStatus][] = [

        'date' =>
            $statusDate,

        'changed_by' =>
            $changedBy

    ];


    /*
    |--------------------------------------------------------------------------
    | SAVE STATUS EVENT
    |--------------------------------------------------------------------------
    |
    | Used for calculating completion times.
    |
    |--------------------------------------------------------------------------
    */

    $exportData[$requestId]['status_events'][] = [

        'status' =>
            $newStatus,

        'date' =>
            $statusDate,

        'changed_by' =>
            $changedBy

    ];
}


/*
|--------------------------------------------------------------------------
| CREATE EXACTLY ONE COLUMN FOR EACH STATUS
|--------------------------------------------------------------------------
|
| There will NEVER be:
|
| Pending 2
| Pending 3
| Negotiation 2
| Canvassing 2
|
| Only:
|
| Pending
| Checking Requirements
| Canvassing
| Negotiation
| ...
|
|--------------------------------------------------------------------------
*/

$dynamicStatusColumns = [];


foreach (
    $statusColumns
    as $statusKey => $statusLabel
) {

    $dynamicStatusColumns[] = [

        'key' =>
            $statusKey,

        'label' =>
            $statusLabel

    ];
}


/*
|--------------------------------------------------------------------------
| CALCULATE COMPLETION TIMES
|--------------------------------------------------------------------------
*/

foreach (
    $exportData
    as $requestId => &$data
) {

    $finalPoApprovedDate = null;

    $closedDate = null;


    /*
    |--------------------------------------------------------------------------
    | FIND FIRST FINAL PO APPROVED
    |--------------------------------------------------------------------------
    */

    foreach (
        $data['status_events']
        as $event
    ) {

        if (
            $event['status'] ===
                'final po approved'
            &&
            $finalPoApprovedDate === null
        ) {

            $finalPoApprovedDate =
                $event['date'];
        }
    }


    /*
    |--------------------------------------------------------------------------
    | FIND FIRST CLOSED
    |--------------------------------------------------------------------------
    */

    foreach (
        $data['status_events']
        as $event
    ) {

        if (
            $event['status'] ===
                'closed'
            &&
            $closedDate === null
        ) {

            $closedDate =
                $event['date'];
        }
    }


    /*
    |--------------------------------------------------------------------------
    | CREATION → FINAL PO APPROVED
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
    | FINAL PO APPROVED → CLOSED
    |--------------------------------------------------------------------------
    */

    $data['po_approved_to_closed'] =

        (
            $finalPoApprovedDate !== null
            &&
            $closedDate !== null
        )

            ? calculateWorkingDays(
                $finalPoApprovedDate,
                $closedDate
            )

            : '';


    /*
    |--------------------------------------------------------------------------
    | CREATION → CLOSED
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

$safeLmr =
    preg_replace(
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

header(
    'Content-Type: text/csv; charset=UTF-8'
);

header(
    'Content-Disposition: attachment; filename="' .
    $filename .
    '"'
);

header(
    'Pragma: no-cache'
);

header(
    'Expires: 0'
);


/*
|--------------------------------------------------------------------------
| OPEN CSV
|--------------------------------------------------------------------------
*/

$output =
    fopen(
        'php://output',
        'w'
    );


/*
|--------------------------------------------------------------------------
| UTF-8 BOM
|--------------------------------------------------------------------------
|
| Allows Excel to correctly detect UTF-8.
|
*/

fprintf(
    $output,
    chr(0xEF) .
    chr(0xBB) .
    chr(0xBF)
);


/*
|--------------------------------------------------------------------------
| REPORT TITLE
|--------------------------------------------------------------------------
*/

fputcsv(
    $output,
    [
        'PURCHASING REQUEST STATUS HISTORY'
    ]
);


fputcsv(
    $output,
    [
        'LMR No.',
        $lmr_no
    ]
);


fputcsv(
    $output,
    [
        'Generated Date',
        date(
            'Y-m-d H:i:s'
        )
    ]
);


fputcsv(
    $output,
    []
);


/*
|--------------------------------------------------------------------------
| COLUMN HEADERS
|--------------------------------------------------------------------------
*/

$headers = [

    'Request ID',

    'LMR No.',

    'Item',

    'Changed By'

];


/*
|--------------------------------------------------------------------------
| ADD ONE COLUMN PER STATUS
|--------------------------------------------------------------------------
*/

foreach (
    $dynamicStatusColumns
    as $column
) {

    $headers[] =
        $column['label'];
}


/*
|--------------------------------------------------------------------------
| ADD COMPLETION COLUMNS
|--------------------------------------------------------------------------
*/

$headers[] =
    'Creation to Final PO Approved';

$headers[] =
    'Final PO Approved to Closed';

$headers[] =
    'Creation to Closed';


/*
|--------------------------------------------------------------------------
| WRITE HEADERS
|--------------------------------------------------------------------------
*/

fputcsv(
    $output,
    $headers
);


/*
|--------------------------------------------------------------------------
| WRITE DATA
|--------------------------------------------------------------------------
*/

foreach (
    $exportData
    as $data
) {


    /*
    |--------------------------------------------------------------------------
    | BASIC INFORMATION
    |--------------------------------------------------------------------------
    */

    $rowData = [

        $data['request_id'],

        $data['lmr_no'],

        $data['item'],

        implode(
            ' / ',
            $data['changed_by']
        )

    ];


    /*
    |--------------------------------------------------------------------------
    | STATUS COLUMNS
    |--------------------------------------------------------------------------
    |
    | Each status gets exactly ONE column.
    |
    | If the status happened multiple times,
    | every date is placed inside the same Excel cell.
    |
    |--------------------------------------------------------------------------
    */

    foreach (
        $dynamicStatusColumns
        as $column
    ) {

        $statusKey =
            $column['key'];


        $occurrences =
            $data['statuses'][$statusKey]
            ?? [];


        $dates = [];


        /*
        |--------------------------------------------------------------------------
        | COLLECT ALL STATUS DATES
        |--------------------------------------------------------------------------
        */

        foreach (
            $occurrences
            as $event
        ) {

            $dates[] =
                date(
                    'm-d-Y H:i',
                    strtotime(
                        $event['date']
                    )
                );
        }


        /*
        |--------------------------------------------------------------------------
        | PUT ALL DATES IN ONE CELL
        |--------------------------------------------------------------------------
        |
        | Example:
        |
        | 10-05-2026 08:30
        | 10-08-2026 10:15
        | 10-12-2026 09:20
        |
        |--------------------------------------------------------------------------
        */

        $rowData[] =
            implode(
                "\n",
                $dates
            );
    }


    /*
    |--------------------------------------------------------------------------
    | CREATION → FINAL PO APPROVED
    |--------------------------------------------------------------------------
    */

    $rowData[] =

        $data['creation_to_po_approved'] !== ''

            ? $data['creation_to_po_approved'] .
                ' working day' .
                (
                    $data['creation_to_po_approved'] != 1
                        ? 's'
                        : ''
                )

            : '';


    /*
    |--------------------------------------------------------------------------
    | FINAL PO APPROVED → CLOSED
    |--------------------------------------------------------------------------
    */

    $rowData[] =

        $data['po_approved_to_closed'] !== ''

            ? $data['po_approved_to_closed'] .
                ' working day' .
                (
                    $data['po_approved_to_closed'] != 1
                        ? 's'
                        : ''
                )

            : '';


    /*
    |--------------------------------------------------------------------------
    | CREATION → CLOSED
    |--------------------------------------------------------------------------
    */

    $rowData[] =

        $data['creation_to_closed'] !== ''

            ? $data['creation_to_closed'] .
                ' working day' .
                (
                    $data['creation_to_closed'] != 1
                        ? 's'
                        : ''
                )

            : '';


    /*
    |--------------------------------------------------------------------------
    | WRITE ROW
    |--------------------------------------------------------------------------
    */

    fputcsv(
        $output,
        $rowData
    );
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