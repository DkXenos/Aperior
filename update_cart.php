<?php
session_start();
require 'db_connect.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

$is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

if (!$is_ajax) { 
    $redirect_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'cart.php';
}

// Debug: Log all POST data
error_log("Cart Debug - POST data: " . print_r($_POST, true));
error_log("Cart Debug - Session user_id: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'NOT SET'));

if (!isset($_SESSION['user_id'])) {
    $response = ['success' => false, 'message' => 'User not logged in.'];
    if ($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode($response);
    } else {
        header("Location: login.php");
    }
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response = ['success' => false, 'message' => 'Invalid request method.'];
    if ($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode($response);
    } else {
        header("Location: " . $redirect_url);
    }
    exit();
}

$user_id = $_SESSION['user_id'];
$game_id = isset($_POST['game_id']) ? (int)$_POST['game_id'] : 0;
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
$action = isset($_POST['action']) ? $_POST['action'] : '';

// Debug: Log parsed values
error_log("Cart Debug - Parsed values: user_id=$user_id, game_id=$game_id, quantity=$quantity, action=$action");

if ($game_id <= 0) {
    $response = ['success' => false, 'message' => 'Invalid game ID.'];
    if ($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode($response);
    } else {
        $_SESSION['cart_message'] = 'Invalid game ID.';
        header("Location: " . $redirect_url);
    }
    exit();
}

$response = ['success' => false, 'message' => 'An error occurred.'];

try {
    // Debug: Check if game exists
    $game_check = $conn->prepare("SELECT id, title FROM games WHERE id = ?");
    $game_check->bind_param("i", $game_id);
    $game_check->execute();
    $game_result = $game_check->get_result();
    
    if ($game_result->num_rows === 0) {
        $response = ['success' => false, 'message' => 'Game not found.'];
        error_log("Cart Debug - Game not found: $game_id");
    } else {
        $game_data = $game_result->fetch_assoc();
        error_log("Cart Debug - Game found: " . $game_data['title']);
        
        if ($action === 'add') {
            if ($quantity <= 0) $quantity = 1;
            
            // Check if item already exists in cart
            $stmt_check = $conn->prepare("SELECT quantity FROM cart_items WHERE user_id = ? AND game_id = ?");
            $stmt_check->bind_param("ii", $user_id, $game_id);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();
            
            if ($result_check->num_rows > 0) { 
                // Update existing item
                $existing_item = $result_check->fetch_assoc();
                $new_quantity = $existing_item['quantity'] + $quantity;
                $stmt = $conn->prepare("UPDATE cart_items SET quantity = ? WHERE user_id = ? AND game_id = ?");
                $stmt->bind_param("iii", $new_quantity, $user_id, $game_id);
                error_log("Cart Debug - Updating existing item to quantity: $new_quantity");
            } else { 
                // Insert new item
                $stmt = $conn->prepare("INSERT INTO cart_items (user_id, game_id, quantity) VALUES (?, ?, ?)");
                $stmt->bind_param("iii", $user_id, $game_id, $quantity);
                error_log("Cart Debug - Inserting new item with quantity: $quantity");
            }
            $stmt_check->close();

            if ($stmt->execute()) {
                $response = ['success' => true, 'message' => 'Item added/updated in cart.'];
                error_log("Cart Debug - Success: Item added/updated");
            } else {
                $response = ['success' => false, 'message' => 'Failed to update cart. Error: ' . $stmt->error];
                error_log("Cart Debug - SQL Error: " . $stmt->error);
            }
            
            if (isset($stmt)) $stmt->close();

        } elseif ($action === 'update') {
            if ($quantity <= 0) { 
                 $stmt = $conn->prepare("DELETE FROM cart_items WHERE user_id = ? AND game_id = ?");
                 $stmt->bind_param("ii", $user_id, $game_id);
            } else {
                $stmt = $conn->prepare("UPDATE cart_items SET quantity = ? WHERE user_id = ? AND game_id = ?");
                $stmt->bind_param("iii", $quantity, $user_id, $game_id);
            }
            if ($stmt->execute()) {
                $response = ['success' => true, 'message' => 'Cart updated.'];
            } else {
                $response = ['success' => false, 'message' => 'Failed to update cart. Error: ' . $stmt->error];
            }
            
            if (isset($stmt)) $stmt->close();

        } elseif ($action === 'remove') {
            $stmt = $conn->prepare("DELETE FROM cart_items WHERE user_id = ? AND game_id = ?");
            $stmt->bind_param("ii", $user_id, $game_id);
            
            if ($stmt->execute()) {
                $response = ['success' => true, 'message' => 'Item removed from cart.'];
            } else {
                $response = ['success' => false, 'message' => 'Failed to remove item. Error: ' . $stmt->error];
            }
            
            if (isset($stmt)) $stmt->close();

        } else {
            $response = ['success' => false, 'message' => 'Invalid action.'];
            error_log("Cart Debug - Invalid action: $action");
        }
    }
    $game_check->close();
    
} catch (Exception $e) { 
    error_log("Cart Debug - Exception: " . $e->getMessage());
    $response = ['success' => false, 'message' => 'A server-side error occurred: ' . $e->getMessage()];
} finally {
    if (isset($stmt_check) && $stmt_check instanceof mysqli_stmt) {
        $stmt_check->close();
    }
}

if (isset($conn) && $conn instanceof mysqli && $conn->ping()) {
    $conn->close();
}

if ($is_ajax) {
    header('Content-Type: application/json');
    echo json_encode($response);
} else {
    $_SESSION['cart_message'] = $response['message']; 
    header("Location: " . $redirect_url);
}
exit();
?>