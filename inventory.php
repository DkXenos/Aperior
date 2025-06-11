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
            background-color: rgba(255, 255, 255, 0.1);
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

<body class="bg-gray-900 min-h-screen text-white">
    <!-- Main Container -->
    <div class="flex flex-col md:flex-row h-screen overflow-hidden">
        <!-- Mobile Header -->
        <div class="md:hidden bg-gray-800 p-4 flex justify-between items-center">
            <div class="flex items-center">
                <img src="./assets/aperior.svg" alt="Aperior Logo" class="w-8 h-8 mr-2" />
                <h1 class="text-lg font-bold text-pink-400 apply-custom-title-font">APERIOR</h1>
            </div>
            <button id="sidebar-toggle" class="text-white p-2">
                <i class="fas fa-bars"></i>
            </button>
        </div>

        <!-- Sidebar -->
        <div id="sidebar" class="w-full md:w-56 bg-gray-800 flex-shrink-0 p-4 flex flex-col h-full md:static fixed top-0 left-0 z-40 transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out">
            <div class="flex items-center mb-8 md:block">
                <button id="sidebar-close" class="md:hidden ml-auto text-white p-2">
                    <i class="fas fa-times"></i>
                </button>
                <div class="flex items-center">
                    <img src="./assets/aperior.svg" alt="Aperior Logo" class="w-10 h-10 mr-3" />
                    <h1 class="text-xl font-bold text-pink-400 apply-custom-title-font">APERIOR</h1>
                </div>
            </div>

            <div class="mb-6">
                <div class="text-sm text-gray-400 uppercase mb-2 pl-2">Library</div>
                <div class="sidebar-item active p-2 text-white flex items-center">
                    <i class="fas fa-home mr-3 w-5"></i> Home
                </div>
                <div class="sidebar-item p-2 text-gray-300 flex items-center">
                    <i class="fas fa-download mr-3 w-5"></i> Downloads
                </div>
            </div>

            <div class="mb-6">
                <div class="text-sm text-gray-400 uppercase mb-2 pl-2">Collections</div>
                <div class="sidebar-item p-2 text-gray-300 flex items-center">
                    <i class="fas fa-star mr-3 w-5"></i> Favorites
                </div>
                <div class="sidebar-item p-2 text-gray-300 flex items-center">
                    <i class="fas fa-gamepad mr-3 w-5"></i> All Games
                </div>
                <div class="sidebar-item p-2 text-gray-300 flex items-center">
                    <i class="fas fa-clock-rotate-left mr-3 w-5"></i> Recently Played
                </div>
            </div>

            <div class="mt-auto">
                <a href="./index.php" class="sidebar-item p-2 text-gray-300 flex items-center">
                    <i class="fas fa-arrow-left mr-3 w-5"></i> Back to Home
                </a>
                <a href="./catalogue/index.php" class="sidebar-item p-2 text-gray-300 flex items-center">
                    <i class="fas fa-shopping-cart mr-3 w-5"></i> Store
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col h-full overflow-hidden">
            <!-- Header/Search -->
            <div class="bg-gray-700 p-3 sm:p-4 flex flex-col sm:flex-row justify-between items-center gap-3">
                <div class="flex space-x-3 w-full sm:w-auto justify-center">
                    <button id="grid-view-btn" class="bg-gray-600 hover:bg-gray-500 text-white px-3 py-1 rounded text-sm active">
                        <i class="fas fa-th-large"></i>
                    </button>
                    <button id="list-view-btn" class="bg-gray-600 hover:bg-gray-500 text-white px-3 py-1 rounded text-sm">
                        <i class="fas fa-list"></i>
                    </button>
                </div>
                <div class="flex-1 w-full sm:w-auto sm:max-w-lg mx-0 sm:mx-4">
                    <div class="relative">
                        <input type="text" placeholder="Search games..." class="w-full bg-gray-800 text-white px-4 py-2 rounded text-sm focus:outline-none focus:ring-2 focus:ring-pink-500">
                        <i class="fas fa-search absolute right-3 top-2.5 text-gray-400"></i>
                    </div>
                </div>
                <div class="w-full sm:w-auto flex justify-center">
                    <button class="bg-gray-600 hover:bg-gray-500 text-white px-3 py-1 rounded text-sm">
                        <i class="fas fa-sliders mr-1"></i> Filter
                    </button>
                </div>
            </div>

            <!-- Content Area -->
            <div class="flex-1 overflow-y-auto p-3 sm:p-6 bg-gradient-to-br from-gray-800 to-gray-900">
                <?php if (isset($_SESSION['payment_message'])): ?>
                    <div class="mb-4 p-3 rounded-md bg-green-800 text-green-100">
                        <?php echo $_SESSION['payment_message'];
                        unset($_SESSION['payment_message']); ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($recent_games)): ?>
                    <div class="mb-8">
                        <h2 class="text-xl font-bold text-white mb-4">Recently Added</h2>
                        <div class="grid grid-cols-1 xs:grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4 sm:flex sm:space-x-4 sm:overflow-x-auto sm:pb-4">
                            <?php foreach ($recent_games as $game): ?>
                                <div class="flex-shrink-0 sm:w-60">
                                    <div class="game-card bg-gray-700 rounded-lg overflow-hidden">
                                        <img src="<?php echo htmlspecialchars($game['image_url'] ?: './assets/image_placeholder.png'); ?>" alt="<?php echo htmlspecialchars($game['title']); ?>" class="w-full h-28 object-cover">
                                        <div class="p-3">
                                            <h3 class="font-medium text-white"><?php echo htmlspecialchars($game['title']); ?></h3>
                                            <p class="text-xs text-gray-400 mt-1">Added <?php echo date("M j", strtotime($game['purchase_date'])); ?></p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <h2 class="text-xl font-bold text-white mb-4">All Games</h2>

                <?php if (empty($inventory_games)): ?>
                    <div class="bg-gray-700 rounded-lg p-4 sm:p-8 text-center">
                        <i class="fas fa-gamepad text-5xl text-gray-500 mb-4"></i>
                        <p class="text-gray-300 text-lg mb-4">Your library is empty</p>
                        <a href="./catalogue/index.php" class="inline-block bg-pink-600 hover:bg-pink-700 text-white font-medium px-6 py-2 rounded-md transition-colors">
                            Browse Games
                        </a>
                    </div>
                <?php else: ?>
                    <!-- Grid View (default) -->
                    <div id="grid-view" class="grid grid-cols-1 xs:grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                        <?php foreach ($inventory_games as $game): ?>
                            <div class="game-card bg-gray-700 rounded-lg overflow-hidden">
                                <img src="<?php echo htmlspecialchars($game['image_url'] ?: './assets/image_placeholder.png'); ?>" alt="<?php echo htmlspecialchars($game['title']); ?>" class="w-full h-32 sm:h-40 object-cover">
                                <div class="p-3 sm:p-4">
                                    <h3 class="font-medium text-white"><?php echo htmlspecialchars($game['title']); ?></h3>
                                    <p class="text-xs text-gray-400 mt-1">Added <?php echo date("M j, Y", strtotime($game['purchase_date'])); ?></p>
                                    <div class="flex mt-3">
                                        <button class="flex-1 bg-green-600 hover:bg-green-700 text-white font-medium py-1.5 px-3 rounded-md text-sm transition-colors">
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
                            <div class="game-card bg-gray-700 rounded-lg overflow-hidden flex">
                                <img src="<?php echo htmlspecialchars($game['image_url'] ?: './assets/image_placeholder.png'); ?>" alt="<?php echo htmlspecialchars($game['title']); ?>" class="w-20 h-20 object-cover">
                                <div class="p-3 flex-1 flex items-center justify-between">
                                    <div>
                                        <h3 class="font-medium text-white"><?php echo htmlspecialchars($game['title']); ?></h3>
                                        <p class="text-xs text-gray-400">Added <?php echo date("M j, Y", strtotime($game['purchase_date'])); ?></p>
                                    </div>
                                    <button class="bg-green-600 hover:bg-green-700 text-white font-medium py-1 px-3 rounded-md text-sm transition-colors">
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

        // Mobile sidebar toggle
        const sidebarToggle = document.getElementById('sidebar-toggle');
        const sidebarClose = document.getElementById('sidebar-close');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.createElement('div');

        overlay.className = 'fixed inset-0 bg-black bg-opacity-50 z-30 hidden transition-opacity duration-300 ease-in-out';
        document.body.appendChild(overlay);

        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.remove('hidden');
        });

        sidebarClose.addEventListener('click', function() {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
        });

        overlay.addEventListener('click', function() {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
        });

        // Close sidebar when resizing to desktop if it was open
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 768 && !sidebar.classList.contains('-translate-x-full')) {
                overlay.classList.add('hidden');
            }
        });
    </script>
</body>

</html>