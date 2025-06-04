document.addEventListener('DOMContentLoaded', () => {
    const tree = document.getElementById('tree');
    const sun = document.getElementById('sun');
    const mountainLeft = document.getElementById('mountainLeft');
    const mountainRight = document.getElementById('mountainRight');
    const centralBox = document.getElementById('centralBox');
    const aperiorLogo = document.getElementById('aperiorLogo');
    const titleText = document.getElementById('titleText');
    const sakura = document.getElementById('sakura');
    const cloudsContainer = document.getElementById('cloudsContainer');
    
    const popupMenu = document.getElementById('popupMenu');
    const popupButton = document.getElementById('popupButton');
    const popupIconPath = document.getElementById('popupIconPath');

    let isPopupMenuOpen = false;
    let popupTimeline = null;

    if (popupMenu) {
        popupTimeline = gsap.timeline({
            paused: true,
            onComplete: () => { if (popupMenu) gsap.set(popupMenu, { pointerEvents: 'auto' }); },
            onReverseComplete: () => { if (popupMenu) gsap.set(popupMenu, { pointerEvents: 'none' }); }
        });

        popupTimeline.fromTo(popupMenu,
            { autoAlpha: 0, x: -20, scale: 0.95 },
            { autoAlpha: 1, x: 0, scale: 1, duration: 0.3, ease: 'power3.out' }
        );
    }
    
    function updatePopupIcon() {
        if (popupIconPath) {
            popupIconPath.setAttribute('d', isPopupMenuOpen ? "m19.5 8.25-7.5 7.5-7.5-7.5" : "m8.25 4.5 7.5 7.5-7.5 7.5");
        }
    }

    function handlePopupButtonClick() {
        isPopupMenuOpen = !isPopupMenuOpen;
        updatePopupIcon();
        if (popupTimeline) {
            if (isPopupMenuOpen) {
                gsap.set(popupMenu, { pointerEvents: 'none' }); 
                popupTimeline.play();
            } else {
                gsap.set(popupMenu, { pointerEvents: 'none' });
                if (popupTimeline.progress() > 0 || popupTimeline.isActive()) {
                    popupTimeline.reverse();
                } else {
                    gsap.set(popupMenu, { autoAlpha: 0, pointerEvents: 'none' });
                }
            }
        }
    }

    if (popupButton) {
        popupButton.addEventListener('click', handlePopupButtonClick);
    }

    function handleClickOutside(event) {
        if (isPopupMenuOpen && popupMenu && !popupMenu.contains(event.target) && popupButton && !popupButton.contains(event.target)) {
            isPopupMenuOpen = false;
            updatePopupIcon();
            if (popupTimeline && (popupTimeline.progress() > 0 || popupTimeline.isActive())) {
                gsap.set(popupMenu, { pointerEvents: 'none' });
                popupTimeline.reverse();
            } else {
                gsap.set(popupMenu, { autoAlpha: 0, pointerEvents: 'none' });
            }
        }
    }
    document.addEventListener('mousedown', handleClickOutside);

    const pageLoadTl = gsap.timeline({ defaults: { ease: 'power3.out' } });

    if (cloudsContainer) {
        pageLoadTl.fromTo(cloudsContainer,
            { opacity: 0, x: 100 },
            { opacity: 1, x: 0, duration: 1.2, ease: 'power1.inOut' },
            0 
        );
        gsap.to(cloudsContainer, { 
            x: "-=20",
            duration: 150,
            repeat: -1,
            yoyo: true,
            ease: "sine.inOut"
        });
    }

    if (tree) {
        pageLoadTl.fromTo(tree, { opacity: 0, x: -50 }, { opacity: 1, x: 0, duration: 1 }, 0);
    }
    if (sun) {
        pageLoadTl.fromTo(sun, { opacity: 0, y: -50, x: -50 }, { opacity: 1, y: 0, x: 0, duration: 1 }, "-=0.7");
        gsap.to(sun, { rotation: 360, duration: 60, repeat: -1, ease: "none" });
    }
    if (mountainLeft) {
        pageLoadTl.fromTo(mountainLeft, { opacity: 0, x: 50 }, { opacity: 1, x: 0, duration: 1 }, "-=0.7");
    }
    if (mountainRight) {
        pageLoadTl.fromTo(mountainRight, { opacity: 0, x: 50 }, { opacity: 1, x: 0, duration: 1 }, "-=0.8");
    }
    if (sakura && typeof window !== 'undefined' && window.innerWidth < 640) {
        pageLoadTl.fromTo(sakura, { opacity: 0, x: -30 }, { opacity: 1, x: 0, duration: 0.8, delay: 0.1 }, "-=0.6");
    }
    if (centralBox) {
        pageLoadTl.fromTo(centralBox, { opacity: 0, scale: 0.9 }, { opacity: 1, scale: 1, duration: 0.5 }, "-=0.5");
    }
    if (aperiorLogo) {
        pageLoadTl.fromTo(aperiorLogo, { opacity: 0, y: 20 }, { opacity: 1, y: 0, duration: 0.6 }, "-=0.4");
    }
    if (titleText) {
        pageLoadTl.fromTo(titleText, { opacity: 0, y: 20 }, { opacity: 1, y: 0, duration: 0.6 }, "-=0.3");
    }
});