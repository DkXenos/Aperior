<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aperior - Game Distribution Platform</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <link rel="stylesheet" href="./styles.css">
    <style>
        
        .bg-asset {
            z-index: -10;
            pointer-events: none;
            position: absolute;
        }
        
        
        .bg-main-cloud {
            z-index: -20;
            pointer-events: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            object-fit: cover;
            opacity: 0.4;
        }
        
        
        .content-layer {
            z-index: 10;
            position: relative;
        }
        
        
        .nav-layer {
            z-index: 20;
            position: relative;
        }

        #sun{
            z-index: -12;
            rotate: 25deg;
        }
        #wheat{
            rotate: -90deg;
            right: 10px;
            top: 10px;
        }

        
        .animate-mountain-front {
            transform: translateY(200px);
            opacity: 0;
        }
        
        .animate-mountain-back {
            transform: translateX(200px) translateY(200px);
            opacity: 0;
        }

        .animate-sakura {
            transform: translateX(-200px) translateY(200px);
            opacity: 0;
        }

        .animate-tree {
            transform: translateY(200px);
            opacity: 0;
        }

        .animate-sun {
            transform: translateY(300px) scale(0.1);
            opacity: 0;
        }
    </style>
</head>

<body class="bg-gradient-to-br from-[#FFF7AD] to-[#FFA9F9] min-h-screen flex flex-col">
    <!-- Main Cloud Background - Full screen behind everything -->
    <img src="./assets/cloud.svg" alt="Background Cloud" class="bg-main-cloud"/>
    
    <!-- Background SVG Assets - Always behind everything -->
    <div class="fixed inset-0 overflow-hidden">
        <!-- Navigation decorations -->
        <img
            src="./assets/vine-left.svg"
            alt="Top-left decoration"
            class="bg-asset top-0 left-2 
                   w-32 h-32 -mt-3 -ml-6
                   md:w-48 md:h-48 md:-mt-4 md:-ml-8
                   lg:w-64 lg:h-64 lg:-mt-4 lg:-ml-10
                   opacity-100"
        />
        <img
            src="./assets/wheat.svg"
            alt="Top-right decoration"
            class="bg-asset top-0 right-0 
                   w-20 h-20 -mt-4 -mr-4
                   md:w-32 md:h-32 md:-mt-5 md:-mr-5
                   lg:w-48 lg:h-48 lg:-mt-6 lg:-mr-6
                   opacity-100"
            id="wheat"
        />
        
        <!-- Main content decorations with GSAP animation classes -->
        <!-- Back Mountain (comes from right) -->
        <img src="./assets/mountain.svg" alt="Mountain decoration" 
             class="bg-asset
                    w-[800px] h-auto bottom-[20px] -right-[120px]
                    md:w-[1000px] md:h-auto md:-bottom-10 md:-right-[200px]
                    lg:w-[1200px] lg:h-auto lg:-bottom-20 lg:-right-[450px]
                    opacity-70 animate-mountain-back"
             id="mountain-back"/>

        <!-- Front Mountain (comes from bottom) -->
        <img src="./assets/mountain.svg" alt="Mountain decoration" 
             class="bg-asset -z-13
                    w-[600px] h-auto -bottom-[40px] -right-[70px]
                    md:w-[650px] md:h-auto md:-bottom-10 md:-right-[10px]
                    lg:w-[900px] lg:h-auto lg:-bottom-10 lg:-right-[20px]
                    opacity-100 animate-mountain-front"
             id="mountain-front"/>
        
        <!-- Sakura (comes from left) -->
        <img src="./assets/sakura.svg" alt="Sakura decoration" 
             class="bg-asset -z-11
                    w-[1000px] h-auto -left-[80px] bottom-[20px]
                    md:w-[750px] md:h-auto md:top-[250px] md:-left-[100px]
                    lg:w-[900px] lg:h-auto lg:top-[100px] lg:-left-[200px]
                    opacity-50 animate-sakura"
             id="sakura"/>
    
        <!-- Tree (comes from bottom) -->
        <img src="./assets/tree.svg" alt="Tree decoration" 
             class="bg-asset
                    w-[600px] h-auto -bottom-[20px] -left-[50px]
                    md:w-[600px] md:h-auto md:-bottom-[20px] md:-left-[25px]
                    lg:w-[1000px] lg:h-auto lg:-bottom-[42px] lg:-left-[50px]
                    opacity-100 animate-tree"
             id="tree"/>
    
        <!-- Sun (comes from bottom with scale) -->
        <img src="./assets/sun.svg" alt="Sun decoration" 
             class="bg-asset 
                    w-[1200px] h-auto -bottom-[50px] -right-[120px]
                    md:w-[900px] md:h-auto md:top-[200px] md:right-[0px]
                    lg:w-[1200px] lg:h-auto lg:top-[50px] lg:right-[90px]
                    opacity-100 animate-sun"
             id="sun"/>
    </div>

    <!-- Navigation -->
    <nav class="nav-layer flex items-center justify-between p-4 lg:p-8">
        <div class="flex items-center space-x-3 opacity-0">
            <img src="./assets/aperior.svg" alt="Aperior Logo" class="w-10 h-10 lg:w-12 lg:h-12"/>
            <h1 class="text-2xl lg:text-3xl font-bold text-pink-600 apply-custom-title-font">Aperior</h1>
        </div>

        <!-- Desktop Navigation -->
        <div class="hidden md:flex items-center space-x-6">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="./catalogue/index.php" class="text-pink-600 hover:text-pink-800 hover:underline text-sm lg:text-base">Browse Games</a>
                <a href="./wishlist.php" class="text-pink-600 hover:text-pink-800 hover:underline text-sm lg:text-base">Wishlist</a>
                <a href="./cart.php" class="text-pink-600 hover:text-pink-800 hover:underline text-sm lg:text-base">Cart</a>
                <a href="./inventory.php" class="text-pink-600 hover:text-pink-800 hover:underline text-sm lg:text-base">Library</a>
                <a href="./friends.php" class="text-pink-600 hover:text-pink-800 hover:underline text-sm lg:text-base">Friends</a>
                <span class="text-pink-700 font-medium">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
                <a href="./logout.php" class="text-pink-600 hover:text-pink-800 hover:underline text-sm lg:text-base">Logout</a>
            <?php elseif (isset($_SESSION['developer_id'])): ?>
                <a href="./catalogue/index.php" class="text-pink-600 hover:text-pink-800 hover:underline text-sm lg:text-base">Browse Games</a>
                <a href="./developer/dashboard.php" class="text-purple-600 hover:text-purple-800 hover:underline text-sm lg:text-base font-medium">
                    <i class="fas fa-tachometer-alt mr-1"></i>Dashboard
                </a>
                <span class="text-purple-700 font-medium">Welcome, <?php echo htmlspecialchars($_SESSION['company_name'] ?? $_SESSION['developer_username']); ?>!</span>
                <a href="./developer/logout.php" class="text-purple-600 hover:text-purple-800 hover:underline text-sm lg:text-base">Logout</a>
            <?php else: ?>
                <a href="./catalogue/index.php" class="text-pink-600 hover:text-pink-800 hover:underline text-sm lg:text-base">Browse Games</a>
                <a href="./login.php" class="text-pink-600 hover:text-pink-800 hover:underline text-sm lg:text-base">Login</a>
                <a href="./register.php" class="text-pink-600 hover:text-pink-800 hover:underline text-sm lg:text-base">Register</a>
                <!-- Developer Login Link -->
                <div class="relative group">
                    <a href="./developer/login.php" class="text-purple-600 hover:text-purple-800 hover:underline text-sm lg:text-base font-medium flex items-center">
                        <i class="fas fa-code mr-1"></i>Developer
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Mobile Menu Button -->
        <button id="mobileMenuBtn" class="md:hidden text-pink-600 hover:text-pink-800">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
        </button>

        <!-- Mobile Menu -->
        <div id="mobileMenu" class="absolute top-full left-0 right-0 bg-white/95 backdrop-blur-md shadow-lg rounded-lg mx-4 mt-2 py-4 px-6 space-y-3 hidden md:hidden z-30">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="./catalogue/index.php" class="block text-pink-600 hover:text-pink-800 hover:underline text-sm md:text-base text-left">Browse Games</a>
                <a href="./wishlist.php" class="block text-pink-600 hover:text-pink-800 hover:underline text-sm md:text-base text-left">Wishlist</a>
                <a href="./cart.php" class="block text-pink-600 hover:text-pink-800 hover:underline text-sm md:text-base text-left">Cart</a>
                <a href="./inventory.php" class="block text-pink-600 hover:text-pink-800 hover:underline text-sm md:text-base text-left">Library</a>
                <a href="./friends.php" class="block text-pink-600 hover:text-pink-800 hover:underline text-sm md:text-base text-left">Friends</a>
                <div class="border-t border-pink-200 my-2"></div>
                <span class="block text-pink-700 font-medium text-sm">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
                <a href="./logout.php" class="block text-pink-600 hover:text-pink-800 hover:underline text-sm md:text-base text-left">Logout</a>
            <?php elseif (isset($_SESSION['developer_id'])): ?>
                <a href="./catalogue/index.php" class="block text-pink-600 hover:text-pink-800 hover:underline text-sm md:text-base text-left">Browse Games</a>
                <a href="./developer/dashboard.php" class="block text-purple-600 hover:text-purple-800 hover:underline text-sm md:text-base text-left font-medium">
                    <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                </a>
                <div class="border-t border-purple-200 my-2"></div>
                <span class="block text-purple-700 font-medium text-sm">Welcome, <?php echo htmlspecialchars($_SESSION['company_name'] ?? $_SESSION['developer_username']); ?>!</span>
                <a href="./developer/logout.php" class="block text-purple-600 hover:text-purple-800 hover:underline text-sm md:text-base text-left">Logout</a>
            <?php else: ?>
                <a href="./catalogue/index.php" class="block text-pink-600 hover:text-pink-800 hover:underline text-sm md:text-base text-left">Browse Games</a>
                <a href="./login.php" class="block text-pink-600 hover:text-pink-800 hover:underline text-sm md:text-base text-left">Login</a>
                <a href="./register.php" class="block text-pink-600 hover:text-pink-800 hover:underline text-sm md:text-base text-left">Register</a>
                <div class="border-t border-pink-200 my-2"></div>
                <a href="./developer/login.php" class="block text-purple-600 hover:text-purple-800 hover:underline text-sm md:text-base text-left font-medium">
                    <i class="fas fa-code mr-2"></i>Developer Login
                </a>
            <?php endif; ?>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="content-layer flex-1 flex items-center justify-center px-4 lg:px-8 -mt-16 lg:-mt-20">
        <div class="max-w-4xl mx-auto text-center">
            <!-- Hero Content -->
            <div class="mb-8 lg:mb-12">
                <img src="./assets/aperior.svg" alt="Aperior Logo" class="w-24 h-24 lg:w-32 lg:h-32 mx-auto mb-6"/>
                <h1 class="text-4xl lg:text-6xl font-bold text-pink-600 mb-4 lg:mb-6 apply-custom-title-font">
                    Welcome to Aperior
                </h1>
                <p class="text-lg lg:text-xl text-pink-700 mb-6 lg:mb-8 max-w-2xl mx-auto">
                    Your ultimate destination for discovering, purchasing, and enjoying incredible games. 
                    Connect with friends and build your digital library.
                </p>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center items-center mb-8">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="./catalogue/index.php" 
                       class="w-full sm:w-auto px-8 py-3 bg-pink-500 hover:bg-pink-600 text-white font-semibold rounded-lg shadow-lg transition-all duration-200 hover:shadow-xl">
                        Browse Games
                    </a>
                    <a href="./inventory.php" 
                       class="w-full sm:w-auto px-8 py-3 bg-purple-500 hover:bg-purple-600 text-white font-semibold rounded-lg shadow-lg transition-all duration-200 hover:shadow-xl">
                        My Library
                    </a>
                <?php elseif (isset($_SESSION['developer_id'])): ?>
                    <a href="./developer/dashboard.php" 
                       class="w-full sm:w-auto px-8 py-3 bg-purple-500 hover:bg-purple-600 text-white font-semibold rounded-lg shadow-lg transition-all duration-200 hover:shadow-xl">
                        <i class="fas fa-tachometer-alt mr-2"></i>Developer Dashboard
                    </a>
                    <a href="./catalogue/index.php" 
                       class="w-full sm:w-auto px-8 py-3 bg-pink-500 hover:bg-pink-600 text-white font-semibold rounded-lg shadow-lg transition-all duration-200 hover:shadow-xl">
                        Browse Games
                    </a>
                <?php else: ?>
                    <a href="./register.php" 
                       class="w-full sm:w-auto px-8 py-3 bg-pink-500 hover:bg-pink-600 text-white font-semibold rounded-lg shadow-lg transition-all duration-200 hover:shadow-xl">
                        Get Started
                    </a>
                    <a href="./catalogue/index.php" 
                       class="w-full sm:w-auto px-8 py-3 bg-purple-500 hover:bg-purple-600 text-white font-semibold rounded-lg shadow-lg transition-all duration-200 hover:shadow-xl">
                        Browse Games
                    </a>
                <?php endif; ?>
            </div>

            <!-- Developer Section -->
            <?php if (!isset($_SESSION['user_id']) && !isset($_SESSION['developer_id'])): ?>
                <div class="bg-white/80 backdrop-blur-sm rounded-lg p-6 shadow-lg border border-purple-200 mb-12">
                    <h3 class="text-xl font-bold text-purple-600 mb-3">Are you a Game Developer?</h3>
                    <p class="text-purple-700 mb-4">Join our platform to publish and sell your games to thousands of players worldwide!</p>
                    <div class="flex flex-col sm:flex-row gap-3 justify-center">
                        <a href="./developer/register.php" 
                           class="px-6 py-2 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-md transition-colors">
                            <i class="fas fa-plus mr-2"></i>Register as Developer
                        </a>
                        <a href="./developer/login.php" 
                           class="px-6 py-2 border border-purple-600 text-purple-600 hover:bg-purple-50 font-medium rounded-md transition-colors">
                            <i class="fas fa-sign-in-alt mr-2"></i>Developer Login
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Features -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8 opacity-0">
                <div class="bg-white/70 backdrop-blur-sm p-6 rounded-lg shadow-lg">
                    <div class="w-12 h-12 bg-pink-100 rounded-lg flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Discover Amazing Games</h3>
                    <p class="text-gray-600 text-sm">Explore our vast collection of games across all genres and platforms.</p>
                </div>

                <div class="bg-white/70 backdrop-blur-sm p-6 rounded-lg shadow-lg">
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Connect with Friends</h3>
                    <p class="text-gray-600 text-sm">Add friends, share your favorite games, and play together.</p>
                </div>

                <div class="bg-white/70 backdrop-blur-sm p-6 rounded-lg shadow-lg">
                    <div class="w-12 h-12 bg-pink-100 rounded-lg flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 002 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2H5a2 2 0 00-2 2v2M7 7h10"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Build Your Library</h3>
                    <p class="text-gray-600 text-sm">Purchase games and access them anytime from your personal library.</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Mobile menu toggle
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const mobileMenu = document.getElementById('mobileMenu');

        mobileMenuBtn.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });

        // Close mobile menu when clicking outside
        document.addEventListener('click', (e) => {
            if (!mobileMenuBtn.contains(e.target) && !mobileMenu.contains(e.target)) {
                mobileMenu.classList.add('hidden');
            }
        });

        // Add Font Awesome for icons
        if (!document.querySelector('link[href*="font-awesome"]')) {
            const fontAwesome = document.createElement('link');
            fontAwesome.rel = 'stylesheet';
            fontAwesome.href = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css';
            document.head.appendChild(fontAwesome);
        }

        // GSAP Animation Timeline - Faster and Smoother
        document.addEventListener('DOMContentLoaded', function() {
            // Create timeline for sequential animations with faster timing
            const tl = gsap.timeline({ delay: 0.2 });

            // 1. Front mountain and tree come up from bottom together (first layer)
            tl.to("#mountain-front", {
                duration: 0.8,
                y: 0,
                opacity: 1,
                ease: "power2.out"
            })
            .to("#tree", {
                duration: 0.8,
                y: 0,
                opacity: 1,
                ease: "power2.out"
            }, "<") // Start at same time as mountain-front

            // 2. Sakura comes from left and back mountain from right (simultaneously)
            .to("#sakura", {
                duration: 0.7,
                x: 0,
                y: 0,
                opacity: 0.5,
                ease: "power2.out"
            }, "-=0.6") // Start before previous animations finish

            .to("#mountain-back", {
                duration: 0.7,
                x: 0,
                y: 0,
                opacity: 0.7,
                ease: "power2.out"
            }, "<") // Start at same time as sakura

            // 3. Sun comes up from bottom last with scaling effect
            .to("#sun", {
                duration: 1,
                y: 0,
                scale: 1,
                opacity: 1,
                ease: "back.out(1.7)",
                rotation: 25 // Maintain the rotation while animating
            }, "-=0.4"); // Start before previous animations finish

            // Add subtle continuous animations after initial sequence
            tl.call(() => {
                // Gentle floating animation for sun
                gsap.to("#sun", {
                    duration: 4,
                    y: "+=15",
                    repeat: -1,
                    yoyo: true,
                    ease: "sine.inOut"
                });

                // Slower continuous rotation for sun
                gsap.to("#sun", {
                    duration: 75,
                    rotation: "+=360",
                    repeat: -1,
                    ease: "none"
                });
            });
        });
    </script>
</body>

</html>