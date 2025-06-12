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


$conn->begin_transaction();

try {
    
    $cart_items_stmt = $conn->prepare("SELECT game_id, quantity FROM cart_items WHERE user_id = ?");
    $cart_items_stmt->bind_param("i", $user_id);
    $cart_items_stmt->execute();
    $cart_result = $cart_items_stmt->get_result();

    if ($cart_result->num_rows === 0) {
        $_SESSION['payment_message'] = "Your cart is empty. Nothing to process.";
        $conn->rollback(); 
        header("Location: payment.php");
        exit();
    }

    
    
     //INSERT DISINI
    $insert_inventory_stmt = $conn->prepare("INSERT IGNORE INTO user_inventory (user_id, game_id, purchase_date) VALUES (?, ?, NOW())");
    
    while ($item = $cart_result->fetch_assoc()) {
        $insert_inventory_stmt->bind_param("ii", $user_id, $item['game_id']);
        if (!$insert_inventory_stmt->execute()) {
            throw new Exception("Failed to add game ID " . $item['game_id'] . " to inventory: " . $insert_inventory_stmt->error);
        }
    }
    $insert_inventory_stmt->close();
    $cart_items_stmt->close();

    
    $clear_cart_stmt = $conn->prepare("DELETE FROM cart_items WHERE user_id = ?");
    $clear_cart_stmt->bind_param("i", $user_id);
    if (!$clear_cart_stmt->execute()) {
        throw new Exception("Failed to clear cart: " . $clear_cart_stmt->error);
    }
    $clear_cart_stmt->close();

    
    $conn->commit();
    $_SESSION['payment_message'] = "Payment successful! Games have been added to your inventory.";
    header("Location: inventory.php"); 

} catch (Exception $e) {
    $conn->rollback(); 
    error_log("Payment processing error for user $user_id: " . $e->getMessage());
    $_SESSION['payment_message'] = "An error occurred during payment processing. Please try again. Details: " . $e->getMessage();
    header("Location: payment.php"); 
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
exit();
?>