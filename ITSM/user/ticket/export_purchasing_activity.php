<?php

include '../../includes/auth.php';
include '../../includes/db.php';

header('Content-Type: application/json; charset=utf-8');


// =====================================================
// CHECK REQUEST IDS
// =====================================================

$requestIds = $_POST['request_ids'] ?? [];

if (!is_array($requestIds)) {

    echo json_encode([
        'success' => false,
        'message' => 'Invalid request IDs.'
    ]);

    exit;
}


// =====================================================
// CLEAN REQUEST IDS
// =====================================================

$requestIds = array_values(
    array_unique(
        array_filter(
            array_map(
                'intval',
                $requestIds
            ),
            function ($id) {
                return $id > 0;
            }
        )
    )
);


if (empty($requestIds)) {

    echo json_encode([
        'success' => false,
        'message' => 'No requests selected.'
    ]);

    exit;
}


// =====================================================
// BUILD IN CLAUSE SAFELY
// =====================================================

$placeholders =
    implode(
        ',',
        array_fill(
            0,
            count($requestIds),
            '?'
        )
    );


// =====================================================
// GET ACTIVITY
//
// CHANGE THESE COLUMN/TABLE NAMES IF YOUR
// HISTORY TABLE USES DIFFERENT NAMES.
// =====================================================

$sql = "
    SELECT

        h.history_id,

        h.request_id,

        r.lmr_no,

        r.po_no,

        u.fullname AS performed_by,

        r.company,

        r.department,

        r.item,

        h.action,

        h.old_value,

        h.new_value,

        h.comment,

        h.date_created

    FROM purch_request_history_tb h

    LEFT JOIN purch_request_tb r
        ON h.request_id = r.request_id

    LEFT JOIN user_tb u
        ON h.user_id = u.user_id

    WHERE h.request_id IN ($placeholders)

    ORDER BY
        h.request_id ASC,
        h.date_created ASC,
        h.history_id ASC
";

$stmt = $conn->prepare($sql);

if (!$stmt) {

    echo json_encode([
        'success' => false,
        'message' => 'Unable to prepare activity query.'
    ]);

    exit;
}


// =====================================================
// BIND DYNAMIC PARAMETERS
// =====================================================

$types =
    str_repeat(
        'i',
        count($requestIds)
    );

$stmt->bind_param(
    $types,
    ...$requestIds
);

$stmt->execute();

$result =
    $stmt->get_result();


// =====================================================
// CSV
// =====================================================

$rows = [];


// CSV HEADER

$rows[] = [

    'History ID',

    'Request ID',

    'LMR No.',

    'PO No.',

    'Company',

    'Department',

    'Item',

    'Performed By',

    'Activity',

    'Old Value',

    'New Value',

    'Comment',

    'Date & Time'

];


// =====================================================
// CSV ESCAPE
// =====================================================

function escapeCSV($value)
{

    if (
        $value === null ||
        $value === ''
    ) {
        return '""';
    }

    $value =
        (string)$value;

    $value =
        str_replace(
            '"',
            '""',
            $value
        );

    return '"' .
        $value .
        '"';
}


// =====================================================
// DATE FORMAT
// =====================================================

function formatActivityDate($value)
{

    if (!$value) {
        return '';
    }

    $timestamp =
        strtotime($value);

    if (!$timestamp) {
        return $value;
    }

    return date(
        'm-d-Y H:i:s',
        $timestamp
    );
}


// =====================================================
// BUILD CSV ROWS
// =====================================================

while (
    $row =
    $result->fetch_assoc()
) {

    $rows[] = [

        $row['history_id'],

        $row['request_id'],

        $row['lmr_no'],

        $row['po_no'],

        $row['company'],

        $row['department'],

        $row['item'],

        $row['performed_by'],

        $row['action'],

        $row['old_value'],

        $row['new_value'],

        $row['comment'],

        formatActivityDate(
            $row['date_created']
        )

    ];
}


$stmt->close();


// =====================================================
// CHECK ACTIVITY
// =====================================================

if (count($rows) === 1) {

    echo json_encode([
        'success' => false,
        'message' =>
            'No activity found for the filtered requests.'
    ]);

    exit;
}


// =====================================================
// BUILD CSV STRING
// =====================================================

$csv = '';

foreach ($rows as $row) {

    $csv .=
        implode(
            ',',
            array_map(
                'escapeCSV',
                $row
            )
        ) .
        "\r\n";
}


// =====================================================
// FILENAME
// =====================================================

$filename =
    'purchasing_activity_' .
    date('Y-m-d') .
    '.csv';


// =====================================================
// RESPONSE
// =====================================================

echo json_encode([

    'success' => true,

    'filename' => $filename,

    'csv' => $csv

]);

exit;
