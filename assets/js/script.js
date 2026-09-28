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

    // Check for PayMongo return parameters
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('paymongo_success')) {
        const refId = urlParams.get('ref_id');
        if (typeof window.showToast === 'function') {
            window.showToast(`Payment Confirmed via PayMongo! Ref: ${refId || ''}`, 'success');
        }
        if (refId) {
            fetch(`actions/bookings_action.php?action=get_single&ref_id=${encodeURIComponent(refId)}`)
                .then(r => r.json())
                .then(data => {
                    if (data.success && data.booking && typeof window.showInvoiceModal === 'function') {
                        const b = data.booking;
                        window.showInvoiceModal(b.ref_id, {
                            roomName: b.room_name || 'Room Stay',
                            checkIn: b.check_in_date || b.check_in,
                            checkOut: b.check_out_date || b.check_out,
                            nights: parseInt(b.nights || 1),
                            guests: (parseInt(b.adults || 1) + parseInt(b.children || 0)),
                            adults: parseInt(b.adults || 1),
                            children: parseInt(b.children || 0),
                            roomsCount: parseInt(b.rooms_count || 1),
                            total: parseFloat(b.total_amount || b.total_price)
                        }, {
                            payment_method: b.payment_method,
                            booking_status: b.booking_status,
                            paid_amount: parseFloat(b.paid_amount !== undefined ? b.paid_amount : b.total_amount),
                            balance_due: parseFloat(b.balance_due !== undefined ? b.balance_due : 0)
                        });
                    }
                }).catch(err => console.error('Error opening voucher:', err));
        }
        window.history.replaceState({}, document.title, window.location.pathname + (window.location.hash || ''));
    } else if (urlParams.has('paymongo_cancelled')) {
        if (typeof window.showToast === 'function') {
            window.showToast("PayMongo checkout was cancelled. You can complete your reservation anytime.", "info");
        }
        window.history.replaceState({}, document.title, window.location.pathname + (window.location.hash || ''));
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
