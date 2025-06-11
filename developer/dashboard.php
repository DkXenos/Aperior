<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require '../db_connect.php';

if (!isset($_SESSION['developer_id'])) {
    header("Location: login.php");
    exit();
}

$developer_id = $_SESSION['developer_id'];
$developer_games = [];

try {
    // Check if developer_id column exists, if not add it (without foreign key constraint for now)
    $check_column = $conn->query("SHOW COLUMNS FROM games LIKE 'developer_id'");
    if ($check_column->num_rows == 0) {
        $conn->query("ALTER TABLE games ADD COLUMN developer_id INT DEFAULT NULL");
    }

    // Get developer's games
    $stmt = $conn->prepare("SELECT id, title, description, price, image_url, genre, release_date, is_featured FROM games WHERE developer_id = ? ORDER BY release_date DESC");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("i", $developer_id);
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $developer_games[] = $row;
        }
        $result->free();
    }
    $stmt->close();
    
} catch (Exception $e) {
    error_log("Dashboard error: " . $e->getMessage());
    // Continue with empty games array for now
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Developer Dashboard - Aperior</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../styles.css">
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div class="flex items-center">
                    <img src="../assets/aperior.svg" alt="Aperior Logo" class="w-10 h-10 mr-3"/>
                    <div>
                        <h1 class="text-2xl font-bold text-purple-600 apply-custom-title-font">Developer Dashboard</h1>
                        <p class="text-sm text-gray-600">Welcome back, <?php echo isset($_SESSION['company_name']) ? htmlspecialchars($_SESSION['company_name']) : 'Developer'; ?></p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="../index.php" class="text-gray-600 hover:text-purple-600 transition-colors">
                        <i class="fas fa-home mr-1"></i> Home
                    </a>
                    <a href="logout.php" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-md transition-colors">
                        <i class="fas fa-sign-out-alt mr-1"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Debug Info (remove in production) -->
        <div class="mb-4 p-4 bg-blue-100 border border-blue-300 rounded-lg">
            <h3 class="text-blue-800 font-medium">Debug Info:</h3>
            <p class="text-blue-700">Developer ID: <?php echo $developer_id; ?></p>
            <p class="text-blue-700">Games found: <?php echo count($developer_games); ?></p>
        </div>

        <!-- Success Message -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="mb-6 p-4 rounded-lg bg-green-100 border border-green-400 text-green-700">
                <div class="flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    <?php 
                    echo htmlspecialchars($_SESSION['success_message']); 
                    unset($_SESSION['success_message']);
                    ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-purple-100 rounded-lg">
                        <i class="fas fa-gamepad text-purple-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Games</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo count($developer_games); ?></p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-green-100 rounded-lg">
                        <i class="fas fa-star text-green-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Featured Games</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo count(array_filter($developer_games, function($game) { return $game['is_featured']; })); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Games Management -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h2 class="text-lg font-medium text-gray-900">Your Games</h2>
                <a href="add_game.php" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-md transition-colors">
                    <i class="fas fa-plus mr-2"></i>Add New Game
                </a>
            </div>
            
            <div class="p-6">
                <?php if (empty($developer_games)): ?>
                    <div class="text-center py-12">
                        <i class="fas fa-gamepad text-4xl text-gray-400 mb-4"></i>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No games yet</h3>
                        <p class="text-gray-600 mb-4">Start by adding your first game to the marketplace</p>
                        <a href="add_game.php" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-md transition-colors">
                            Add Your First Game
                        </a>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php foreach ($developer_games as $game): ?>
                            <div class="border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg transition-shadow">
                                <img src="<?php echo htmlspecialchars($game['image_url'] ?: '../assets/image_placeholder.png'); ?>" 
                                     alt="<?php echo htmlspecialchars($game['title']); ?>" 
                                     class="w-full h-48 object-cover">
                                <div class="p-4">
                                    <div class="flex items-start justify-between mb-2">
                                        <h3 class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($game['title']); ?></h3>
                                        <?php if ($game['is_featured']): ?>
                                            <span class="bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded-full">Featured</span>
                                        <?php endif; ?>
                                    </div>
                                    <p class="text-sm text-gray-600 mb-2"><?php echo htmlspecialchars($game['genre']); ?></p>
                                    <p class="text-lg font-bold text-purple-600 mb-3">$<?php echo number_format($game['price'], 2); ?></p>
                                    <div class="flex space-x-2">
                                        <a href="edit_game.php?id=<?php echo $game['id']; ?>" 
                                           class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-center py-2 px-3 rounded text-sm transition-colors">
                                            <i class="fas fa-edit mr-1"></i>Edit
                                        </a>
                                        <a href="delete_game.php?id=<?php echo $game['id']; ?>" 
                                           onclick="return confirm('Are you sure you want to delete this game?')"
                                           class="flex-1 bg-red-600 hover:bg-red-700 text-white text-center py-2 px-3 rounded text-sm transition-colors">
                                            <i class="fas fa-trash mr-1"></i>Delete
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Database Fix -->
        <!-- <div class="mt-8 p-4 bg-yellow-100 border border-yellow-300 rounded-lg">
            <h3 class="text-yellow-800 font-medium mb-2">Having issues? Try this quick fix:</h3>
            <p class="text-yellow-700 text-sm mb-3">If you can't see your games or login issues persist, click the button below to reset the database structure:</p>
            <a href="fix_database.php" class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-md transition-colors text-sm">
                <i class="fas fa-wrench mr-2"></i>Fix Database Issues
            </a>
        </div> -->
    </div>
</body>
</html>