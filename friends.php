<?php
session_start();
// require 'db_connect.php'; // Not needed for placeholder

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Friends - Aprerior</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="./styles.css">
</head>
<body class="bg-gradient-to-br from-[#FFF7AD] to-[#FFA9F9] min-h-screen py-8">
    <div class="container mx-auto max-w-3xl bg-white/90 backdrop-blur-md p-6 md:p-8 rounded-xl shadow-2xl">
        <div class="flex items-center mb-6">
            <img src="./assets/aperior.svg" alt="Aperior Logo" class="w-16 h-16 mr-4"/>
            <h1 class="text-3xl font-bold text-pink-600 apply-custom-title-font">Friends</h1>
        </div>

        <div class="text-center text-gray-700 p-10 border-2 border-dashed border-pink-300 rounded-lg">
            <h2 class="text-2xl font-semibold mb-3">Coming Soon!</h2>
            <p class="text-lg">Our friends feature is currently under development.</p>
            <p>Stay tuned for updates to connect with other players!</p>
        </div>

        <p class="mt-8 text-center text-sm text-gray-600">
            <a href="./index.php" class="font-medium text-pink-600 hover:text-pink-500 hover:underline">Back to Home</a> | 
            <a href="./catalogue/index.php" class="font-medium text-pink-600 hover:text-pink-500 hover:underline">Browse Catalogue</a>
        </p>
    </div>
</body>
</html>