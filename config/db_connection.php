<?php
mysqli_report(MYSQLI_REPORT_OFF);

$servername = getenv('DB_HOST') ?: '127.0.0.1';
$username = getenv('DB_USERNAME') ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';
$dbname = getenv('DB_NAME') ?: 'siticookies';
$port = (int) (getenv('DB_PORT') ?: 3306);

$conn = @new mysqli($servername, $username, $password, $dbname, $port);

if ($conn->connect_errno) {
    error_log('Database connection failed: ' . $conn->connect_error);
    http_response_code(500);
    exit('A database error occurred. Please check that MySQL is running and try again.');
}

$conn->set_charset('utf8mb4');
