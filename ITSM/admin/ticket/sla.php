<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include __DIR__ . '/../../includes/auth.php';
include __DIR__ . '/../../includes/db.php';

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
        AND new_value IN('in progress','pending','escalated','waiting for customer', 'canceled')
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
$whereRatings = "WHERE 1=1";

if (!empty($_GET['start']) && !empty($_GET['end'])) {
    $start = $_GET['start'];
    $end   = $_GET['end'];

    $whereRatings .= " AND DATE(t.date_created) BETWEEN '$start' AND '$end'";
}
/* ==============================
   CSAT PER ADMIN (AVERAGE)
============================== */
$csatStats = [];

$csatQuery = "
SELECT 
    t.assigned_to,
    u.fullname AS admin_name,
    AVG(r.rating) AS avg_rating,
    COUNT(r.rating) AS rating_count
FROM ticket_ratings r
LEFT JOIN ticket_tb t ON r.ticket_id = t.ticket_id
LEFT JOIN user_tb u ON t.assigned_to = u.user_id
$whereRatings
GROUP BY t.assigned_to
";

$csatResult = $conn->query($csatQuery);

while ($row = $csatResult->fetch_assoc()) {
    $adminName = $row['admin_name'] ?? 'Unassigned';

    $csatStats[$adminName] = [
        'avg' => round($row['avg_rating'], 2),
        'count' => $row['rating_count']
    ];
}
?>
<?php
/* ==============================
   OVERALL CSAT %
============================== */

$overallCsatQuery = "
SELECT 
    AVG(r.rating) AS avg_rating,
    COUNT(r.rating) AS total_ratings
FROM ticket_ratings r
LEFT JOIN ticket_tb t ON r.ticket_id = t.ticket_id
$whereRatings
";

$overallCsatResult = $conn->query($overallCsatQuery);
$overallCsat = $overallCsatResult->fetch_assoc();

$overallAvg = $overallCsat['avg_rating'] ?? 0;
$overallCount = $overallCsat['total_ratings'] ?? 0;

// convert to percentage (5-star scale → 100%)
$overallPercent = $overallAvg > 0 ? round(($overallAvg / 5) * 100, 2) : 0;

?>

<style>
.ticket-number{
    font-weight:bold;
    font-family:monospace;
    font-size:14px;
}
</style>



<div class="card shadow-sm">
<div class="card-header d-flex justify-content-between align-items-center text-white">
<h5>ITSM SLA Dashboard</h5>

<form class="d-flex gap-2">
<input type="hidden" name="page" value="ticket/sla">
<input type="date" name="start" value="<?= $startDate ?>" class="form-control">
<input type="date" name="end" value="<?= $endDate ?>" class="form-control">
<button class="btn btn-primary btn-sm">Filter</button>
</form>
</div>

<div class="card-body">
<div class="row mb-4">

<div class="col-md-4">
<div class="card shadow-sm border-0 text-center">
<div class="card-body">
<h6 class="text-muted">Team Response SLA</h6>
<h2 class="<?= $teamResponsePercent>=90?'text-success':'text-danger' ?>">
<?= $teamResponsePercent ?>%
</h2>
<p class="mb-0">First Response Compliance</p>
</div>
</div>
</div>

<div class="col-md-4">
<div class="card shadow-sm border-0 text-center">
<div class="card-body">
<h6 class="text-muted">Team Resolution SLA</h6>
<h2 class="<?= $teamResolutionPercent>=90?'text-success':'text-danger' ?>">
<?= $teamResolutionPercent ?>%
</h2>
<p class="mb-0">Resolution Target Compliance</p>
</div>
</div>
</div>

<div class="col-md-4">
<div class="col-md-12 mb-3">
    <div class="card shadow-sm border-0 text-center">
        <div class="card-body">
            <h6 class="text-muted">Overall Customer Satisfaction</h6>

            <h2 class="<?= $overallPercent >= 80 ? 'text-success' : 'text-warning' ?>">
                <?= $overallPercent ?>%
            </h2>

            <p class="mb-0 text-muted">
                Based on <?= $overallCount ?> ratings
            </p>
        </div>
    </div>
</div>
</div>

</div>

<h5>🏆 Admin Leaderboard</h5>

<table class="table table-bordered table-hover">
<tr>
<th>Admin</th>
<th>Ongoing Tickets</th>
<th>Resolved Tickets</th>
<th>Met SLA</th>
<th>Failed SLA</th>
<th>SLA Rating</th>
<th>CSAT Rating</th>
</tr>

<?php 

uasort($adminStats, function($a, $b) {
    return $b['total'] <=> $a['total']; // DESC order
});

foreach($adminStats as $admin=>$data):
$percent=$data['total']>0?round(($data['met']/$data['total'])*100,2):0;
?>
<tr>
<td><?= $admin ?></td>

<td class="fw-bold text-primary">
<?= $ongoingStats[$admin] ?? 0 ?>
</td>
<td><?= $data['total'] ?></td>

<td>
<a href="?page=sla_ticket_logs
&admin=<?= urlencode($admin) ?>
&type=met
&start=<?= $startDate ?>
&end=<?= $endDate ?>"
class="text-success fw-bold">
<?= $data['met'] ?>
</a>
</td>

<td>
<a href="?page=sla_ticket_logs
&admin=<?= urlencode($admin) ?>
&type=not_met
&start=<?= $startDate ?>
&end=<?= $endDate ?>"
class="text-danger fw-bold">
<?= $data['not_met'] ?>
</a>
</td>

<td class="<?= $percent>=90?'text-success':'text-danger' ?>">
<?= $percent ?>%
</td>
<td>
<?php
$avg = $csatStats[$admin]['avg'] ?? 0;

if ($avg > 0) {
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= round($avg)) {
            echo '<i class="fa-solid fa-star text-warning"></i>';
        } else {
            echo '<i class="fa-regular fa-star text-muted"></i>';
        }
    }
    echo "<br><small class='text-muted'>($avg)</small>";
} else {
    echo "<span class='text-muted'>Not rated</span>";
}
?>
</td>
</tr>
<?php endforeach; ?>
</table>

<div id="detailContainer" class="mt-4" style="display:none;">
<div class="card shadow-sm">
<div class="card-header d-flex justify-content-between">
<h6 id="detailTitle"></h6>
<button class="btn btn-sm btn-outline-secondary" onclick="closeDetails()">Close</button>
</div>
<div class="card-body" id="detailContent"></div>
</div>
</div>

</div>
</div>

<script>
let currentOpen = null;

document.querySelectorAll('.toggle-detail').forEach(link=>{
link.onclick=function(e){
e.preventDefault();

let key = this.dataset.admin + '_' + this.dataset.type;

if(currentOpen === key){
    closeDetails();
    return;
}

currentOpen = key;

fetch('?ajax=sla_ticket_logs&admin='+this.dataset.admin+
'&type='+this.dataset.type+
'&start=<?= $startDate ?>&end=<?= $endDate ?>')
.then(r=>r.text())
.then(html=>{
    document.getElementById('detailTitle').innerHTML =
        this.dataset.admin + " - " +
        (this.dataset.type === 'met' ? 'Met SLA Tickets' : 'Not Met SLA Tickets');

    document.getElementById('detailContent').innerHTML = html;
    document.getElementById('detailContainer').style.display='block';
});
}
});

function closeDetails(){
    document.getElementById('detailContainer').style.display='none';
    document.getElementById('detailContent').innerHTML='';
    currentOpen=null;
}
</script>


<?php $conn->close(); ?>