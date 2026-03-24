<?php

/* ===============================
   FETCH DESKTOP RECORDS + AREA (for table)
================================ */
$sql = "
    SELECT 
        d.*,
        a.area AS area_name,
            u.user_id, 
        u.fullname AS user_name,
        u.position AS user_position,
        u.department AS user_department
    FROM desktop_tb d
    LEFT JOIN desktop_area_tb a ON d.desktop_area_id = a.desktop_area_id
    LEFT JOIN user_desktop_tb ud ON d.desktop_id = ud.desktop_id
    LEFT JOIN user_tb u ON ud.user_id = u.user_id
    ORDER BY d.date_created DESC
";
$result = $conn->query($sql);

/* ===============================
   FETCH AREAS FOR FILTER BUTTONS
================================ */
$areaSql = "SELECT DISTINCT area FROM desktop_area_tb ORDER BY desktop_area_id ASC";
$areaResult = $conn->query($areaSql);

/* ===============================
   SUMMARY DATA FOR ANALYTICS (without RAM/CPU)
================================ */

// 1. Windows Key Status (Old pie chart — can remove if desired)
$winSql = "SELECT 
    CASE 
        WHEN windows_key = '' OR windows_key IS NULL THEN 'No Key'
        ELSE 'Has Key'
    END as win_status,
    COUNT(*) as count
FROM desktop_tb GROUP BY win_status";
$winResult = $conn->query($winSql);
$winData = [];
while ($row = $winResult->fetch_assoc()) {
    $winData[] = $row;
}

// 2. Antivirus Status
$avSql = "SELECT antivirus, COUNT(*) as count FROM desktop_tb GROUP BY antivirus";
$avResult = $conn->query($avSql);
$avData = [];
while ($row = $avResult->fetch_assoc()) {
    $avData[] = $row;
}

// 3. SAP Usage & Total Count
$sapSql = "SELECT 
    SUM(CASE WHEN LOWER(computer_name) LIKE '%sap%' THEN 1 ELSE 0 END) as sap_count,
    COUNT(*) as total
FROM desktop_tb";
$sapRow = $conn->query($sapSql)->fetch_assoc();
$sapCount = (int)$sapRow['sap_count'];
$totalDesktops = (int)$sapRow['total'];

// 4. Desktops per Area
$areaCountSql = "
    SELECT 
        COALESCE(a.area, 'Unassigned') as area_name,
        COUNT(d.desktop_id) as count
    FROM desktop_tb d
    LEFT JOIN desktop_area_tb a ON d.desktop_area_id = a.desktop_area_id
    GROUP BY a.area
    ORDER BY count DESC
";
$areaCountResult = $conn->query($areaCountSql);
$areaCountData = [];
while ($row = $areaCountResult->fetch_assoc()) {
    $areaCountData[] = $row;
}

// Calculate Antivirus YES percentage
$avYes = 0;
foreach ($avData as $item) {
    if (in_array(strtoupper(trim($item['antivirus'] ?? '')), ['YES', 'Y', '1'])) {
        $avYes += $item['count'];
    }
}
$avPct = $totalDesktops > 0 ? round(($avYes / $totalDesktops) * 100, 1) : 0;

/* ===============================
   WINDOWS OS + LICENSE STATUS
   WHEN windows_key LIKE 'WIN7%' THEN 'Windows 7'
================================ */
$osSql = "
    SELECT
        CASE
            
            WHEN windows_key LIKE 'WIN10%' THEN 'Windows 10'
            WHEN windows_key LIKE 'WIN11%' THEN 'Windows 11'
            ELSE 'Windows 7'
        END AS os_version,
        SUM(CASE WHEN windows_key LIKE '%- N/A' OR windows_key IS NULL THEN 0 ELSE 1 END) AS with_key,
        SUM(CASE WHEN windows_key LIKE '%- N/A' OR windows_key IS NULL THEN 1 ELSE 0 END) AS without_key
    FROM desktop_tb
    GROUP BY os_version
    ORDER BY os_version ASC
";
$osResult = $conn->query($osSql);
$osData = [];
while ($row = $osResult->fetch_assoc()) {
    $osData[] = $row;
}

// Prepare arrays for chart
$osLabels = array_column($osData, 'os_version');
$osWithKey = array_column($osData, 'with_key');
$osWithoutKey = array_column($osData, 'without_key');
?>