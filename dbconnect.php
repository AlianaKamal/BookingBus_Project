<?php
$servername = "localhost"; // Update with your database host
$username = "root"; // Update with your database username
$password = ""; // Update with your database password
$dbname = "bus_booking"; // Update with your database name

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>

