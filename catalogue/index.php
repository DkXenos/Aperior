<?php
session_start();
require '../db_connect.php';

$games_all = [];

$result = $conn->query("SELECT id, title, description, price, image_url, genre, release_date, is_featured FROM games ORDER BY release_date DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $games_all[] = $row;
    }
    $result->free();
}

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    foreach ($games_all as $key => $game) {

        $stmt_wish = $conn->prepare("SELECT id FROM wishlist_items WHERE user_id = ? AND game_id = ?");
        $stmt_wish->bind_param("ii", $user_id, $game['id']);
        $stmt_wish->execute();
        $games_all[$key]['in_wishlist'] = $stmt_wish->get_result()->num_rows > 0;
        $stmt_wish->close();


        $stmt_cart = $conn->prepare("SELECT quantity FROM cart_items WHERE user_id = ? AND game_id = ?");
        $stmt_cart->bind_param("ii", $user_id, $game['id']);
        $stmt_cart->execute();
        $cart_result = $stmt_cart->get_result();
        if ($cart_result->num_rows > 0) {
            $games_all[$key]['in_cart'] = true;
            $games_all[$key]['cart_quantity'] = $cart_result->fetch_assoc()['quantity'];
        } else {
            $games_all[$key]['in_cart'] = false;
            $games_all[$key]['cart_quantity'] = 0;
        }
        $stmt_cart->close();
    }
} else {

    foreach ($games_all as $key => $game) {
        $games_all[$key]['in_wishlist'] = false;
        $games_all[$key]['in_cart'] = false;
        $games_all[$key]['cart_quantity'] = 0;
    }
}

$carousel_games_data = [];
foreach ($games_all as $game) {
    if (!empty($game['is_featured'])) {
        $carousel_games_data[] = $game;
    }
}

if (empty($carousel_games_data) && !empty($games_all)) {
    $carousel_games_data = array_slice($games_all, 0, min(6, count($games_all)));
} elseif (!empty($carousel_games_data) && count($carousel_games_data) > 6) {
    $carousel_games_data = array_slice($carousel_games_data, 0, 6);
}

// Get unique genres for category tabs
$genres = [];
foreach ($games_all as $game) {
    if (!empty($game['genre']) && !in_array($game['genre'], $genres)) {
        $genres[] = $game['genre'];
    }
}
sort($genres);

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Game Catalogue</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="./styles.css">
    <style>
        .action-button {
            padding: 8px 12px;
            border-radius: 6px;
            color: white;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-top: 8px;
            display: inline-block;
            font-size: 0.875rem;
        }

        .wishlist-btn {
            background-color: #FF69B4;
        }

        .wishlist-btn:hover {
            background-color: #FF1493;
        }

        .wishlist-btn.in-wishlist {
            background-color: #C71585;
        }

        .cart-btn {
            background-color: #4CAF50;
        }

        .cart-btn:hover {
            background-color: #45a049;
        }

        .cart-btn.in-cart {
            background-color: #FFA500;
        }

        .category-tab {
            transition: all 0.3s ease;
            border-bottom: 3px solid transparent;
        }

        .category-tab.active {
            background-color: #ff5cf4;
            color: white;
            border-bottom-color: #ff1cf0;
            transform: translateY(-2px);
        }

        .category-tab:hover:not(.active) {
            background-color: #ffc0cb;
            transform: translateY(-1px);
        }

        .game-card {
            display: block;
        }

        .game-card.hidden {
            display: none;
        }

        
        .hero-carousel {
            position: relative;
            height: 500px;
            overflow: hidden;
            border-radius: 16px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            background: #1a1a1a;
        }

        .hero-slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            transition: opacity 0.8s ease-in-out;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .hero-slide.active {
            opacity: 1;
        }

        .hero-slide-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: 1;
        }

        .hero-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, rgba(0, 0, 0, 0.8) 0%, rgba(0, 0, 0, 0.3) 50%, rgba(0, 0, 0, 0.1) 100%);
            z-index: 2;
        }

        .hero-content {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 40px;
            z-index: 3;
            color: white;
        }

        .hero-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            color: white;
            border: none;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            z-index: 4;
        }

        .hero-nav:hover {
            background: rgba(255, 92, 244, 0.8);
            transform: translateY(-50%) scale(1.1);
        }

        .hero-nav.prev {
            left: 20px;
        }

        .hero-nav.next {
            right: 20px;
        }

        .hero-dots {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 12px;
            z-index: 4;
        }

        .hero-dot {
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.4);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .hero-dot.active {
            background: #ff5cf4;
            transform: scale(1.2);
        }

        .hero-title {
            font-size: 3rem;
            font-weight: bold;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
            line-height: 1.1;
        }

        .hero-description {
            font-size: 1.2rem;
            margin-bottom: 1.5rem;
            opacity: 0.9;
            max-width: 600px;
            line-height: 1.4;
        }

        .hero-price {
            font-size: 2rem;
            font-weight: bold;
            color: #ff5cf4;
            margin-bottom: 1.5rem;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
        }

        .hero-buttons {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
        }

        .hero-button {
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            backdrop-filter: blur(10px);
            font-size: 1rem;
        }

        .hero-button.primary {
            background: linear-gradient(45deg, #ff5cf4, #ff1cf0);
            color: white;
        }

        .hero-button.primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(255, 92, 244, 0.3);
        }

        .hero-button.secondary {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border-color: rgba(255, 255, 255, 0.3);
        }

        .hero-button.secondary:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }

        
        @media (max-width: 768px) {
            .hero-carousel {
                height: 350px;
                min-height: 350px;
                border-radius: 12px;
            }

            .hero-slide {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                opacity: 0;
                transition: opacity 0.8s ease-in-out;
            }

            .hero-slide.active {
                opacity: 1;
            }

            .hero-slide-bg {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                object-fit: cover;
                z-index: 1;
            }

            .hero-overlay {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: linear-gradient(45deg, rgba(0, 0, 0, 0.8) 0%, rgba(0, 0, 0, 0.4) 100%);
                z-index: 2;
            }

            .hero-content {
                position: absolute;
                bottom: 0;
                left: 0;
                right: 0;
                padding: 16px;
                z-index: 3;
                color: white;
            }

            .hero-title {
                font-size: 1.5rem;
                color: white;
                text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.8);
                margin-bottom: 0.5rem;
                line-height: 1.2;
            }

            .hero-description {
                font-size: 0.85rem;
                color: white;
                margin-bottom: 0.75rem;
                opacity: 0.9;
                line-height: 1.4;
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
                overflow: hidden;
            }

            .hero-price {
                font-size: 1.25rem;
                color: #ff5cf4;
                text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.8);
                margin-bottom: 0.75rem;
                font-weight: bold;
            }

            .hero-buttons {
                gap: 8px;
                flex-direction: row;
                flex-wrap: wrap;
            }

            .hero-button {
                padding: 8px 16px;
                font-size: 0.8rem;
                text-align: center;
                flex: 1;
                min-width: 120px;
                font-weight: 600;
                border-radius: 8px;
            }

            .hero-button.primary {
                background: linear-gradient(45deg, #ff5cf4, #ff1cf0);
                color: white;
                border: none;
            }

            .hero-button.secondary {
                background: rgba(255, 255, 255, 0.9);
                color: #ff5cf4;
                border: 1px solid #ff5cf4;
            }

            .hero-nav {
                width: 40px;
                height: 40px;
                top: 50%;
                transform: translateY(-50%);
                background: rgba(0, 0, 0, 0.6);
                backdrop-filter: blur(10px);
            }

            .hero-nav.prev {
                left: 10px;
            }

            .hero-nav.next {
                right: 10px;
            }

            .hero-nav:hover {
                background: rgba(255, 92, 244, 0.8);
                transform: translateY(-50%) scale(1.05);
            }

            .hero-dots {
                position: absolute;
                bottom: 8px;
                left: 50%;
                transform: translateX(-50%);
                background: rgba(0, 0, 0, 0.5);
                padding: 6px 12px;
                border-radius: 15px;
                backdrop-filter: blur(10px);
            }

            .hero-dot {
                width: 8px;
                height: 8px;
                background: rgba(255, 255, 255, 0.6);
            }

            .hero-dot.active {
                background: #ff5cf4;
                transform: scale(1.2);
            }
        }

        
        @media (max-width: 480px) {
            .hero-carousel {
                height: 280px;
                min-height: 280px;
                border-radius: 8px;
            }

            .hero-content {
                padding: 12px;
            }

            .hero-title {
                font-size: 1.25rem;
                margin-bottom: 0.25rem;
            }

            .hero-description {
                font-size: 0.75rem;
                margin-bottom: 0.5rem;
                -webkit-line-clamp: 1;
            }

            .hero-price {
                font-size: 1.1rem;
                margin-bottom: 0.5rem;
            }

            .hero-button {
                padding: 6px 12px;
                font-size: 0.75rem;
                min-width: 100px;
            }

            .hero-nav {
                width: 35px;
                height: 35px;
            }

            .hero-nav.prev {
                left: 8px;
            }

            .hero-nav.next {
                right: 8px;
            }

            .hero-dots {
                bottom: 6px;
                padding: 4px 10px;
            }

            .hero-dot {
                width: 6px;
                height: 6px;
            }
        }

        
        @media (max-width: 640px) {
            .search-container {
                flex-direction: column;
                gap: 8px;
            }

            .search-container input {
                width: 100%;
            }

            .search-container button {
                width: 100%;
            }
        }

        
        @media (hover: none) and (pointer: coarse) {
            .hero-nav {
                background: rgba(0, 0, 0, 0.7);
            }

            .hero-button:active {
                transform: scale(0.98);
            }

            .category-tab:active {
                transform: translateY(-1px);
            }
        }
    </style>
</head>

<body class="bg-[#FFA9F9]">

    <div id="cataloguePageContainer">
        <header id="header" class="fixed top-0 left-0 right-0 z-50 w-full">
            <div class="relative flex items-center justify-between bg-pink-200 p-3 md:p-4 shadow-lg">
                <div class="flex items-center">
                    <img src="../assets/aperior.svg" alt="Aperior Logo" class="w-10 h-10 lg:w-20 lg:h-20 md:w-12 md:h-12" />
                    <h1 class="text-[#ff5cf4] ml-2 md:ml-3 text-xl md:text-2xl lg:text-6xl apply-custom-title-font">Aprerior</h1>
                </div>

                <div class="relative">
                    <button id="catalogueMenuButton" class="p-2 bg-pink-300 hover:bg-pink-400 rounded-full text-[#ff5cf4] focus:outline-none transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5 md:w-6 md:h-6">
                            <path id="catalogueMenuIconPath" stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    <div id="cataloguePopupMenu"
                        class="absolute top-full right-0 mt-2 w-56 md:w-60
                                bg-white/95 backdrop-blur-md p-3 md:p-4 rounded-xl shadow-2xl space-y-1
                                opacity-0 pointer-events-none origin-top-right z-[60]" style="display: none;">
                        <a href="../index.php" class="catalogue-nav-link block text-pink-600 hover:text-pink-800 hover:underline text-sm md:text-base text-left py-1.5 px-2 rounded-md hover:bg-pink-100">Home</a>
                        <a href="../inventory.php" class="catalogue-nav-link block text-pink-600 hover:text-pink-800 hover:underline text-sm md:text-base text-left py-1.5 px-2 rounded-md hover:bg-pink-100">Inventory</a>
                        <a href="../friends.php" class="catalogue-nav-link block text-pink-600 hover:text-pink-800 hover:underline text-sm md:text-base text-left py-1.5 px-2 rounded-md hover:bg-pink-100">Friends</a>
                        <a href="../wishlist.php" class="catalogue-nav-link block text-pink-600 hover:text-pink-800 hover:underline text-sm md:text-base text-left py-1.5 px-2 rounded-md hover:bg-pink-100">Wishlist</a>
                        <a href="../cart.php" class="catalogue-nav-link block text-pink-600 hover:text-pink-800 hover:underline text-sm md:text-base text-left py-1.5 px-2 rounded-md hover:bg-pink-100">Shopping Cart</a>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <div class="border-t border-pink-200 my-1"></div>
                            <a href="../logout.php" class="catalogue-nav-link block text-pink-700 hover:text-pink-900 hover:underline text-sm md:text-base text-left py-1.5 px-2 rounded-md hover:bg-pink-100 font-medium">Logout</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </header>

        <div class="relative pt-20 md:pt-26 flex flex-col items-center w-full">
            
            <section id="featuredSection" class="w-full bg-[#FFE4E1] py-8 md:py-12 lg:py-20">
                <div class="w-full max-w-7xl mx-auto px-4 sm:px-6">
                    <div id="featuredTitle" class="text-center mb-6 md:mb-8">
                        <h2 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-bold text-[#ff5cf4] mb-2 md:mb-4 apply-custom-title-font">Featured & Popular Games</h2>
                        <p class="text-sm sm:text-base md:text-lg lg:text-xl text-[#ff1cf0] opacity-90 apply-custom-title-font">Discover the hottest titles and player favorites</p>
                    </div>

                    
                    <div class="hero-carousel" id="heroCarousel">
                        <?php foreach ($carousel_games_data as $index => $game): ?>
                            <div class="hero-slide <?php echo $index === 0 ? 'active' : ''; ?>" data-index="<?php echo $index; ?>">
                                <img src="<?php echo htmlspecialchars($game['image_url'] ?: '../assets/image_placeholder.png'); ?>"
                                    alt="<?php echo htmlspecialchars($game['title']); ?>"
                                    class="hero-slide-bg" />
                                <div class="hero-overlay"></div>
                                <div class="hero-content">
                                    <h3 class="hero-title"><?php echo htmlspecialchars($game['title']); ?></h3>
                                    <p class="hero-description"><?php echo htmlspecialchars($game['description'] ?: 'Experience this amazing game with stunning graphics and immersive gameplay.'); ?></p>
                                    <div class="hero-price">$<?php echo number_format($game['price'], 2); ?></div>
                                    <div class="hero-buttons">
                                        <button
                                            class="hero-button primary cart-btn <?php echo $game['in_cart'] ? 'in-cart' : ''; ?>"
                                            data-game-id="<?php echo $game['id']; ?>">
                                            <?php echo $game['in_cart'] ? "In Cart ({$game['cart_quantity']})" : 'Add to Cart'; ?>
                                        </button>
                                        <button
                                            class="hero-button secondary wishlist-btn <?php echo $game['in_wishlist'] ? 'in-wishlist' : ''; ?>"
                                            data-game-id="<?php echo $game['id']; ?>"
                                            data-action="<?php echo $game['in_wishlist'] ? 'remove' : 'add'; ?>">
                                            <?php echo $game['in_wishlist'] ? 'In Wishlist' : 'Add to Wishlist'; ?>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        
                        <button class="hero-nav prev" id="heroPrevBtn">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="15,18 9,12 15,6"></polyline>
                            </svg>
                        </button>
                        <button class="hero-nav next" id="heroNextBtn">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="9,18 15,12 9,6"></polyline>
                            </svg>
                        </button>

                        
                        <div class="hero-dots -mb-15" id="heroDots">
                            <?php for ($i = 0; $i < count($carousel_games_data); $i++): ?>
                                <div class="hero-dot <?php echo $i === 0 ? 'active' : ''; ?>" data-index="<?php echo $i; ?>"></div>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>
            </section>

            
            <section id="catalog" class="w-full bg-[#FFE4E1] py-6 md:py-8 lg:py-12">
                <div class="container mx-auto px-4">
                    <div id="catalogTitle" class="flex flex-col md:flex-row items-center justify-center md:justify-start mb-6 md:mb-8">
                        <img src="../assets/aperior.svg" alt="Aperior Logo" class="w-12 h-12 sm:w-16 sm:h-16 md:w-20 md:h-20 lg:w-24 lg:h-24 mb-3 md:mb-0 md:mr-4" />
                        <h2 class="apply-custom-title-font text-2xl sm:text-3xl md:text-4xl font-bold text-[#ff5cf4] text-center md:text-left transition-all duration-300 ease-in-out hover:scale-110 hover:text-shadow-pink origin-center md:origin-left">Game Catalog</h2>
                    </div>

                    
                    <div class="mb-6 space-y-4">
                        
                        <div class="bg-pink-200 rounded-lg p-4 shadow-xl">
                            <h3 class="text-base md:text-lg font-bold text-[#ff5cf4] mb-3 md:mb-4">Search Games</h3>
                            <div class="search-container flex gap-2">
                                <input
                                    type="text"
                                    id="gameSearch"
                                    placeholder="Search games by title..."
                                    class="flex-1 px-3 md:px-4 py-2 border border-pink-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#ff5cf4] focus:border-[#ff5cf4] text-sm md:text-base"
                                    onkeyup="filterGames()"> 
                                </div>
                        </div>

                        
                        <div class="bg-pink-200 rounded-lg p-4 shadow-xl">
                            <h3 class="text-base md:text-lg font-bold text-[#ff5cf4] mb-3 md:mb-4">Browse by Category</h3>
                            <div class="flex flex-wrap gap-2">
                                <button class="category-tab active px-3 md:px-4 py-2 rounded-lg font-medium text-[#ff5cf4] bg-pink-100 text-xs md:text-sm" data-category="all" onclick="selectCategory('all', this)">
                                    All Games
                                </button>
                                <?php foreach ($genres as $genre): ?>
                                    <button class="category-tab px-3 md:px-4 py-2 rounded-lg font-medium text-[#ff5cf4] bg-pink-100 text-xs md:text-sm" data-category="<?php echo strtolower($genre); ?>" onclick="selectCategory('<?php echo strtolower($genre); ?>', this)">
                                        <?php echo htmlspecialchars($genre); ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    
                    <div class="bg-pink-100 rounded-lg shadow-xl overflow-hidden">
                        <div class="bg-pink-100 p-3 md:p-4 border-b border-pink-200">
                            <h4 class="text-base md:text-lg font-bold text-[#ff5cf4]">Available Games</h4>
                            <p class="text-xs md:text-sm text-[#ff37f0]">Browse our collection</p>
                            <div class="mt-2">
                                <span id="gameCount" class="text-xs md:text-sm text-[#ff5cf4] font-medium">Showing <?php echo count($games_all); ?> games</span>
                            </div>
                        </div>
                        <div class="p-3 md:p-4 max-h-[500px] md:max-h-[600px] overflow-y-auto">
                            <div id="gameGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 md:gap-4">
                                <?php foreach ($games_all as $game): ?>
                                    <div class="game-card bg-white p-3 md:p-4 rounded-lg shadow-md hover:shadow-lg transition-shadow" data-title="<?php echo strtolower($game['title']); ?>" data-genre="<?php echo strtolower($game['genre']); ?>">
                                        <img src="<?php echo htmlspecialchars($game['image_url'] ?: '../assets/image_placeholder.png'); ?>" alt="<?php echo htmlspecialchars($game['title']); ?>" class="w-full h-32 md:h-40 object-cover rounded mb-2">
                                        <h3 class="text-base md:text-lg font-semibold text-pink-700"><?php echo htmlspecialchars($game['title']); ?></h3>
                                        <p class="text-xs md:text-sm text-gray-600"><?php echo htmlspecialchars($game['genre'] ?: 'N/A'); ?></p>
                                        <p class="text-sm md:text-base font-bold text-pink-500 my-1">$<?php echo number_format($game['price'], 2); ?></p>
                                        <button
                                            class="action-button wishlist-btn <?php echo $game['in_wishlist'] ? 'in-wishlist' : ''; ?> w-full mb-1 text-xs md:text-sm"
                                            data-game-id="<?php echo $game['id']; ?>"
                                            data-action="<?php echo $game['in_wishlist'] ? 'remove' : 'add'; ?>">
                                            <?php echo $game['in_wishlist'] ? 'In Wishlist' : 'Add to Wishlist'; ?>
                                        </button>
                                        <button
                                            class="action-button cart-btn <?php echo $game['in_cart'] ? 'in-cart' : ''; ?> w-full text-xs md:text-sm"
                                            data-game-id="<?php echo $game['id']; ?>">
                                            <?php echo $game['in_cart'] ? "In Cart ({$game['cart_quantity']})" : 'Add to Cart'; ?>
                                        </button>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <script>
        const isUserLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
        let currentCategory = 'all';
        let currentSearchTerm = '';

        //
        let currentHeroSlide = 0;
        const heroSlides = document.querySelectorAll('.hero-slide');
        const totalHeroSlides = heroSlides.length;
        const heroDots = document.querySelectorAll('.hero-dot');
        const heroPrevBtn = document.getElementById('heroPrevBtn');
        const heroNextBtn = document.getElementById('heroNextBtn');

        function updateHeroCarousel() {
            
            heroSlides.forEach((slide, index) => {
                slide.classList.toggle('active', index === currentHeroSlide);
            });

            
            heroDots.forEach((dot, index) => {
                dot.classList.toggle('active', index === currentHeroSlide);
            });
        }

        function nextHeroSlide() {
            currentHeroSlide = (currentHeroSlide + 1) % totalHeroSlides;
            updateHeroCarousel();
        }

        function prevHeroSlide() {
            currentHeroSlide = (currentHeroSlide - 1 + totalHeroSlides) % totalHeroSlides;
            updateHeroCarousel();
        }

        function goToHeroSlide(index) {
            currentHeroSlide = index;
            updateHeroCarousel();
        }

        
        if (heroNextBtn) heroNextBtn.addEventListener('click', nextHeroSlide);
        if (heroPrevBtn) heroPrevBtn.addEventListener('click', prevHeroSlide);

        heroDots.forEach((dot, index) => {
            dot.addEventListener('click', () => goToHeroSlide(index));
        });

        
        let heroAutoPlayInterval = setInterval(nextHeroSlide, 5000);

        
        const heroCarousel = document.getElementById('heroCarousel');
        if (heroCarousel) {
            heroCarousel.addEventListener('mouseenter', () => clearInterval(heroAutoPlayInterval));
            heroCarousel.addEventListener('mouseleave', () => {
                heroAutoPlayInterval = setInterval(nextHeroSlide, 5000);
            });
        }

        
        let startX = 0;
        let endX = 0;

        if (heroCarousel) {
            heroCarousel.addEventListener('touchstart', (e) => {
                startX = e.touches[0].clientX;
            }, {
                passive: true
            });

            heroCarousel.addEventListener('touchend', (e) => {
                endX = e.changedTouches[0].clientX;
                const diff = startX - endX;

                if (Math.abs(diff) > 50) { 
                    if (diff > 0) {
                        nextHeroSlide();
                    } else {
                        prevHeroSlide();
                    }
                }
            }, {
                passive: true
            });
        }

        
        updateHeroCarousel();

        
        document.getElementById('catalogueMenuButton').addEventListener('click', function() {
            const menu = document.getElementById('cataloguePopupMenu');
            const isVisible = menu.style.display === 'block';
            menu.style.display = isVisible ? 'none' : 'block';
            menu.style.opacity = isVisible ? '0' : '1';
            menu.style.pointerEvents = isVisible ? 'none' : 'auto';
        });

        
        document.addEventListener('click', function(e) {
            const menu = document.getElementById('cataloguePopupMenu');
            const button = document.getElementById('catalogueMenuButton');
            if (!menu.contains(e.target) && !button.contains(e.target)) {
                menu.style.display = 'none';
            }
        });

        
        function selectCategory(category, element) {
            
            document.querySelectorAll('.category-tab').forEach(tab => tab.classList.remove('active'));
            
            element.classList.add('active');
            currentCategory = category;
            filterGames();
        }

        
        function filterGames() {
            const searchTerm = document.getElementById('gameSearch').value.toLowerCase();
            currentSearchTerm = searchTerm;

            const gameCards = document.querySelectorAll('.game-card');
            let visibleCount = 0;

            gameCards.forEach(card => {
                const title = card.dataset.title;
                const genre = card.dataset.genre;

                const matchesSearch = searchTerm === '' || title.includes(searchTerm);
                const matchesCategory = currentCategory === 'all' || genre === currentCategory;

                if (matchesSearch && matchesCategory) {
                    card.style.display = 'block';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            document.getElementById('gameCount').textContent = `Showing ${visibleCount} games`;
        }

        
        function clearSearch() {
            document.getElementById('gameSearch').value = '';
            currentSearchTerm = '';
            filterGames();
        }

        
        document.addEventListener('click', async function(e) {
            
            if (e.target.classList.contains('wishlist-btn')) {
                e.preventDefault();
                const button = e.target;
                const gameId = button.dataset.gameId;
                const action = button.dataset.action;

                if (!isUserLoggedIn) {
                    alert('Please log in to manage your wishlist.');
                    window.location.href = '../login.php';
                    return;
                }

                button.disabled = true;
                const originalText = button.textContent;
                button.textContent = 'Processing...';

                try {
                    const formData = new FormData();
                    formData.append('game_id', gameId);
                    formData.append('action', action);

                    const response = await fetch('../update_wishlist.php', {
                        method: 'POST',
                        body: formData
                    });

                    const result = await response.json();

                    if (result.success) {
                        if (action === 'add') {
                            button.textContent = 'In Wishlist';
                            button.classList.add('in-wishlist');
                            button.dataset.action = 'remove';
                        } else {
                            button.textContent = 'Add to Wishlist';
                            button.classList.remove('in-wishlist');
                            button.dataset.action = 'add';
                        }
                    } else {
                        
                        button.textContent = originalText;
                    }
                } catch (error) {
                    console.error('Wishlist error:', error);
                    
                    button.textContent = originalText;
                } finally {
                    button.disabled = false;
                }
            }

            
            if (e.target.classList.contains('cart-btn')) {
                e.preventDefault();
                const button = e.target;
                const gameId = button.dataset.gameId;

                if (!isUserLoggedIn) {
                    alert('Please log in to add items to your cart.');
                    window.location.href = '../login.php';
                    return;
                }

                button.disabled = true;
                const originalText = button.textContent;
                button.textContent = 'Adding...';

                try {
                    const formData = new FormData();
                    formData.append('game_id', gameId);
                    formData.append('quantity', 1);
                    formData.append('action', 'add');

                    const response = await fetch('../update_cart.php', {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: formData
                    });

                    const result = await response.json();

                    if (result.success) {
                        button.textContent = 'Added to Cart!';
                        button.classList.add('in-cart');
                        setTimeout(() => {
                            button.textContent = 'In Cart';
                        }, 2000);
                    } else {
                        alert('Error: ' + result.message);
                        button.textContent = originalText;
                    }
                } catch (error) {
                    console.error('Cart error:', error);
                    alert('An error occurred. Please try again.');
                    button.textContent = originalText;
                } finally {
                    button.disabled = false;
                }
            }
        });
    </script>
</body>

</html>