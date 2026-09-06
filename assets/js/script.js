/**
 * MAIN CLIENT APPLICATION ENTRY POINT
 * Coordinates modular frontend components, loader dismissal, GSAP entrance animations,
 * and page lifecycle initialization.
 * 
 * Modular components located in: /assets/js/modules/
 * - state.js     : Application state & persistence
 * - ui.js        : Toast alerts, modals, password toggles, navigation
 * - router.js    : Client-side routing & route guards
 * - carousel.js  : Hero slider & continuous marquee
 * - auth.js      : Session management, forms, Google OAuth, logout
 * - rooms.js     : Room fetching, catalog rendering, date constraints
 * - booking.js   : Reservations, payment options, receipt uploads
 * - reviews.js   : Room-specific review list & submissions
 * - invoice.js   : Booking voucher modal & printing
 */

/**
 * Main application initialization routine.
 * Handles splash screen loader dismissal, asynchronous asset fetching,
 * auth state rendering, date sync, and URL hash routing.
 */
async function init() {
    const skipLoader = sessionStorage.getItem('skipLoader') === 'true';
    if (skipLoader) {
        sessionStorage.removeItem('skipLoader');
    }

    document.body.style.overflow = 'hidden';

    // Bootstrap functional modules immediately so UI and interactions are ready
    if (typeof window.updateAuthUI === 'function') window.updateAuthUI();
    if (typeof window.setupMobileNav === 'function') window.setupMobileNav();
    if (typeof window.initGuestSelector === 'function') window.initGuestSelector();
    if (typeof window.initDateConstraints === 'function') window.initDateConstraints();
    if (typeof window.fetchRooms === 'function') window.fetchRooms();

    // Check for deep-linked hash navigation immediately
    const hash = window.location.hash.replace('#', '');
    if (hash && typeof window.router === 'function') {
        window.router(hash);
    }

    // Parallel fetch for visual carousel and marquee elements in the background
    Promise.all([
        typeof window.initHeroCarousel === 'function' ? window.initHeroCarousel() : Promise.resolve(),
        typeof window.initGalleryCarousel === 'function' ? window.initGalleryCarousel() : Promise.resolve(),
        typeof window.loadHomepageReviews === 'function' ? window.loadHomepageReviews() : Promise.resolve(),
        typeof window.setupMarquee === 'function' ? window.setupMarquee() : Promise.resolve()
    ]).catch(err => console.error('Carousel/Marquee load error:', err));

    // Handle splash loader dismissal
    if (skipLoader) {
        const loader = document.getElementById('loader');
        if (loader) loader.style.display = 'none';
        document.body.style.overflow = 'auto';
    } else if (window.gsap) {
        const tl = gsap.timeline({
            onComplete: () => {
                const loader = document.getElementById('loader');
                if (loader) loader.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        });
        tl.to("#loader", { y: "-100%", duration: 0.6, ease: "power4.in", delay: 1.5 });
    } else {
        setTimeout(() => {
            const loader = document.getElementById('loader');
            if (loader) {
                loader.classList.add('hidden');
                setTimeout(() => { 
                    loader.style.display = 'none'; 
                    document.body.style.overflow = 'auto'; 
                }, 500);
            }
        }, 2500);
    }

    // GSAP entrance typography animations
    if (window.gsap) {
        gsap.from(".gsap-hero-text", {
            y: 50, 
            opacity: 0, 
            duration: 1, 
            stagger: 0.2, 
            delay: 0.2, 
            ease: "power3.out"
        });
    }
}

// Global bootstrap binding
window.init = init;

// DOM Ready Event Listener: Initializes page visibility and starts app
if (document.readyState === 'loading') {
    window.addEventListener('DOMContentLoaded', () => {
        if (typeof window.initPageVisibility === 'function') {
            window.initPageVisibility();
        }
        init();
    });
} else {
    if (typeof window.initPageVisibility === 'function') {
        window.initPageVisibility();
    }
    init();
}
