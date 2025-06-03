<?php
session_start();
require 'db_connect.php';

// Determine if AJAX or form submission for redirection
$is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

if (!$is_ajax) { // If it's a form submission, set a default redirect
    $redirect_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'cart.php';
}

if (!isset($_SESSION['user_id'])) {
    if ($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    } else {
        header("Location: login.php");
    }
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if ($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    } else {
        header("Location: " . $redirect_url);
    }
    exit();
}

$user_id = $_SESSION['user_id'];
$game_id = isset($_POST['game_id']) ? (int)$_POST['game_id'] : 0;
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
$action = isset($_POST['action']) ? $_POST['action'] : ''; // 'add', 'update', 'remove'

if ($game_id <= 0) {
    if ($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid game ID.']);
    } else {
        $_SESSION['cart_message'] = 'Invalid game ID.';
        header("Location: " . $redirect_url);
    }
    exit();
}

$response = ['success' => false, 'message' => 'An error occurred.'];

try {
    if ($action === 'add') {
        if ($quantity <= 0) $quantity = 1;
        // Check if item already in cart
        $stmt_check = $conn->prepare("SELECT quantity FROM cart_items WHERE user_id = ? AND game_id = ?");
        $stmt_check->bind_param("ii", $user_id, $game_id);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        if ($result_check->num_rows > 0) { // Item exists, update quantity
            $existing_item = $result_check->fetch_assoc();
            $new_quantity = $existing_item['quantity'] + $quantity;
            $stmt = $conn->prepare("UPDATE cart_items SET quantity = ? WHERE user_id = ? AND game_id = ?");
            $stmt->bind_param("iii", $new_quantity, $user_id, $game_id);
        } else { // Item not in cart, insert new
            $stmt = $conn->prepare("INSERT INTO cart_items (user_id, game_id, quantity) VALUES (?, ?, ?)");
            $stmt->bind_param("iii", $user_id, $game_id, $quantity);
        }
        $stmt_check->close();

        if ($stmt->execute()) {
            $response = ['success' => true, 'message' => 'Item added/updated in cart.'];
        } else {
            $response = ['success' => false, 'message' => 'Failed to update cart. Error: ' . $stmt->error];
        }
        // Ensure $stmt is closed if it was prepared
        if (isset($stmt)) $stmt->close();


    } elseif ($action === 'update') {
        if ($quantity <= 0) { // If quantity is 0 or less, remove item
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
        // Ensure $stmt is closed if it was prepared
        if (isset($stmt)) $stmt->close();

    } elseif ($action === 'remove') {
        $stmt = $conn->prepare("DELETE FROM cart_items WHERE user_id = ? AND game_id = ?");
        $stmt->bind_param("ii", $user_id, $game_id);
        if ($stmt->execute()) {
            $response = ['success' => true, 'message' => 'Item removed from cart.'];
        } else {
            $response = ['success' => false, 'message' => 'Failed to remove item. Error: ' . $stmt->error];
        }
        // Ensure $stmt is closed if it was prepared
        if (isset($stmt)) $stmt->close();

    } else {
        $response = ['success' => false, 'message' => 'Invalid action.'];
    }
} catch (Throwable $e) { // Catch any throwable error or exception
    // Log detailed error information
    error_log(
        "Critical Error in update_cart.php: " . $e->getMessage() . "\n" .
        "File: " . $e->getFile() . "\n" .
        "Line: " . $e->getLine() . "\n" .
        "Trace: " . $e->getTraceAsString()
    );
    // Set a generic error response for the client
    $response = ['success' => false, 'message' => 'A server-side error occurred. Please try again later.'];
} finally {
    // Close $stmt_check if it was initialized in the 'add' action
    if (isset($stmt_check) && $stmt_check instanceof mysqli_stmt) {
        $stmt_check->close();
    }
    // The main $stmt is now closed within each action block if prepared.
    // Connection is closed after sending the response.
}

// Close connection before sending output, if not already closed by an error path
if (isset($conn) && $conn instanceof mysqli && $conn->ping()) {
    $conn->close();
}


if ($is_ajax) {
    header('Content-Type: application/json');
    echo json_encode($response);
} else {
    $_SESSION['cart_message'] = $response['message']; // Store message for display on redirect
    header("Location: " . $redirect_url);
}
exit();