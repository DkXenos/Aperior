<?php
session_start();

unset($_SESSION['developer_id']);
unset($_SESSION['developer_username']);
unset($_SESSION['company_name']);


session_destroy();


header("Location: login.php");
exit();
?>