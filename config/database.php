<?php
function getDBConnection()
{
    // Try to get Railway's MySQL URL first
    $mysql_url = getenv('MYSQL_PUBLIC_URL');

    if ($mysql_url) {
        // Parse the MySQL URL: mysql://user:password@host:port/database
        $url_parts = parse_url($mysql_url);
        $host = $url_parts['host'];
        $username = $url_parts['user'];
        $password = $url_parts['pass'];
        $dbname = ltrim($url_parts['path'], '/');
        $port = $url_parts['port'];
    } else {
        // Fallback to localhost for local development
        $host = 'localhost';
        $dbname = 'griotshelf';
        $username = 'root';
        $password = '';
        $port = 3306;
    }

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