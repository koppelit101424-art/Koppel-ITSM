<?php
    // Database connection
    // $host = "CHANGE_ME";
    // $user = "CHANGE_ME";
    // $pass = "CHANGE_ME";
    // $db   = "CHANGE_ME";


    $host = "CHANGE_ME";
    $user = "CHANGE_ME";
    $pass = "CHANGE_ME";
    $db   = "CHANGE_ME";

    $conn = new mysqli($host, $user, $pass, $db);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
?>
