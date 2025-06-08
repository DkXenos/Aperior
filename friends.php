<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$current_user_id = $_SESSION['user_id'];
$users = [];

// Get all users except the current user
$stmt = $conn->prepare("SELECT id, username, email FROM users WHERE id != ? ORDER BY username ASC");
$stmt->bind_param("i", $current_user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
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
    <title>Friends - Aprerior</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="./styles.css">
    <style>
        .user-card {
            transition: all 0.2s ease;
        }
        .user-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .friend-btn {
            background-color: #4CAF50;
            color: white;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 500;
            transition: background-color 0.3s;
        }
        .friend-btn:hover {
            background-color: #45a049;
        }
        .message-btn {
            background-color: #2196F3;
            color: white;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 500;
            margin-left: 8px;
            transition: background-color 0.3s;
        }
        .message-btn:hover {
            background-color: #1976D2;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-[#FFF7AD] to-[#FFA9F9] min-h-screen py-8">
    <div class="container mx-auto max-w-4xl bg-white/90 backdrop-blur-md p-6 md:p-8 rounded-xl shadow-2xl">
        <div class="flex items-center mb-6">
            <img src="./assets/aperior.svg" alt="Aperior Logo" class="w-16 h-16 mr-4"/>
            <h1 class="text-3xl font-bold text-pink-600 apply-custom-title-font">Find Friends</h1>
        </div>

        <div class="mb-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <p class="text-gray-600">Connect with other Aperior users</p>
                <div class="flex items-center space-x-2">
                    <i class="fas fa-users text-pink-500"></i>
                    <span class="text-sm text-gray-500"><?php echo count($users); ?> users available</span>
                </div>
            </div>
        </div>

        <?php if (empty($users)): ?>
            <div class="text-center text-gray-700 p-10 border-2 border-dashed border-pink-300 rounded-lg">
                <i class="fas fa-user-friends text-4xl text-pink-400 mb-4"></i>
                <h2 class="text-xl font-semibold mb-2">No Other Users Yet</h2>
                <p>You're one of the first users! Invite friends to join Aperior.</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php foreach ($users as $user): ?>
                    <div class="user-card bg-white border border-pink-200 rounded-lg p-4 shadow-sm">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center flex-1">
                                <div class="w-12 h-12 bg-gradient-to-br from-pink-400 to-purple-500 rounded-full flex items-center justify-center text-white font-bold text-lg mr-3">
                                    <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-lg font-semibold text-gray-800 truncate">
                                        <?php echo htmlspecialchars($user['username']); ?>
                                    </h3>
                                    <p class="text-sm text-gray-500 truncate">
                                        <?php echo htmlspecialchars($user['email']); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4 flex flex-col space-y-2">
                            <button class="friend-btn w-full" onclick="addFriend(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>')">
                                <i class="fas fa-user-plus mr-2"></i>Add Friend
                            </button>
                            <button class="message-btn w-full" onclick="sendMessage(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>')">
                                <i class="fas fa-envelope mr-2"></i>Message
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Search/Filter Section -->
            <div class="mt-8 bg-pink-50 rounded-lg p-4">
                <h3 class="text-lg font-semibold text-pink-700 mb-3">Find Specific Users</h3>
                <div class="flex flex-col sm:flex-row gap-2">
                    <input 
                        type="text" 
                        id="userSearch" 
                        placeholder="Search by username..." 
                        class="flex-1 px-3 py-2 border border-pink-300 rounded-md focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-pink-500"
                        onkeyup="filterUsers()"
                    >
                    <button 
                        onclick="clearSearch()" 
                        class="px-4 py-2 bg-pink-500 text-white rounded-md hover:bg-pink-600 transition-colors"
                    >
                        Clear
                    </button>
                </div>
            </div>
        <?php endif; ?>

        <p class="mt-8 text-center text-sm text-gray-600">
            <a href="./index.php" class="font-medium text-pink-600 hover:text-pink-500 hover:underline">Back to Home</a> | 
            <a href="./catalogue/index.php" class="font-medium text-pink-600 hover:text-pink-500 hover:underline">Browse Catalogue</a>
        </p>
    </div>

    <script>
        function addFriend(userId, username) {
            // For now, just show an alert. You can implement actual friend request functionality later
            alert(`Friend request sent to ${username}! (Feature coming soon)`);
        }

        function sendMessage(userId, username) {
            // For now, just show an alert. You can implement messaging functionality later
            alert(`Opening chat with ${username}! (Feature coming soon)`);
        }

        function filterUsers() {
            const searchTerm = document.getElementById('userSearch').value.toLowerCase();
            const userCards = document.querySelectorAll('.user-card');
            
            userCards.forEach(card => {
                const username = card.querySelector('h3').textContent.toLowerCase();
                const email = card.querySelector('p').textContent.toLowerCase();
                
                if (username.includes(searchTerm) || email.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        function clearSearch() {
            document.getElementById('userSearch').value = '';
            const userCards = document.querySelectorAll('.user-card');
            userCards.forEach(card => {
                card.style.display = 'block';
            });
        }

        // Add enter key support for search
        document.getElementById('userSearch')?.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                filterUsers();
            }
        });
    </script>
</body>
</html>