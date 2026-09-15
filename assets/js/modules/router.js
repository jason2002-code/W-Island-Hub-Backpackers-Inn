/**
 * ROUTER MODULE
 * Provides client-side Single Page Application (SPA) routing, route protection,
 * and page lifecycle callbacks.
 */

/**
 * Toggles visibility of page sections based on section ID.
 * Handles admin route protection, auto-closing mobile menus, and page transition side effects.
 * 
 * @param {string} pageId - Target section ID (e.g. 'home', 'rooms', 'about', 'contact', 'login', 'register', 'rules', 'booking-summary').
 */
function router(pageId, anchorId) {
    // If pageId is 'discovery-bohol', route to 'home' and scroll to the section
    if (pageId === 'discovery-bohol') {
        pageId = 'home';
        anchorId = 'discovery-bohol';
    }
    // If pageId is 'inn-amenities' or 'amenities', route to 'home' and scroll to the amenities section
    if (pageId === 'inn-amenities' || pageId === 'amenities') {
        pageId = 'home';
        anchorId = 'inn-amenities';
    }

    // Close mobile menu if currently open
    const menu = document.getElementById('mobile-menu');
    if (menu && !menu.classList.contains('hidden')) {
        menu.classList.add('hidden');
    }

    // Close user dropdown if open
    if (typeof window.closeUserDropdown === 'function') {
        window.closeUserDropdown();
    }

    // Security & Role-Based Route Guards
    if (pageId === 'admin-dashboard' || pageId === 'admin') {
        const currentUser = window.appState ? window.appState.currentUser : null;
        if (currentUser && currentUser.role === 'admin') {
            window.location.href = 'admin/dashboard.php';
        } else {
            router('home');
        }
        return;
    }

    // Prevent authenticated users from accessing login or registration forms
    if (pageId === 'login' || pageId === 'register' || pageId === 'forgot-password') {
        const currentUser = window.appState ? window.appState.currentUser : null;
        if (currentUser) {
            if (currentUser.role === 'admin') {
                window.location.href = 'admin/dashboard.php';
            } else {
                router('home');
                if (typeof window.showToast === 'function') {
                    window.showToast(`You are already logged in as ${currentUser.name ? currentUser.name.split(' ')[0] : 'User'}.`, 'info');
                }
            }
            return;
        }
    }

    // Hide all page sections (explicit display:none to override layout conflicts)
    document.querySelectorAll('.page-section').forEach(el => {
        el.classList.remove('active');
        el.style.display = 'none';
    });
    
    // Display the requested target section, falling back to 'home' if not found
    let target = document.getElementById(pageId);
    if (!target) {
        target = document.getElementById('home');
    }

    if (target) {
        target.classList.add('active');
        target.style.display = 'block';
        if (anchorId) {
            setTimeout(() => {
                const el = document.getElementById(anchorId);
                if (el) {
                    el.scrollIntoView({ behavior: 'smooth' });
                }
            }, 100);
        } else {
            window.scrollTo(0, 0);
        }
    }

    // Lifecycle: Synchronize date pickers when opening rooms or booking summary
    if (pageId === 'rooms' || pageId === 'booking-summary') {
        if (typeof window.initDateConstraints === 'function') {
            window.initDateConstraints();
        }
    }

    // Lifecycle: Ensure room catalog cards are rendered when navigating to rooms
    if (pageId === 'rooms') {
        if (typeof window.renderRooms === 'function' && window.appState && window.appState.rooms && window.appState.rooms.length > 0) {
            const container = document.getElementById('rooms-container');
            if (container && container.children.length === 0) {
                window.renderRooms();
            }
        }
    }

    // Lifecycle: Dynamic room reviews loading when navigating to room details
    if (pageId && pageId.startsWith('room-')) {
        const roomId = pageId.split('-')[1];
        if (typeof window.loadRoomReviews === 'function') {
            window.loadRoomReviews(roomId);
        }
    }
}

// Global window binding for inline HTML attributes and other modules
window.router = router;

