/**
 * AUTHENTICATION MODULE
 * Handles guest/admin session rendering in navbar, login form submission with rate-limiting,
 * registration with live password strength validation, forgot password, social login (Google OAuth),
 * and logout workflows.
 */

let loginAttempts = 0;

/**
 * Updates navbar authentication buttons based on current login status.
 * Renders user profile/admin panel link & logout button if logged in;
 * otherwise displays Login & Sign Up buttons.
 */
function updateAuthUI() {
    const container = document.getElementById('auth-buttons');
    const mobileContainer = document.getElementById('mobile-auth-buttons');
    const basePath = (window.location.pathname.includes('/admin/') || window.location.pathname.includes('/user/')) ? '../' : '';
    const currentUser = window.appState ? window.appState.currentUser : null;
    
    let html = '';
    if (currentUser) {            
        const dashboardLink = currentUser.role === 'admin'
            ? `<a href="${basePath}admin/dashboard.php" class="flex items-center justify-center gap-2 text-sm font-semibold cursor-pointer text-white bg-brand-dark hover:bg-gray-800 transition-colors px-5 py-2 rounded-full shadow-md"><i class="fas fa-shield-alt"></i> Admin Panel</a>`
            : `<a href="${basePath}user/profile.php" class="flex items-center justify-center gap-2 text-sm font-semibold cursor-pointer text-brand-dark bg-white border border-gray-200 hover:border-brand-blue hover:text-brand-blue hover:shadow-md transition-all px-5 py-2 rounded-full shadow-sm"><i class="fas fa-user-circle"></i> My Profile</a>`;
        
        const firstName = currentUser.name ? currentUser.name.split(' ')[0] : 'Guest';
        const avatarHTML = currentUser.avatar 
            ? `<img src="${basePath}${currentUser.avatar}" alt="Avatar" class="w-full h-full object-cover">`
            : `${firstName.charAt(0).toUpperCase()}`;

        html = `
            <div class="flex flex-col md:flex-row md:items-center gap-3">
                <div class="flex items-center gap-3 px-4 py-1.5 bg-gradient-to-r from-gray-50 to-white rounded-full border border-gray-100 shadow-sm justify-center md:justify-start">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-brand-blue to-brand-cyan text-white flex items-center justify-center text-sm font-bold shadow-md overflow-hidden">
                        ${avatarHTML}
                    </div>
                    <span class="text-sm font-semibold text-gray-800 tracking-wide pr-2">Hi, ${firstName}</span>
                </div>
                ${dashboardLink}
                <button onclick="logout()" class="flex items-center justify-center gap-2 text-sm font-semibold bg-red-50 text-red-600 border border-red-100 hover:bg-red-600 hover:text-white px-5 py-2 rounded-full transition-all w-full md:w-auto shadow-sm"><i class="fas fa-sign-out-alt"></i> Logout</button>
            </div>
        `;
    } else {  
        html = `
            <div class="flex flex-col md:flex-row items-stretch md:items-center gap-3">
                <a onclick="router('login')" class="text-gray-600 hover:text-brand-dark font-semibold cursor-pointer px-5 py-2.5 rounded-full hover:bg-gray-100 transition-all text-sm tracking-wide text-center flex items-center justify-center gap-2"><i class="fas fa-sign-in-alt"></i> Login</a>
                <a onclick="router('register')" class="bg-gradient-to-r from-brand-blue to-brand-cyan hover:shadow-lg hover:-translate-y-0.5 text-white px-6 py-2.5 rounded-full font-semibold transition-all shadow-md cursor-pointer text-sm tracking-wide text-center flex items-center justify-center gap-2"><i class="fas fa-user-plus"></i> Sign Up</a>
            </div>
        `;
    }
    
    if (container) container.innerHTML = html;
    if (mobileContainer) mobileContainer.innerHTML = html; 

    // Re-render rooms if room list is loaded to update status badges
    if (window.appState && window.appState.rooms && window.appState.rooms.length > 0 && typeof window.renderRooms === 'function') {
        window.renderRooms();
    }
}

/**
 * Initializes form submission listeners for Login, Registration, and Forgot Password.
 */
function initAuthForms() {
    // --- Login Form Handler ---
    const loginForm = document.getElementById('login-form');
    if (loginForm && !loginForm.dataset.initialized) {
        loginForm.dataset.initialized = 'true';
        loginForm.addEventListener('submit', (e) => {
            e.preventDefault();
            
            if (loginAttempts >= 5) {
                window.showToast("Too many failed attempts. Please try again later.", "error");
                return;
            }

            const email = document.getElementById('login-email').value;
            const pass = document.getElementById('login-password').value;
            
            fetch('actions/login_action.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email, password: pass })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    loginAttempts = 0; // Reset attempts on successful authentication
                    window.appState.currentUser = data.user;
                    window.saveState();
                    updateAuthUI();
                    if (data.user.role === 'admin' && data.redirect) {
                        window.location.href = data.redirect;
                    } else {
                        window.router('home');
                    }
                } else {
                    loginAttempts++;
                    const attemptsLeft = 5 - loginAttempts;
                    
                    if (attemptsLeft > 0) {
                        window.showToast(`${data.message} ${attemptsLeft} attempts left.`, 'error');
                    } else {
                        window.showToast("Login locked temporarily due to too many failed attempts.", 'error');
                    }
                    
                    // Visual feedback UX: clear password, bring back focus and shake input
                    const passInput = document.getElementById('login-password');
                    if (passInput) {
                        passInput.value = '';
                        passInput.focus();
                        passInput.classList.add('border-red-500', 'animate-shake');
                        setTimeout(() => passInput.classList.remove('border-red-500', 'animate-shake'), 500);
                    }
                }
            })
            .catch(err => console.error('Login Error:', err));
        });
    }

    // --- Registration Form Handler ---
    const registerForm = document.getElementById('register-form');
    if (registerForm && !registerForm.dataset.initialized) {
        registerForm.dataset.initialized = 'true';
        registerForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const name = document.getElementById('reg-name').value;
            const email = document.getElementById('reg-email').value;
            const pass = document.getElementById('reg-pass').value;

            // Client-side password validation
            const validationErrors = [];
            if (pass.length < 8) validationErrors.push("At least 8 characters");
            if (!/[A-Z]/.test(pass)) validationErrors.push("One uppercase letter");
            if (!/[a-z]/.test(pass)) validationErrors.push("One lowercase letter");
            if (!/[0-9]/.test(pass)) validationErrors.push("One number");
            if (!/[^A-Za-z0-9]/.test(pass)) validationErrors.push("One special character");
            
            if (validationErrors.length > 0) {
                window.showToast("Please meet all password requirements: " + validationErrors.join(", "), 'error');
                return;
            }

            fetch('actions/register_action.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ name, email, password: pass })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    window.showToast(data.message, 'success');
                    window.router('login');
                } else {
                    window.showToast(data.message, 'error');
                }
            })
            .catch(err => console.error('Registration Error:', err));
        });
    }
    
    // --- Forgot Password Form Handler ---
    const forgotPasswordForm = document.getElementById('forgot-password-form');
    if (forgotPasswordForm && !forgotPasswordForm.dataset.initialized) {
        forgotPasswordForm.dataset.initialized = 'true';
        forgotPasswordForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const email = document.getElementById('forgot-email').value;

            fetch('actions/forgot_password_action.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    window.showToast(data.message, 'success');
                    window.router('login');
                } else {
                    window.showToast(data.message, 'error');
                }
            })
            .catch(err => console.error('Forgot Password Error:', err));
        });
    }

    // --- Live Password Strength Indicators ---
    const regPassInput = document.getElementById('reg-pass');
    if (regPassInput && !regPassInput.dataset.initialized) {
        regPassInput.dataset.initialized = 'true';
        regPassInput.addEventListener('input', function(e) {
            const password = e.target.value;
            
            const hasLength = password.length >= 8;
            const hasUppercase = /[A-Z]/.test(password);
            const hasLowercase = /[a-z]/.test(password);
            const hasNumber = /[0-9]/.test(password);
            const hasSpecial = /[^A-Za-z0-9]/.test(password);
            
            const updateIndicator = (id, isValid) => {
                const el = document.getElementById(id);
                if (el) {
                    el.style.color = isValid ? 'green' : 'gray';
                    el.style.fontWeight = isValid ? 'bold' : 'normal';
                }
            };
            
            updateIndicator('req-length', hasLength);
            updateIndicator('req-uppercase', hasUppercase);
            updateIndicator('req-lowercase', hasLowercase);
            updateIndicator('req-number', hasNumber);
            updateIndicator('req-special', hasSpecial);
        });
    }
}

/**
 * Handles third-party social login integration (Google OAuth2).
 * 
 * @param {'Google'|string} provider - Name of the social authentication provider.
 */
function handleSocialLogin(provider) {
    if (provider === 'Google') {
        if (typeof google === 'undefined') {
            window.showToast('Google Auth SDK is still loading or blocked. Please try again.', 'error');
            return;
        }
        const client = google.accounts.oauth2.initTokenClient({
            client_id: '830544901615-sv51g2sg31v1dluucrrr9tfmpop8k2j8.apps.googleusercontent.com',
            scope: 'email profile',
            callback: (response) => {
                if (response.access_token) {
                    fetch('https://www.googleapis.com/oauth2/v3/userinfo', {
                        headers: { Authorization: `Bearer ${response.access_token}` }
                    })
                    .then(res => res.json())
                    .then(data => {
                        processSocialLogin('Google', data.email, data.name);
                    });
                }
            }
        });
        client.requestAccessToken();
    } else {
        // Fallback simulation for other providers
        const email = prompt(`[${provider} Login]\n\nPlease enter your email address:`);
        if (email) processSocialLogin(provider, email, email.split('@')[0]);
    }
}

/**
 * Sends validated social credentials to the backend for account provisioning or sign-in.
 * 
 * @param {string} provider - Social provider name.
 * @param {string} email - Verified user email address.
 * @param {string} name - User display name.
 */
function processSocialLogin(provider, email, name) {
    fetch('actions/social_login_action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email, name, provider })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            window.appState.currentUser = data.user;
            window.saveState();
            updateAuthUI();
            window.router('home');
        } else {
            window.showToast(data.message, 'error');
        }
    })
    .catch(err => console.error('Social Login Error:', err));
}

/**
 * Terminates user session, clears local/session storage, and redirects to server-side logout script.
 */
function logout() {
    const role = (window.appState && window.appState.currentUser) ? window.appState.currentUser.role : 'user';
    if (window.appState) {
        window.appState.currentUser = null;
    }
    if (typeof window.saveState === 'function') {
        window.saveState(role);
    }
    sessionStorage.removeItem('w_currentUser');
    localStorage.removeItem('w_currentUser');
    sessionStorage.setItem('w_justLoggedOut', 'true');
    const basePath = (window.location.pathname.includes('/admin/') || window.location.pathname.includes('/user/')) ? '../' : '';
    window.location.href = basePath + 'logout.php?type=' + role;
}

// Auto-bind form listeners on DOMContentLoaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAuthForms);
} else {
    initAuthForms();
}

// Global window bindings for inline HTML and cross-module access
window.updateAuthUI = updateAuthUI;
window.initAuthForms = initAuthForms;
window.handleSocialLogin = handleSocialLogin;
window.processSocialLogin = processSocialLogin;
window.logout = logout;

