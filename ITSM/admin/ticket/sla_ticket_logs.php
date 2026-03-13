<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include __DIR__ . '/../../includes/auth.php';
include __DIR__ . '/../../includes/db.php';

/* ==============================
   PARAMETERS
============================== */
$admin = $_GET['admin'] ?? null;
$type  = $_GET['type'] ?? null;
$start = $_GET['start'] ?? date('Y-m-01');
$end   = $_GET['end'] ?? date('Y-m-d');

/* ==============================
   LOAD SLA SETTINGS
============================== */
$slaMatrix = [];

$slaQuery = $conn->query("SELECT * FROM sla_settings");
while ($row = $slaQuery->fetch_assoc()) {
    $priority = strtolower($row['priority']);
    $slaMatrix[$priority] = [
        'resolution' => (int)$row['resolution_minutes'],
        'response'   => (int)$row['response_minutes']
    ];
}

/* ==============================
   BUSINESS HOURS FUNCTIONS
============================== */

function businessMinutesBetween($start,$end){
    if ($end <= $start) return 0;

    $total = 0;

    while ($start < $end) {

        $day = date('Y-m-d',$start);
        $dayStart = strtotime("$day 07:30:00");
        $dayEnd   = strtotime("$day 18:00:00");

        if (date('N',$start)>=6){
            $start = strtotime("+1 day",strtotime("$day 00:00:00"));
            continue;
        }

        $periodStart = max($start,$dayStart);
        $periodEnd   = min($end,$dayEnd);

        if ($periodEnd>$periodStart){
            $total += ($periodEnd-$periodStart)/60;
        }

        $start = strtotime("+1 day",strtotime("$day 00:00:00"));
    }

    return $total;
}

function calculateBusinessMinutes($conn,$ticketId,$start,$end){

    $startTime=strtotime($start);
    $endTime=strtotime($end);
    if($endTime<=$startTime) return 0;

    $total=0;
    $current=$startTime;

    $logs=$conn->query("
        SELECT old_value,new_value,created_at
        FROM ticket_logs
        WHERE ticket_id=$ticketId
        AND field_name='status'
        ORDER BY created_at ASC
    ");

    while($log=$logs->fetch_assoc()){

        $logTime=strtotime($log['created_at']);
        if($logTime>$endTime) break;

        if($log['new_value']=='pending'){
            $total+=businessMinutesBetween($current,$logTime);
            $current=null;
        }

        if($log['old_value']=='pending'){
            $current=$logTime;
        }
    }

    if($current){
        $total+=businessMinutesBetween($current,$endTime);
    }

    return round($total);
}

/* ==============================
   FETCH TICKETS
============================== */

$sql = "
SELECT t.*, u.fullname AS admin_name
FROM ticket_tb t
LEFT JOIN user_tb u ON t.assigned_to = u.user_id
WHERE u.fullname = ?
AND DATE(t.date_created) BETWEEN ? AND ?
AND t.status IN ('resolved','closed')
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sss",$admin,$start,$end);
$stmt->execute();
$tickets = $stmt->get_result();

// echo "<h5>Admin: <b>$admin</b></h5>";

while($ticket = $tickets->fetch_assoc()){

    $ticketId = $ticket['ticket_id'];
    $ticketNumber = htmlspecialchars($ticket['ticket_number']);
    $priority = strtolower($ticket['priority'] ?? 'medium');
    $created  = $ticket['date_created'];

    $targetResolution = $slaMatrix[$priority]['resolution'] ?? 4320;
    $targetResponse   = $slaMatrix[$priority]['response'] ?? 60;

    /* ==============================
       GET FIRST RESPONSE TIME
    ============================== */

    $firstResponseLog = $conn->query("
        SELECT created_at FROM ticket_logs
        WHERE ticket_id=$ticketId
        AND field_name='status'
        AND new_value IN ('in progress','waiting for customer','resolved')
        ORDER BY created_at ASC LIMIT 1
    ")->fetch_assoc();

    $responseMinutes = 0;

    if($firstResponseLog){
        $responseMinutes = calculateBusinessMinutes(
            $conn,$ticketId,$created,$firstResponseLog['created_at']
        );
    }

    /* ==============================
       GET RESOLUTION TIME
    ============================== */

    $resolvedLog = $conn->query("
        SELECT created_at FROM ticket_logs
        WHERE ticket_id=$ticketId
        AND field_name='status'
        AND new_value='resolved'
        ORDER BY created_at ASC LIMIT 1
    ")->fetch_assoc();

    if(!$resolvedLog) continue;

    $resolvedTime = $resolvedLog['created_at'];

    $resolutionMinutes = calculateBusinessMinutes(
        $conn,$ticketId,$created,$resolvedTime
    );

    $met = $resolutionMinutes <= $targetResolution;

    if($type=='met' && !$met) continue;
    if($type=='not_met' && $met) continue;

    // echo "<hr>";

    echo "<h6>
            <a href='?page=ticket/view_ticket&ticket_id=$ticketId' 
               class='fw-bold text-primary'
               style='font-size:16px;'>
               $ticketNumber
            </a>
            | Priority: <b>".ucfirst($priority)."</b>
          </h6>";

    echo "<table class='table table-bordered table-sm'>";
    echo "<tr>
            <th>From</th>
            <th>To</th>
            <th>Date</th>
            <th>Business Minutes</th>
          </tr>";

    $logs = $conn->query("
        SELECT old_value,new_value,created_at
        FROM ticket_logs
        WHERE ticket_id=$ticketId
        AND field_name='status'
        ORDER BY created_at ASC
    ");

    $previousTime = $created;

    while($log = $logs->fetch_assoc()){

        $minutes = calculateBusinessMinutes(
            $conn,$ticketId,$previousTime,$log['created_at']
        );

        echo "<tr>";
        echo "<td>".$log['old_value']."</td>";
        echo "<td>".$log['new_value']."</td>";
        echo "<td>".$log['created_at']."</td>";
        echo "<td>".$minutes." mins</td>";
        echo "</tr>";

        $previousTime = $log['created_at'];
    }

    echo "</table>";

    echo "<div class='mb-2'>

            <b>Response Target:</b> $targetResponse mins
            <br>
            <b>Resolution Time:</b> $resolutionMinutes mins
            <br>
            <b>Resolution Target:</b> $targetResolution mins
          </div>";
}
//             <b>Response Time:</b> $responseMinutes mins <br>
           
$conn->close();
?>
