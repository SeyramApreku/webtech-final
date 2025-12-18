<?php
function getDBConnection() {
    $host = 'localhost';
    $dbname = 'griotshelf';
    $username = 'root';
    $password = '';


    $conn = new mysqli($host, $username, $password, $dbname);
    
    // Check if connection was successful
    if ($conn->connect_error) {
        // If connection failed, stop and show error
        die("Connection failed: " . $conn->connect_error);
    }

    $conn->set_charset("utf8mb4");
    return $conn;
}
?>