<?php
// ============================================================
// db.php - Database Connection File
// Update credentials if deploying to live server
// ============================================================

$host     = "localhost";
$username = "root";       // Change for live server
$password = "";           // Change for live server
$database = "voting_system";

// Create connection using mysqli
$conn = mysqli_connect($host, $username, $password, $database);

// Check connection
if (!$conn) {
    die("<h3 style='color:red; text-align:center;'>Database Connection Failed: " . mysqli_connect_error() . "</h3>");
}
?>
