<?php
// Database connection – adjust to your XAMPP settings
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "baking_db";   // make sure this matches your DB name

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8");
?>