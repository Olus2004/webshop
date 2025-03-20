<?php
$host = "localhost";
$db_username = "root";
$db_password = "";
$database = "cuahang";

$conn = mysqli_connect($host, $db_username, $db_password, $database);

if (!$conn) {
    die("Kết nối thất bại: " . mysqli_connect_error());
} else {
  
}
?>