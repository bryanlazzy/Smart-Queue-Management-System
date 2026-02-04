<?php
$host = "localhost";
$user = "u737260546_admin";
$password = "y0U>|HyMZx"; 
$database = "u737260546_capstone";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>