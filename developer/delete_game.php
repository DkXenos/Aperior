<?php
session_start();
require '../db_connect.php';

if (!isset($_SESSION['developer_id'])) {
    header("Location: login.php");
    exit();
}

$developer_id = $_SESSION['developer_id'];
$game_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($game_id <= 0) {
    $_SESSION['error_message'] = "Invalid game ID.";
    header("Location: dashboard.php");
    exit();
}

// Verify that the game belongs to this developer
$verify_stmt = $conn->prepare("SELECT title FROM games WHERE id = ? AND developer_id = ?");
$verify_stmt->bind_param("ii", $game_id, $developer_id);
$verify_stmt->execute();
$result = $verify_stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error_message'] = "Game not found or you don't have permission to delete it.";
    header("Location: dashboard.php");
    exit();
}

$game = $result->fetch_assoc();
$game_title = $game['title'];
$verify_stmt->close();

// Handle the deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    try {
        $conn->begin_transaction();
        
        // Remove from user wishlists
        $remove_wishlist = $conn->prepare("DELETE FROM wishlist_items WHERE game_id = ?");
        $remove_wishlist->bind_param("i", $game_id);
        $remove_wishlist->execute();
        $remove_wishlist->close();
        
        // Remove from user carts
        $remove_cart = $conn->prepare("DELETE FROM cart_items WHERE game_id = ?");
        $remove_cart->bind_param("i", $game_id);
        $remove_cart->execute();
        $remove_cart->close();
        
        // Note: We might want to keep user_inventory records for purchased games
        // or handle refunds. For now, we'll remove them as well.
        $remove_inventory = $conn->prepare("DELETE FROM user_inventory WHERE game_id = ?");
        $remove_inventory->bind_param("i", $game_id);
        $remove_inventory->execute();
        $remove_inventory->close();
        
        // Finally, delete the game
        $delete_game = $conn->prepare("DELETE FROM games WHERE id = ? AND developer_id = ?");
        $delete_game->bind_param("ii", $game_id, $developer_id);
        $delete_game->execute();
        $delete_game->close();
        
        $conn->commit();
        $_SESSION['success_message'] = "Game '{$game_title}' has been deleted successfully.";
        header("Location: dashboard.php");
        exit();
        
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error_message'] = "Error deleting game: " . $e->getMessage();
        header("Location: dashboard.php");
        exit();
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Game - Aperior Developer</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../styles.css">
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="max-w-md mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="text-center mb-6">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-trash text-2xl text-red-600"></i>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 mb-2">Delete Game</h1>
                <p class="text-gray-600">Are you sure you want to delete this game?</p>
            </div>

            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                <h3 class="font-semibold text-red-800 mb-2"><?php echo htmlspecialchars($game_title); ?></h3>
                <div class="text-sm text-red-700">
                    <p class="mb-2"><strong>Warning:</strong> This action cannot be undone.</p>
                    <p>This will:</p>
                    <ul class="list-disc list-inside mt-2 space-y-1">
                        <li>Remove the game from the marketplace</li>
                        <li>Remove it from all user wishlists and carts</li>
                        <li>Remove it from user inventories (if purchased)</li>
                        <li>Delete all associated data permanently</li>
                    </ul>
                </div>
            </div>

            <form method="POST" class="space-y-4">
                <div class="flex space-x-3">
                    <a href="dashboard.php" 
                       class="flex-1 px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors text-center">
                        <i class="fas fa-arrow-left mr-2"></i>Cancel
                    </a>
                    <button type="submit" name="confirm_delete" value="1"
                            class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-md transition-colors"
                            onclick="return confirm('Are you absolutely sure you want to delete \'<?php echo addslashes($game_title); ?>\'? This cannot be undone!')">
                        <i class="fas fa-trash mr-2"></i>Delete Game
                    </button>
                </div>
            </form>

            <div class="mt-6 text-center">
                <p class="text-xs text-gray-500">
                    Game ID: <?php echo $game_id; ?> | Developer ID: <?php echo $developer_id; ?>
                </p>
            </div>
        </div>
    </div>
</body>
</html>