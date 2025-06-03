<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$inventory_games = [];

$stmt = $conn->prepare(
    "SELECT g.id as game_id, g.title, g.image_url, g.description, ui.purchase_date
     FROM user_inventory ui
     JOIN games g ON ui.game_id = g.id 
     WHERE ui.user_id = ?
     ORDER BY ui.purchase_date DESC"
);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $inventory_games[] = $row;
    }
    $result->free();
}
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Inventory - Aprerior</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="./styles.css">
</head>
<body class="bg-gradient-to-br from-[#FFF7AD] to-[#FFA9F9] min-h-screen py-8">
    <div class="container mx-auto max-w-4xl bg-white/90 backdrop-blur-md p-6 md:p-8 rounded-xl shadow-2xl">
        <div class="flex items-center mb-6">
            <img src="./assets/aperior.svg" alt="Aperior Logo" class="w-16 h-16 mr-4"/>
            <h1 class="text-3xl font-bold text-pink-600 apply-custom-title-font">My Games Inventory</h1>
        </div>

        <?php if (isset($_SESSION['payment_message'])): // Display message from payment processing ?>
            <div class="mb-4 p-3 rounded-md bg-green-100 text-green-700">
                <?php echo $_SESSION['payment_message']; unset($_SESSION['payment_message']); ?>
            </div>
        <?php endif; ?>

        <?php if (empty($inventory_games)): ?>
            <p class="text-center text-gray-600 text-lg">You don't own any games yet.</p>
            <div class="text-center mt-6">
                <a href="./catalogue/index.php" class="text-pink-600 hover:text-pink-800 hover:underline font-semibold">Browse Games to Purchase</a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($inventory_games as $game): ?>
                    <div class="bg-pink-50 p-4 rounded-lg shadow-md flex flex-col justify-between">
                        <img src="<?php echo htmlspecialchars($game['image_url'] ?: './assets/image_placeholder.png'); ?>" alt="<?php echo htmlspecialchars($game['title']); ?>" class="w-full h-40 object-cover rounded mb-3">
                        <div>
                            <h2 class="text-lg font-semibold text-pink-700"><?php echo htmlspecialchars($game['title']); ?></h2>
                            <p class="text-xs text-gray-500 mt-1">Purchased: <?php echo date("M j, Y", strtotime($game['purchase_date'])); ?></p>
                        </div>
                        <button onclick="alert('Play game feature coming soon!')" class="mt-4 w-full bg-green-500 hover:bg-green-600 text-white font-semibold py-2 px-4 rounded-md transition-colors text-sm">
                            Play Game
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <p class="mt-8 text-center text-sm text-gray-600">
            <a href="./index.php" class="font-medium text-pink-600 hover:text-pink-500 hover:underline">Back to Home</a> | 
            <a href="./catalogue/index.php" class="font-medium text-pink-600 hover:text-pink-500 hover:underline">Browse Catalogue</a>
        </p>
    </div>
</body>
</html>