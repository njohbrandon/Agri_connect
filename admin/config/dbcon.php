<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "agri_connect";

// Create connection
$con = mysqli_connect($host, $username, $password, $database);

// Check connection
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set charset to utf8
mysqli_set_charset($con, "utf8");
?> 