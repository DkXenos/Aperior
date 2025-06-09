<?php
session_start();

// Unset all developer session variables
unset($_SESSION['developer_id']);
unset($_SESSION['developer_username']);
unset($_SESSION['company_name']);

// Destroy the session
session_destroy();

// Redirect to developer login
header("Location: login.php");
exit();
?>