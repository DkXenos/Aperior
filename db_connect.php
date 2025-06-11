<?php
$servername = "localhost"; 
$username = "root"; 
$password = ""; 
$dbname = "aperior_db"; 


$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

/*
<?php
// InfinityFree database settings (you'll get these from your control panel)
$servername = "sql201.infinityfree.com"; // or similar
$username = "if0_39186518"; // your actual database username
$password = "buYdooVQMAVdw"; // your actual database password
$dbname = "if0_39186518_aperior_db"; // your actual database name

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
ini buat yang versi hosting 

*/
