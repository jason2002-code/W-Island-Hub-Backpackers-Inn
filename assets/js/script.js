/**
 * MAIN CLIENT-SIDE LOGIC
 * Handles routing, state management, booking calculations, and UI updates.
 */

// --- DATA & STATE MANAGEMENT ---
        let appState = {
            currentUser: JSON.parse(sessionStorage.getItem('w_currentUser')) || null,
            rooms: [], // Will be populated with defaultRooms initially
            bookings: []
        };





        // Persist current user state to browser storage (Session & Local)
        const saveState = () => {
            if(appState.currentUser) {
                sessionStorage.setItem('w_currentUser', JSON.stringify(appState.currentUser));
            } else {
                sessionStorage.removeItem('w_currentUser');
            }
            // Also save to localStorage for persistence
            localStorage.setItem('w_currentUser', JSON.stringify(appState.currentUser));
        };

        const defaultRooms = [
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
        
        // Initialize rooms in state
        appState.rooms = defaultRooms;


        
        // --- HERO CAROUSEL LOGIC ---
        let currentSlideIdx = 0;
        let slideTimer;

// Fetch hero slides from server and initialize the carousel
async function initHeroCarousel() {
            const container = document.getElementById('hero-slides');
            const dotsContainer = document.getElementById('hero-dots');
            
            if(!container) return;

            try {
                const res = await fetch('actions/sliders_action.php?action=get_all');
                const data = await res.json();
                
                // Filter only 'hero' type sliders
                const heroSlides = data.success ? data.sliders.filter(s => s.type === 'hero') : [];
                const slides = heroSlides.length > 0 
                    ? heroSlides 
                    : [{image_url: 'assets/image/background.jpg'}]; // Fallback

                // Generate Slides
                container.innerHTML = slides.map((slide, i) => `
                    <div class="hero-slide absolute inset-0 w-full h-full transition-opacity duration-1000 ease-in-out ${i === 0 ? 'opacity-100' : 'opacity-0'}">
                        <img src="${slide.image_url}" class="w-full h-full object-cover" alt="Hero Slide ${i}">
                        <div class="absolute inset-0 bg-black/30"></div>
                    </div>
                `).join('');

                // Generate Dots
                dotsContainer.innerHTML = slides.map((_, i) => `
                    <button onclick="goToSlide(${i})" class="hero-dot w-3 h-3 rounded-full transition-all duration-300 cursor-pointer ${i === 0 ? 'bg-white scale-125' : 'bg-white/50 hover:bg-white/80'}"></button>
                `).join('');
                
                startSlideTimer();
            } catch (err) {
                console.error('Hero carousel load failed:', err);
                // Fallback to single placeholder slide
                container.innerHTML = `
                    <div class="hero-slide absolute inset-0 w-full h-full">
                        <img src="assets/image/background.jpg" class="w-full h-full object-cover" alt="Fallback">
                        <div class="absolute inset-0 bg-black/30"></div>
                    </div>
                `;
                dotsContainer.innerHTML = '<button class="hero-dot w-3 h-3 rounded-full bg-white scale-125"></button>';
                startSlideTimer();
            }
        }

        // Display a specific slide by index
        function showSlide(index) {
            const slides = document.querySelectorAll('.hero-slide');
            const dots = document.querySelectorAll('.hero-dot');
            
            if(index >= slides.length) index = 0;
            if(index < 0) index = slides.length - 1;
            
            currentSlideIdx = index;

            slides.forEach((slide, i) => {
                slide.classList.toggle('opacity-100', i === index);
                slide.classList.toggle('opacity-0', i !== index);
            });

            dots.forEach((dot, i) => {
                if(i === index) {
                    dot.className = "hero-dot w-3 h-3 rounded-full transition-all duration-300 cursor-pointer bg-white scale-125";
                } else {
                    dot.className = "hero-dot w-3 h-3 rounded-full transition-all duration-300 cursor-pointer bg-white/50 hover:bg-white/80";
                }
            });
        }

        // Advance to the next slide automatically
        function nextSlide() {
            showSlide(currentSlideIdx + 1);
            resetSlideTimer();
        }

        // Go back to the previous slide
        function prevSlide() {
            showSlide(currentSlideIdx - 1);
            resetSlideTimer();
        }

        // Navigate to a specific slide via dots
        function goToSlide(index) {
            showSlide(index);
            resetSlideTimer();
        }

        // Start the automatic slide rotation timer
        function startSlideTimer() {
            slideTimer = setInterval(() => nextSlide(), 5000);
        }

        // Reset the timer when user interacts manually
        function resetSlideTimer() {
            clearInterval(slideTimer);
            startSlideTimer();
        }


        // --- CORE FUNCTIONS ---

       

        /**
         * Application Entry Point
         * Initializes loader, animations, mobile nav, and data fetching.
         */


async function init() {
            const skipLoader = sessionStorage.getItem('skipLoader') === 'true';
            if (skipLoader) {
                sessionStorage.removeItem('skipLoader');
            }

            // Simple fast loader: 1.5s total
            document.body.style.overflow = 'hidden';

            // W logo is already inserted in the loader HTML (header.php)
            // Just ensure it exists and animate it if GSAP is available
            const loadContent = Promise.all([
                initHeroCarousel(),
                setupMarquee()
            ]);

            if (skipLoader) {
                const loader = document.getElementById('loader');
                if (loader) {
                    loader.style.display = 'none';
                }
                document.body.style.overflow = 'auto';
            } else if (window.gsap) {
                const tl = gsap.timeline({
                    onComplete: () => {
                        const loader = document.getElementById('loader');
                        if (loader) {
                            loader.style.display = 'none';
                        }
                        document.body.style.overflow = 'auto';
                    }
                });
                tl.to("#loader", { y: "-100%", duration: 0.6, ease: "power4.in", delay: 1.5 });
            } else {
                // Fallback: 1.5s CSS fade
                setTimeout(() => {
                    const loader = document.getElementById('loader');
                    if (loader) {
                        loader.classList.add('hidden');
                        setTimeout(() => { loader.style.display = 'none'; document.body.style.overflow = 'auto'; }, 500);
                    }
                }, 2500);
            }

            await loadContent;
            updateAuthUI();
            setupMobileNav();
            initGuestSelector();
            fetchRooms();

            const hash = window.location.hash.replace('#', '');
            if(hash) router(hash);

            if(window.gsap) {
                gsap.from(".gsap-hero-text", {
                    y: 50, opacity: 0, duration: 1, stagger: 0.2, delay: 0.2, ease: "power3.out"
                });
            }
        }

// Fetch active rooms from the backend and update application state
        function fetchRooms() {
            fetch('actions/rooms_action.php')
                .then(res => res.json())
                .then(data => {
                    if(data.success && data.rooms.length > 0) {
                        // Transform database fields to match frontend expectations
                        appState.rooms = data.rooms.map(room => ({
                            id: room.id,
                            name: room.name,
                            type: room.type,
                            price: parseFloat(room.price) || 0,
                            capacity: parseInt(room.capacity) || 1,
                            image: room.image || 'assets/image/background.jpg',
                            desc: room.description || room.desc || '',
                            amenities: (() => {
                                if (typeof room.amenities === 'string') {
                                    try {
                                        return JSON.parse(room.amenities || '[]');
                                    } catch(e) {
                                        return room.amenities.split(',').map(s => s.trim());
                                    }
                                }
                                return room.amenities || [];
                            })(),
                            rating: parseFloat(room.rating) || 5.0,
                            status: room.status || 'Available'
                        }));
                        
                        renderRooms(); // Let's re-render to enforce JS login visibility logic
                    }
                })
                .catch(err => console.error('Failed to load rooms:', err));
        }

        // Setup event listeners for mobile navigation
        function setupMobileNav() {
            const btn = document.getElementById('mobile-menu-btn');
            const menu = document.getElementById('mobile-menu');
            
            if(btn && menu) {
                btn.addEventListener('click', () => {
                    menu.classList.toggle('hidden');
                });
            }
        }
        
        // Toggle the admin sidebar visibility
        function toggleAdminSidebar() {
            const sidebar = document.getElementById('admin-sidebar');
            const overlay = document.getElementById('admin-sidebar-overlay');
            
            if(sidebar.classList.contains('-translate-x-full')) {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
            } else {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
            }
        }

        // Fetch and render the infinite scrolling marquee gallery
async function setupMarquee() {
            const track = document.getElementById('marquee-track');
            if (!track) return;
            
            try {
                const res = await fetch('actions/sliders_action.php?action=get_all');
                const data = await res.json();
                
                const marqueeImages = data.success ? data.sliders.filter(s => s.type === 'marquee') : [];
                const images = marqueeImages.length > 0 
                    ? marqueeImages 
                    : [{image_url: 'assets/image/background.jpg'}];
                
                // Duplicate for seamless loop
                const allImages = [...images, ...images];
                track.innerHTML = allImages.map(slide => `
                    <div class="marquee-item" onclick="router('rooms')">
                        <img src="${slide.image_url}" alt="Gallery Image">
                    </div>
                `).join('');
            } catch (err) {
                console.error('Marquee load failed:', err);
                // Fallback
                const fallbackImages = Array(12).fill({image_url: 'assets/image/background.jpg'});
                track.innerHTML = fallbackImages.map(slide => `
                    <div class="marquee-item" onclick="router('rooms')">
                        <img src="${slide.image_url}" alt="Gallery Image">
                    </div>
                `).join('');
            }
        }

// Simple Client-Side Router: Toggles visibility of page sections based on ID
        function router(pageId) {
            // Close mobile menu if open
            const menu = document.getElementById('mobile-menu');
            if(menu && !menu.classList.contains('hidden')) menu.classList.add('hidden');

            // Security: Redirect admins or protect routes
            if (pageId === 'admin-dashboard' || pageId === 'admin') {
                if (appState.currentUser && appState.currentUser.role === 'admin') {
                    window.location.href = 'admin/dashboard.php';
                } else {
                    router('home');
                }
                return;
            }

            // Hide all pages (use explicit display:none to override any conflicting styles)
            document.querySelectorAll('.page-section').forEach(el => {
                el.classList.remove('active');
                el.style.display = 'none';
            });
            
            // Show specific page
            const target = document.getElementById(pageId);
            if(target) {
                target.classList.add('active');
                target.style.display = 'block';
                window.scrollTo(0,0);
            }

            if (pageId.startsWith('room-')) {
                loadRoomReviews(pageId.split('-')[1]);
            }
        }

// Toggle between "Login/Signup" and "User Profile/Logout" buttons in navbar
        function updateAuthUI() {
            const container = document.getElementById('auth-buttons');
            const mobileContainer = document.getElementById('mobile-auth-buttons');
            const basePath = (window.location.pathname.includes('/admin/') || window.location.pathname.includes('/user/')) ? '../' : '';
            
            let html = '';
        if (appState.currentUser) {            
                const dashboardLink = appState.currentUser.role === 'admin'
                    ? `<a href="${basePath}admin/dashboard.php" class="flex items-center justify-center gap-2 text-sm font-semibold cursor-pointer text-white bg-brand-dark hover:bg-gray-800 transition-colors px-5 py-2 rounded-full shadow-md"><i class="fas fa-shield-alt"></i> Admin Panel</a>`
                    : `<a href="${basePath}user/profile.php" class="flex items-center justify-center gap-2 text-sm font-semibold cursor-pointer text-brand-dark bg-white border border-gray-200 hover:border-brand-blue hover:text-brand-blue hover:shadow-md transition-all px-5 py-2 rounded-full shadow-sm"><i class="fas fa-user-circle"></i> My Profile</a>`;
                
                const firstName = appState.currentUser.name ? appState.currentUser.name.split(' ')[0] : 'Guest';
                const avatarHTML = appState.currentUser.avatar 
                    ? `<img src="${basePath}${appState.currentUser.avatar}" alt="Avatar" class="w-full h-full object-cover">`
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
        
        if (appState.rooms && appState.rooms.length > 0) {
            renderRooms();
        }
        }

        // --- AUTHENTICATION ---

        // Professional Password Toggle Logic
        window.togglePassword = function(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            
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
        };

        // --- MODERN NOTIFICATIONS ---
        window.showToast = function(message, type = 'info') {
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
        };

        window.showConfirm = function(message, onConfirm) {
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
        };

        // Handle Login Form Submission
        let loginAttempts = 0;
        const loginForm = document.getElementById('login-form');
        if (loginForm) {
            loginForm.addEventListener('submit', (e) => {
            e.preventDefault();
            
            if (loginAttempts >= 5) {
                showToast("Too many failed attempts. Please try again later.", "error");
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
                    loginAttempts = 0; // Reset attempts on success
                    appState.currentUser = data.user;
                    saveState();
                    updateAuthUI();
                     if (data.user.role === 'admin' && data.redirect) {
                        window.location.href = data.redirect;
                    } else {
                        router('home');
                    }
                } else {
                    loginAttempts++;
                    const attemptsLeft = 5 - loginAttempts;
                    
                    if (attemptsLeft > 0) {
                        showToast(`${data.message} ${attemptsLeft} attempts left.`, 'error');
                    } else {
                        showToast("Login locked temporarily due to too many failed attempts.", 'error');
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

        // Handle Registration Form Submission
        const registerForm = document.getElementById('register-form');
        if (registerForm) {
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
                showToast("Please meet all password requirements: " + validationErrors.join(", "), 'error');
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
                    showToast(data.message, 'success');
                    router('login');
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(err => console.error('Registration Error:', err));
            });
        }
        
        // Handle Forgot Password Form Submission
        const forgotPasswordForm = document.getElementById('forgot-password-form');
        if (forgotPasswordForm) {
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
                    showToast(data.message, 'success');
                    router('login');
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(err => console.error('Forgot Password Error:', err));
            });
        }

        // Real-time password strength validation
        const regPassInput = document.getElementById('reg-pass');
        if (regPassInput) {
            regPassInput.addEventListener('input', function(e) {
            const password = e.target.value;
            
            // Validate each requirement
            const hasLength = password.length >= 8;
            const hasUppercase = /[A-Z]/.test(password);
            const hasLowercase = /[a-z]/.test(password);
            const hasNumber = /[0-9]/.test(password);
            const hasSpecial = /[^A-Za-z0-9]/.test(password);
            
            // Update requirement indicators
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

        // --- REAL SOCIAL LOGIN ---
        function handleSocialLogin(provider) {
            if (provider === 'Google') {
                if (typeof google === 'undefined') {
                    showToast('Google Auth SDK is still loading or blocked. Please try again.', 'error');
                    return;
                }
                const client = google.accounts.oauth2.initTokenClient({
                    // ⚠️ IMPORTANT: Replace this with your actual Client ID from Google Cloud Console
                    client_id: '830544901615-sv51g2sg31v1dluucrrr9tfmpop8k2j8.apps.googleusercontent.com', // 👈 PASTE YOUR COPIED CLIENT ID HERE
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
                client.requestAccessToken(); // Triggers the real Google popup!
            } else {
                // Fallback simulation for other providers
                const email = prompt(`[${provider} Login]\n\nPlease enter your email address:`);
                if (email) processSocialLogin(provider, email, email.split('@')[0]);
            }
        }

        function processSocialLogin(provider, email, name) {
            fetch('actions/social_login_action.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email, name, provider })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    appState.currentUser = data.user;
                    saveState();
                    updateAuthUI();
                    router('home');
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(err => console.error('Social Login Error:', err));
        }

// Log out the current user
      function logout() {
              const role = appState.currentUser ? appState.currentUser.role : 'user';
              appState.currentUser = null;
              saveState(role); // Clears storage for this role
              // Set flag to indicate user just logged out (for redirect handling)
              sessionStorage.setItem('w_justLoggedOut', 'true');
              const basePath = (window.location.pathname.includes('/admin/') || window.location.pathname.includes('/user/')) ? '../' : '';
              // Redirect to logout.php for server-side session cleanup
            window.location.href = basePath + 'logout.php?type=' + role;
      }

        // --- BOOKING LOGIC ---

        // Render the list of rooms to the UI
        function renderRooms(filterDate = null) {
            const container = document.getElementById('rooms-container');
            if (!container) return;
            
            const isLoggedIn = appState.currentUser !== null;
            
            container.innerHTML = appState.rooms.map(room => {
                const stars = Array(Math.round(room.rating)).fill('<i class="fas fa-star text-yellow-400 text-xs"></i>').join('');
                
                let statusBadge = '';
                if (isLoggedIn) {
                    let statusClass = room.status === 'Maintenance' ? 'bg-red-100 text-red-800' : (room.status === 'Available' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800');
                    statusBadge = `<span class="room-status-badge ${statusClass} text-xs font-bold px-2 py-1 rounded uppercase ml-2">${room.status || 'Available'}</span>`;
                }
                
                return `
                <div class="room-card bg-white rounded-xl shadow-sm hover:shadow-lg transition-all duration-300 overflow-hidden border border-gray-100 flex flex-col" data-room-id="${room.id}" data-capacity="${room.capacity || 2}">
                    <div class="h-48 overflow-hidden relative">
                         <img src="${room.image}" alt="${room.name}" class="w-full h-full object-cover transition duration-500 hover:scale-110">
                         <div class="absolute top-2 right-2 bg-white/90 backdrop-blur px-2 py-1 rounded text-xs font-bold text-gray-800">
                            ${stars}
                         </div>
                    </div>
                    <div class="p-6 flex-1 flex flex-col">
                        <div class="flex justify-between items-start mb-2">
                            <h3 class="text-xl font-bold text-brand-dark">${room.name}</h3>
                            <div class="flex items-center text-right justify-end flex-wrap gap-1">
                                <span class="bg-blue-50 text-brand-blue text-xs font-bold px-2 py-1 rounded uppercase">${room.type}</span>
                                ${statusBadge}
                            </div>
                        </div>
                        <p class="text-gray-500 text-sm mb-4 line-clamp-2">${room.desc}</p>
                        
                        <div class="flex flex-wrap gap-2 mb-4">
                            <span class="text-xs text-brand-blue bg-blue-50 px-2 py-1 rounded font-semibold flex items-center gap-1"><i class="fas fa-user-friends text-[10px]"></i> Up to ${room.capacity || 2} Guest${(room.capacity || 2) > 1 ? 's' : ''}</span>
                            ${room.amenities.slice(0,3).map(a => `<span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">${a}</span>`).join('')}
                        </div>
                        
                        <!-- Reviews Section -->
                        <details class="reviews-section group mt-4 border-t pt-4">
                            <summary class="cursor-pointer font-semibold text-brand-dark mb-3 flex items-center gap-2 hover:text-brand-blue transition">
                                <i class="fas fa-star text-yellow-400"></i>
                                <span>Reviews <span class="reviews-count text-gray-500 ml-1">(0)</span></span>
                                <i class="fas fa-chevron-down text-sm transition-transform duration-200"></i>
                            </summary>
                            
                            <div class="reviews-content space-y-4 mt-3" id="reviews-${room.id}">
                                <!-- Dynamic content loaded by JS -->
                            </div>

                            ${isLoggedIn ? `
                            <div class="submit-review-section mt-6 p-4 bg-blue-50/50 rounded-lg border" data-room-id="${room.id}">
                                <h4 class="font-bold text-brand-dark mb-4 flex items-center gap-2">
                                    <i class="fas fa-edit text-blue-500"></i>
                                    Write Your Review
                                </h4>
                                <div class="star-picker mb-4 flex gap-1" data-room-id="${room.id}">
                                    ${[1,2,3,4,5].map(i => `
                                        <i class="review-star far fa-star text-2xl text-gray-400 cursor-pointer hover:text-yellow-400 transition-all duration-200" data-rating="${i}" title="Rate ${i} star${i > 1 ? 's' : ''}"></i>
                                    `).join('')}
                                    <span class="ml-3 font-medium text-gray-700 selected-rating">Select rating</span>
                                </div>
                                <textarea id="review-comment-${room.id}" placeholder="Share your experience with this room... What did you like? Any suggestions?" class="w-full p-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-blue focus:border-transparent resize-vertical min-h-[100px]" maxlength="500"></textarea>
                                <div class="text-right mt-2 text-xs text-gray-500">Max 500 characters</div>
                                <button class="submit-review-btn mt-4 bg-brand-blue hover:bg-blue-700 text-white px-6 py-2 rounded-xl font-medium transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed w-full md:w-auto" disabled>
                                    <span class="btn-text">Submit Review</span>
                                    <i class="fas fa-spinner fa-spin hidden ml-2"></i>
                                </button>
                                <div class="submit-message mt-2 hidden"></div>
                            </div>
                            ` : ''}
                        </details>

                        <div class="mt-auto pt-4 border-t flex justify-between items-center">
                            <div>
                                <span class="text-2xl font-bold text-brand-blue">₱${room.price}</span>
                                <span class="text-gray-400 text-sm">/ night</span>
                            </div>
                            <button onclick="initiateBooking(${room.id})" class="bg-brand-dark hover:bg-gray-800 text-white px-4 py-2 rounded-lg font-medium transition text-sm ${room.status === 'Maintenance' ? 'opacity-50 cursor-not-allowed' : ''}" ${room.status === 'Maintenance' ? 'disabled' : ''}>
                                ${room.status === 'Maintenance' ? 'Maintenance' : 'Book Now'}
                            </button>
                        </div>
                    </div>
                </div>
                `;
            }).join('');

            // Initialize reviews for each room card
            appState.rooms.forEach(room => {
                initReviewsSection(room.id);
            });
        }

        // Reviews initialization for JS-rendered room cards
        async function initReviewsSection(roomId) {
            const content = document.getElementById(`reviews-${roomId}`);
            if (!content) return;

            try {
                const res = await fetch(`actions/reviews_action.php?action=get_reviews&room_id=${roomId}`);
                const data = await res.json();

                if (data.success) {
                    const countEl = document.querySelector(`.room-card[data-room-id="${roomId}"] .reviews-count`);
                    if (countEl) countEl.textContent = `(${data.total_reviews})`;

                    content.innerHTML = `
                        <div class="avg-rating flex items-center gap-2 mb-4 p-3 bg-white rounded-lg shadow-sm">
                            <div class="flex gap-0.5">
                                ${[...Array(5)].map((_, i) => 
                                    `<i class="fas fa-star text-yellow-400 ${i < Math.round(data.avg_rating) ? '' : 'opacity-40'}"></i>`
                                ).join('')}
                            </div>
                            <span class="font-bold text-xl ml-2">${data.avg_rating}</span>
                            <span class="text-gray-500 text-sm ml-1">from ${data.total_reviews} reviews</span>
                        </div>
                        ${data.reviews.length ? data.reviews.map(review => `
                            <div class="review-item bg-white p-4 rounded-lg shadow-sm border">
                                <div class="flex items-center gap-3 mb-2">
                                    <div class="flex gap-0.5 text-sm">
                                        ${[...Array(5)].map((_, i) => 
                                            `<i class="fas fa-star text-yellow-400 ${i < Math.round(review.rating) ? '' : 'opacity-40'}"></i>`
                                        ).join('')}
                                    </div>
                                    <span class="font-semibold text-gray-800">${review.user_name || 'Anonymous'}</span>
                                    <span class="text-xs text-gray-400">${review.formatted_date}</span>
                                </div>
                                <p class="text-gray-700">${review.comment}</p>
                                ${review.admin_reply ? `
                                    <div class="mt-3 p-3 bg-blue-50 border-l-4 border-brand-blue rounded-r text-sm">
                                        <strong class="text-brand-blue block mb-1"><i class="fas fa-reply mr-1"></i> Response from Host:</strong>
                                        <span class="text-gray-700">${review.admin_reply}</span>
                                    </div>
                                ` : ''}
                            </div>
                        `).join('') : '<p class="text-gray-500 italic text-center py-8">No reviews yet. Be the first!</p>'}
                    `;
                }
            } catch (e) {
                content.innerHTML = '<p class="text-red-500 text-center py-8">Failed to load reviews.</p>';
            }
        }

        let pendingBooking = null;

        // Initialize Guest Selector (handles manual input with max 5 validation)
        function initGuestSelector() {
            const input = document.getElementById('guest-count');
            if (!input) return;

            input.addEventListener('input', (e) => {
                let val = parseInt(e.target.value, 10);
                if (val > 5) {
                    e.target.value = 5;
                    showToast('Maximum 5 guests allowed', 'info');
                }
            });

            input.addEventListener('blur', (e) => {
                let val = parseInt(e.target.value, 10);
                if (isNaN(val) || val < 1) {
                    e.target.value = 1;
                } else if (val > 5) {
                    e.target.value = 5;
                }
            });

            input.addEventListener('change', (e) => {
                let val = parseInt(e.target.value, 10);
                if (isNaN(val) || val < 1) {
                    e.target.value = 1;
                } else if (val > 5) {
                    e.target.value = 5;
                }
            });
        }

        // Availability check form handler
        const availabilityForm = document.getElementById('check-availability-form');
        if (availabilityForm) {
            availabilityForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const checkIn = document.getElementById('check-in-date').value;
                const checkOut = document.getElementById('check-out-date').value;
                const guestCountEl = document.getElementById('guest-count');
                let guests = parseInt(guestCountEl ? guestCountEl.value : 1, 10) || 1;
                if (guests > 5) {
                    guests = 5;
                    if (guestCountEl) guestCountEl.value = 5;
                    showToast('Maximum 5 guests allowed', 'warning');
                } else if (guests < 1) {
                    guests = 1;
                    if (guestCountEl) guestCountEl.value = 1;
                }

                if (!checkIn || !checkOut) {
                    showToast('Please select dates', 'warning');
                    return;
                }
                
                if (new Date(checkOut) <= new Date(checkIn)) {
                    showToast('Check-out must be after check-in', 'warning');
                    return;
                }

                // Filter: only show available rooms for selected dates & guest capacity
                const roomsContainer = document.getElementById('rooms-container');
                let allRoomEls = Array.from(document.querySelectorAll('.room-card[data-room-id]'));

                // Fallback if data-room-id or .room-card class is missing in PHP-rendered rooms
                if (allRoomEls.length === 0 && roomsContainer) {
                    const buttons = roomsContainer.querySelectorAll('button[onclick^="initiateBooking"]');
                    buttons.forEach(btn => {
                        const match = btn.getAttribute('onclick').match(/initiateBooking\((\d+)\)/);
                        if (match && match[1]) {
                            const card = btn.closest('.room-card') || btn.closest('[class*="col-"]') || btn.parentElement.parentElement;
                            if (card) {
                                card.dataset.roomId = match[1];
                                if (!allRoomEls.includes(card)) {
                                    allRoomEls.push(card);
                                }
                            }
                        }
                    });
                }

                allRoomEls.forEach(el => {
                    el.style.display = '';
                });

                const results = await Promise.all(allRoomEls.map(async (roomEl) => {
                    const roomId = roomEl.dataset.roomId;
                    const roomCapacity = parseInt(roomEl.dataset.capacity, 10) || 
                                         (appState.rooms.find(r => r.id == roomId)?.capacity) || 2;

                    // If room capacity is less than requested guests, it cannot accommodate them
                    if (roomCapacity < guests) {
                        roomEl.style.display = 'none';
                        return { roomId, available: false, capacityMatch: false };
                    }

                    try {
                        const response = await fetch('actions/book_action.php', {
                            method: 'POST',
                            headers: {'Content-Type': 'application/json'},
                            body: JSON.stringify({
                                action: 'check_availability',
                                room_id: roomId,
                                check_in: checkIn,
                                check_out: checkOut
                            })
                        });
                        const data = await response.json();
                        const available = data.available;

                        if (!available) {
                            roomEl.style.display = 'none';
                        } else {
                            // Optional: update badge text for selected filter
                            const isLoggedIn = appState.currentUser !== null;
                            if (isLoggedIn) {
                                let statusBadge = roomEl.querySelector('.room-status-badge');
                                if (!statusBadge) {
                                    const typeContainer = roomEl.querySelector('.flex.items-center.text-right.justify-end');
                                    if (typeContainer) {
                                        statusBadge = document.createElement('span');
                                        typeContainer.appendChild(statusBadge);
                                    }
                                }
                                if (statusBadge) {
                                    statusBadge.textContent = 'Available';
                                    statusBadge.classList.remove('hidden');
                                    statusBadge.className = 'room-status-badge bg-green-100 text-green-800 text-xs font-bold px-2 py-1 rounded uppercase ml-2';
                                }
                            }
                        }

                        return { roomId, available, capacityMatch: true };
                    } catch (err) {
                        console.error(err);
                        return { roomId, available: false, capacityMatch: true };
                    }
                }));

                const anyAvailable = results.some(r => r.available && r.capacityMatch);
                let noAvailEl = document.getElementById('no-available-rooms');
                if (!noAvailEl && roomsContainer) {
                    noAvailEl = document.createElement('div');
                    noAvailEl.id = 'no-available-rooms';
                    noAvailEl.className = 'col-span-full text-center text-gray-500 py-8';
                    roomsContainer.appendChild(noAvailEl);
                }
                if (noAvailEl) {
                    noAvailEl.innerHTML = `<p class="text-gray-500"><i class="fas fa-info-circle mr-1"></i> No rooms available for ${guests} ${guests === 1 ? 'guest' : 'guests'} on selected dates.</p>`;
                    noAvailEl.style.display = anyAvailable ? 'none' : '';
                }
            });
        }


        function showOccupiedModal(room, ranges, nextVacant) {
            const modal = document.createElement('div');
            modal.id = 'occupied-modal';
            modal.className = 'fixed inset-0 bg-black/70 flex items-center justify-center z-50 p-4';
            modal.innerHTML = `
                <div class="bg-white rounded-2xl p-6 max-w-lg w-full mx-4 shadow-2xl">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-2xl font-bold text-gray-800">${room.name} Status</h3>
                        <button onclick="document.getElementById('occupied-modal').remove()" class="text-3xl font-bold text-gray-500 hover:text-gray-700">&times;</button>
                    </div>
                    <div class="mb-6">
                        <h4 class="font-semibold text-lg mb-3 text-gray-700">Upcoming Bookings:</h4>
                        ${ranges.map(r => `
                            <div class="bg-orange-50 border border-orange-200 rounded-lg p-3 mb-2">
                                <div class="flex justify-between">
                                    <span class="font-medium text-gray-800">${r.ref}</span>
                                    <span class="text-sm text-gray-600">${r.from} → ${r.to}</span>
                                </div>
                                <div class="text-xs text-gray-500">Guest: ${r.guest}</div>
                            </div>
                        `).join('')}
                    </div>
                    ${nextVacant ? `
                        <div class="bg-green-50 border-2 border-green-200 rounded-xl p-4 mb-6 text-center">
                            <h4 class="font-bold text-xl text-green-800 mb-1">Next Available</h4>
                            <div class="text-2xl font-bold text-green-700">${nextVacant}</div>
                        </div>
                    ` : ''}
                    <button onclick="setSuggestedDates('${nextVacant}'); document.getElementById('occupied-modal').remove();" 
                            class="w-full bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white py-3 px-6 rounded-xl font-semibold text-lg shadow-lg transition-all">
                        Use Suggested Dates
                    </button>
                </div>
            `;
            document.body.appendChild(modal);
        }

        function setSuggestedDates(nextVacant) {
            if (nextVacant) {
                const checkInEl = document.getElementById('check-in-date');
                checkInEl.value = nextVacant;
                const tomorrow = new Date(nextVacant);
                tomorrow.setDate(tomorrow.getDate() + 1);
                document.getElementById('check-out-date').value = tomorrow.toISOString().split('T')[0];
                checkInEl.dispatchEvent(new Event('change'));
            }
        }

        // Initialize booking with availability checks
        async function initiateBooking(roomId) {
            if (!appState.currentUser || appState.currentUser.role === 'admin') {
                showToast('Please log in as a guest to book a room.', 'warning');
                router('login');
                return;
            }

            const room = appState.rooms.find(r => r.id == roomId) || { id: roomId, name: 'Room ' + roomId, price: 0, image: 'assets/image/background.jpg', type: '' };

            try {
                // Get selected dates
                const checkIn = document.getElementById('check-in-date').value || new Date().toISOString().split('T')[0];
                const checkOut = document.getElementById('check-out-date').value || new Date(Date.now() + 24*60*60*1000).toISOString().split('T')[0];

                if (new Date(checkOut) <= new Date(checkIn)) {
                    showToast('Check-out must be after check-in', 'error');
                    return;
                }

                // Get room availability for selected dates
                const availRes = await fetch('actions/book_action.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({
                        action: 'get_room_availability', 
                        room_id: roomId,
                        check_in: checkIn,
                        check_out: checkOut
                    })
                });
                const availData = await availRes.json();

                if (availData.room && availData.room.status === 'Maintenance') {
                    showToast('This room is currently under maintenance.', 'error');
                    return;
                }

                if (availData.room && availData.room.price) {
                    room.name = availData.room.name;
                    room.price = parseFloat(availData.room.price);
                    room.type = availData.room.type || room.type;
                    if (availData.room.image) room.image = availData.room.image;
                }

                if (!availData.is_currently_available) {
                    showOccupiedModal(room, availData.occupied_ranges || [], availData.next_vacant);
                    return;
                }

                // All good - create pending booking
                const nights = Math.ceil((new Date(checkOut) - new Date(checkIn)) / (24*60*60*1000));
                const total = (room.price * nights) + 15; // tax
                const guestCountEl = document.getElementById('guest-count');
                let guests = parseInt(guestCountEl ? guestCountEl.value : 1, 10) || 1;
                if (guests > 5) guests = 5;
                if (guests < 1) guests = 1;

                pendingBooking = {
                    roomId: parseInt(roomId),
                    roomName: room.name,
                    guests,
                    checkIn,
                    checkOut,
                    nights: Math.max(1, nights),
                    total,
                    pricePerNight: room.price
                };

                // Update summary UI
                document.getElementById('summary-room-img').src = room.image;
                document.getElementById('summary-room-name').textContent = room.name;
                document.getElementById('summary-room-desc').textContent = room.type;
                document.getElementById('summary-price').textContent = '₱' + room.price;
                document.getElementById('summary-nights').textContent = pendingBooking.nights;
                document.getElementById('summary-total').textContent = '₱' + total.toFixed(2);

                document.getElementById('booking-details-content').innerHTML = `
                    <div class="p-4 bg-green-50 border border-green-200 rounded-lg mb-4 space-y-1.5 text-sm">
                        <div class="flex justify-between"><span>Check In:</span> <strong>${checkIn}</strong></div>
                        <div class="flex justify-between"><span>Check Out:</span> <strong>${checkOut}</strong></div>
                        <div class="flex justify-between"><span>Guests:</span> <strong>${guests} ${guests === 1 ? 'Guest' : 'Guests'}</strong></div>
                        <div class="flex justify-between"><span>Room:</span> <strong>${room.name}</strong></div>
                        <div class="flex justify-between text-green-700 font-bold mt-2 pt-2 border-t border-green-200">
                            <span>Availability OK</span><i class="fas fa-check-circle ml-1"></i>
                        </div>
                    </div>
                `;

                document.getElementById('book-name').value = appState.currentUser.name;
                document.getElementById('book-email').value = appState.currentUser.email;

                router('booking-summary');
            } catch (error) {
                console.error('Booking init error:', error);
                showToast('Unable to verify availability. Please try again.', 'error');
            }
        }

        // Submit the final booking to the server
        const bookingForm = document.getElementById('booking-form');
        if (bookingForm) {
            bookingForm.addEventListener('submit', (e) => {
            e.preventDefault();
            
            if (!appState.currentUser) {
                showToast("Please login to continue.", 'warning');
                return;
            }

            if(appState.currentUser.role === 'admin') {
                showToast("Admins are not allowed to book rooms.", 'error');
                return;
            }

            const booking = {
                action: 'create',
                user_id: appState.currentUser.id,
                room_id: pendingBooking.roomId,
                check_in: pendingBooking.checkIn,
                check_out: pendingBooking.checkOut,
                nights: pendingBooking.nights,
                total: pendingBooking.total,
                guest_name: document.getElementById('book-name').value,
                payment_method: 'Full Payment'
            };

            fetch('actions/bookings_action.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(booking)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast(`Booking Confirmed! Reference: ${data.ref_id}. Receipt sent to email.`, 'success');
                    showInvoiceModal(data.ref_id, pendingBooking);
                } else {
                    showToast("Booking Failed: " + data.message, 'error');
                }
            })
            .catch(err => console.error('Booking Error:', err));
            });
        }

        // --- ROOM-SPECIFIC REVIEWS LOGIC ---

        async function loadRoomReviews(roomId) {
            const reviewsContainer = document.getElementById('room-reviews-container');
            const reviewFormContainer = document.getElementById('room-review-form-container');
            const reviewStatsContainer = document.getElementById('room-review-stats');
            const roomNameEl = document.getElementById('room-page-name');

            if (!reviewsContainer || !reviewFormContainer || !reviewStatsContainer || !roomNameEl) return;

            const room = appState.rooms.find(r => r.id == roomId);
            if (room) {
                roomNameEl.textContent = room.name;
            }

            try {
                const response = await fetch(`actions/reviews_action.php?action=get_reviews&room_id=${roomId}`);
                const data = await response.json();

                if (data.success) {
                    // Render stats
                    const stars = Array(Math.round(data.avg_rating)).fill('<i class="fas fa-star text-yellow-400"></i>').join('') + Array(5 - Math.round(data.avg_rating)).fill('<i class="fas fa-star text-gray-300"></i>').join('');
                    reviewStatsContainer.innerHTML = `
                        <div class="flex items-center gap-2">
                            <span class="text-3xl font-bold text-brand-dark">${data.avg_rating.toFixed(1)}</span>
                            <div class="flex flex-col">
                                <div class="text-lg">${stars}</div>
                                <span class="text-sm text-gray-500">Based on ${data.total_reviews} reviews</span>
                            </div>
                        </div>
                    `;

                    // Render reviews
                    if (data.reviews.length > 0) {
                        reviewsContainer.innerHTML = data.reviews.map(review => `
                            <div class="border-b border-gray-100 py-4">
                                <div class="flex items-center mb-2">
                                    <div class="w-10 h-10 rounded-full bg-gray-200 text-gray-600 flex items-center justify-center font-bold mr-3">${review.user_name.charAt(0)}</div>
                                    <div>
                                        <div class="font-bold text-gray-800">${review.user_name}</div>
                                        <div class="text-xs text-gray-500">${review.formatted_date}</div>
                                    </div>
                                    <div class="ml-auto text-yellow-400">${'★'.repeat(review.rating)}<span class="text-gray-300">${'★'.repeat(5-review.rating)}</span></div>
                                </div>
                                <p class="text-gray-600 pl-13">${review.comment}</p>
                            </div>
                        `).join('');
                    } else {
                        reviewsContainer.innerHTML = '<p class="text-center text-gray-500 py-8">No reviews yet. Be the first to write one!</p>';
                    }
                }

                // Render review form
                if (appState.currentUser) {
                    reviewFormContainer.innerHTML = `
                        <h3 class="text-xl font-bold text-brand-dark mb-4">Leave a Review</h3>
                        <form id="room-review-form" onsubmit="submitRoomReview(event, ${roomId})">
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Your Rating</label>
                                <div class="flex items-center gap-1 text-2xl text-gray-300" id="star-rating">
                                    ${[1,2,3,4,5].map(i => `<i class="fas fa-star cursor-pointer" data-value="${i}"></i>`).join('')}
                                </div>
                                <input type="hidden" id="review-rating" name="rating" required>
                            </div>
                            <div class="mb-4">
                                <label for="review-comment" class="block text-sm font-medium text-gray-700 mb-2">Your Comment</label>
                                <textarea id="review-comment" name="comment" rows="4" class="w-full border rounded-lg p-2 focus:ring-2 focus:ring-brand-blue outline-none" placeholder="Share your experience..." required></textarea>
                            </div>
                            <button type="submit" class="bg-brand-blue hover:bg-sky-600 text-white font-bold py-2 px-4 rounded-lg transition">Submit Review</button>
                        </form>
                    `;
                    // Add star rating interaction
                    const stars = reviewFormContainer.querySelectorAll('#star-rating .fa-star');
                    stars.forEach(star => {
                        star.addEventListener('mouseover', () => {
                            const rating = star.dataset.value;
                            stars.forEach(s => {
                                s.classList.toggle('text-yellow-400', s.dataset.value <= rating);
                                s.classList.toggle('text-gray-300', s.dataset.value > rating);
                            });
                        });
                        star.addEventListener('click', () => {
                            const rating = star.dataset.value;
                            document.getElementById('review-rating').value = rating;
                        });
                    });
                } else {
                    reviewFormContainer.innerHTML = '<div class="text-center p-6 bg-gray-50 rounded-lg"><p>Please <a onclick="router(\'login\')" class="text-brand-blue font-bold hover:underline cursor-pointer">log in</a> to leave a review.</p></div>';
                }

            } catch (error) {
                console.error("Failed to load reviews:", error);
                reviewsContainer.innerHTML = '<p class="text-red-500">Could not load reviews at this time.</p>';
            }
        }

        async function submitRoomReview(event, roomId) {
            event.preventDefault();
            const rating = document.getElementById('review-rating').value;
            const comment = document.getElementById('review-comment').value;

            // The rest of the submission logic will be handled by the existing `submitReview` function in `user_profile.js`
            // We can call it directly if we move it to a global scope, or replicate the fetch call here.
            // For simplicity, let's just use the existing `reviews_action.php` endpoint.
            submitReview({ rating, comment, room_id: roomId }, () => loadRoomReviews(roomId));
        }

        // --- INVOICE / RECEIPT MODAL ---
        function showInvoiceModal(refId, bookingDetails) {
            const modal = document.createElement('div');
            modal.id = 'invoice-modal';
            modal.className = 'fixed inset-0 bg-black/70 flex items-center justify-center z-[100] p-4';
            
            const guestName = document.getElementById('book-name').value;
            const guestEmail = document.getElementById('book-email').value;
            const paymentMethodEl = document.querySelector('input[name="payment"]:checked');
            const paymentMethod = paymentMethodEl ? paymentMethodEl.value : 'Full Payment';
            const dateNow = new Date().toLocaleDateString();
        
            modal.innerHTML = `
                <div class="bg-white rounded-2xl w-full max-w-2xl max-h-[90vh] flex flex-col shadow-2xl overflow-hidden">
                    <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-brand-blue text-white">
                        <h3 class="text-2xl font-bold">Booking Receipt</h3>
                        <button onclick="closeInvoiceModal()" class="text-white/80 hover:text-white transition-colors">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                    
                    <div id="invoice-content" class="p-8 overflow-y-auto flex-1 bg-white text-gray-800">
                        <div class="flex justify-between items-start mb-8 border-b pb-6">
                            <div>
                                <h2 class="text-3xl font-serif font-bold text-brand-dark mb-2">W Island Hub</h2>
                                <p class="text-sm text-gray-500">Backpackers Inn</p>
                                <p class="text-sm text-gray-500">Paradise Island, PH</p>
                            </div>
                            <div class="text-right flex items-center gap-4">
                                <div class="text-right">
                                    <div class="text-xl font-bold text-brand-blue mb-1">INVOICE</div>
                                    <div class="text-sm font-semibold text-gray-600">Ref: ${refId}</div>
                                    <div class="text-sm text-gray-500">Date: ${dateNow}</div>
                                </div>
                                <div class="bg-white p-1 border border-gray-100 rounded-lg shadow-sm">
                                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=80x80&data=${encodeURIComponent(refId)}" alt="QR Code" class="w-20 h-20">
                                </div>
                            </div>
                        </div>
        
                        <div class="grid grid-cols-2 gap-8 mb-8">
                            <div>
                                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Billed To:</h4>
                                <div class="font-bold text-gray-800">${guestName}</div>
                                <div class="text-sm text-gray-600">${guestEmail}</div>
                            </div>
                            <div class="text-right">
                                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Payment Method:</h4>
                                <div class="font-bold text-gray-800">${paymentMethod}</div>
                                <div class="text-sm text-gray-600">Status: Pending</div>
                            </div>
                        </div>
        
                        <div class="mb-8">
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Reservation Details:</h4>
                            <div class="bg-gray-50 rounded-xl p-4 border border-gray-100 grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="text-gray-500 block mb-1">Room</span>
                                    <strong class="text-gray-800 text-base">${bookingDetails.roomName}</strong>
                                </div>
                                <div>
                                    <span class="text-gray-500 block mb-1">Nights</span>
                                    <strong class="text-gray-800">${bookingDetails.nights}</strong>
                                </div>
                                <div>
                                    <span class="text-gray-500 block mb-1">Check-in</span>
                                    <strong class="text-gray-800">${bookingDetails.checkIn}</strong>
                                </div>
                                <div>
                                    <span class="text-gray-500 block mb-1">Check-out</span>
                                    <strong class="text-gray-800">${bookingDetails.checkOut}</strong>
                                </div>
                            </div>
                        </div>
        
                        <table class="w-full text-left border-collapse mb-8">
                            <thead>
                                <tr class="border-b-2 border-gray-200">
                                    <th class="py-3 font-bold text-gray-800">Description</th>
                                    <th class="py-3 font-bold text-gray-800 text-right">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="border-b border-gray-100">
                                    <td class="py-4">
                                        <div class="font-bold text-gray-800">Room Rate</div>
                                        <div class="text-xs text-gray-500">₱${bookingDetails.pricePerNight} x ${bookingDetails.nights} nights</div>
                                    </td>
                                    <td class="py-4 text-right font-medium">₱${(bookingDetails.pricePerNight * bookingDetails.nights).toFixed(2)}</td>
                                </tr>
                                <tr class="border-b border-gray-100">
                                    <td class="py-4">
                                        <div class="font-bold text-gray-800">Taxes & Fees</div>
                                    </td>
                                    <td class="py-4 text-right font-medium">₱15.00</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td class="py-4 font-bold text-xl text-gray-800 text-right pr-4">Total:</td>
                                    <td class="py-4 font-bold text-2xl text-brand-blue text-right">₱${bookingDetails.total.toFixed(2)}</td>
                                </tr>
                            </tfoot>
                        </table>
                        
                        <div class="text-center text-sm text-gray-500 mt-8 pt-8 border-t border-gray-100">
                            <p>Thank you for choosing W Island Hub Backpackers Inn!</p>
                            <p>We look forward to welcoming you.</p>
                        </div>
                    </div>
                    
                    <div class="p-6 bg-gray-50 border-t border-gray-100 flex gap-4 justify-end">
                        <button onclick="printInvoice()" class="px-6 py-2.5 bg-gray-800 hover:bg-gray-900 text-white rounded-xl font-medium transition-colors shadow-sm flex items-center gap-2">
                            <i class="fas fa-print"></i> Print
                        </button>
                        <button onclick="closeInvoiceModal()" class="px-6 py-2.5 bg-brand-blue hover:bg-sky-600 text-white rounded-xl font-medium transition-colors shadow-sm">
                            Done
                        </button>
                    </div>
                </div>
            `;
            
            document.body.appendChild(modal);
        }
        
        window.closeInvoiceModal = function() {
            const modal = document.getElementById('invoice-modal');
            if (modal) {
                modal.remove();
            }
            router('home');
        };
        
        window.printInvoice = function() {
            const content = document.getElementById('invoice-content').innerHTML;
            const printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <html>
                    <head>
                        <title>Invoice</title>
                        <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
                        <style>
                            body { font-family: sans-serif; padding: 20px; }
                            @media print {
                                .no-print { display: none; }
                            }
                        </style>
                    </head>
                    <body>
                        ${content}
                        <script>
                            window.onload = function() { 
                                setTimeout(function() {
                                    window.print(); 
                                    window.close(); 
                                }, 500);
                            }
                        </script>
                    </body>
                </html>
            `);
            printWindow.document.close();
        };

// Initialize page visibility on load to ensure proper display
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

        // Initialize App
        if (document.readyState === 'loading') {
            window.addEventListener('DOMContentLoaded', () => {
                initPageVisibility();
                init();
            });
        } else {
            initPageVisibility();
            init();
        }
        
