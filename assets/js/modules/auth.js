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
    let mobileHtml = '';

    if (currentUser) {            
        const firstName = currentUser.name ? currentUser.name.trim().split(' ')[0] : 'Guest';
        const fullName = currentUser.name || firstName;
        const avatarHTML = currentUser.avatar 
            ? `<img src="${basePath}${currentUser.avatar}" alt="Avatar" class="w-full h-full object-cover">`
            : `${firstName.charAt(0).toUpperCase()}`;

        // Desktop Dropdown
        html = `
            <div class="relative inline-block text-left" id="user-dropdown-wrapper">
                <button type="button" 
                        onclick="toggleUserDropdown(event)" 
                        id="user-dropdown-trigger" 
                        class="flex items-center gap-2.5 pl-1.5 pr-3.5 py-1 bg-white hover:bg-slate-50 border border-slate-200/90 rounded-full shadow-xs hover:shadow transition-all duration-200 cursor-pointer select-none group focus:outline-none focus:ring-2 focus:ring-brand-blue/30"
                        aria-expanded="false" 
                        aria-haspopup="true">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-brand-blue to-brand-cyan text-white flex items-center justify-center text-xs font-bold shadow-xs overflow-hidden flex-shrink-0 ring-2 ring-white">
                        ${avatarHTML}
                    </div>
                    <span class="text-sm font-semibold text-slate-800 tracking-wide group-hover:text-brand-blue transition-colors">Hi, ${firstName}</span>
                    <i class="fas fa-chevron-down text-[10px] text-slate-400 group-hover:text-slate-600 transition-transform duration-200 ml-0.5" id="user-dropdown-chevron"></i>
                </button>

                <!-- Dropdown Menu -->
                <div id="user-dropdown-menu" 
                     class="hidden absolute right-0 top-full mt-2 w-56 bg-white/95 backdrop-blur-md rounded-2xl shadow-xl border border-slate-100 py-1.5 z-50 transition-all duration-150 transform origin-top-right">
                    <div class="px-4 py-2.5 border-b border-slate-100 mb-1">
                        <p class="text-[10px] uppercase font-bold tracking-wider text-slate-400">Signed in as</p>
                        <p class="text-sm font-bold text-slate-800 truncate">${fullName}</p>
                        ${currentUser.email ? `<p class="text-xs text-slate-400 truncate mt-0.5">${currentUser.email}</p>` : ''}
                    </div>

                    <div class="p-1.5 space-y-0.5">
                        ${currentUser.role === 'admin' ? `
                            <a href="${basePath}admin/dashboard.php" class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-semibold text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition-colors group">
                                <div class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-xs group-hover:scale-105 transition-transform">
                                    <i class="fas fa-shield-alt"></i>
                                </div>
                                <span>Admin Panel</span>
                            </a>
                            <a href="${basePath}user/profile.php" class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-semibold text-slate-700 hover:bg-blue-50 hover:text-brand-blue transition-colors group">
                                <div class="w-7 h-7 rounded-lg bg-blue-50 text-brand-blue flex items-center justify-center text-xs group-hover:scale-105 transition-transform">
                                    <i class="fas fa-user-circle"></i>
                                </div>
                                <span>My Profile</span>
                            </a>
                        ` : `
                            <a href="${basePath}user/profile.php" class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-semibold text-slate-700 hover:bg-blue-50 hover:text-brand-blue transition-colors group">
                                <div class="w-7 h-7 rounded-lg bg-blue-50 text-brand-blue flex items-center justify-center text-xs group-hover:scale-105 transition-transform">
                                    <i class="fas fa-user-circle"></i>
                                </div>
                                <span>My Profile</span>
                            </a>
                        `}
                    </div>

                    <div class="border-t border-slate-100 my-1"></div>

                    <div class="p-1.5">
                        <button onclick="logout()" class="w-full flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-semibold text-red-600 hover:bg-red-50 transition-colors text-left cursor-pointer group">
                            <div class="w-7 h-7 rounded-lg bg-red-50 text-red-500 flex items-center justify-center text-xs group-hover:scale-105 transition-transform">
                                <i class="fas fa-sign-out-alt"></i>
                            </div>
                            <span>Logout</span>
                        </button>
                    </div>
                </div>
            </div>
        `;

        // Mobile Dropdown / Accordion
        mobileHtml = `
            <div class="w-full bg-white rounded-2xl border border-gray-200/80 shadow-xs overflow-hidden" id="mobile-user-dropdown-wrapper">
                <button type="button" 
                        onclick="toggleMobileUserDropdown(event)" 
                        class="w-full flex items-center justify-between p-3.5 bg-gradient-to-r from-gray-50 to-white hover:bg-gray-50 cursor-pointer select-none transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-gradient-to-br from-brand-blue to-brand-cyan text-white flex items-center justify-center text-sm font-bold shadow-xs overflow-hidden flex-shrink-0 ring-2 ring-white">
                            ${avatarHTML}
                        </div>
                        <div class="text-left">
                            <p class="text-xs font-medium text-gray-400">Signed in as</p>
                            <p class="text-sm font-bold text-gray-800 tracking-wide">Hi, ${firstName}</p>
                        </div>
                    </div>
                    <i class="fas fa-chevron-down text-xs text-gray-400 transition-transform duration-200" id="mobile-user-dropdown-chevron"></i>
                </button>
                <div id="mobile-user-dropdown-menu" class="hidden border-t border-gray-100 p-2 space-y-1 bg-white">
                    ${currentUser.role === 'admin' ? `
                        <a href="${basePath}admin/dashboard.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold text-gray-700 hover:bg-gray-100 transition-colors">
                            <i class="fas fa-shield-alt text-amber-500 w-5 text-center"></i>
                            <span>Admin Panel</span>
                        </a>
                        <a href="${basePath}user/profile.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold text-gray-700 hover:bg-blue-50 hover:text-brand-blue transition-colors">
                            <i class="fas fa-user-circle text-brand-blue w-5 text-center"></i>
                            <span>My Profile</span>
                        </a>
                    ` : `
                        <a href="${basePath}user/profile.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold text-gray-700 hover:bg-blue-50 hover:text-brand-blue transition-colors">
                            <i class="fas fa-user-circle text-brand-blue w-5 text-center"></i>
                            <span>My Profile</span>
                        </a>
                    `}
                    <div class="border-t border-gray-100 my-1"></div>
                    <button onclick="logout()" class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold text-red-600 hover:bg-red-50 transition-colors text-left cursor-pointer">
                        <i class="fas fa-sign-out-alt text-red-500 w-5 text-center"></i>
                        <span>Logout</span>
                    </button>
                </div>
            </div>
        `;
    } else {  
        html = `
            <div class="flex items-center gap-2.5">
                <a onclick="router('login')" class="text-slate-700 hover:text-amber-600 font-semibold cursor-pointer px-4 py-2 rounded-full hover:bg-slate-100 transition-all text-sm tracking-wide text-center flex items-center justify-center gap-2"><i class="fas fa-sign-in-alt text-amber-500 text-xs"></i> Login</a>
                <a onclick="router('register')" class="bg-gradient-to-r from-amber-500 via-amber-600 to-yellow-500 hover:from-amber-600 hover:to-yellow-600 hover:shadow-md hover:-translate-y-0.5 text-slate-950 px-5 py-2 rounded-full font-bold transition-all shadow-xs cursor-pointer text-sm tracking-wide text-center flex items-center justify-center gap-2"><i class="fas fa-user-plus text-xs"></i> Sign Up</a>
            </div>
        `;
        mobileHtml = `
            <div class="flex flex-col gap-2.5">
                <a onclick="router('login')" class="text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 font-semibold cursor-pointer px-5 py-2.5 rounded-xl transition-all text-sm tracking-wide text-center flex items-center justify-center gap-2 shadow-xs"><i class="fas fa-sign-in-alt text-amber-500"></i> Login</a>
                <a onclick="router('register')" class="bg-gradient-to-r from-amber-500 via-amber-600 to-yellow-500 hover:from-amber-600 hover:to-yellow-600 hover:shadow-md text-slate-950 px-5 py-2.5 rounded-xl font-bold transition-all shadow-sm cursor-pointer text-sm tracking-wide text-center flex items-center justify-center gap-2"><i class="fas fa-user-plus"></i> Sign Up</a>
            </div>
        `;
    }
    
    if (container) container.innerHTML = html;
    if (mobileContainer) mobileContainer.innerHTML = mobileHtml; 

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
                    const icon = el.querySelector('i');
                    if (isValid) {
                        el.classList.remove('text-slate-400');
                        el.classList.add('text-emerald-600', 'font-medium');
                        if (icon) {
                            icon.className = 'fas fa-check-circle text-[10px] mr-1.5 text-emerald-500';
                        }
                    } else {
                        el.classList.remove('text-emerald-600', 'font-medium');
                        el.classList.add('text-slate-400');
                        if (icon) {
                            icon.className = 'far fa-circle text-[10px] mr-1.5 text-slate-300';
                        }
                    }
                }
            };
            
            updateIndicator('req-length', hasLength);
            updateIndicator('req-uppercase', hasUppercase);
            updateIndicator('req-lowercase', hasLowercase);
            updateIndicator('req-number', hasNumber);
            updateIndicator('req-special', hasSpecial);

            // Dynamic Password Strength Meter
            const strengthBar = document.getElementById('reg-password-strength-bar');
            const strengthText = document.getElementById('reg-password-strength-text');
            if (strengthBar && strengthText) {
                let score = 0;
                if (hasLength) score++;
                if (hasUppercase) score++;
                if (hasLowercase) score++;
                if (hasNumber) score++;
                if (hasSpecial) score++;

                if (password.length === 0) {
                    strengthBar.style.width = '0%';
                    strengthBar.className = 'h-full bg-slate-200 transition-all duration-300 rounded-full';
                    strengthText.textContent = 'Enter password';
                    strengthText.className = 'text-[11px] font-semibold text-slate-400';
                } else {
                    const levels = [
                        { width: '20%', text: 'Very Weak', color: 'bg-rose-500', textColor: 'text-rose-500' },
                        { width: '40%', text: 'Weak', color: 'bg-orange-500', textColor: 'text-orange-500' },
                        { width: '60%', text: 'Fair', color: 'bg-amber-500', textColor: 'text-amber-500' },
                        { width: '80%', text: 'Good', color: 'bg-emerald-500', textColor: 'text-emerald-600' },
                        { width: '100%', text: 'Strong & Secure ✓', color: 'bg-emerald-600', textColor: 'text-emerald-700' }
                    ];
                    const current = levels[Math.max(0, score - 1)];
                    strengthBar.style.width = current.width;
                    strengthBar.className = `h-full ${current.color} transition-all duration-300 rounded-full`;
                    strengthText.textContent = current.text;
                    strengthText.className = `text-[11px] font-semibold ${current.textColor}`;
                }
            }
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

/**
 * Toggles the desktop user profile & logout dropdown menu.
 */
function toggleUserDropdown(event) {
    if (event) event.stopPropagation();
    const menu = document.getElementById('user-dropdown-menu');
    const chevron = document.getElementById('user-dropdown-chevron');
    const trigger = document.getElementById('user-dropdown-trigger');
    if (!menu) return;

    const isHidden = menu.classList.contains('hidden');
    if (isHidden) {
        menu.classList.remove('hidden');
        if (chevron) chevron.classList.add('rotate-180');
        if (trigger) trigger.setAttribute('aria-expanded', 'true');
    } else {
        menu.classList.add('hidden');
        if (chevron) chevron.classList.remove('rotate-180');
        if (trigger) trigger.setAttribute('aria-expanded', 'false');
    }
}

/**
 * Closes the desktop user dropdown menu.
 */
function closeUserDropdown() {
    const menu = document.getElementById('user-dropdown-menu');
    const chevron = document.getElementById('user-dropdown-chevron');
    const trigger = document.getElementById('user-dropdown-trigger');
    if (menu && !menu.classList.contains('hidden')) {
        menu.classList.add('hidden');
        if (chevron) chevron.classList.remove('rotate-180');
        if (trigger) trigger.setAttribute('aria-expanded', 'false');
    }
}

/**
 * Toggles the mobile user profile & logout dropdown menu.
 */
function toggleMobileUserDropdown(event) {
    if (event) event.stopPropagation();
    const menu = document.getElementById('mobile-user-dropdown-menu');
    const chevron = document.getElementById('mobile-user-dropdown-chevron');
    if (!menu) return;

    const isHidden = menu.classList.contains('hidden');
    if (isHidden) {
        menu.classList.remove('hidden');
        if (chevron) chevron.classList.add('rotate-180');
    } else {
        menu.classList.add('hidden');
        if (chevron) chevron.classList.remove('rotate-180');
    }
}

// Close user dropdown on outside click or escape key
if (!window._userDropdownListenerAttached) {
    window._userDropdownListenerAttached = true;
    document.addEventListener('click', (e) => {
        const wrapper = document.getElementById('user-dropdown-wrapper');
        if (wrapper && !wrapper.contains(e.target)) {
            closeUserDropdown();
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeUserDropdown();
        }
    });
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
window.toggleUserDropdown = toggleUserDropdown;
window.closeUserDropdown = closeUserDropdown;
window.toggleMobileUserDropdown = toggleMobileUserDropdown;

