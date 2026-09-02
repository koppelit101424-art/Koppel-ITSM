<?php
    // $host = "localhost";
    // $user = "root";
    // $pass = "";
    // $db   = "inventory_db";

    Database connection
    $host = "localhost";
    $user = "root";
    $pass = "Koppels3cur1ty";
    $db   = "inventory_db";

    $conn = new mysqli($host, $user, $pass, $db);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
?>
