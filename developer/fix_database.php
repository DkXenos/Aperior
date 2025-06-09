<?php
session_start();
require '../db_connect.php';

if (!isset($_SESSION['developer_id'])) {
    header("Location: login.php");
    exit();
}

$messages = [];
$errors = [];

try {
    // Fix 1: Ensure developer_id column exists in games table
    $check_column = $conn->query("SHOW COLUMNS FROM games LIKE 'developer_id'");
    if ($check_column->num_rows == 0) {
        $conn->query("ALTER TABLE games ADD COLUMN developer_id INT DEFAULT NULL");
        $messages[] = "Added developer_id column to games table";
    } else {
        $messages[] = "developer_id column already exists";
    }

    // Fix 2: Remove any problematic foreign key constraints
    try {
        $conn->query("ALTER TABLE games DROP FOREIGN KEY fk_developer");
        $messages[] = "Removed existing foreign key constraint";
    } catch (Exception $e) {
        $messages[] = "No foreign key constraint to remove (this is fine)";
    }

    // Fix 3: ONLY update games that were created by this specific developer
    // We'll check if there are games without developer_id that might belong to this developer
    $developer_id = $_SESSION['developer_id'];
    
    // Check how many games don't have a developer_id
    $orphan_check = $conn->query("SELECT COUNT(*) as count FROM games WHERE developer_id IS NULL");
    $orphan_count = $orphan_check->fetch_assoc()['count'];
    
    if ($orphan_count > 0) {
        $messages[] = "Found {$orphan_count} games without developer assignment";
        $messages[] = "These games will remain unassigned to prevent incorrect ownership";
        $messages[] = "If you created specific games, please manually assign them in the database";
    } else {
        $messages[] = "All games already have proper developer assignment";
    }

    // Fix 4: Verify developer session data
    $dev_stmt = $conn->prepare("SELECT username, company_name FROM developers WHERE id = ?");
    $dev_stmt->bind_param("i", $developer_id);
    $dev_stmt->execute();
    $dev_result = $dev_stmt->get_result();
    
    if ($dev_result->num_rows > 0) {
        $dev_data = $dev_result->fetch_assoc();
        $_SESSION['developer_username'] = $dev_data['username'];
        $_SESSION['company_name'] = $dev_data['company_name'];
        $messages[] = "Refreshed session data for " . $dev_data['company_name'];
    } else {
        $errors[] = "Could not find developer data in database";
    }
    $dev_stmt->close();

    // Fix 5: Create assets/game_images directory if it doesn't exist
    $assets_dir = __DIR__ . '/../assets/game_images/';
    if (!is_dir($assets_dir)) {
        if (mkdir($assets_dir, 0755, true)) {
            $messages[] = "Created game images directory";
        } else {
            $errors[] = "Could not create game images directory";
        }
    } else {
        $messages[] = "Game images directory already exists";
    }

    $success = true;

} catch (Exception $e) {
    $errors[] = "Database error: " . $e->getMessage();
    $success = false;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Fix - Aperior Developer</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../styles.css">
</head>
<body class="bg-gray-100 min-h-screen py-8">
    <div class="max-w-2xl mx-auto px-4">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center mb-6">
                <i class="fas fa-wrench text-2xl text-purple-600 mr-3"></i>
                <h1 class="text-2xl font-bold text-gray-900">Database Fix Utility</h1>
            </div>

            <?php if ($success): ?>
                <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                    <div class="flex items-center mb-2">
                        <i class="fas fa-check-circle mr-2"></i>
                        <strong>Fix completed successfully!</strong>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($messages)): ?>
                <div class="mb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-3">Actions Performed:</h3>
                    <ul class="space-y-2">
                        <?php foreach ($messages as $message): ?>
                            <li class="flex items-center text-green-700">
                                <i class="fas fa-check mr-2"></i>
                                <?php echo htmlspecialchars($message); ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="mb-6">
                    <h3 class="text-lg font-medium text-red-900 mb-3">Errors:</h3>
                    <ul class="space-y-2">
                        <?php foreach ($errors as $error): ?>
                            <li class="flex items-center text-red-700">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                <?php echo htmlspecialchars($error); ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Important Notice -->
            <div class="mb-6 p-4 bg-yellow-100 border border-yellow-400 text-yellow-800 rounded-lg">
                <div class="flex items-start">
                    <i class="fas fa-info-circle mr-2 mt-1"></i>
                    <div>
                        <h4 class="font-medium mb-2">Important Notice:</h4>
                        <p class="text-sm">This fix utility no longer automatically assigns existing games to developers to prevent ownership conflicts. If you need to assign specific games you created to your developer account, please contact an administrator or manually update the database.</p>
                    </div>
                </div>
            </div>

            <div class="flex justify-between items-center pt-4 border-t border-gray-200">
                <a href="dashboard.php" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-md transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
                </a>
                <a href="add_game.php" class="text-purple-600 hover:text-purple-800 transition-colors">
                    <i class="fas fa-plus mr-2"></i>Add New Game
                </a>
            </div>
        </div>
    </div>
</body>
</html>