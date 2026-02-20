<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "drivezone_db";

// Connection eka hadana widiya
$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>