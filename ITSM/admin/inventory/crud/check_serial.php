<?php
include __DIR__ . '/../../../includes/db.php';

if(isset($_POST['serial'])){

    $serial = trim($_POST['serial']);

    if($serial === "" || strtoupper($serial) === "N/A"){
        echo "available";
        exit;
    }

    $stmt = $conn->prepare("SELECT item_id FROM item_tb WHERE serial_number = ?");
    $stmt->bind_param("s",$serial);
    $stmt->execute();
    $stmt->store_result();

    if($stmt->num_rows > 0){
        echo "taken";
    } else {
        echo "available";
    }

}