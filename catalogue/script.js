gsap.registerPlugin(ScrollTrigger);

document.addEventListener('DOMContentLoaded', () => {
    // --- Element Selectors ---
    // const mobileMenuButton = document.getElementById('mobileMenuButton'); // Removed
    // const mobileMenuNav = document.getElementById('mobileMenuNav'); // Removed
    // const mobileMenuIconOpen = document.getElementById('mobileMenuIconOpen'); // Removed
    // const mobileMenuIconClose = document.getElementById('mobileMenuIconClose'); // Removed
    
    const dropdownContainer = document.querySelector('.category-dropdown-container');
    
    const carouselTrack = document.getElementById('carouselTrack');
    const carouselDotsContainer = document.getElementById('carouselDots');
    const carouselPrevBtn = document.getElementById('carouselPrevBtn');
    const carouselNextBtn = document.getElementById('carouselNextBtn');
    
    const gameGrid = document.getElementById('gameGrid');
    
    const header = document.getElementById('header');
    const featuredSection = document.getElementById('featuredSection');
    const featuredTitle = document.getElementById('featuredTitle');
    const gameCarouselContainer = document.getElementById('gameCarouselContainer');
    const catalogSectionContainer = document.getElementById('catalog');
    const catalogTitle = document.getElementById('catalogTitle');
    const categoriesSidebar = document.getElementById('categoriesSidebar');

    // --- New Catalogue Popup Menu Elements ---
    const catalogueMenuButton = document.getElementById('catalogueMenuButton');
    const cataloguePopupMenu = document.getElementById('cataloguePopupMenu');
    const catalogueMenuIconPath = document.getElementById('catalogueMenuIconPath');
    let isCataloguePopupMenuOpen = false;
    let cataloguePopupTimeline = null;

    // --- Catalogue Popup Menu Logic ---
    if (cataloguePopupMenu && catalogueMenuButton && catalogueMenuIconPath) {
        cataloguePopupTimeline = gsap.timeline({
            paused: true,
            onStart: () => { // Use onStart to set pointerEvents when opening
                if (isCataloguePopupMenuOpen && cataloguePopupMenu) {
                    gsap.set(cataloguePopupMenu, { pointerEvents: 'auto' });
                }
            },
            onReverseComplete: () => {
                if (cataloguePopupMenu) {
                    gsap.set(cataloguePopupMenu, { pointerEvents: 'none' });
                }
            }
        });

        cataloguePopupTimeline.fromTo(cataloguePopupMenu,
            { autoAlpha: 0, y: -10, scale: 0.95 }, // Adjusted for dropdown from top
            { autoAlpha: 1, y: 0, scale: 1, duration: 0.25, ease: 'power2.out' }
        );

        function updateCatalogueMenuIcon() {
            catalogueMenuIconPath.setAttribute('d', isCataloguePopupMenuOpen ? "M6 18L18 6M6 6l12 12" : "M4 6h16M4 12h16M4 18h16");
        }

        function handleCatalogueMenuButtonClick() {
            isCataloguePopupMenuOpen = !isCataloguePopupMenuOpen;
            updateCatalogueMenuIcon();
            if (isCataloguePopupMenuOpen) {
                // No need to set pointerEvents here, onStart of timeline handles it
                cataloguePopupTimeline.play();
            } else {
                gsap.set(cataloguePopupMenu, { pointerEvents: 'none' }); // Set immediately for closing
                if (cataloguePopupTimeline.progress() > 0 || cataloguePopupTimeline.isActive()) {
                    cataloguePopupTimeline.reverse();
                } else {
                     // If timeline never played (e.g. closed before animation finished once)
                    gsap.set(cataloguePopupMenu, { autoAlpha: 0, pointerEvents: 'none' });
                }
            }
        }

        catalogueMenuButton.addEventListener('click', handleCatalogueMenuButtonClick);

        function handleCatalogueClickOutside(event) {
            if (isCataloguePopupMenuOpen &&
                cataloguePopupMenu && !cataloguePopupMenu.contains(event.target) &&
                catalogueMenuButton && !catalogueMenuButton.contains(event.target)) {
                
                isCataloguePopupMenuOpen = false;
                updateCatalogueMenuIcon();
                gsap.set(cataloguePopupMenu, { pointerEvents: 'none' });
                if (cataloguePopupTimeline.progress() > 0 || cataloguePopupTimeline.isActive()) {
                    cataloguePopupTimeline.reverse();
                } else {
                    gsap.set(cataloguePopupMenu, { autoAlpha: 0, pointerEvents: 'none' });
                }
            }
        }
        document.addEventListener('mousedown', handleCatalogueClickOutside);

        // Close popup when an anchor link inside it is clicked
        cataloguePopupMenu.querySelectorAll('a.catalogue-nav-link').forEach(link => {
            link.addEventListener('click', () => {
                if (isCataloguePopupMenuOpen) {
                    isCataloguePopupMenuOpen = false;
                    updateCatalogueMenuIcon();
                    gsap.set(cataloguePopupMenu, { pointerEvents: 'none' });
                    if (cataloguePopupTimeline.progress() > 0 || cataloguePopupTimeline.isActive()) {
                        cataloguePopupTimeline.reverse();
                    } else {
                         gsap.set(cataloguePopupMenu, { autoAlpha: 0, pointerEvents: 'none' });
                    }
                    // For anchor links like #catalog, smooth scroll if desired
                    const targetId = link.getAttribute('href');
                    if (targetId && targetId.startsWith('#')) {
                        const targetElement = document.querySelector(targetId);
                        if (targetElement) {
                            // Basic scroll, replace with smooth scroll if GSAP ScrollToPlugin is used
                            // window.scrollTo({ top: targetElement.offsetTop - header.offsetHeight, behavior: 'smooth' });
                        }
                    }
                }
            });
        });
    }

    // --- Remove Old Mobile Menu Logic ---
    // The block from "let mobileMenuOpen = false;" down to its event listener is removed.

    // --- Category Dropdown ---
    const categoryData = [
        { title: "Action", items: ["Adventure", "FPS", "Third-person", "Fighting"] },
        { title: "Adventure", items: ["Open World", "Puzzle", "Platformer"] },
        { title: "Role-Playing", items: ["MMORPG", "Single-player", "Turn-based"] },
        { title: "Simulation", items: ["Life Simulation", "Vehicle Simulation", "Strategy"] },
        { title: "Sports", items: ["Football", "Basketball", "Racing"] },
        { title: "Indie", items: ["Art Games", "Experimental", "Narrative"] },
        { title: "Multiplayer", items: ["Co-op", "Competitive", "Battle Royale"] },
        { title: "Free-to-Play", items: ["Battle Pass", "Microtransactions", "Cosmetics"] }
    ];

    if (dropdownContainer) {
        categoryData.forEach(cat => {
            const dropdownDiv = document.createElement('div');
            dropdownDiv.className = 'border-b border-pink-300 pb-2';
            dropdownDiv.innerHTML = `
                <button class="category-toggle w-full flex justify-between items-center text-[#ff5cf4] font-medium hover:text-[#ff37f0] transition-colors">
                    <span>${cat.title}</span>
                    <svg class="dropdown-icon w-5 h-5 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div class="dropdown-content overflow-hidden transition-all duration-300 ease-in-out max-h-0 opacity-0">
                    <div class="mt-2 ml-4 space-y-1 py-1">
                        ${cat.items.map((item, index) => `
                            <a href="#${item.toLowerCase().replace(/\s+/g, "-")}" 
                               class="dropdown-item block text-[#ff5cf4] hover:text-[#ff37f0] hover:translate-x-1 transition-all text-sm py-1 transform -translate-y-2"
                               style="transition-delay: 0ms;">
                                ${item}
                            </a>`).join('')}
                    </div>
                </div>
            `;
            dropdownContainer.appendChild(dropdownDiv);
        });

        document.querySelectorAll('.category-toggle').forEach(button => {
            button.addEventListener('click', () => {
                const content = button.nextElementSibling;
                const icon = button.querySelector('.dropdown-icon');
                const items = content.querySelectorAll('.dropdown-item');
                const isOpen = content.classList.contains('max-h-[500px]');

                if (isOpen) {
                    content.classList.remove('max-h-[500px]', 'opacity-100');
                    content.classList.add('max-h-0', 'opacity-0');
                    icon.classList.remove('transform', 'rotate-180');
                    items.forEach(item => {
                        item.style.transitionDelay = '0ms';
                        item.classList.add('transform', '-translate-y-2');
                    });
                } else {
                    content.classList.remove('max-h-0', 'opacity-0');
                    content.classList.add('max-h-[500px]', 'opacity-100');
                    icon.classList.add('transform', 'rotate-180');
                    items.forEach((item, index) => {
                        item.style.transitionDelay = `${index * 50}ms`;
                        item.classList.remove('transform', '-translate-y-2');
                    });
                }
            });
        });
    }
    
    // --- Unified Game Card HTML Creation ---
    function createGameCardHTML(game, isCarouselCard = false) {
        const wishlistBtnClass = game.in_wishlist ? 'in-wishlist' : '';
        const wishlistBtnText = game.in_wishlist ? 'In Wishlist' : 'Add to Wishlist';
        const wishlistBtnAction = game.in_wishlist ? 'remove' : 'add';

        const cartBtnClass = game.in_cart ? 'in-cart' : '';
        const cartBtnText = game.in_cart ? `In Cart (${game.cart_quantity})` : 'Add to Cart';
        
        if (isCarouselCard) {
            return `
                <div class="bg-white rounded-lg overflow-hidden shadow-lg hover:shadow-xl transition-all">
                    <div class="relative h-[180px] bg-gray-200 overflow-hidden">
                        <img src="${game.image_url || '../assets/image_placeholder.png'}" alt="${game.title}" class="absolute inset-0 w-full h-full object-cover transition-transform duration-700" />
                        ${game.discount ? `<div class="absolute top-2 right-2 bg-red-500 text-white text-xs font-bold px-2 py-1 rounded">${game.discount} OFF</div>` : ''}
                    </div>
                    <div class="p-4">
                        <h3 class="text-lg font-semibold text-gray-800 truncate">${game.title}</h3>
                        <div class="flex justify-between items-center mt-2">
                            <p class="text-xl font-bold text-pink-500">$${parseFloat(game.price).toFixed(2)}</p>
                            ${game.discount ? `<p class="text-sm text-gray-500 line-through">$${(parseFloat(game.price) / (1 - parseFloat(game.discount)/100)).toFixed(2)}</p>` : ''}
                        </div>
                        <button 
                            class="action-button wishlist-btn ${wishlistBtnClass} mt-3 w-full py-2 px-4" 
                            data-game-id="${game.id}" 
                            data-action="${wishlistBtnAction}">
                            ${wishlistBtnText}
                        </button>
                        <button 
                            class="action-button cart-btn ${cartBtnClass} mt-1 w-full py-2 px-4" 
                            data-game-id="${game.id}"
                            data-action="add"> 
                            ${cartBtnText}
                        </button>
                    </div>
                </div>
            `;
        } else {
            return `
                <div class="game-card bg-white p-4 rounded-lg shadow-md">
                    <img src="${game.image_url || '../assets/image_placeholder.png'}" alt="${game.title}" class="w-full h-40 object-cover rounded mb-2">
                    <h3 class="text-lg font-semibold text-pink-700">${game.title}</h3>
                    <p class="text-sm text-gray-600">${game.genre || 'N/A'}</p>
                    <p class="text-md font-bold text-pink-500 my-1">$${parseFloat(game.price).toFixed(2)}</p>
                    <button 
                        class="action-button wishlist-btn ${wishlistBtnClass}" 
                        data-game-id="${game.id}" 
                        data-action="${wishlistBtnAction}">
                        ${wishlistBtnText}
                    </button>
                    <button 
                        class="action-button cart-btn ${cartBtnClass}" 
                        data-game-id="${game.id}"
                        data-action="add"> 
                        ${cartBtnText}
                    </button>
                </div>
            `;
        }
    }

    // --- Game Carousel (using gamesData) ---
    if (carouselTrack && carouselDotsContainer && carouselPrevBtn && carouselNextBtn) {
        const cardWidth = 320; 
        let currentCarouselIndex = 0;
        let autoScrollInterval;
        let carouselGames = []; // To store the games for the carousel

        function initializeCarouselData() {
            // Use the new carouselData passed from PHP
            if (typeof carouselData !== 'undefined' && carouselData.length > 0) {
                carouselGames = carouselData;
            } else {
                // Fallback if carouselData is empty for some reason, though PHP should handle this
                // Or, if you prefer the carousel to be empty if no featured games,
                // you can simply set carouselGames = [] here.
                // For robustness, let's keep a fallback to gamesData if carouselData is unexpectedly empty.
                if (typeof gamesData !== 'undefined' && gamesData.length > 0) {
                    carouselGames = gamesData.slice(0, Math.min(5, gamesData.length));
                } else {
                    carouselGames = [];
                }
            }
        }
        
        function renderCarousel() {
            if (!carouselTrack || !carouselDotsContainer) return; 

            carouselTrack.innerHTML = carouselGames.map((game, index) => {
                const isActive = index === currentCarouselIndex;
                const distance = Math.abs(index - currentCarouselIndex);
                const isBefore = index < currentCarouselIndex;
                const isAfter = index > currentCarouselIndex;

                let outerCardClasses = `relative flex-shrink-0 w-[280px] mx-5 transition-all duration-700 ease-[cubic-bezier(0.25,0.1,0.25,1.0)] cursor-pointer`;
                if (isActive) outerCardClasses += ` z-20 scale-110 shadow-2xl brightness-100`;
                else if (distance === 1) outerCardClasses += ` z-10 scale-90 opacity-70 blur-[1px]`;
                else outerCardClasses += ` z-0 scale-80 opacity-50 blur-[2px]`;
                
                if (isBefore) outerCardClasses += ` -rotate-y-5`;
                else if (isAfter) outerCardClasses += ` rotate-y-5`;
                
                const transformOrigin = isActive ? "center" : isBefore ? "right center" : "left center";
                const innerCardHTML = createGameCardHTML(game, true); 
                
                const imageTransformStyle = isActive ? 'scale(1.05)' : 'scale(1)';
                
                return `
                    <div class="${outerCardClasses}" style="transform-origin: ${transformOrigin};" data-index="${index}">
                        ${innerCardHTML.replace(
                            /<img src="([^"]*)" alt="([^"]*)" class="([^"]*)" \/>/, 
                            `<img src="$1" alt="$2" class="$3" style="transform: ${imageTransformStyle};" />`
                        )}
                    </div>
                `;
            }).join('');

            carouselTrack.style.transform = `translateX(calc(50% - ${currentCarouselIndex * cardWidth + cardWidth / 2}px))`;
            
            carouselDotsContainer.innerHTML = carouselGames.map((_, index) => 
                `<button data-index="${index}" class="carousel-dot transition-all duration-500 rounded-full ${currentCarouselIndex === index ? "bg-[#ffa9f9] w-6 h-3" : "bg-gray-300 w-3 h-3 hover:bg-pink-300"}" aria-label="Go to slide ${index + 1}"></button>`
            ).join('');

            document.querySelectorAll('.carousel-dot').forEach(dot => {
                dot.addEventListener('click', (e) => setCurrentCarouselIndex(parseInt(e.target.dataset.index)));
            });
            carouselTrack.querySelectorAll('[data-index]').forEach(card => {
                card.addEventListener('click', (e) => setCurrentCarouselIndex(parseInt(e.currentTarget.dataset.index)));
            });
        }

        function setCurrentCarouselIndex(index) {
            if (index < 0 || index >= carouselGames.length) return;
            currentCarouselIndex = index;
            renderCarousel();
            resetAutoScroll();
        }

        function goToNextCarousel() {
            if (carouselGames.length === 0) return;
            setCurrentCarouselIndex((currentCarouselIndex === carouselGames.length - 1) ? 0 : currentCarouselIndex + 1);
        }
        function goToPrevCarousel() {
            if (carouselGames.length === 0) return;
            setCurrentCarouselIndex((currentCarouselIndex === 0) ? carouselGames.length - 1 : currentCarouselIndex - 1);
        }

        function startAutoScroll() {
            if (carouselGames.length > 1) { 
                autoScrollInterval = setInterval(goToNextCarousel, 5000);
            }
        }
        function resetAutoScroll() {
            clearInterval(autoScrollInterval);
            startAutoScroll();
        }
        
        carouselNextBtn.addEventListener('click', goToNextCarousel);
        carouselPrevBtn.addEventListener('click', goToPrevCarousel);
        
        initializeCarouselData(); 
        if (carouselGames.length > 0) {
            renderCarousel();
            startAutoScroll();
        } else {
            if(carouselTrack) carouselTrack.innerHTML = '<p class="text-center text-gray-500 w-full">No featured games available.</p>';
            if(carouselDotsContainer) carouselDotsContainer.innerHTML = '';
        }
    }

    // --- Game Grid (using gamesData) ---
    function renderGameGrid() {
        if (gameGrid) {
            if (typeof gamesData !== 'undefined' && gamesData.length > 0) {
                gameGrid.innerHTML = gamesData.map(game => createGameCardHTML(game, false)).join('');
            } else {
                gameGrid.innerHTML = '<p class="text-center col-span-full text-gray-500">No games available at the moment.</p>';
            }
        }
    }
    renderGameGrid(); 

    // --- Event Listeners for Wishlist/Cart Buttons ---
    function addEventListenersToButtons() {
        document.querySelectorAll('.wishlist-btn').forEach(button => {
            button.addEventListener('click', handleWishlistAction);
        });
        document.querySelectorAll('.cart-btn').forEach(button => {
            button.addEventListener('click', handleCartAction);
        });
    }
    addEventListenersToButtons(); 

    async function handleWishlistAction(event) {
        const button = event.target.closest('.wishlist-btn'); 
        if (!button) return;
        const gameId = button.dataset.gameId;
        let action = button.dataset.action;

        if (!isUserLoggedIn) {
            alert('Please log in to manage your wishlist.');
            window.location.href = '../login.php';
            return;
        }

        const formData = new FormData();
        formData.append('game_id', gameId);
        formData.append('action', action);

        try {
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
                alert('Error: ' + result.message);
            }
        } catch (error) {
            console.error('Wishlist action failed:', error);
            alert('An error occurred. Please try again.');
        }
    }

    async function handleCartAction(event) {
        const button = event.target.closest('.cart-btn'); 
        if (!button) return;
        const gameId = button.dataset.gameId;
        
        if (!isUserLoggedIn) {
            alert('Please log in to add items to your cart.');
            window.location.href = '../login.php';
            return;
        }

        const formData = new FormData();
        formData.append('game_id', gameId);
        formData.append('quantity', 1); 
        formData.append('action', 'add');

        try {
            const response = await fetch('../update_cart.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();

            if (result.success) {
                const gameInDataSource = gamesData.find(g => g.id.toString() === gameId);
                if (gameInDataSource) {
                    gameInDataSource.in_cart = true;
                    gameInDataSource.cart_quantity = (gameInDataSource.cart_quantity || 0) + 1;
                     button.textContent = `In Cart (${gameInDataSource.cart_quantity})`;
                } else {
                     button.textContent = 'Added to Cart!';
                }
                button.classList.add('in-cart');
            } else {
                alert('Error: ' + result.message);
            }
        } catch (error) {
            console.error('Cart action failed:', error);
            alert('An error occurred. Please try again.');
        }
    }

    // --- GSAP Animations ---
    if(header) gsap.from(header, { y: -60, opacity: 0, duration: 0.8, ease: 'power3.out', delay: 0.2 });
    if(featuredTitle) gsap.from(featuredTitle, { y: 40, opacity: 0, duration: 0.7, ease: 'power2.out', delay: 0.4 });
    if(gameCarouselContainer) gsap.from(gameCarouselContainer, { opacity: 0, scale: 0.9, duration: 0.8, ease: 'power3.out', delay: 0.6 });

    if(featuredTitle && featuredSection) {
        gsap.to(featuredTitle, {
            yPercent: -15, ease: 'none',
            scrollTrigger: { trigger: featuredSection, start: 'top bottom', end: 'bottom top', scrub: 0.5 }
        });
    }

    if (catalogSectionContainer) {
        if(catalogTitle) {
            gsap.from(catalogTitle, {
                y: 40, opacity: 0, duration: 0.7, ease: 'power2.out',
                scrollTrigger: { trigger: catalogTitle, start: 'top 85%', toggleActions: 'play none none none' }
            });
        }
        if(categoriesSidebar) {
            gsap.from(categoriesSidebar, {
                x: -50, opacity: 0, duration: 0.8, ease: 'power3.out',
                scrollTrigger: { trigger: categoriesSidebar, start: 'top 85%', toggleActions: 'play none none none' }
            });
        }
        if (gameGrid && gameGrid.children.length > 0) {
            gsap.from(gsap.utils.toArray(gameGrid.children), {
                opacity: 0, y: 50, duration: 0.5, ease: 'power2.out', stagger: 0.1,
                scrollTrigger: { trigger: gameGrid, start: 'top 85%', toggleActions: 'play none none none' }
            });
        }
    }
});