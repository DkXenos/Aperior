<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$wishlist_items = [];

$stmt = $conn->prepare(
    "SELECT g.id as game_id, g.title, g.price, g.image_url, g.description 
     FROM wishlist_items wi 
     JOIN games g ON wi.game_id = g.id 
     WHERE wi.user_id = ?"
);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $wishlist_items[] = $row;
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
    <title>My Wishlist - Aprerior</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="./styles.css">
     <style>
        .remove-btn { background-color: #DC143C; color: white; padding: 5px 10px; border-radius: 4px; font-size: 0.8rem;}
        .remove-btn:hover { background-color: #b21030;}
        .add-to-cart-btn { background-color: #4CAF50; color: white; padding: 5px 10px; border-radius: 4px; font-size: 0.8rem; margin-left: 8px;}
        .add-to-cart-btn:hover { background-color: #45a049;}
        .add-to-cart-btn:disabled { background-color: #cccccc; cursor: not-allowed; }
    </style>
</head>
<body class="bg-gradient-to-br from-[#FFF7AD] to-[#FFA9F9] min-h-screen py-8">
    <div class="container mx-auto max-w-3xl bg-white/90 backdrop-blur-md p-6 md:p-8 rounded-xl shadow-2xl">
        <div class="flex items-center mb-6">
            <img src="./assets/aperior.svg" alt="Aperior Logo" class="w-16 h-16 mr-4"/>
            <h1 class="text-3xl font-bold text-pink-600 apply-custom-title-font">My Wishlist</h1>
        </div>

        <?php if (isset($_SESSION['wishlist_message'])): ?>
            <div class="mb-4 p-4 rounded-lg <?php echo strpos($_SESSION['wishlist_message'], 'successfully') !== false ? 'bg-green-100 border border-green-400 text-green-700' : 'bg-red-100 border border-red-400 text-red-700'; ?>">
                <?php 
                echo htmlspecialchars($_SESSION['wishlist_message']); 
                unset($_SESSION['wishlist_message']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['cart_message'])): ?>
            <div class="mb-4 p-4 rounded-lg <?php echo strpos($_SESSION['cart_message'], 'successfully') !== false ? 'bg-green-100 border border-green-400 text-green-700' : 'bg-red-100 border border-red-400 text-red-700'; ?>">
                <?php 
                echo htmlspecialchars($_SESSION['cart_message']); 
                unset($_SESSION['cart_message']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (empty($wishlist_items)): ?>
            <p class="text-center text-gray-600 text-lg">Your wishlist is empty.</p>
            <div class="text-center mt-6">
                <a href="./catalogue/index.php" class="text-pink-600 hover:text-pink-800 hover:underline font-semibold">Browse Games</a>
            </div>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($wishlist_items as $item): ?>
                    <div class="flex items-start justify-between p-4 border border-pink-200 rounded-lg bg-pink-50">
                        <div class="flex items-start">
                            <img src="<?php echo htmlspecialchars($item['image_url'] ?: './assets/image_placeholder.png'); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="w-20 h-20 object-cover rounded mr-4">
                            <div>
                                <h2 class="text-lg font-semibold text-pink-700"><?php echo htmlspecialchars($item['title']); ?></h2>
                                <p class="text-sm text-gray-600 mt-1"><?php echo htmlspecialchars(substr($item['description'], 0, 100)) . (strlen($item['description']) > 100 ? '...' : ''); ?></p>
                                <p class="text-md font-semibold text-pink-500 mt-1">$<?php echo number_format($item['price'], 2); ?></p>
                            </div>
                        </div>
                        <div class="flex flex-col items-end space-y-2">
                             <form action="update_wishlist.php" method="POST" class="inline">
                                <input type="hidden" name="action" value="remove">
                                <input type="hidden" name="game_id" value="<?php echo $item['game_id']; ?>">
                                <button type="submit" class="remove-btn">Remove</button>
                            </form>
                            <button 
                                class="add-to-cart-btn cart-button" 
                                data-game-id="<?php echo $item['game_id']; ?>"
                                onclick="addToCart(this, <?php echo $item['game_id']; ?>)">
                                Add to Cart
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <p class="mt-8 text-center text-sm text-gray-600">
            <a href="./index.php" class="font-medium text-pink-600 hover:text-pink-500 hover:underline">Back to Home</a> | 
            <a href="./catalogue/index.php" class="font-medium text-pink-600 hover:text-pink-500 hover:underline">Continue Shopping</a>
        </p>
    </div>

    <script>
        async function addToCart(button, gameId) {
            // Disable button and show loading state
            button.disabled = true;
            const originalText = button.textContent;
            button.textContent = 'Adding...';

            try {
                const formData = new FormData();
                formData.append('game_id', gameId);
                formData.append('quantity', 1);
                formData.append('action', 'add');

                const response = await fetch('update_cart.php', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    button.textContent = 'Added to Cart!';
                    button.style.backgroundColor = '#28a745';
                    
                    // Show success message
                    showMessage('Game added to cart successfully!', 'success');
                    
                    // Reset button after 2 seconds
                    setTimeout(() => {
                        button.textContent = originalText;
                        button.style.backgroundColor = '#4CAF50';
                        button.disabled = false;
                    }, 2000);
                } else {
                    button.textContent = originalText;
                    button.disabled = false;
                    showMessage('Error: ' + result.message, 'error');
                }
            } catch (error) {
                console.error('Cart error:', error);
                button.textContent = originalText;
                button.disabled = false;
                showMessage('An error occurred. Please try again.', 'error');
            }
        }

        function showMessage(message, type) {
            // Create message element
            const messageDiv = document.createElement('div');
            messageDiv.className = `mb-4 p-4 rounded-lg ${type === 'success' ? 'bg-green-100 border border-green-400 text-green-700' : 'bg-red-100 border border-red-400 text-red-700'}`;
            messageDiv.textContent = message;

            // Insert at the top of the content
            const container = document.querySelector('.container > div');
            const firstChild = container.firstElementChild.nextElementSibling; // After the header
            container.insertBefore(messageDiv, firstChild);

            // Auto-remove after 5 seconds
            setTimeout(() => {
                messageDiv.remove();
            }, 5000);
        }
    </script>
</body>
</html>