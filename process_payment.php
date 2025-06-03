<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['payment_message'] = "Error: You must be logged in to make a purchase.";
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['payment_message'] = "Error: Invalid request method.";
    header("Location: payment.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Start transaction
$conn->begin_transaction();

try {
    // 1. Get items from user's cart
    $cart_items_stmt = $conn->prepare("SELECT game_id, quantity FROM cart_items WHERE user_id = ?");
    $cart_items_stmt->bind_param("i", $user_id);
    $cart_items_stmt->execute();
    $cart_result = $cart_items_stmt->get_result();

    if ($cart_result->num_rows === 0) {
        $_SESSION['payment_message'] = "Your cart is empty. Nothing to process.";
        $conn->rollback(); // Rollback if cart is empty
        header("Location: payment.php");
        exit();
    }

    // 2. Add each item to user_inventory (simplified: adds each game once, ignores quantity for ownership)
    // More complex logic could handle multiple copies if your game store supports that.
    // For simplicity, we assume owning a game means having at least one copy.
    $insert_inventory_stmt = $conn->prepare("INSERT IGNORE INTO user_inventory (user_id, game_id, purchase_date) VALUES (?, ?, NOW())");
    
    while ($item = $cart_result->fetch_assoc()) {
        $insert_inventory_stmt->bind_param("ii", $user_id, $item['game_id']);
        if (!$insert_inventory_stmt->execute()) {
            throw new Exception("Failed to add game ID " . $item['game_id'] . " to inventory: " . $insert_inventory_stmt->error);
        }
    }
    $insert_inventory_stmt->close();
    $cart_items_stmt->close();

    // 3. Clear the user's cart
    $clear_cart_stmt = $conn->prepare("DELETE FROM cart_items WHERE user_id = ?");
    $clear_cart_stmt->bind_param("i", $user_id);
    if (!$clear_cart_stmt->execute()) {
        throw new Exception("Failed to clear cart: " . $clear_cart_stmt->error);
    }
    $clear_cart_stmt->close();

    // If all operations were successful, commit the transaction
    $conn->commit();
    $_SESSION['payment_message'] = "Payment successful! Games have been added to your inventory.";
    header("Location: inventory.php"); // Redirect to inventory page

} catch (Exception $e) {
    $conn->rollback(); // Rollback on any error
    error_log("Payment processing error for user $user_id: " . $e->getMessage());
    $_SESSION['payment_message'] = "An error occurred during payment processing. Please try again. Details: " . $e->getMessage();
    header("Location: payment.php"); // Redirect back to payment page with error
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
exit();
?>