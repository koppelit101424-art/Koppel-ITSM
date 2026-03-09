<?php
// Example data (can be fetched from DB)
$data = [
    "branch"       => "ILOILO BRANCH",
    "ref_no"       => "INV10301",
    "asset"        => [
        "qty"        => 1,
        "serial_no"  => "GPR1251200318",
        "brand"      => "NGTECO",
        "name"       => "BIOMETRIC",
        "unit_type"  => "NGTECO",
        "model"      => "SAMPLE",
        "date_issued"=> "08/29/2025",
        "item_no"    => "NGTECO"
    ],
    "description"  => "Deployment of new biometric for iloilo branch",
    "prepared_by"  => "HR DEPARTMENT",
    "checked_by"   => "IT SUPERVISOR",
    "noted1"       => "IT DIRECTOR",
    "noted2"       => "USER",
    "received_by"  => "IT ASSISTANT"
];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Issuance Form</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { text-align: center; font-size: 20px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: center; }
        .signatures td { height: 50px; vertical-align: bottom; }
    </style>
</head>
<body>

    <div class="header">ISSUANCE FORM</div>
    <p><b>Area:</b> <?= $data["branch"] ?><br>
       <b>Ref No:</b> <?= $data["ref_no"] ?></p>

    <table>
        <tr>
            <th>Qty</th>
            <th>Serial No.</th>
            <th>Brand</th>
            <th>Name</th>
            <th>Unit Type</th>
            <th>Model</th>
            <th>Date Issued</th>
            <th>Item No.</th>
        </tr>
        <tr>
            <td><?= $data["asset"]["qty"] ?></td>
            <td><?= $data["asset"]["serial_no"] ?></td>
            <td><?= $data["asset"]["brand"] ?></td>
            <td><?= $data["asset"]["name"] ?></td>
            <td><?= $data["asset"]["unit_type"] ?></td>
            <td><?= $data["asset"]["model"] ?></td>
            <td><?= $data["asset"]["date_issued"] ?></td>
            <td><?= $data["asset"]["item_no"] ?></td>
        </tr>
    </table>

    <p><b>Description:</b> <?= $data["description"] ?></p>
    <p><b>Reason of Issuance / Remarks:</b> <?= $data["description"] ?></p>

    <br><br>
    <table class="signatures">
        <tr>
            <td><b>Prepared by:</b><br><?= $data["prepared_by"] ?></td>
            <td><b>Checked by:</b><br><?= $data["checked_by"] ?></td>
            <td><b>Noted by:</b><br><?= $data["noted1"] ?></td>
            <td><b>Noted by:</b><br><?= $data["noted2"] ?></td>
            <td><b>Received by:</b><br><?= $data["received_by"] ?></td>
        </tr>
    </table>

</body>
</html>
