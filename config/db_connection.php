<?php
$servername = "localhost";
$username = "root";
$password = ""; // Update with your database password
$dbname = "siticookies";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
