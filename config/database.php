<?php
function getDBConnection()
{
    // Use Railway environment variables, fallback to localhost for local development
    $host = getenv('MYSQLHOST') ?: 'localhost';
    $dbname = getenv('MYSQLDATABASE') ?: 'griotshelf';
    $username = getenv('MYSQLUSER') ?: 'root';
    $password = getenv('MYSQLPASSWORD') ?: '';
    $port = getenv('MYSQLPORT') ?: '3306';

    $conn = new mysqli($host, $username, $password, $dbname, $port);

    // Check if connection was successful
    if ($conn->connect_error) {
        // If connection failed, stop and show error
        die("Connection failed: " . $conn->connect_error);
    }

    $conn->set_charset("utf8mb4");
    return $conn;
}
?>