// --- DATA & STATE MANAGEMENT ---
        let appState = {
            currentUser: JSON.parse(sessionStorage.getItem('w_currentUser')) || null,
            rooms: [], // Will be populated with defaultRooms initially
            bookings: []
        };

        // Save state to sessionStorage (this function was missing!)
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

        const marqueeImages = [
            "assets/image/background.jpg",
            "assets/image/background.jpg",
            "assets/image/background.jpg",
            "assets/image/background.jpg",
            "assets/image/background.jpg",
            "assets/image/background.jpg"
        ];
        
        // --- HERO CAROUSEL LOGIC ---
        const heroSlides = [
            "assets/image/background.jpg",
            "assets/image/background.jpg",
            "assets/image/background.jpg",
            "assets/image/background.jpg"
        ];
        
        let currentSlideIdx = 0;
        let slideTimer;

        // Initialize the hero carousel logic and DOM elements
        function initHeroCarousel() {
            const container = document.getElementById('hero-slides');
            const dotsContainer = document.getElementById('hero-dots');
            
            if(!container) return;

            // Generate Slides
            container.innerHTML = heroSlides.map((img, i) => `
                <div class="hero-slide absolute inset-0 w-full h-full transition-opacity duration-1000 ease-in-out ${i === 0 ? 'opacity-100' : 'opacity-0'}">
                    <img src="${img}" class="w-full h-full object-cover" alt="Slide ${i}">
                    <div class="absolute inset-0 bg-black/30"></div>
                </div>
            `).join('');

            // Generate Dots
            dotsContainer.innerHTML = heroSlides.map((_, i) => `
                <button onclick="goToSlide(${i})" class="hero-dot w-3 h-3 rounded-full transition-all duration-300 cursor-pointer ${i === 0 ? 'bg-white scale-125' : 'bg-white/50 hover:bg-white/80'}"></button>
            `).join('');
            
            startSlideTimer();
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

        // Advance to the next slide
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

       

        // Main initialization function for the app
        function init() {
            // Fallback to hide loader after 5 seconds
            setTimeout(() => {
                const loader = document.getElementById('loader');
                if(loader && loader.style.display !== 'none') {
                    loader.style.display = 'none';
                    document.body.style.overflow = 'auto';
                }
            }, 5000);

            // Loader Animation
            if(window.gsap) {
                const tl = gsap.timeline({
                    onComplete: () => {
                        const loader = document.getElementById('loader');
                        if(loader) {
                            loader.style.display = 'none';
                            document.body.style.overflow = 'auto';
                        }
                    }
                });
                tl.to(".w-logo", { rotation: 360, duration: 2, ease: "elastic.out(1, 0.3)" })
                  .to("#loader", { y: "-100%", duration: 0.8, ease: "power4.in", delay: 0.5 });
            } else {
                // Hide loader immediately if GSAP not available
                const loader = document.getElementById('loader');
                if(loader) {
                    loader.style.display = 'none';
                    document.body.style.overflow = 'auto';
                }
            }

            initHeroCarousel();
            setupMarquee();
            updateAuthUI();
            setupMobileNav();
            fetchRooms(); // Fetch real data

            // Check if URL has hash to route
            const hash = window.location.hash.replace('#', '');
            if(hash) router(hash);

            // GSAP Animations
            if(window.gsap) {
                gsap.from(".gsap-hero-text", {
                    y: 50, opacity: 0, duration: 1, stagger: 0.2, delay: 2.8, ease: "power3.out"
                });
            }
        }

        // Fetch rooms from the database
        function fetchRooms() {
            fetch('actions/rooms_action.php')
                .then(res => res.json())
                .then(data => {
                    if(data.success && data.rooms.length > 0) {
                        appState.rooms = data.rooms;
                        // If currently on rooms page, re-render
                        if(document.getElementById('rooms') && document.getElementById('rooms').classList.contains('active')) {
                            renderRooms();
                        }
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

        // Setup the scrolling marquee gallery
        function setupMarquee() {
            const track = document.getElementById('marquee-track');
            if (!track) return;
            // Duplicate images to create seamless loop
            const images = [...marqueeImages, ...marqueeImages];
            track.innerHTML = images.map(src => `
                <div class="marquee-item" onclick="router('rooms')">
                    <img src="${src}" alt="Gallery Image">
                </div>
            `).join('');
        }

// Handle client-side routing and page navigation
        function router(pageId) {
            // Close mobile menu if open
            const menu = document.getElementById('mobile-menu');
            if(menu && !menu.classList.contains('hidden')) menu.classList.add('hidden');

            // Protected Routes
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

            // Logic triggers based on page
            if (pageId === 'rooms') renderRooms();

            
            // Navbar visibility
            const nav = document.getElementById('navbar');
            const footer = document.getElementById('main-footer');
        }

// Update the authentication UI based on user state
        function updateAuthUI() {
            const container = document.getElementById('auth-buttons');
            const mobileContainer = document.getElementById('mobile-auth-buttons');
            
            let html = '';
        if (appState.currentUser) {            
                const dashboardLink = appState.currentUser.role === 'admin' 
                    ? `<a href="admin/dashboard.php" class="text-sm font-bold mr-2 cursor-pointer text-brand-blue block md:inline mb-2 md:mb-0">Admin Panel</a>` 
                    : `<a href="user/profile.php" class="text-sm font-bold mr-2 cursor-pointer text-brand-blue block md:inline mb-2 md:mb-0">My Profile</a>`;
                
                html = `
                    <div class="flex flex-col md:flex-row md:items-center md:ml-4">
                        <span class="text-sm mr-2 block md:inline mb-2 md:mb-0">Hi, ${appState.currentUser.name.split(' ')[0]}</span>
                        ${dashboardLink}
                        <button onclick="logout()" class="text-sm border border-red-500 text-red-500 px-3 py-1 rounded hover:bg-red-50 transition w-full md:w-auto">Logout</button>
                    </div>
                `;
            } else {  
                html = `
                    <div class="flex items-center md:space-x-4">
                        <a onclick="router('login')" class="text-brand-dark hover:text-brand-blue font-medium cursor-pointer px-4 transition">Login</a>
                        <a onclick="router('register')" class="bg-brand-blue hover:bg-sky-600 text-white px-4 py-2 rounded-full font-medium transition shadow-md cursor-pointer text-center">Sign Up</a>
                    </div>
                `;
            }
            
            container.innerHTML = html;
            mobileContainer.innerHTML = html; 
        }

        // --- AUTHENTICATION ---

        document.getElementById('login-form').addEventListener('submit', (e) => {
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

        document.getElementById('register-form').addEventListener('submit', (e) => {
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

        // Real-time password strength validation
        document.getElementById('reg-pass').addEventListener('input', function(e) {
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
                <div class="bg-white rounded-xl shadow-sm hover:shadow-lg transition overflow-hidden border border-gray-100 flex flex-col">
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

        // Start the booking process for a selected room
        function initiateBooking(roomId) {
            if(!appState.currentUser) {
                alert("Please login to book a room.");
                router('login');
                return;
            }

            const room = appState.rooms.find(r => r.id === roomId);
            const checkInInput = document.getElementById('check-in-date').value;
            const checkOutInput = document.getElementById('check-out-date').value;

            // Default dates if not selected in filter
            let checkIn = checkInInput ? new Date(checkInInput) : new Date();
            let checkOut = checkOutInput ? new Date(checkOutInput) : new Date();
            
            if(!checkInInput) checkOut.setDate(checkOut.getDate() + 1);

            const nights = Math.max(1, Math.ceil((checkOut - checkIn) / (1000 * 60 * 60 * 24)));
            const tax = 15;
            const total = (room.price * nights) + tax;

            pendingBooking = {
                roomId: room.id,
                roomName: room.name,
                checkIn: checkIn.toISOString().split('T')[0],
                checkOut: checkOut.toISOString().split('T')[0],
                nights,
                pricePerNight: room.price,
                tax,
                total,
                status: 'Pending'
            };

            // Populate Summary
            document.getElementById('summary-room-name').innerText = room.name;
            document.getElementById('summary-room-desc').innerText = room.type;
            document.getElementById('summary-room-img').src = room.image;
            document.getElementById('summary-price').innerText = `$${room.price}`;
            document.getElementById('summary-nights').innerText = nights;
            document.getElementById('summary-total').innerText = `$${total}`;
            
            // Auto fill user details
            document.getElementById('book-name').value = appState.currentUser.name;
            document.getElementById('book-email').value = appState.currentUser.email;

            document.getElementById('booking-details-content').innerHTML = `
                <div class="flex justify-between"><span>Check-In:</span> <strong>${pendingBooking.checkIn}</strong></div>
                <div class="flex justify-between"><span>Check-Out:</span> <strong>${pendingBooking.checkOut}</strong></div>
                <div class="flex justify-between"><span>Guests:</span> <strong>${document.getElementById('guest-count').value || 1}</strong></div>
            `;

            router('booking-summary');
        }

        document.getElementById('booking-form').addEventListener('submit', (e) => {
            e.preventDefault();
            
            if (!appState.currentUser) {
                alert("Please login to continue.");
                return;
            }

            const booking = {
                action: 'create',
                user_id: appState.currentUser.id,
                room_id: pendingBooking.roomId,
                ...pendingBooking,
                paymentMethod: document.querySelector('input[name="payment"]:checked').value
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

        // Initialize App
        window.addEventListener('DOMContentLoaded', init);
