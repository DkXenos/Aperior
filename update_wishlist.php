<?php
session_start();
require 'db_connect.php';

header('Content-Type: application/json'); // Important for AJAX responses

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$game_id = isset($_POST['game_id']) ? (int)$_POST['game_id'] : 0;
$action = isset($_POST['action']) ? $_POST['action'] : ''; // 'add' or 'remove'

if ($game_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid game ID.']);
    exit();
}

if ($action === 'add') {
    $stmt = $conn->prepare("INSERT INTO wishlist_items (user_id, game_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE game_id=game_id"); // Ignore if already exists
    $stmt->bind_param("ii", $user_id, $game_id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Added to wishlist.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add to wishlist. Error: ' . $stmt->error]);
    }
    $stmt->close();
} elseif ($action === 'remove') {
    $stmt = $conn->prepare("DELETE FROM wishlist_items WHERE user_id = ? AND game_id = ?");
    $stmt->bind_param("ii", $user_id, $game_id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Removed from wishlist.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to remove from wishlist. Error: ' . $stmt->error]);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action.']);
}

$conn->close();
?>