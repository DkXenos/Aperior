<?php
$servername = "localhost"; // Usually correct for XAMPP
$username = "root"; // Default XAMPP username, change if you set a different one
$password = ""; // Default XAMPP password is empty, change if you set one
$dbname = "aperior_db"; // <<< MAKE SURE THIS IS CORRECT

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// echo "Connected successfully"; // Optional: for testing connection
