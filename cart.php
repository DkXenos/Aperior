<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_id'])) {

    exit();
}

$user_id = $_SESSION['user_id'];
$cart_items = [];
$total_price = 0;


$stmt = $conn->prepare(
    "SELECT g.id as game_id, g.title, g.price, g.image_url, ci.quantity 
     FROM cart_items ci 
     JOIN games g ON ci.game_id = g.id 
     WHERE ci.user_id = ?"
);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $cart_items[] = $row;
        $total_price += $row['price'] * $row['quantity'];
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
    <title>Shopping Cart - Aprerior</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="./styles.css">
    <style>
        .remove-btn { background-color: #DC143C; color: white; padding: 5px 10px; border-radius: 4px; font-size: 0.8rem;}
        .remove-btn:hover { background-color: #b21030;}
        .update-quantity-btn { background-color: #4682B4; color: white; padding: 3px 7px; border-radius: 4px; font-size: 0.7rem; margin: 0 2px;}
    </style>
</head>
<body class="bg-gradient-to-br from-[#FFF7AD] to-[#FFA9F9] min-h-screen py-8">
    <div class="container mx-auto max-w-3xl bg-white/90 backdrop-blur-md p-6 md:p-8 rounded-xl shadow-2xl">
        <div class="flex items-center mb-6">
            <img src="./assets/aperior.svg" alt="Aperior Logo" class="w-16 h-16 mr-4"/>
            <h1 class="text-3xl font-bold text-pink-600 apply-custom-title-font">Your Shopping Cart</h1>
        </div>

        <?php if (empty($cart_items)): ?>
            <p class="text-center text-gray-600 text-lg">Your cart is empty.</p>
            <div class="text-center mt-6">
                <a href="./catalogue/index.php" class="text-pink-600 hover:text-pink-800 hover:underline font-semibold">Continue Shopping</a>
            </div>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($cart_items as $item): ?>
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between p-4 border border-pink-200 rounded-lg bg-pink-50 space-y-3 sm:space-y-0">
                        
                        <div class="flex items-center">
                            <img src="<?php echo htmlspecialchars($item['image_url'] ?: './assets/image_placeholder.png'); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="w-16 h-16 sm:w-20 sm:h-20 object-cover rounded mr-3 sm:mr-4 flex-shrink-0">
                            <div class="min-w-0 flex-1">
                                <h2 class="text-base sm:text-lg font-semibold text-pink-700 truncate"><?php echo htmlspecialchars($item['title']); ?></h2>
                                <p class="text-xs sm:text-sm text-gray-600">Price: $<?php echo number_format($item['price'], 2); ?></p>
                            </div>
                        </div>
                        
                        
                        <div class="flex flex-col sm:flex-row sm:items-center space-y-2 sm:space-y-0 sm:space-x-4">
                            
                            <div class="flex items-center justify-between sm:justify-start space-x-2">
                                <form action="update_cart.php" method="POST" class="flex items-center">
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="game_id" value="<?php echo $item['game_id']; ?>">
                                    <label for="quantity_<?php echo $item['game_id']; ?>" class="text-xs sm:text-sm text-gray-600 mr-2">Qty:</label>
                                    <input type="number" name="quantity" id="quantity_<?php echo $item['game_id']; ?>" value="<?php echo $item['quantity']; ?>" min="1" class="w-12 sm:w-16 border border-pink-300 rounded-md px-1 sm:px-2 py-1 text-xs sm:text-sm focus:ring-pink-500 focus:border-pink-500">
                                    <button type="submit" class="update-quantity-btn ml-1 sm:ml-2">Update</button>
                                </form>
                                
                                <form action="update_cart.php" method="POST" class="inline">
                                    <input type="hidden" name="action" value="remove">
                                    <input type="hidden" name="game_id" value="<?php echo $item['game_id']; ?>">
                                    <button type="submit" class="remove-btn">Remove</button>
                                </form>
                            </div>
                            
                            
                            <div class="text-right sm:text-left">
                                <p class="text-base sm:text-lg font-semibold text-pink-600">$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            
            <div class="mt-6 sm:mt-8 pt-4 sm:pt-6 border-t border-pink-300">
                <div class="flex flex-col sm:flex-row sm:justify-end sm:items-center space-y-4 sm:space-y-0">
                    <div class="text-center sm:text-right">
                        <div class="flex justify-center sm:justify-end items-center mb-4 sm:mb-0">
                            <span class="text-lg sm:text-xl font-bold text-gray-700">Total:</span>
                            <span class="text-xl sm:text-2xl font-bold text-pink-600 ml-2 sm:ml-4">$<?php echo number_format($total_price, 2); ?></span>
                        </div>
                        <a href="payment.php" class="w-full sm:w-auto bg-pink-500 hover:bg-pink-600 text-white font-bold py-3 px-6 rounded-lg shadow-md transition-colors inline-block text-center">
                            Proceed to Checkout
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
         <p class="mt-8 text-center text-sm text-gray-600">
            <a href="./index.php" class="font-medium text-pink-600 hover:text-pink-500 hover:underline">Back to Home</a>
        </p>
    </div>
</body>
</html>