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
        $host = getenv('MYSQLHOST');
        $dbname = getenv('MYSQLDATABASE');
        $username = getenv('MYSQLUSER');
        $password = getenv('MYSQLPASSWORD');
        $port = getenv('MYSQLPORT') ?: 3306;

        if (!$host || !$username) {
            error_log("Missing Railway DB Variables: MYSQLHOST=" . ($host ? 'set' : 'MISSING') . ", MYSQLUSER=" . ($username ? 'set' : 'MISSING'));
            // Default to localhost only if we are truly local
            if (php_sapi_name() === 'cli' || $_SERVER['REMOTE_ADDR'] === '127.0.0.1' || $_SERVER['REMOTE_ADDR'] === '::1') {
                $host = $host ?: 'localhost';
                $dbname = $dbname ?: 'griotshelf';
                $username = $username ?: 'root';
                $password = $password ?: '';
            }
        }
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