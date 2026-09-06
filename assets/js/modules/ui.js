/**
 * UI & NOTIFICATIONS MODULE
 * Manages toast notifications, custom confirmation modals, password toggles,
 * mobile drawer navigation, and initial section visibility.
 */

/**
 * Displays a modern toast notification on screen.
 * Automatically injects the toast container if it does not exist and auto-removes after timeout.
 * 
 * @param {string} message - The notification text to display.
 * @param {'info'|'success'|'error'|'warning'} [type='info'] - Style category of the toast.
 */
function showToast(message, type = 'info') {
    if (!document.body) return;
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        document.body.appendChild(container);
    }
    
    const toast = document.createElement('div');
    toast.className = `modern-toast ${type}`;
    
    let icon = 'fa-info-circle';
    if (type === 'success') icon = 'fa-check-circle';
    if (type === 'error') icon = 'fa-exclamation-circle';
    if (type === 'warning') icon = 'fa-exclamation-triangle';
    
    toast.innerHTML = `<i class="fas ${icon}"></i> <span>${message}</span>`;
    container.appendChild(toast);
    
    setTimeout(() => toast.classList.add('show'), 10);
    
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 400);
    }, 3500);
}

/**
 * Displays a custom confirmation modal dialog with Cancel and Confirm buttons.
 * 
 * @param {string} message - The confirmation prompt message.
 * @param {Function} onConfirm - Callback function executed when the user confirms.
 */
function showConfirm(message, onConfirm) {
    let overlay = document.getElementById('custom-confirm-overlay');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.id = 'custom-confirm-overlay';
        overlay.innerHTML = `
            <div class="custom-confirm-box">
                <h4>Confirmation</h4>
                <p id="custom-confirm-message"></p>
                <div class="custom-confirm-buttons">
                    <button class="btn-confirm-cancel" id="custom-confirm-cancel">Cancel</button>
                    <button class="btn-confirm-ok" id="custom-confirm-ok">Confirm</button>
                </div>
            </div>
        `;
        document.body.appendChild(overlay);
    }
    
    document.getElementById('custom-confirm-message').textContent = message;
    
    const btnCancel = document.getElementById('custom-confirm-cancel');
    const btnOk = document.getElementById('custom-confirm-ok');
    
    const newBtnCancel = btnCancel.cloneNode(true);
    const newBtnOk = btnOk.cloneNode(true);
    btnCancel.parentNode.replaceChild(newBtnCancel, btnCancel);
    btnOk.parentNode.replaceChild(newBtnOk, btnOk);
    
    const close = () => overlay.classList.remove('show');
    
    newBtnCancel.addEventListener('click', close);
    newBtnOk.addEventListener('click', () => {
        close();
        if (onConfirm) onConfirm();
    });
    
    overlay.classList.add('show');
}

/**
 * Toggles visibility of a password input field between masked and plaintext.
 * Also toggles the FontAwesome eye/eye-slash icon.
 * Supports passing either string element IDs or direct DOM element references.
 * 
 * @param {string|HTMLElement} inputId - DOM ID or direct element of the password input.
 * @param {string|HTMLElement} iconId - DOM ID or direct element of the eye icon.
 */
function togglePassword(inputId, iconId) {
    const input = typeof inputId === 'string' ? document.getElementById(inputId) : inputId;
    const icon = typeof iconId === 'string' ? document.getElementById(iconId) : iconId;
    
    if (input && icon) {
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
}

/**
 * Sets up click event listener for toggling the mobile navigation drawer.
 */
function setupMobileNav() {
    const btn = document.getElementById('mobile-menu-btn');
    const menu = document.getElementById('mobile-menu');
    
    if (btn && menu) {
        btn.addEventListener('click', () => {
            menu.classList.toggle('hidden');
        });
    }
}

/**
 * Toggles the admin sidebar drawer and backdrop overlay.
 */
function toggleAdminSidebar() {
    const sidebar = document.getElementById('admin-sidebar');
    const overlay = document.getElementById('admin-sidebar-overlay');
    
    if (!sidebar || !overlay) return;

    if (sidebar.classList.contains('-translate-x-full')) {
        sidebar.classList.remove('-translate-x-full');
        overlay.classList.remove('hidden');
    } else {
        sidebar.classList.add('-translate-x-full');
        overlay.classList.add('hidden');
    }
}

/**
 * Initializes initial page section visibility on document load.
 * Ensures the 'home' section is shown and non-home sections are hidden.
 */
function initPageVisibility() {
    const homeSection = document.getElementById('home');
    if (homeSection && homeSection.classList.contains('active')) {
        homeSection.style.display = 'block';
    }
    // Hide all other page sections
    document.querySelectorAll('.page-section:not(#home)').forEach(el => {
        el.style.display = 'none';
    });
}

// Global window bindings for inline HTML and cross-module access
window.showToast = showToast;
window.showConfirm = showConfirm;
window.togglePassword = togglePassword;
window.setupMobileNav = setupMobileNav;
window.toggleAdminSidebar = toggleAdminSidebar;
window.initPageVisibility = initPageVisibility;

