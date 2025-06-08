<?php
session_start();
require 'db_connect.php';

// Check if this is an AJAX request
$is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

// Only set JSON header for AJAX requests
if ($is_ajax) {
    header('Content-Type: application/json');
}

if (!isset($_SESSION['user_id'])) {
    if ($is_ajax) {
        echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    } else {
        header("Location: login.php");
    }
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if ($is_ajax) {
        echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    } else {
        header("Location: wishlist.php");
    }
    exit();
}

$user_id = $_SESSION['user_id'];
$game_id = isset($_POST['game_id']) ? (int)$_POST['game_id'] : 0;
$action = isset($_POST['action']) ? $_POST['action'] : ''; 

if ($game_id <= 0) {
    if ($is_ajax) {
        echo json_encode(['success' => false, 'message' => 'Invalid game ID.']);
    } else {
        $_SESSION['wishlist_message'] = 'Invalid game ID.';
        header("Location: wishlist.php");
    }
    exit();
}

if ($action === 'add') {
    $stmt = $conn->prepare("INSERT INTO wishlist_items (user_id, game_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE game_id=game_id"); 
    $stmt->bind_param("ii", $user_id, $game_id);
    
    if ($stmt->execute()) {
        if ($is_ajax) {
            echo json_encode(['success' => true, 'message' => 'Added to wishlist.']);
        } else {
            $_SESSION['wishlist_message'] = 'Game added to wishlist successfully!';
            header("Location: wishlist.php");
        }
    } else {
        if ($is_ajax) {
            echo json_encode(['success' => false, 'message' => 'Failed to add to wishlist. Error: ' . $stmt->error]);
        } else {
            $_SESSION['wishlist_message'] = 'Failed to add to wishlist.';
            header("Location: wishlist.php");
        }
    }
    $stmt->close();
    
} elseif ($action === 'remove') {
    $stmt = $conn->prepare("DELETE FROM wishlist_items WHERE user_id = ? AND game_id = ?");
    $stmt->bind_param("ii", $user_id, $game_id);
    
    if ($stmt->execute()) {
        if ($is_ajax) {
            echo json_encode(['success' => true, 'message' => 'Removed from wishlist.']);
        } else {
            $_SESSION['wishlist_message'] = 'Game removed from wishlist successfully!';
            header("Location: wishlist.php");
        }
    } else {
        if ($is_ajax) {
            echo json_encode(['success' => false, 'message' => 'Failed to remove from wishlist. Error: ' . $stmt->error]);
        } else {
            $_SESSION['wishlist_message'] = 'Failed to remove from wishlist.';
            header("Location: wishlist.php");
        }
    }
    $stmt->close();
    
} else {
    if ($is_ajax) {
        echo json_encode(['success' => false, 'message' => 'Invalid action.']);
    } else {
        $_SESSION['wishlist_message'] = 'Invalid action.';
        header("Location: wishlist.php");
    }
}

$conn->close();
exit();
?>