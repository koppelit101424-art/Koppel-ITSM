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
| GET LMR NUMBER
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT lmr_no
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
| CSV FILENAME
|--------------------------------------------------------------------------
*/

$safeLmr = preg_replace(
    '/[^A-Za-z0-9_-]/',
    '_',
    $lmr_no
);

$filename =
    'Purchasing_Activity_' .
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
| OPEN OUTPUT
|--------------------------------------------------------------------------
*/

$output = fopen('php://output', 'w');


/*
|--------------------------------------------------------------------------
| UTF-8 BOM
|--------------------------------------------------------------------------
|
| This helps Microsoft Excel correctly recognize UTF-8.
|
*/

fprintf(
    $output,
    chr(0xEF) . chr(0xBB) . chr(0xBF)
);


/*
|--------------------------------------------------------------------------
| CSV TITLE
|--------------------------------------------------------------------------
*/

fputcsv($output, [
    'PURCHASING REQUEST ACTIVITY REPORT'
]);

fputcsv($output, [
    'LMR No.',
    $lmr_no
]);

fputcsv($output, [
    'Generated Date',
    date('Y-m-d H:i:s')
]);

fputcsv($output, []);


/*
|--------------------------------------------------------------------------
| CSV COLUMN HEADERS
|--------------------------------------------------------------------------
*/

fputcsv($output, [
    'Activity ID',
    'Request ID',
    'LMR No.',
    'Item',
    'Changed By',
    'Date / Time',
    'Changes',
    'Comment'
]);


/*
|--------------------------------------------------------------------------
| FORMAT CHANGE DATA
|--------------------------------------------------------------------------
*/

while ($row = $result->fetch_assoc()) {

    $changesText = '';

    if (!empty($row['changes_json'])) {

        $changes = json_decode(
            $row['changes_json'],
            true
        );

        if (is_array($changes)) {

            $changeLines = [];

            foreach ($changes as $field => $change) {

                /*
                |--------------------------------------------------------------------------
                | FRIENDLY FIELD NAME
                |--------------------------------------------------------------------------
                */

                $fieldLabel = match ($field) {

                    'status' =>
                        'Status',

                    'purchaser' =>
                        'Assigned Purchaser',

                    'priority' =>
                        'Priority',

                    'category' =>
                        'Category',

                    'order_status' =>
                        'Order Status',

                    'po_no' =>
                        'PO Number',

                    default =>
                        ucwords(
                            str_replace(
                                '_',
                                ' ',
                                $field
                            )
                        )
                };


                /*
                |--------------------------------------------------------------------------
                | OLD / NEW VALUES
                |--------------------------------------------------------------------------
                */

                $oldValue =
                    $change['old'] ?? '';

                $newValue =
                    $change['new'] ?? '';


                /*
                |--------------------------------------------------------------------------
                | HANDLE ARRAYS / OBJECTS
                |--------------------------------------------------------------------------
                */

                if (is_array($oldValue)) {
                    $oldValue = json_encode(
                        $oldValue,
                        JSON_UNESCAPED_UNICODE
                    );
                }

                if (is_array($newValue)) {
                    $newValue = json_encode(
                        $newValue,
                        JSON_UNESCAPED_UNICODE
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | BUILD CHANGE TEXT
                |--------------------------------------------------------------------------
                */

                $changeLines[] =
                    $fieldLabel .
                    ': ' .
                    ($oldValue !== ''
                        ? $oldValue
                        : 'N/A'
                    ) .
                    ' -> ' .
                    ($newValue !== ''
                        ? $newValue
                        : 'N/A'
                    );
            }

            $changesText = implode(
                ' | ',
                $changeLines
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | COMMENT
    |--------------------------------------------------------------------------
    */

    $comment = trim(
        $row['comment'] ?? ''
    );


    /*
    |--------------------------------------------------------------------------
    | WRITE CSV ROW
    |--------------------------------------------------------------------------
    */

    fputcsv($output, [

        $row['history_id'],

        $row['request_id'],

        $row['lmr_no'],

        $row['item'],

        $row['fullname']
            ?: 'Unknown User',

        $row['date_created'],

        $changesText,

        $comment

    ]);
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
