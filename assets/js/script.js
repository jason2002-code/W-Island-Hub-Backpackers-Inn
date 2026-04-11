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
            // Simple fast loader: 1.5s total
            document.body.style.overflow = 'hidden';

            if(window.gsap) {
                const tl = gsap.timeline({
                    onComplete: async () => {
                        const loader = document.getElementById('loader');
                        if(loader) {
                            loader.style.display = 'none';
                        }
                        document.body.style.overflow = 'auto';
                        // Init content
                        await initHeroCarousel();
                        await setupMarquee();
                    }
                });
                tl.to(".w-logo", { rotation: 360, duration: 1.2, ease: "elastic.out(1, 0.3)" })
                  .to("#loader", { y: "-100%", duration: 0.6, ease: "power4.in", delay: 0.7 });
            } else {
                // Fallback: 1.5s CSS fade
                setTimeout(async () => {
                    const loader = document.getElementById('loader');
                    if(loader) {
                        loader.classList.add('hidden');
                        setTimeout(() => {
                            loader.style.display = 'none';
                            document.body.style.overflow = 'auto';
                        }, 500);
                    }
                    await initHeroCarousel();
                    await setupMarquee();
                }, 2500);
            }
            
            updateAuthUI();
            setupMobileNav();
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
                        
                        // Rooms are now rendered via PHP, no need to re-render with JS
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

            // Hide all pages
            document.querySelectorAll('.page-section').forEach(el => el.classList.remove('active'));
            
            // Show specific page
            const target = document.getElementById(pageId);
            if(target) {
                target.classList.add('active');
                window.scrollTo(0,0);
            }
        }

// Toggle between "Login/Signup" and "User Profile/Logout" buttons in navbar
        function updateAuthUI() {
            const container = document.getElementById('auth-buttons');
            const mobileContainer = document.getElementById('mobile-auth-buttons');
            
            let html = '';
        if (appState.currentUser) {            
                const dashboardLink = appState.currentUser.role === 'admin'
                    ? `<a href="admin/dashboard.php" class="text-sm font-semibold cursor-pointer text-brand-dark hover:text-brand-cyan transition-colors block md:inline text-center bg-gray-100 hover:bg-gray-200 px-4 py-2 rounded-full">Admin Panel</a>`
                    : `<a href="user/profile.php" class="text-sm font-semibold cursor-pointer text-brand-dark hover:text-brand-cyan transition-colors block md:inline text-center bg-gray-100 hover:bg-gray-200 px-4 py-2 rounded-full">My Profile</a>`;
                
                const firstName = appState.currentUser.name ? appState.currentUser.name.split(' ')[0] : 'Guest';

                html = `
                    <div class="flex flex-col md:flex-row md:items-center gap-4">

                        <div class="flex items-center gap-2 px-3 py-1.5 bg-white md:bg-gray-50 rounded-full md:border border-gray-100 md:shadow-sm justify-center md:justify-start">
                            <div class="w-7 h-7 rounded-full bg-gradient-to-br from-brand-blue to-brand-cyan text-white flex items-center justify-center text-xs font-bold shadow-inner">
                                ${firstName.charAt(0).toUpperCase()}
                            </div>
                            <span class="text-sm font-semibold text-gray-700 tracking-wide pr-1">Hi, ${firstName}</span>
                        </div>
                        ${dashboardLink}
                        <button onclick="logout()" class="text-sm font-semibold border border-red-200 text-red-500 px-4 py-2 rounded-full hover:bg-red-50 hover:border-red-300 transition-all w-full md:w-auto shadow-sm">Logout</button>
                    </div>
                `;
            } else {  
                html = `
                    <div class="flex flex-col md:flex-row items-stretch md:items-center gap-3">

                        <a onclick="router('login')" class="text-gray-600 hover:text-brand-dark font-semibold cursor-pointer px-4 py-2.5 rounded-full hover:bg-gray-100 transition-all text-sm tracking-wide text-center">Login</a>
                        <a onclick="router('register')" class="bg-gradient-to-r from-brand-blue to-brand-cyan hover:shadow-lg hover:-translate-y-0.5 text-white px-6 py-2.5 rounded-full font-semibold transition-all shadow-md cursor-pointer text-sm tracking-wide text-center">Sign Up</a>
                    </div>
                `;
            }
            
        if (container) container.innerHTML = html;
        if (mobileContainer) mobileContainer.innerHTML = html; 
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

        // Handle Login Form Submission
        const loginForm = document.getElementById('login-form');
        if (loginForm) {
            loginForm.addEventListener('submit', (e) => {
            e.preventDefault();
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
                    appState.currentUser = data.user;
                    saveState();
                    updateAuthUI();
                     if (data.user.role === 'admin' && data.redirect) {
                        window.location.href = data.redirect;
                    } else {
                        router('home');
                    }
                } else {
                    alert(data.message);
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
                alert("Please meet all password requirements: " + validationErrors.join(", "));
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
                    alert(data.message);
                    router('login');
                } else {
                    alert(data.message);
                }
            })
            .catch(err => console.error('Registration Error:', err));
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
                    alert('Google Auth SDK is still loading or blocked. Please try again.');
                    return;
                }
                const client = google.accounts.oauth2.initTokenClient({
                    // ⚠️ IMPORTANT: Replace this with your actual Client ID from Google Cloud Console
                    client_id: 'YOUR_GOOGLE_CLIENT_ID_HERE.apps.googleusercontent.com',
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
                // Fallback simulation for Apple/X until their SDKs are configured
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
                    alert(data.message);
                }
            })
            .catch(err => console.error('Social Login Error:', err));
        }

// Log out the current user
      function logout() {
              appState.currentUser = null;
              saveState(); // Clears sessionStorage
              // Set flag to indicate user just logged out (for redirect handling)
              sessionStorage.setItem('w_justLoggedOut', 'true');
              // Redirect to logout.php for server-side session cleanup
            window.location.href = 'logout.php';
      }

        // --- BOOKING LOGIC ---

        // Render the list of rooms to the UI
        function renderRooms(filterDate = null) {
            const container = document.getElementById('rooms-container');
            container.innerHTML = appState.rooms.map(room => {
                const stars = Array(Math.round(room.rating)).fill('<i class="fas fa-star text-yellow-400 text-xs"></i>').join('');
                
                return `
                <div class="bg-white rounded-xl shadow-sm hover:shadow-lg transition-all duration-300 overflow-hidden border border-gray-100 flex flex-col">
                    <div class="h-48 overflow-hidden relative">
                         <img src="${room.image}" alt="${room.name}" class="w-full h-full object-cover transition duration-500 hover:scale-110">
                         <div class="absolute top-2 right-2 bg-white/90 backdrop-blur px-2 py-1 rounded text-xs font-bold text-gray-800">
                            ${stars}
                         </div>
                    </div>
                    <div class="p-6 flex-1 flex flex-col">
                        <div class="flex justify-between items-start mb-2">
                            <h3 class="text-xl font-bold text-brand-dark">${room.name}</h3>
                            <span class="bg-blue-50 text-brand-blue text-xs font-bold px-2 py-1 rounded uppercase">${room.type}</span>
                        </div>
                        <p class="text-gray-500 text-sm mb-4 line-clamp-2">${room.desc}</p>
                        
                        <div class="flex flex-wrap gap-2 mb-6">
                            ${room.amenities.slice(0,3).map(a => `<span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">${a}</span>`).join('')}
                        </div>
                        
                        <div class="mt-auto pt-4 border-t flex justify-between items-center">
                            <div>
                                <span class="text-2xl font-bold text-brand-blue">$${room.price}</span>
                                <span class="text-gray-400 text-sm">/ night</span>
                            </div>
                            <button onclick="initiateBooking(${room.id})" class="bg-brand-dark hover:bg-gray-800 text-white px-4 py-2 rounded-lg font-medium transition text-sm">
                                Book Now
                            </button>
                        </div>
                    </div>
                </div>
                `;
            }).join('');
        }

        let pendingBooking = null;

        // Availability check form handler
        const availabilityForm = document.getElementById('check-availability-form');
        if (availabilityForm) {
            availabilityForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const checkIn = document.getElementById('check-in-date').value;
                const checkOut = document.getElementById('check-out-date').value;
                if (!checkIn || !checkOut) {
                    alert('Please select dates');
                    return;
                }
                // Update room statuses
                document.querySelectorAll('[data-room-id]').forEach(roomEl => {
                    const roomId = roomEl.dataset.roomId;
                    checkRoomAvailability(roomId, checkIn, checkOut);
                });
            });
        }

        async function checkRoomAvailability(roomId, checkIn, checkOut) {
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
                const statusBadge = document.querySelector(`[data-room-id="${roomId}"] .room-status-badge`);
                if (statusBadge) {
                    statusBadge.textContent = data.available ? 'Available ✓' : 'Occupied ✗';
                    statusBadge.className = `room-status-badge px-2 py-1 rounded text-xs font-bold ${data.available ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}`;
                }
            } catch (e) {
                console.error(e);
            }
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
                alert('Please login as guest user to book.');
                router('login');
                return;
            }

            const room = appState.rooms.find(r => r.id === roomId);
            if (!room) return;

            try {
                // Get room availability
                const availRes = await fetch('actions/book_action.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({action: 'get_room_availability', room_id: roomId})
                });
                const availData = await availRes.json();

                if (!availData.is_currently_available && availData.occupied_ranges?.length > 0) {
                    showOccupiedModal(room, availData.occupied_ranges, availData.next_vacant);
                    return;
                }

                // Get selected dates
                const checkIn = document.getElementById('check-in-date').value || new Date().toISOString().split('T')[0];
                const checkOut = document.getElementById('check-out-date').value || new Date(Date.now() + 24*60*60*1000).toISOString().split('T')[0];

                // Specific date check
                const checkRes = await fetch('actions/book_action.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({
                        action: 'check_availability',
                        room_id: roomId,
                        check_in: checkIn,
                        check_out: checkOut
                    })
                });
                const checkData = await checkRes.json();

                if (!checkData.available) {
                    alert('Dates not available: ' + checkData.message);
                    return;
                }

                // All good - create pending booking
                const nights = Math.ceil((new Date(checkOut) - new Date(checkIn)) / (24*60*60*1000));
                const total = (room.price * nights) + 15; // tax

                pendingBooking = {
                    roomId: parseInt(roomId),
                    roomName: room.name,
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
                document.getElementById('summary-price').textContent = '$' + room.price;
                document.getElementById('summary-nights').textContent = pendingBooking.nights;
                document.getElementById('summary-total').textContent = '$' + total.toFixed(2);

                document.getElementById('booking-details-content').innerHTML = `
                    <div class="p-4 bg-green-50 border rounded-lg mb-4">
                        <div class="flex justify-between"><span>Check In:</span> <strong>${checkIn}</strong></div>
                        <div class="flex justify-between"><span>Check Out:</span> <strong>${checkOut}</strong></div>
                        <div class="flex justify-between"><span>Room:</span> <strong>${room.name}</strong></div>
                        <div class="flex justify-between text-green-700 font-bold mt-2">
                            <span>Availability OK</span><i class="fas fa-check-circle ml-1"></i>
                        </div>
                    </div>
                `;

                document.getElementById('book-name').value = appState.currentUser.name;
                document.getElementById('book-email').value = appState.currentUser.email;

                router('booking-summary');
            } catch (error) {
                console.error('Booking init error:', error);
                alert('Unable to verify availability. Please try again.');
            }
        }

        // Submit the final booking to the server
        const bookingForm = document.getElementById('booking-form');
        if (bookingForm) {
            bookingForm.addEventListener('submit', (e) => {
            e.preventDefault();
            
            if (!appState.currentUser) {
                alert("Please login to continue.");
                return;
            }

            if(appState.currentUser.role === 'admin') {
                alert("Admins are not allowed to book rooms.");
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
                payment_method: document.querySelector('input[name="payment"]:checked').value
            };

            fetch('actions/bookings_action.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(booking)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert(`Booking Confirmed!\nReference: ${data.ref_id}\nWe have sent a receipt to your email.`);
                    router('home');
                } else {
                    alert("Booking Failed: " + data.message);
                }
            })
            .catch(err => console.error('Booking Error:', err));
            });
        }

        // Initialize App
        if (document.readyState === 'loading') {
            window.addEventListener('DOMContentLoaded', init);
        } else {
            init(); // DOM is already loaded
        }
        
