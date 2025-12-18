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
        // Fallback - Try individual variables
        $host = getenv('MYSQLHOST') ?: 'localhost';
        $dbname = getenv('MYSQLDATABASE') ?: 'griotshelf';
        $username = getenv('MYSQLUSER') ?: 'root';
        $password = getenv('MYSQLPASSWORD') ?: '';
        $port = getenv('MYSQLPORT') ?: 3306;
    }

    // Suppress errors and handle them ourselves
    mysqli_report(MYSQLI_REPORT_OFF);

    // Create connection
    $conn = new mysqli($host, $username, $password, $dbname, $port);

    // Check if connection was successful
    if ($conn->connect_error) {
        error_log("MySQL Connection Error: " . $conn->connect_error . " | Host: " . $host . " | Port: " . $port);
        return false;
    }

    $conn->set_charset("utf8mb4");
    return $conn;
}
?>