<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
$startDate = $_GET['start'] ?? date('Y-m-01');
$endDate   = $_GET['end'] ?? date('Y-m-d');

/* ==============================
   LOAD SLA SETTINGS
============================== */
$slaMatrix = [];
$responseMatrix = [];

$slaQuery = "SELECT * FROM sla_settings";
$slaResult = $conn->query($slaQuery);

while ($row = $slaResult->fetch_assoc()) {
    $priority = strtolower($row['priority']);
    $responseMatrix[$priority] = (int)$row['response_minutes'];
    $slaMatrix[$priority]      = (int)$row['resolution_minutes'];
}

/* ==============================
   BUSINESS HOURS
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

        if($periodEnd>$periodStart){
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

        if(strtolower($log['new_value'])=='pending'){
            if($current){
                $total+=businessMinutesBetween($current,$logTime);
            }
            $current=null;
        }

        if(strtolower($log['old_value'])=='pending'){
            $current=$logTime;
        }
    }

    if($current){
        $total+=businessMinutesBetween($current,$endTime);
    }

    return round($total);
}

/* ==============================
   MAIN QUERY (Resolved/Closed)
============================== */
$sql="
SELECT t.*,u.fullname AS admin_name
FROM ticket_tb t
LEFT JOIN user_tb u ON t.assigned_to=u.user_id
WHERE t.status IN('resolved','closed')
AND DATE(t.date_created) BETWEEN ? AND ?
";

$stmt=$conn->prepare($sql);
$stmt->bind_param("ss",$startDate,$endDate);
$stmt->execute();
$result=$stmt->get_result();

$adminStats=[];
$totalTickets=0;
$totalResponseMet=0;
$totalResolutionMet=0;

while($t=$result->fetch_assoc()){

    $priority=strtolower($t['priority']??'medium');
    $created=$t['date_created'];

    $firstResponse=$conn->query("
        SELECT created_at FROM ticket_logs
        WHERE ticket_id=".$t['ticket_id']."
        AND field_name='status'
        AND old_value='waiting for support'
        AND new_value IN('in progress','pending','ongoing')
        ORDER BY created_at ASC LIMIT 1
    ")->fetch_assoc()['created_at']??null;

    $resolvedTime=$conn->query("
        SELECT created_at FROM ticket_logs
        WHERE ticket_id=".$t['ticket_id']."
        AND field_name='status'
        AND new_value='resolved'
        ORDER BY created_at ASC LIMIT 1
    ")->fetch_assoc()['created_at']??null;

    $responseMet=false;
    $resolutionMet=false;

    if($firstResponse){
        $resp=calculateBusinessMinutes($conn,$t['ticket_id'],$created,$firstResponse);
        $responseMet=$resp<=($responseMatrix[$priority]??240);
    }

    if($resolvedTime){
        $res=calculateBusinessMinutes($conn,$t['ticket_id'],$created,$resolvedTime);
        $resolutionMet=$res<=($slaMatrix[$priority]??4320);
    }

    $slaMet=($responseMet&&$resolutionMet);

    $admin=$t['admin_name']??'Unassigned';

    if(!isset($adminStats[$admin])){
        $adminStats[$admin]=[
            'total'=>0,
            'met'=>0,
            'not_met'=>0
        ];
    }

    $adminStats[$admin]['total']++;

    if($slaMet){
        $adminStats[$admin]['met']++;
    }else{
        $adminStats[$admin]['not_met']++;
    }

    $totalTickets++;
    if($responseMet) $totalResponseMet++;
    if($resolutionMet) $totalResolutionMet++;
}

/* ==============================
   TEAM SLA %
============================== */
$teamResolutionPercent = $totalTickets>0 ? round(($totalResolutionMet/$totalTickets)*100,2) : 0;
$teamResponsePercent   = $totalTickets>0 ? round(($totalResponseMet/$totalTickets)*100,2) : 0;

/* ==============================
   ONGOING TICKETS PER ADMIN
============================== */
$ongoingStats = [];

$ongoingQuery = "
SELECT u.fullname AS admin_name, COUNT(t.ticket_id) AS ongoing_count
FROM ticket_tb t
LEFT JOIN user_tb u ON t.assigned_to = u.user_id
WHERE t.status NOT IN('resolved','closed','canceled')
GROUP BY t.assigned_to
";

$ongoingResult = $conn->query($ongoingQuery);

while ($row = $ongoingResult->fetch_assoc()) {
    $adminName = $row['admin_name'] ?? 'Unassigned';
    $ongoingStats[$adminName] = (int)$row['ongoing_count'];
}
?>
