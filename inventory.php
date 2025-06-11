<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$inventory_games = [];

$stmt = $conn->prepare(
    "SELECT g.id as game_id, g.title, g.image_url, g.description, ui.purchase_date
     FROM user_inventory ui
     JOIN games g ON ui.game_id = g.id 
     WHERE ui.user_id = ?
     ORDER BY ui.purchase_date DESC"
);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $inventory_games[] = $row;
    }
    $result->free();
}
$stmt->close();
$conn->close();

// Get recent games (last 5 purchased)
$recent_games = array_slice($inventory_games, 0, 5);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Library - Aperior</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="./styles.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    screens: {
                        'xs': '475px',
                    },
                    colors: {
                        aperior: {
                            yellow: '#FFF7AD',
                            pink: '#FFA9F9',
                            purple: '#9D4EDD',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .game-card {
            transition: all 0.2s ease;
        }

        .game-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        .sidebar-item {
            border-radius: 4px;
            transition: background-color 0.2s;
        }

        .sidebar-item:hover,
        .sidebar-item.active {
            background-color: rgba(255, 255, 255, 0.25);
        }

        @media (max-width: 767px) {
            body.sidebar-open {
                overflow: hidden;
            }

            #sidebar {
                height: 100vh;
                overflow-y: auto;
            }
        }
    </style>
</head>

<body class="bg-gradient-to-br from-[#FFF7AD] to-[#FFA9F9] min-h-screen text-gray-800">
    <!-- Main Container -->
    <div class="flex flex-col h-screen overflow-hidden">
        <!-- Header -->
        <div class="bg-white/80 backdrop-blur-sm p-4 flex justify-between items-center">
            <div class="flex items-center">
                <a href="./index.php" class="mr-2 text-pink-600 hover:text-pink-700">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <img src="./assets/aperior.svg" alt="Aperior Logo" class="w-8 h-8 mr-2" />
                <h1 class="text-lg font-bold text-pink-600 apply-custom-title-font">APERIOR LIBRARY</h1>
            </div>
            <a href="./catalogue/index.php" class="text-pink-600 hover:text-pink-700 flex items-center">
                <i class="fas fa-shopping-cart mr-1"></i>
                <span class="hidden sm:inline">Store</span>
            </a>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col h-full overflow-hidden">
            <!-- Search/View Controls -->
            <div class="bg-pink-200 rounded-lg p-4 shadow-xl">
                <h3 class="text-base md:text-lg font-bold text-[#ff5cf4] mb-3 md:mb-4">Search Games</h3>
                <div class="search-container flex gap-2">
                    <input
                        type="text"
                        id="gameSearch"
                        placeholder="Search games by title..."
                        class="flex-1 px-3 md:px-4 py-2 border border-pink-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#ff5cf4] focus:border-[#ff5cf4] text-sm md:text-base"
                        onkeyup="filterGames()">
                    <button
                        onclick="clearSearch()"
                        class="px-3 md:px-4 py-2 bg-[#ff5cf4] text-white rounded-lg hover:bg-[#ff1cf0] transition-colors text-sm md:text-base whitespace-nowrap">
                        Clear
                    </button>
                </div>
                <div class="flex-1 w-full sm:w-auto sm:max-w-lg mx-0 sm:mx-4">
                    <div class="relative">
                        <input type="text" placeholder="Search games..." class="w-full bg-white/90 border border-pink-200 text-gray-800 px-4 py-2 rounded text-sm focus:outline-none focus:ring-2 focus:ring-pink-500">
                        <i class="fas fa-search absolute right-3 top-2.5 text-pink-400"></i>
                    </div>
                </div>
                <div class="w-full sm:w-auto flex justify-center">
                    <button class="bg-pink-300 hover:bg-pink-400 text-pink-800 px-3 py-1 rounded text-sm">
                        <i class="fas fa-sliders mr-1"></i> Filter
                    </button>
                </div>
            </div>

            <!-- Content Area -->
            <div class="flex-1 overflow-y-auto p-3 sm:p-6 bg-gradient-to-br from-[#FFFBE6] to-[#FFEBFF]">
                <?php if (isset($_SESSION['payment_message'])): ?>
                    <div class="mb-4 p-3 rounded-md bg-green-100 text-green-800 border border-green-200">
                        <?php echo $_SESSION['payment_message'];
                        unset($_SESSION['payment_message']); ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($recent_games)): ?>
                    <div class="mb-8">
                        <h2 class="text-xl font-bold text-pink-600 mb-4">Recently Added</h2>
                        <div class="grid grid-cols-1 xs:grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4 sm:flex sm:space-x-4 sm:overflow-x-auto sm:pb-4">
                            <?php foreach ($recent_games as $game): ?>
                                <div class="flex-shrink-0 sm:w-60">
                                    <div class="game-card bg-white/90 backdrop-blur-sm rounded-lg overflow-hidden shadow-md">
                                        <img src="<?php echo htmlspecialchars($game['image_url'] ?: './assets/image_placeholder.png'); ?>" alt="<?php echo htmlspecialchars($game['title']); ?>" class="w-full h-28 object-cover">
                                        <div class="p-3">
                                            <h3 class="font-medium text-gray-800"><?php echo htmlspecialchars($game['title']); ?></h3>
                                            <p class="text-xs text-pink-500 mt-1">Added <?php echo date("M j", strtotime($game['purchase_date'])); ?></p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <h2 class="text-xl font-bold text-pink-600 mb-4">All Games</h2>

                <?php if (empty($inventory_games)): ?>
                    <div class="bg-white/90 backdrop-blur-sm rounded-lg p-4 sm:p-8 text-center shadow-md">
                        <i class="fas fa-gamepad text-5xl text-pink-400 mb-4"></i>
                        <p class="text-gray-700 text-lg mb-4">Your library is empty</p>
                        <a href="./catalogue/index.php" class="inline-block bg-pink-600 hover:bg-pink-700 text-white font-medium px-6 py-2 rounded-md transition-colors">
                            Browse Games
                        </a>
                    </div>
                <?php else: ?>
                    <!-- Grid View (default) -->
                    <div id="grid-view" class="grid grid-cols-1 xs:grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                        <?php foreach ($inventory_games as $game): ?>
                            <div class="game-card bg-white/90 backdrop-blur-sm rounded-lg overflow-hidden shadow-md">
                                <img src="<?php echo htmlspecialchars($game['image_url'] ?: './assets/image_placeholder.png'); ?>" alt="<?php echo htmlspecialchars($game['title']); ?>" class="w-full h-32 sm:h-40 object-cover">
                                <div class="p-3 sm:p-4">
                                    <h3 class="font-medium text-gray-800"><?php echo htmlspecialchars($game['title']); ?></h3>
                                    <p class="text-xs text-pink-500 mt-1">Added <?php echo date("M j, Y", strtotime($game['purchase_date'])); ?></p>
                                    <div class="flex mt-3">
                                        <button class="flex-1 bg-pink-600 hover:bg-pink-700 text-white font-medium py-1.5 px-3 rounded-md text-sm transition-colors">
                                            <i class="fas fa-play mr-1"></i> Play
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- List View (hidden by default) -->
                    <div id="list-view" class="hidden space-y-2">
                        <?php foreach ($inventory_games as $game): ?>
                            <div class="game-card bg-white/90 backdrop-blur-sm rounded-lg overflow-hidden shadow-md flex">
                                <img src="<?php echo htmlspecialchars($game['image_url'] ?: './assets/image_placeholder.png'); ?>" alt="<?php echo htmlspecialchars($game['title']); ?>" class="w-20 h-20 object-cover">
                                <div class="p-3 flex-1 flex items-center justify-between">
                                    <div>
                                        <h3 class="font-medium text-gray-800"><?php echo htmlspecialchars($game['title']); ?></h3>
                                        <p class="text-xs text-pink-500">Added <?php echo date("M j, Y", strtotime($game['purchase_date'])); ?></p>
                                    </div>
                                    <button class="bg-pink-600 hover:bg-pink-700 text-white font-medium py-1 px-3 rounded-md text-sm transition-colors">
                                        <i class="fas fa-play mr-1"></i> Play
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // View toggling functionality
        document.getElementById('grid-view-btn').addEventListener('click', function() {
            document.getElementById('grid-view').classList.remove('hidden');
            document.getElementById('list-view').classList.add('hidden');
            this.classList.add('active');
            document.getElementById('list-view-btn').classList.remove('active');
        });

        document.getElementById('list-view-btn').addEventListener('click', function() {
            document.getElementById('list-view').classList.remove('hidden');
            document.getElementById('grid-view').classList.add('hidden');
            this.classList.add('active');
            document.getElementById('grid-view-btn').classList.remove('active');
        });

        // Play button functionality
        const playButtons = document.querySelectorAll('.game-card button');
        playButtons.forEach(button => {
            button.addEventListener('click', function() {
                alert('Play game feature coming soon!');
            });
        });
    </script>
</body>

</html>