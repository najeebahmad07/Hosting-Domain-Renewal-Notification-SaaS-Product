<?php
// Database Connection for UltraServe

$host = "localhost";      // Server name
$user = "root";           // MySQL username
$pass = "";               // MySQL password
$dbname = "ultraserve";   // Database name

// Create connection
$conn = mysqli_connect($host, $user, $pass, $dbname);

// Check connection
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>
