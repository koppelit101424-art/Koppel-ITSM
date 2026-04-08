<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include __DIR__ . '/../../../includes/auth.php';
include __DIR__ . '/../../../includes/db.php';

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
    echo "<div class='card p-3'>";
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
    $subject = strtolower($ticket['subject']);
    $subject_details = strtolower($ticket['subject_details']);
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

    echo "<div class='ticket-card mb-2 border rounded'>";
    /* ================= HEADER (CLICKABLE) ================= */
    echo "<div class='ticket-header p-2 bg-light'
            style='cursor:pointer;'
            onclick='toggleTicket($ticketId)'>

            <h6 class='mb-1'>
                <a href='?page=ticket/view_ticket&ticket_id=$ticketId' 
                class='fw-bold text-primary me-2'
                onclick='event.stopPropagation();'>
                $ticketNumber
                </a><br>
                Priority: <b>".ucfirst($priority)."</b>
                | Subject: <b>".ucfirst($subject)."</b>
                | Details: <b>".ucfirst($subject_details)."</b>
            </h6>
    </div>";

    echo "<div id='ticket_$ticketId' class='ticket-body p-2' style='display:none;'>";

    // ===== YOUR EXISTING TABLE =====
    echo "<table class='table table-bordered table-sm'>";
    echo "<tr>
            <th>From</th>
            <th>To</th>
            <th>Date</th>
            <th>Resolution Minutes</th>
        </tr>";
    echo "</div>";    echo "</div>";
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
        $conn, $ticketId, $previousTime, $log['created_at']
    );

    // Convert minutes to days, hours, minutes inline
    $days    = floor($minutes / 1440);
    $hours   = floor(($minutes % 1440) / 60);
    $mins    = $minutes % 60;

    $displayTime = "";
    if($days > 0)  $displayTime .= $days . "d ";
    if($hours > 0) $displayTime .= $hours . "h ";
    if($mins > 0)  $displayTime .= $mins . "m";

    echo "<tr>";
    echo "<td>".$log['old_value']."</td>";
    echo "<td>".$log['new_value']."</td>";
    echo "<td>".$log['created_at']."</td>";
    if ($displayTime == "") $displayTime = "0m";
    echo "<td>".$displayTime."";
    // original minutes if($displayTime != "0 m") echo " ({$minutes}m)";
    echo "</td>";
    echo "</tr>";

    $previousTime = $log['created_at'];
}

    echo "</table>";



echo "<div class='row'>
        <div class='col'>
        <b>Response Time:</b> {$responseMinutes}m 

        <br>
        <b>Response Target:</b> {$targetResponse}m 

        </div>
        <div class='col'>
        <b>Resolution Time:</b> " .
            ($resolutionMinutes >= 1440 ? floor($resolutionMinutes/1440)."d " : "") .
            ($resolutionMinutes >= 60 ? floor(($resolutionMinutes%1440)/60)."h " : "") .
            ($resolutionMinutes%60 > 0 ? ($resolutionMinutes%60)."m" : "") .
        "
        <br>
        <b>Resolution Target:</b> " .
            ($targetResolution >= 1440 ? floor($targetResolution/1440)."d " : "") .
            ($targetResolution >= 60 ? floor(($targetResolution%1440)/60)."h " : "") .
            ($targetResolution%60 > 0 ? ($targetResolution%60)."m" : "") ."
         
        </div>
        </div>
        </div>";

}
   
$conn->close();
?>
<script>
let openTicket = null;

function toggleTicket(id){
    let el = document.getElementById('ticket_' + id);

    if(openTicket && openTicket !== el){
        openTicket.style.display = 'none';
    }

    if(el.style.display === 'none'){
        el.style.display = 'block';
        openTicket = el;
    } else {
        el.style.display = 'none';
        openTicket = null;
    }
}
</script>

<!-- 
(" .($responseMinutes >= 1440 ? floor($responseMinutes/1440)."d " : "") .
    ($responseMinutes >= 60 ? floor(($responseMinutes%1440)/60)."h " : "") .
    ($responseMinutes%60 > 0 ? ($responseMinutes%60)."m" : "") .")
            
(" .
    ($targetResponse >= 1440 ? floor($targetResponse/1440)."d " : "") .
    ($targetResponse >= 60 ? floor(($targetResponse%1440)/60)."h " : "") .
    ($targetResponse%60 > 0 ? ($targetResponse%60)."m" : "") .
")
 / ({$resolutionMinutes}m)
   /({$targetResolution}m)
            -->