<?php
include __DIR__ . '/../../../includes/db.php';

if(isset($_POST['emp_id'])){

    $emp_id = trim($_POST['emp_id']);

    $stmt = $conn->prepare("SELECT user_id FROM user_tb WHERE emp_id = ?");
    $stmt->bind_param("s",$emp_id);
    $stmt->execute();
    $stmt->store_result();

    if($stmt->num_rows > 0){
        echo "taken";
    } else {
        echo "available";
    }

}