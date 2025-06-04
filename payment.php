<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
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


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Your Purchase - Aprerior</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="./styles.css">
</head>
<body class="bg-gradient-to-br from-[#FFF7AD] to-[#FFA9F9] min-h-screen py-8">
    <div class="container mx-auto max-w-2xl bg-white/90 backdrop-blur-md p-6 md:p-8 rounded-xl shadow-2xl">
        <div class="flex items-center mb-6">
            <img src="./assets/aperior.svg" alt="Aperior Logo" class="w-16 h-16 mr-4"/>
            <h1 class="text-3xl font-bold text-pink-600 apply-custom-title-font">Confirm Your Order</h1>
        </div>

        <?php if (isset($_SESSION['payment_message'])): ?>
            <div class="mb-4 p-3 rounded-md <?php echo strpos($_SESSION['payment_message'], 'successful') !== false ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                <?php echo $_SESSION['payment_message']; unset($_SESSION['payment_message']); ?>
            </div>
        <?php endif; ?>

        <?php if (empty($cart_items)): ?>
            <p class="text-center text-gray-600 text-lg">Your cart is empty. Nothing to pay for.</p>
            <div class="text-center mt-6">
                <a href="./catalogue/index.php" class="text-pink-600 hover:text-pink-800 hover:underline font-semibold">Browse Games</a>
            </div>
        <?php else: ?>
            <div class="space-y-3 mb-6">
                <h2 class="text-xl font-semibold text-pink-700 mb-3">Order Summary</h2>
                <?php foreach ($cart_items as $item): ?>
                    <div class="flex items-center justify-between p-3 border border-pink-100 rounded-lg bg-pink-50">
                        <div class="flex items-center">
                            <img src="<?php echo htmlspecialchars($item['image_url'] ?: './assets/image_placeholder.png'); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="w-12 h-12 object-cover rounded mr-3">
                            <div>
                                <h3 class="text-md font-medium text-pink-700"><?php echo htmlspecialchars($item['title']); ?></h3>
                                <p class="text-xs text-gray-500">Quantity: <?php echo $item['quantity']; ?> x $<?php echo number_format($item['price'], 2); ?></p>
                            </div>
                        </div>
                        <p class="text-md font-semibold text-pink-600">$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="mt-6 pt-4 border-t border-pink-300">
                <div class="flex justify-end items-center mb-6">
                    <span class="text-xl font-bold text-gray-700">Total Amount:</span>
                    <span class="text-2xl font-bold text-pink-600 ml-4">$<?php echo number_format($total_price, 2); ?></span>
                </div>
                <form action="process_payment.php" method="POST">
                    <button type="submit" class="w-full bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-6 rounded-lg shadow-md transition-colors text-lg">
                        I Have Paid
                    </button>
                </form>
            </div>
        <?php endif; ?>
        <p class="mt-8 text-center text-sm text-gray-600">
            <a href="cart.php" class="font-medium text-pink-600 hover:text-pink-500 hover:underline">Back to Cart</a> |
            <a href="./catalogue/index.php" class="font-medium text-pink-600 hover:text-pink-500 hover:underline">Continue Shopping</a>
        </p>
    </div>
</body>
</html>
<?php $conn->close(); ?>