<?php
session_start();
require '../db_connect.php';

if (!isset($_SESSION['developer_id'])) {
    header("Location: login.php");
    exit();
}

$message = '';
$developer_id = $_SESSION['developer_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $price = (float)$_POST['price'];
    $genre = $_POST['genre'];
    $image_url = trim($_POST['image_url']);
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;

    if (empty($title) || empty($description) || $price < 0 || empty($genre)) {
        $message = "Please fill in all required fields with valid data.";
    } else {
        try {
            $stmt = $conn->prepare("INSERT INTO games (title, description, price, genre, image_url, is_featured, developer_id, release_date) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param("ssdssis", $title, $description, $price, $genre, $image_url, $is_featured, $developer_id);

            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Game '{$title}' has been added successfully!";
                header("Location: dashboard.php");
                exit();
            } else {
                $message = "Database error: " . $stmt->error;
            }
            $stmt->close();
        } catch (Exception $e) {
            $message = "Error adding game: " . $e->getMessage();
        }
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Game - Aperior Developer</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../styles.css">
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="max-w-2xl mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center mb-6">
                <a href="dashboard.php" class="text-purple-600 hover:text-purple-800 mr-4 flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
                </a>
                <h1 class="text-2xl font-bold text-gray-900">Add New Game</h1>
            </div>

            <?php if (!empty($message)): ?>
                <div class="mb-4 p-3 rounded-md bg-red-100 text-red-700 border border-red-300">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form action="add_game.php" method="post" class="space-y-6">
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700">Game Title *</label>
                    <input type="text" name="title" id="title" required maxlength="100"
                           value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Description *</label>
                    <textarea name="description" id="description" rows="4" required maxlength="1000"
                              class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                    <p class="mt-1 text-sm text-gray-500">Maximum 1000 characters</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="price" class="block text-sm font-medium text-gray-700">Price ($) *</label>
                        <input type="number" name="price" id="price" step="0.01" min="0" max="999.99" required
                               value="<?php echo isset($_POST['price']) ? htmlspecialchars($_POST['price']) : ''; ?>"
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                    </div>

                    <div>
                        <label for="genre" class="block text-sm font-medium text-gray-700">Genre *</label>
                        <select name="genre" id="genre" required
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                            <option value="">Select a genre</option>
                            <?php 
                            $genres = ["Action", "Adventure", "RPG", "Strategy", "Simulation", "Sports", "Racing", "Puzzle", "Fighting", "Platformer", "Horror", "Indie"];
                            $selected_genre = isset($_POST['genre']) ? $_POST['genre'] : '';
                            foreach ($genres as $genre_option): 
                            ?>
                                <option value="<?php echo $genre_option; ?>" <?php echo ($selected_genre === $genre_option) ? 'selected' : ''; ?>>
                                    <?php echo $genre_option; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="image_url" class="block text-sm font-medium text-gray-700">Game Cover Image URL</label>
                    <input type="url" name="image_url" id="image_url" placeholder="https://example.com/image.jpg"
                           value="<?php echo isset($_POST['image_url']) ? htmlspecialchars($_POST['image_url']) : ''; ?>"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                    <p class="mt-1 text-sm text-gray-500">Enter a direct link to your game's cover image (optional)</p>
                    
                    <div id="image-preview" class="mt-3 hidden">
                        <p class="text-sm font-medium text-gray-700 mb-2">Preview:</p>
                        <img id="preview-img" src="" alt="Image preview" class="w-32 h-32 object-cover rounded-md border border-gray-300">
                    </div>
                </div>

                <div class="flex items-center">
                    <input type="checkbox" name="is_featured" id="is_featured" 
                           <?php echo (isset($_POST['is_featured']) && $_POST['is_featured']) ? 'checked' : ''; ?>
                           class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded">
                    <label for="is_featured" class="ml-2 block text-sm text-gray-900">
                        <span class="font-medium">Feature this game</span>
                        <span class="text-gray-500">(highlight in main carousel)</span>
                    </label>
                </div>

                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                    <a href="dashboard.php" 
                       class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors flex items-center">
                        <i class="fas fa-times mr-2"></i>Cancel
                    </a>
                    <button type="submit"
                            class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-md transition-colors flex items-center">
                        <i class="fas fa-plus mr-2"></i>Add Game
                    </button>
                </div>
            </form>

            <div class="mt-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
                <h4 class="text-sm font-medium text-blue-800 mb-2">💡 Tips for finding game images:</h4>
                <ul class="text-sm text-blue-700 space-y-1">
                    <li>• Use <a href="https://unsplash.com" target="_blank" class="underline hover:text-blue-900">Unsplash</a> for free high-quality images</li>
                    <li>• Try <a href="https://pixabay.com" target="_blank" class="underline hover:text-blue-900">Pixabay</a> for game-related graphics</li>
                    <li>• Search for "game cover art" or "video game poster"</li>
                    <li>• Make sure to use direct image links (ending in .jpg, .png, etc.)</li>
                    <li>• Recommended size: 400x600 pixels or similar aspect ratio</li>
                </ul>
            </div>
        </div>
    </div>

    <script>
        //
        const imageUrlInput = document.getElementById('image_url');
        const imagePreview = document.getElementById('image-preview');
        const previewImg = document.getElementById('preview-img');

        imageUrlInput.addEventListener('input', function() {
            const url = this.value.trim();
            
            if (url && isValidImageUrl(url)) {
                previewImg.src = url;
                imagePreview.classList.remove('hidden');
                
                
                previewImg.onload = function() {
                    imagePreview.classList.remove('hidden');
                };
                
                previewImg.onerror = function() {
                    imagePreview.classList.add('hidden');
                };
            } else {
                imagePreview.classList.add('hidden');
            }
        });

        function isValidImageUrl(url) {
            try {
                const urlObj = new URL(url);
                const validExtensions = ['.jpg', '.jpeg', '.png', '.gif', '.webp'];
                return validExtensions.some(ext => urlObj.pathname.toLowerCase().endsWith(ext)) ||
                       url.includes('unsplash.com') || url.includes('pixabay.com') || url.includes('imgur.com');
            } catch {
                return false;
            }
        }

        
        document.querySelector('form').addEventListener('submit', function(e) {
            const title = document.getElementById('title').value.trim();
            const description = document.getElementById('description').value.trim();
            const price = parseFloat(document.getElementById('price').value);
            const genre = document.getElementById('genre').value;

            if (!title || !description || !genre || price < 0) {
                e.preventDefault();
                alert('Please fill in all required fields with valid data.');
                return false;
            }

            if (price > 999.99) {
                e.preventDefault();
                alert('Price cannot exceed $999.99');
                return false;
            }

            
            const submitBtn = document.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Adding Game...';
            submitBtn.disabled = true;
        });

        
        window.addEventListener('load', function() {
            if (imageUrlInput.value) {
                imageUrlInput.dispatchEvent(new Event('input'));
            }
        });
    </script>
</body>
</html>