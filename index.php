<?php session_start();?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aprerior Homepage</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.13.0/dist/gsap.min.js"></script>
    <link rel="stylesheet" href="./styles.css">
</head>
<body>

    <div class="relative w-screen h-screen overflow-hidden bg-gradient-to-br from-[#FFF7AD] to-[#FFA9F9]">

        <div id="cloudsContainer" class="absolute inset-0 z-0 overflow-hidden pointer-events-none opacity-0">
            <div class="absolute top-[5%] left-[5%] w-[30vw] h-[20vh]">
                <div class="absolute bg-white rounded-full w-[60%] h-[60%] top-0 left-[5%] opacity-30 blur-lg animate-pulse-slow"></div>
                <div class="absolute bg-white rounded-full w-[50%] h-[50%] top-[20%] left-[30%] opacity-20 blur-xl animate-pulse-medium"></div>
                <div class="absolute bg-white rounded-full w-[70%] h-[70%] top-[10%] left-[50%] opacity-25 blur-2xl animate-pulse-fast"></div>
                <div class="absolute bg-white rounded-full w-[40%] h-[40%] bottom-0 right-[10%] opacity-30 blur-lg animate-pulse-slow"></div>
            </div>
            <div class="absolute top-[15%] right-[10%] w-[25vw] h-[15vh]">
                <div class="absolute bg-white rounded-full w-[70%] h-[70%] -top-[10%] -left-[15%] opacity-40 blur-md animate-pulse-medium"></div>
                <div class="absolute bg-white rounded-full w-[50%] h-[50%] top-[25%] left-[20%] opacity-30 blur-lg animate-pulse-fast"></div>
                <div class="absolute bg-white rounded-full w-[60%] h-[60%] bottom-0 right-0 opacity-35 blur-xl animate-pulse-slow"></div>
            </div>
            <div class="absolute bottom-[10%] left-[20%] w-[20vw] h-[12vh]">
                <div class="absolute bg-white rounded-full w-full h-full opacity-20 blur-2xl animate-pulse-fast"></div>
                <div class="absolute bg-white rounded-full w-[80%] h-[80%] top-[10%] left-[10%] opacity-30 blur-xl animate-pulse-medium"></div>
            </div>
            <div class="absolute top-[5%] right-[35%] w-[15vw] h-[10vh]">
                <div class="absolute bg-white rounded-full w-[80%] h-[80%] opacity-25 blur-lg animate-pulse-slow"></div>
                <div class="absolute bg-white rounded-full w-[60%] h-[60%] top-[20%] left-[20%] opacity-35 blur-md animate-pulse-fast"></div>
            </div>
            <div class="absolute bg-white rounded-full w-[10vw] h-[5vh] top-[30%] left-[40%] opacity-10 blur-2xl animate-pulse-medium"></div>
            <div class="absolute bg-white rounded-full w-[12vw] h-[6vh] bottom-[20%] right-[25%] opacity-15 blur-xl animate-pulse-slow"></div>
        </div>

        <img
            id="tree"
            src="./assets/tree.svg"
            alt="Tree decoration"
            class="absolute -bottom-5 lg:-left-10 -left-[115px] w-175 md:w-200 md:-left-40 md:-bottom-10 lg:w-270 z-10 opacity-0"
        />
        <img
            id="sun"
            src="./assets/sun.svg"
            alt="sun decoration"
            class="absolute 
                   -top-[120px] -left-[110px] w-[305px] 
                   md:-top-[150px] md:-left-[120px] md:w-[350px] 
                   lg:-top-[190px] lg:-left-[180px] lg:w-[480px] 
                   z-10 opacity-0"
        />
        <img
            id="mountainLeft"
            src="./assets/mountain.svg"
            alt="mountain decoration"
            class="absolute lg:-bottom-30 md:w-120 md:-bottom-15 md:left-60 lg:right-60 z-9 bottom-[40px] -right-[75px] w-110 lg:w-270 lg:z-8 opacity-0"
        />
        <img
            id="mountainRight"
            src="./assets/mountain.svg"
            alt="mountain decoration"
            class="absolute lg:-bottom-60 md:w-190 md:-bottom-20 lg:-right-100 z-8 -bottom-[50px] -right-[225px] w-120 lg:w-270 lg:z-9 opacity-0"
        />
        <img
            id="sakura"
            src="./assets/sakura.svg"
            alt="Mobile sakura"
            class="absolute sm:hidden w-100 bottom-7 -left-[175px] z-7 mb-4 opacity-0"
        />

        <div id="centralBox" class="absolute inset-0 flex items-center justify-center z-20 opacity-0">
            <div class="relative bg-[#EF91C5] p-4 md:p-6 rounded-3xl md:rounded-4xl shadow-lg text-center flex flex-row items-center">
                <img
                    id="aperiorLogo"
                    src="./assets/aperior.svg"
                    alt="Aperior Logo"
                    class="w-16 h-16 md:w-24 md:h-24 lg:w-32 lg:h-32 opacity-0 self-center" 
                />
                <div class="flex flex-col items-center ml-2 sm:ml-3 md:ml-4">
                    <h1 id="titleText" class="text-[28px] sm:text-[35px] md:text-[60px] lg:text-[80px] font-bold text-[#FFF7AD] border-0 rounded-full px-2 md:px-4 py-1 md:py-2 apply-custom-title-font text-glow-yellow opacity-0">APERIOR</h1>
                    <?php if (isset($_SESSION['username'])): ?>
                        <span id="welcomeMessage" class="mt-1 md:mt-2 text-white text-sm md:text-base lg:text-lg apply-custom-title-font text-glow-yellow">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
                    <?php endif; ?>
                </div>
                
                <button
                    id="popupButton"
                    class="ml-3 md:ml-6 p-1 md:p-2 bg-white/20 hover:bg-white/40 rounded-full text-yellow-200 focus:outline-none transition-colors self-center" 
                    aria-label="Open menu"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 md:w-6 md:h-6">
                        <path id="popupIconPath" stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                    </svg>
                </button>

                <div
                    id="popupMenu"
                    class="absolute top-1/2 right-0 transform -translate-y-1/2 translate-x-[calc(100%+16px)]
                           bg-white/90 backdrop-blur-md p-3 md:p-4 rounded-xl shadow-2xl space-y-2 md:space-y-3
                           opacity-0 pointer-events-none w-auto min-w-[144px] md:min-w-[176px] z-30" @
                >
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="./catalogue/index.php" class="block text-pink-600 hover:text-pink-800 hover:underline text-sm md:text-base text-left">Browse Catalogue</a>
                        <a href="./inventory.php" class="block text-pink-600 hover:text-pink-800 hover:underline text-sm md:text-base text-left">My Inventory</a>
                        <a href="./wishlist.php" class="block text-pink-600 hover:text-pink-800 hover:underline text-sm md:text-base text-left">My Wishlist</a>
                        <a href="./cart.php" class="block text-pink-600 hover:text-pink-800 hover:underline text-sm md:text-base text-left">Shopping Cart</a>
                        <a href="./friends.php" class="block text-pink-600 hover:text-pink-800 hover:underline text-sm md:text-base text-left">Friends</a>
                        <div class="border-t border-pink-200 my-1 md:my-1.5"></div>
                        <a href="./logout.php" class="block text-pink-600 hover:text-pink-800 hover:underline text-sm md:text-base text-left">Logout</a>
                    <?php else: ?>
                        <a href="./login.php" class="block text-pink-600 hover:text-pink-800 hover:underline text-sm md:text-base text-left">Login</a>
                        <a href="./register.php" class="block text-pink-600 hover:text-pink-800 hover:underline text-sm md:text-base text-left">Register</a>
                        <a href="./catalogue/index.php" class="block text-pink-600 hover:text-pink-800 hover:underline text-sm md:text-base text-left">Browse</a>
                    <?php endif; ?>
                </div>

                <img
                    src="./assets/vine-left.svg"
                    alt="Top-left decoration"
                        class="absolute top-0 left-2 right-0 w-48 h-48 lg:w-38 lg:h-38 -mt-4 -ml-10 pointer-events-none"
                />
                <img
                    src="./assets/wheat.svg"
                    alt="Bottom-right decoration"
                    class="absolute bottom-0 right-6 
                           w-[120px] h-[210px] -mb-17 -mr-14 
                           md:w-[130px] md:h-[130px] md:-mb-2 md:-mr-16 
                           lg:w-[140px] lg:h-[140px] lg:-mb-2 lg:-mr-17 pointer-events-none"
                />
            </div>
        </div>
    </div>

    <script src="./js/script.js"></script>
</body>
</html>