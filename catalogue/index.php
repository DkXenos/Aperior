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
    $carousel_games_data = array_slice($games_all, 0, min(5, count($games_all)));
} elseif (!empty($carousel_games_data) && count($carousel_games_data) > 5) {
    
    $carousel_games_data = array_slice($carousel_games_data, 0, 5); // Example: limit to 5
}


$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Game Catalogue</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.13.0/dist/gsap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.13.0/dist/ScrollTrigger.min.js"></script>
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
        .wishlist-btn { background-color: #FF69B4; }
        .wishlist-btn:hover { background-color: #FF1493; }
        .wishlist-btn.in-wishlist { background-color: #C71585;  }

        .cart-btn { background-color: #4CAF50;  }
        .cart-btn:hover { background-color: #45a049; }
        .cart-btn.in-cart { background-color: #FFA500; }
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
                                opacity-0 pointer-events-none origin-top-right z-[60]"> 
                        <a href="../index.php" class="catalogue-nav-link block text-pink-600 hover:text-pink-800 hover:underline text-sm md:text-base text-left py-1.5 px-2 rounded-md hover:bg-pink-100">Home</a>
                        <a href="../catalog.php" class="catalogue-nav-link block text-pink-600 hover:text-pink-800 hover:underline text-sm md:text-base text-left py-1.5 px-2 rounded-md hover:bg-pink-100">Catalog</a>
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
            <section id="featuredSection" class="w-full bg-[#FFE4E1] py-12 md:py-20 lg:py-24">
                <div class="w-full max-w-7xl mx-auto px-2 sm:px-4">
                    <div id="featuredTitle" class="bg-pink-200 rounded-lg p-3 sm:p-4 mb-6 md:mb-8 shadow-md">
                        <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold text-[#ff5cf4]">Featured & Popular Games</h2>
                        <p class="text-sm sm:text-base md:text-lg text-[#ff1cf0] mt-1 md:mt-2 text-shadow-pink">Discover the hottest titles and player favorites</p>
                    </div>
                    <div id="gameCarouselContainer" class="h-[450px] md:h-[550px] lg:h-[600px] contain-paint contain-layout isolate overflow-hidden">
                        
                        <div class="relative w-full max-w-5xl mx-auto">
                            <button id="carouselPrevBtn" class="absolute left-4 top-1/2 transform -translate-y-1/2 z-30 bg-[#ffa9f9] text-white rounded-full p-3 shadow-lg hover:bg-[#ff8bf0] transition-all hover:scale-110" aria-label="Previous game">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                            </button>
                            <div class="overflow-hidden contain-layout contain-paint">
                                <div id="carouselTrack" class="flex items-center transition-all duration-700 ease-[cubic-bezier(0.25,0.1,0.25,1.0)] py-16" style="perspective: 1000px; will-change: transform;">
                                    
                                </div>
                            </div>
                            <button id="carouselNextBtn" class="absolute right-4 top-1/2 transform -translate-y-1/2 z-30 bg-[#ffa9f9] text-white rounded-full p-3 shadow-lg hover:bg-[#ff8bf0] transition-all hover:scale-110" aria-label="Next game">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                            </button>
                            <div id="carouselDots" class="flex justify-center mt-6 space-x-2">
                                
                            </div>
                        </div>
                        
                    </div>
                </div>
            </section>

            <section id="catalog" class="w-full bg-[#E6E6FA] py-8 md:py-12 isolate">
                <div class="container mx-auto px-4">
                    <div id="catalogTitle" class="flex flex-col md:flex-row items-center justify-center md:justify-start align-center mb-8">
                        <img src="../assets/aperior.svg" alt="Aperior Logo" class="w-16 h-16 sm:w-20 sm:h-20 md:w-24 md:h-24 lg:w-32 lg:h-32" />
                        <h2 class="apply-custom-title-font text-3xl font-bold text-[#ff5cf4] mb-6 text-center md:text-left transition-all duration-300 ease-in-out hover:scale-110 hover:text-shadow-pink origin-left">Game Catalog</h2>
                    </div>
                    <div class="flex flex-col md:flex-row gap-6">
                        <div id="categoriesSidebar" class="w-full md:w-64 flex-shrink-0">
                            <div class="bg-pink-200 rounded-lg shadow-xl p-4">
                                <h3 class="text-xl font-bold text-[#ff5cf4] mb-4">Categories</h3>
                                <div class="space-y-2 category-dropdown-container">
                                    
                                </div>
                            </div>
                        </div>
                        <div class="flex-grow">
                            <div class="bg-pink-100 rounded-lg shadow-xl overflow-hidden">
                                <div class="bg-pink-100 p-4 border-b border-pink-200 sticky top-0 z-10">
                                    <h4 class="text-lg font-bold text-[#ff5cf4]">Available Games</h4>
                                    <p class="text-sm text-[#ff37f0]">Browse our collection</p>
                                </div>
                                <div class="p-4 h-[550px] overflow-y-auto">
                                    <div id="gameGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                        
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
    
    <script>
        
        const gamesData = <?php echo json_encode($games_all); ?>; 
        const carouselData = <?php echo json_encode($carousel_games_data); ?>; 
        const isUserLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
        const userId = <?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'null'; ?>;
    </script>
    <script src="./script.js"></script> 
</body>
</html>