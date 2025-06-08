<?php
session_start();
require 'db_connect.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$friend_id = isset($_POST['friend_id']) ? (int)$_POST['friend_id'] : 0;
$action = isset($_POST['action']) ? $_POST['action'] : '';
$game_id = isset($_POST['game_id']) ? (int)$_POST['game_id'] : 0;

// Debug: Log the received data
error_log("Gift Debug: user_id=$user_id, friend_id=$friend_id, action=$action, game_id=$game_id");

if ($friend_id <= 0 || $friend_id == $user_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid friend ID.']);
    exit();
}

try {
    // First, let's check if the required tables exist
    $tables_check = $conn->query("SHOW TABLES LIKE 'friends'");
    if ($tables_check->num_rows == 0) {
        echo json_encode(['success' => false, 'message' => 'Friends table does not exist. Please create it first.']);
        exit();
    }

    $inventory_check = $conn->query("SHOW TABLES LIKE 'user_inventory'");
    if ($inventory_check->num_rows == 0) {
        echo json_encode(['success' => false, 'message' => 'User inventory table does not exist. Please create it first.']);
        exit();
    }

    if ($action === 'add') {
        // Check if friendship already exists
        $check_stmt = $conn->prepare("
            SELECT id FROM friends 
            WHERE (user_id = ? AND friend_id = ?) OR (user_id = ? AND friend_id = ?)
        ");
        $check_stmt->bind_param("iiii", $user_id, $friend_id, $friend_id, $user_id);
        $check_stmt->execute();
        $existing = $check_stmt->get_result();
        
        if ($existing->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Friendship already exists.']);
        } else {
            // Add new friendship (automatically accepted for simplicity)
            $stmt = $conn->prepare("INSERT INTO friends (user_id, friend_id, status) VALUES (?, ?, 'accepted')");
            $stmt->bind_param("ii", $user_id, $friend_id);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Friend added successfully.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to add friend: ' . $stmt->error]);
            }
            $stmt->close();
        }
        $check_stmt->close();
        
    } elseif ($action === 'remove') {
        // Remove friendship (both directions)
        $stmt = $conn->prepare("
            DELETE FROM friends 
            WHERE (user_id = ? AND friend_id = ?) OR (user_id = ? AND friend_id = ?)
        ");
        $stmt->bind_param("iiii", $user_id, $friend_id, $friend_id, $user_id);
        
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                echo json_encode(['success' => true, 'message' => 'Friend removed successfully.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Friendship not found.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to remove friend: ' . $stmt->error]);
        }
        $stmt->close();
        
    } elseif ($action === 'gift') {
        if ($game_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid game ID.']);
            exit();
        }
        
        // Check if user owns the game
        $check_ownership = $conn->prepare("SELECT id FROM user_inventory WHERE user_id = ? AND game_id = ?");
        if (!$check_ownership) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
            exit();
        }
        
        $check_ownership->bind_param("ii", $user_id, $game_id);
        $check_ownership->execute();
        $owns_game = $check_ownership->get_result();
        
        if ($owns_game->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'You do not own this game.']);
            $check_ownership->close();
            exit();
        }
        $check_ownership->close();
        
        // Check if friend already owns the game
        $check_friend_ownership = $conn->prepare("SELECT id FROM user_inventory WHERE user_id = ? AND game_id = ?");
        $check_friend_ownership->bind_param("ii", $friend_id, $game_id);
        $check_friend_ownership->execute();
        $friend_owns_game = $check_friend_ownership->get_result();
        
        if ($friend_owns_game->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Your friend already owns this game.']);
            $check_friend_ownership->close();
            exit();
        }
        $check_friend_ownership->close();
        
        // Check if they are actually friends
        $check_friendship = $conn->prepare("
            SELECT id FROM friends 
            WHERE ((user_id = ? AND friend_id = ?) OR (user_id = ? AND friend_id = ?)) 
            AND status = 'accepted'
        ");
        $check_friendship->bind_param("iiii", $user_id, $friend_id, $friend_id, $user_id);
        $check_friendship->execute();
        $friendship_exists = $check_friendship->get_result();
        
        if ($friendship_exists->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'You can only gift games to friends.']);
            $check_friendship->close();
            exit();
        }
        $check_friendship->close();
        
        // Add the game to friend's inventory
        $gift_stmt = $conn->prepare("INSERT INTO user_inventory (user_id, game_id, purchase_date) VALUES (?, ?, NOW())");
        $gift_stmt->bind_param("ii", $friend_id, $game_id);
        
        if ($gift_stmt->execute()) {
            // Get the game title for the response
            $game_title_stmt = $conn->prepare("SELECT title FROM games WHERE id = ?");
            $game_title_stmt->bind_param("i", $game_id);
            $game_title_stmt->execute();
            $game_result = $game_title_stmt->get_result();
            
            if ($game_result->num_rows > 0) {
                $game_title = $game_result->fetch_assoc()['title'];
                echo json_encode(['success' => true, 'message' => "Successfully gifted '{$game_title}' to your friend!"]);
            } else {
                echo json_encode(['success' => true, 'message' => "Game gifted successfully!"]);
            }
            $game_title_stmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to gift the game: ' . $gift_stmt->error]);
        }
        $gift_stmt->close();
        
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action: ' . $action]);
    }
    
} catch (Exception $e) {
    error_log("Friend management error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>