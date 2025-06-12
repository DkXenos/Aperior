<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$current_user_id = $_SESSION['user_id'];
$users = [];
$friends = [];
$user_games = [];

// Get all users except the current user and their existing friends
$stmt = $conn->prepare("
    SELECT u.id, u.username, u.email 
    FROM users u 
    WHERE u.id != ? 
    AND u.id NOT IN (
        SELECT friend_id FROM friends 
        WHERE user_id = ? AND status = 'accepted'
        UNION
        SELECT user_id FROM friends 
        WHERE friend_id = ? AND status = 'accepted'
    )
    ORDER BY u.username ASC
");
$stmt->bind_param("iii", $current_user_id, $current_user_id, $current_user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    $result->free();
}
$stmt->close();

// Get current friends (accepted friendships)
$stmt = $conn->prepare("
    SELECT u.id, u.username, u.email, f.created_at as friend_since
    FROM users u
    INNER JOIN friends f ON (
        (f.user_id = ? AND f.friend_id = u.id) OR 
        (f.friend_id = ? AND f.user_id = u.id)
    )
    WHERE f.status = 'accepted' AND u.id != ?
    ORDER BY u.username ASC
");
$stmt->bind_param("iii", $current_user_id, $current_user_id, $current_user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $friends[] = $row;
    }
    $result->free();
}
$stmt->close();

// Get user's owned games for gifting
$stmt = $conn->prepare("
    SELECT g.id, g.title, g.image_url 
    FROM user_inventory ui
    JOIN games g ON ui.game_id = g.id 
    WHERE ui.user_id = ?
    ORDER BY g.title ASC
");
$stmt->bind_param("i", $current_user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $user_games[] = $row;
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
        .user-card, .friend-card {
            transition: all 0.2s ease;
        }
        .user-card:hover, .friend-card:hover {
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
        .gift-btn {
            background-color: #FFD700;
            color: #333;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 500;
            transition: background-color 0.3s;
        }
        .gift-btn:hover {
            background-color: #FFC107;
        }
        .remove-friend-btn {
            background-color: #DC2626;
            color: white;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 500;
            transition: background-color 0.3s;
        }
        .remove-friend-btn:hover {
            background-color: #B91C1C;
        }
        .tab-button {
            transition: all 0.3s ease;
        }
        .tab-button.active {
            background-color: #EC4899;
            color: white;
            transform: translateY(-2px);
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        .modal {
            display: none;
        }
        .modal.active {
            display: flex;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-[#FFF7AD] to-[#FFA9F9] min-h-screen py-8">
    <div class="container mx-auto max-w-4xl bg-white/90 backdrop-blur-md p-6 md:p-8 rounded-xl shadow-2xl">
        <div class="flex items-center mb-6">
            <img src="./assets/aperior.svg" alt="Aperior Logo" class="w-16 h-16 mr-4"/>
            <h1 class="text-3xl font-bold text-pink-600 apply-custom-title-font">Friends</h1>
        </div>

        
        <div class="flex space-x-2 mb-6">
            <button 
                class="tab-button active px-6 py-3 bg-pink-100 text-pink-700 rounded-lg font-medium hover:bg-pink-200 transition-colors"
                onclick="switchTab('find-friends')"
                id="find-friends-tab"
            >
                <i class="fas fa-search mr-2"></i>Find Friends
                <span class="ml-2 bg-pink-200 text-pink-800 text-xs px-2 py-1 rounded-full"><?php echo count($users); ?></span>
            </button>
            <button 
                class="tab-button px-6 py-3 bg-pink-100 text-pink-700 rounded-lg font-medium hover:bg-pink-200 transition-colors"
                onclick="switchTab('my-friends')"
                id="my-friends-tab"
            >
                <i class="fas fa-users mr-2"></i>My Friends
                <span class="ml-2 bg-pink-200 text-pink-800 text-xs px-2 py-1 rounded-full"><?php echo count($friends); ?></span>
            </button>
        </div>

        
        <div id="find-friends-content" class="tab-content active">
            <div class="mb-6">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <p class="text-gray-600">Connect with other Aperior users</p>
                    <div class="flex items-center space-x-2">
                        <i class="fas fa-user-plus text-pink-500"></i>
                        <span class="text-sm text-gray-500"><?php echo count($users); ?> users available</span>
                    </div>
                </div>
            </div>

            <?php if (empty($users)): ?>
                <div class="text-center text-gray-700 p-10 border-2 border-dashed border-pink-300 rounded-lg">
                    <i class="fas fa-user-friends text-4xl text-pink-400 mb-4"></i>
                    <h2 class="text-xl font-semibold mb-2">No More Users Available</h2>
                    <p>You've either added all users as friends or you're one of the first users! Invite more friends to join Aperior.</p>
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
                            
                            <div class="mt-4">
                                <button class="friend-btn w-full" onclick="addFriend(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>')">
                                    <i class="fas fa-user-plus mr-2"></i>Add Friend
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                
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
        </div>

        
        <div id="my-friends-content" class="tab-content">
            <div class="mb-6">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <p class="text-gray-600">Your current friends on Aperior</p>
                    <div class="flex items-center space-x-2">
                        <i class="fas fa-heart text-pink-500"></i>
                        <span class="text-sm text-gray-500"><?php echo count($friends); ?> friends</span>
                    </div>
                </div>
            </div>

            <?php if (empty($friends)): ?>
                <div class="text-center text-gray-700 p-10 border-2 border-dashed border-pink-300 rounded-lg">
                    <i class="fas fa-heart text-4xl text-pink-400 mb-4"></i>
                    <h2 class="text-xl font-semibold mb-2">No Friends Yet</h2>
                    <p>Start building your friend network by adding some users!</p>
                    <button 
                        onclick="switchTab('find-friends')" 
                        class="mt-4 px-6 py-2 bg-pink-500 text-white rounded-lg hover:bg-pink-600 transition-colors"
                    >
                        Find Friends
                    </button>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php foreach ($friends as $friend): ?>
                        <div class="friend-card bg-white border border-green-200 rounded-lg p-4 shadow-sm">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center flex-1">
                                    <div class="w-12 h-12 bg-gradient-to-br from-green-400 to-blue-500 rounded-full flex items-center justify-center text-white font-bold text-lg mr-3">
                                        <?php echo strtoupper(substr($friend['username'], 0, 1)); ?>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h3 class="text-lg font-semibold text-gray-800 truncate">
                                            <?php echo htmlspecialchars($friend['username']); ?>
                                        </h3>
                                        <p class="text-sm text-gray-500 truncate">
                                            <?php echo htmlspecialchars($friend['email']); ?>
                                        </p>
                                        <p class="text-xs text-green-600">
                                            <i class="fas fa-calendar mr-1"></i>
                                            Friends since <?php echo date("M Y", strtotime($friend['friend_since'])); ?>
                                        </p>
                                    </div>
                                </div>
                                <div class="ml-2">
                                    <i class="fas fa-check-circle text-green-500 text-lg" title="Friend"></i>
                                </div>
                            </div>
                            
                            <div class="mt-4 flex flex-col space-y-2">
                                <button class="gift-btn w-full" onclick="openGiftModal(<?php echo $friend['id']; ?>, '<?php echo htmlspecialchars($friend['username']); ?>')">
                                    <i class="fas fa-gift mr-2"></i>Send Gift
                                </button>
                                <button class="remove-friend-btn w-full" onclick="removeFriend(<?php echo $friend['id']; ?>, '<?php echo htmlspecialchars($friend['username']); ?>')">
                                    <i class="fas fa-user-minus mr-2"></i>Remove Friend
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                
                <div class="mt-8 bg-green-50 rounded-lg p-4">
                    <h3 class="text-lg font-semibold text-green-700 mb-3">Search Your Friends</h3>
                    <div class="flex flex-col sm:flex-row gap-2">
                        <input 
                            type="text" 
                            id="friendSearch" 
                            placeholder="Search friends by username..." 
                            class="flex-1 px-3 py-2 border border-green-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                            onkeyup="filterFriends()"
                        >
                        <button 
                            onclick="clearFriendSearch()" 
                            class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 transition-colors"
                        >
                            Clear
                        </button>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <p class="mt-8 text-center text-sm text-gray-600">
            <a href="./index.php" class="font-medium text-pink-600 hover:text-pink-500 hover:underline">Back to Home</a> | 
            <a href="./catalogue/index.php" class="font-medium text-pink-600 hover:text-pink-500 hover:underline">Browse Catalogue</a>
        </p>
    </div>

    
    <div id="giftModal" class="modal fixed inset-0 bg-black bg-opacity-50 items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Send Gift to <span id="giftRecipientName"></span></h3>
                <button onclick="closeGiftModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <?php if (empty($user_games)): ?>
                <div class="text-center py-8">
                    <i class="fas fa-gamepad text-4xl text-gray-400 mb-4"></i>
                    <p class="text-gray-600">You don't own any games to gift yet.</p>
                    <a href="./catalogue/index.php" class="mt-4 inline-block bg-pink-500 text-white px-4 py-2 rounded hover:bg-pink-600">
                        Browse Games
                    </a>
                </div>
            <?php else: ?>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Select a game to gift:</label>
                    <select id="gameSelect" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-pink-500">
                        <option value="">Choose a game...</option>
                        <?php foreach ($user_games as $game): ?>
                            <option value="<?php echo $game['id']; ?>"><?php echo htmlspecialchars($game['title']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="flex space-x-3">
                    <button onclick="sendGift()" class="flex-1 bg-yellow-500 hover:bg-yellow-600 text-white font-medium py-2 px-4 rounded transition-colors">
                        <i class="fas fa-gift mr-2"></i>Send Gift
                    </button>
                    <button onclick="closeGiftModal()" class="px-4 py-2 border border-gray-300 rounded hover:bg-gray-50 transition-colors">
                        Cancel
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        let currentGiftRecipientId = null;

        function switchTab(tabName) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            
            // Remove active class from all tab buttons
            document.querySelectorAll('.tab-button').forEach(button => {
                button.classList.remove('active');
            });
            
            // Show selected tab content
            document.getElementById(tabName + '-content').classList.add('active');
            
            // Add active class to selected tab button
            document.getElementById(tabName + '-tab').classList.add('active');
        }

        function addFriend(userId, username) {
            if (confirm(`Send friend request to ${username}?`)) {
                fetch('manage_friends.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=add&friend_id=${userId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(`Friend request sent to ${username}!`);
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                });
            }
        }

        function removeFriend(userId, username) {
            if (confirm(`Remove ${username} from your friends?`)) {
                fetch('manage_friends.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=remove&friend_id=${userId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(`${username} has been removed from your friends.`);
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                });
            }
        }

        function openGiftModal(friendId, friendName) {
            currentGiftRecipientId = friendId;
            document.getElementById('giftRecipientName').textContent = friendName;
            document.getElementById('gameSelect').value = '';
            document.getElementById('giftModal').classList.add('active');
        }

        function closeGiftModal() {
            document.getElementById('giftModal').classList.remove('active');
            currentGiftRecipientId = null;
        }

        function sendGift() {
            const gameId = document.getElementById('gameSelect').value;
            
            if (!gameId) {
                alert('Please select a game to gift.');
                return;
            }

            if (!currentGiftRecipientId) {
                alert('Error: No recipient selected.');
                return;
            }

            const gameTitle = document.getElementById('gameSelect').options[document.getElementById('gameSelect').selectedIndex].text;
            const friendName = document.getElementById('giftRecipientName').textContent;

            if (confirm(`Are you sure you want to gift "${gameTitle}" to ${friendName}?`)) {
                fetch('manage_friends.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=gift&friend_id=${currentGiftRecipientId}&game_id=${gameId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        closeGiftModal();
                        // Optionally reload to update the page
                        // location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                });
            }
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

        function filterFriends() {
            const searchTerm = document.getElementById('friendSearch').value.toLowerCase();
            const friendCards = document.querySelectorAll('.friend-card');
            
            friendCards.forEach(card => {
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

        function clearFriendSearch() {
            document.getElementById('friendSearch').value = '';
            const friendCards = document.querySelectorAll('.friend-card');
            friendCards.forEach(card => {
                card.style.display = 'block';
            });
        }

        // Add enter key support for search
        document.getElementById('userSearch')?.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                filterUsers();
            }
        });

        document.getElementById('friendSearch')?.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                filterFriends();
            }
        });

        // Close modal when clicking outside
        document.getElementById('giftModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeGiftModal();
            }
        });
    </script>
</body>
</html>