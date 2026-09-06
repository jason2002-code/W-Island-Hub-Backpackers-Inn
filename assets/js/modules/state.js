/**
 * STATE MANAGEMENT MODULE
 * Manages global application state, local storage persistence, and default fallback data.
 */

// Default room fallback data in case API is unreachable
var defaultRooms = [
    { 
        id: 1, 
        name: "Backpacker Dorm", 
        type: "Dorm", 
        price: 15, 
        capacity: 1, 
        image: "assets/image/background.jpg",
        desc: "Cozy bunk in a mixed dorm with A/C and locker.",
        amenities: ["WiFi", "A/C", "Shared Bath", "Locker"],
        rating: 4.5
    },
    { 
        id: 2, 
        name: "Standard Double", 
        type: "Standard", 
        price: 45, 
        capacity: 2, 
        image: "assets/image/background.jpg",
        desc: "Private room with queen bed and garden view.",
        amenities: ["WiFi", "A/C", "Private Bath", "TV"],
        rating: 4.7
    },
    { 
        id: 3, 
        name: "Ocean Deluxe", 
        type: "Deluxe", 
        price: 85, 
        capacity: 2, 
        image: "assets/image/background.jpg",
        desc: "Premium room with balcony overlooking the sea.",
        amenities: ["WiFi", "A/C", "Bath", "Balcony", "Breakfast"],
        rating: 4.9
    },
    { 
        id: 4, 
        name: "Family Suite", 
        type: "Family", 
        price: 120, 
        capacity: 4, 
        image: "assets/image/background.jpg",
        desc: "Spacious suite perfect for families or groups.",
        amenities: ["WiFi", "Kitchenette", "Living Area", "2 Baths"],
        rating: 4.8
    }
];

// Helper to safely parse stored user session data without throwing on corrupted values
function getInitialUser() {
    try {
        const stored = sessionStorage.getItem('w_currentUser') || localStorage.getItem('w_currentUser');
        return stored ? JSON.parse(stored) : null;
    } catch (e) {
        console.warn('Unable to parse stored user session:', e);
        return null;
    }
}

// Core Application State Object - binds to existing window.appState if already initialized
var appState = window.appState || {
    currentUser: getInitialUser(),
    rooms: defaultRooms,
    bookings: []
};

// Ensure currentUser is populated if not already
if (!appState.currentUser) {
    appState.currentUser = getInitialUser();
}
if (!appState.rooms || !appState.rooms.length) {
    appState.rooms = defaultRooms;
}
if (!appState.bookings) {
    appState.bookings = [];
}

/**
 * Persists the current authenticated user state to browser storage (Session & Local).
 * @param {string} [role='user'] - The role of the user, used during logout or role-specific storage.
 */
function saveState(role) {
    try {
        if (appState && appState.currentUser) {
            sessionStorage.setItem('w_currentUser', JSON.stringify(appState.currentUser));
            localStorage.setItem('w_currentUser', JSON.stringify(appState.currentUser));
        } else {
            sessionStorage.removeItem('w_currentUser');
            localStorage.removeItem('w_currentUser');
        }
    } catch (err) {
        console.warn('Failed to persist state:', err);
    }
}

// Expose state and methods globally for cross-module and inline HTML access
window.appState = appState;
window.saveState = saveState;
window.defaultRooms = defaultRooms;

