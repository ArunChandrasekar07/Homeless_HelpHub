<?php
    
error_reporting(0);
ini_set('display_errors', 0);

$servername = "sql113.infinityfree.com";   // MySQL Host Name
$username   = "if0_40204678";              // MySQL User Name
$password   = "q3ckJSoieFvg";              // MySQL Password
$dbname     = "if0_40204678_homeless_hub"; // Database Name

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
