
<!-- ================= HOME / LANDING PAGE ================= -->
<div id="home" class="page-section active">

    <!-- ================= HERO CAROUSEL SECTION ================= -->
    <div id="hero-carousel" class="relative min-h-[85vh] md:min-h-[90vh] w-full overflow-hidden bg-slate-950 group flex items-center justify-center">
        <!-- Dynamic Slides Container (Populated by JS / actions/sliders_action.php) -->
        <div id="hero-slides" class="absolute inset-0 w-full h-full">
            <!-- Populated by JS -->
        </div>

        <!-- Ambient Luxury Gradient & Overlay -->
        <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/50 to-black/35 pointer-events-none z-10"></div>
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_center,_var(--tw-gradient-stops))] from-amber-500/10 via-transparent to-transparent pointer-events-none z-10"></div>

        <!-- Hero Overlay Content -->
        <div class="relative z-20 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-20 text-center text-white flex flex-col items-center justify-center">
            
            <!-- Tropical Island Pill Badge -->
            <div class="gsap-hero-text inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-amber-300 text-xs sm:text-sm font-semibold tracking-wider uppercase mb-6 shadow-xl">
                <i class="fas fa-umbrella-beach text-amber-400"></i>
                <span>Panglao Island, Bohol &bull; Premier Backpackers & Boutique Inn</span>
            </div>

            <!-- Main Headline -->
            <h1 class="gsap-hero-text text-4xl sm:text-6xl md:text-7xl lg:text-8xl font-serif font-bold tracking-tight mb-6 leading-[1.1] text-white drop-shadow-2xl">
                Where Island Soul <br class="hidden sm:inline">
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-200 via-amber-400 to-yellow-500 font-serif italic">
                    Meets Modern Comfort
                </span>
            </h1>

            <!-- Subtitle -->
            <p class="gsap-hero-text max-w-2xl mx-auto text-base sm:text-lg md:text-xl text-slate-200 font-light mb-10 leading-relaxed drop-shadow-md">
                Experience crystal-clear beaches, vibrant community vibes, high-speed Starlink WiFi, and restful tropical rooms tailored for backpackers and modern nomads.
            </p>

            <!-- Call To Actions -->
            <div class="gsap-hero-btn flex flex-col sm:flex-row items-center gap-4 w-full sm:w-auto justify-center mb-12">
                <button onclick="router('rooms')" class="w-full sm:w-auto bg-gradient-to-r from-amber-500 via-yellow-500 to-amber-600 hover:from-amber-600 hover:to-yellow-600 text-slate-950 font-bold py-4 px-9 rounded-full transition-all duration-300 transform hover:scale-105 shadow-xl hover:shadow-amber-500/25 flex items-center justify-center gap-3 text-base pointer-events-auto">
                    <span>Explore Accommodations</span>
                    <i class="fas fa-arrow-right text-sm"></i>
                </button>
                <a href="#inn-amenities" class="w-full sm:w-auto bg-white/10 hover:bg-white/20 backdrop-blur-md text-white border border-white/25 font-semibold py-4 px-8 rounded-full transition-all duration-300 flex items-center justify-center gap-2 text-base pointer-events-auto group">
                    <i class="fas fa-concierge-bell text-amber-400 group-hover:rotate-12 transition-transform"></i>
                    <span>Inn Amenities</span>
                    <i class="fas fa-chevron-down text-xs ml-1 text-amber-300/80 group-hover:translate-y-0.5 transition-transform"></i>
                </a>
                <a href="#discovery-bohol" class="w-full sm:w-auto bg-white/10 hover:bg-white/20 backdrop-blur-md text-white border border-white/25 font-semibold py-4 px-8 rounded-full transition-all duration-300 flex items-center justify-center gap-2 text-base pointer-events-auto">
                    <i class="fas fa-compass text-amber-400"></i>
                    <span>Discover Bohol</span>
                </a>
            </div>

            <!-- Trust Highlights Badges -->
            <div class="gsap-hero-text grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-6 pt-4 border-t border-white/10 w-full max-w-4xl text-xs sm:text-sm text-slate-300">
                <div class="flex items-center justify-center gap-2">
                    <i class="fas fa-star text-amber-400"></i>
                    <span><strong>4.9/5</strong> Guest Rating</span>
                </div>
                <div class="flex items-center justify-center gap-2">
                    <i class="fas fa-wifi text-sky-400"></i>
                    <span><strong>Starlink</strong> Fast WiFi</span>
                </div>
                <div class="flex items-center justify-center gap-2">
                    <i class="fas fa-water text-teal-400"></i>
                    <span><strong>2 Mins</strong> to Shoreline</span>
                </div>
                <div class="flex items-center justify-center gap-2">
                    <i class="fas fa-shield-alt text-emerald-400"></i>
                    <span><strong>Verified</strong> Direct Rates</span>
                </div>
            </div>
        </div>

        <!-- Navigation Controls -->
        <button onclick="prevSlide()" aria-label="Previous Slide" class="absolute left-4 sm:left-6 top-1/2 -translate-y-1/2 z-30 text-white/40 hover:text-white transition p-3.5 rounded-full bg-black/20 hover:bg-black/40 backdrop-blur-sm opacity-0 group-hover:opacity-100 duration-300 cursor-pointer">
            <i class="fas fa-chevron-left text-2xl sm:text-3xl"></i>
        </button>
        <button onclick="nextSlide()" aria-label="Next Slide" class="absolute right-4 sm:right-6 top-1/2 -translate-y-1/2 z-30 text-white/40 hover:text-white transition p-3.5 rounded-full bg-black/20 hover:bg-black/40 backdrop-blur-sm opacity-0 group-hover:opacity-100 duration-300 cursor-pointer">
            <i class="fas fa-chevron-right text-2xl sm:text-3xl"></i>
        </button>
        
        <!-- Slide Dots -->
        <div id="hero-dots" class="absolute bottom-8 left-1/2 -translate-x-1/2 z-30 flex space-x-3">
            <!-- Dots injected via JS -->
        </div>

        <!-- Dragdown Scroll Indicator to Amenities -->
        <a href="#inn-amenities" aria-label="Scroll to Inn Amenities" class="absolute bottom-2 sm:bottom-3 right-6 sm:right-10 z-30 hidden sm:flex items-center gap-2 px-4 py-1.5 rounded-full bg-black/40 hover:bg-black/60 backdrop-blur-md text-slate-300 hover:text-amber-300 text-xs font-semibold tracking-wider transition-all duration-300 border border-white/15 shadow-lg group cursor-pointer">
            <span>Amenities</span>
            <i class="fas fa-chevron-down text-xs text-amber-400 animate-bounce"></i>
        </a>
    </div>

    <!-- ================= FLOATING QUICK BOOKING SEARCH BAR ================= -->
    <div class="relative z-30 max-w-6xl mx-auto px-4 sm:px-6 -mt-10 md:-mt-14 mb-16">
        <div class="bg-white/95 backdrop-blur-xl p-6 sm:p-8 rounded-3xl shadow-2xl border border-white/60">
            <form onsubmit="searchFromHome(event)" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
                
                <!-- Check-in Date -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5 flex items-center gap-1.5">
                        <i class="fas fa-calendar-check text-amber-500"></i> Check-In
                    </label>
                    <div class="relative">
                        <input type="date" id="home-check-in-date" min="<?php echo date('Y-m-d'); ?>" value="<?php echo date('Y-m-d'); ?>" 
                               class="w-full bg-slate-50 border border-slate-200 rounded-2xl p-3.5 text-sm font-semibold text-slate-800 focus:bg-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 outline-none transition cursor-pointer">
                    </div>
                </div>

                <!-- Check-out Date -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5 flex items-center gap-1.5">
                        <i class="fas fa-calendar-times text-amber-500"></i> Check-Out
                    </label>
                    <div class="relative">
                        <input type="date" id="home-check-out-date" min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" value="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" 
                               class="w-full bg-slate-50 border border-slate-200 rounded-2xl p-3.5 text-sm font-semibold text-slate-800 focus:bg-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 outline-none transition cursor-pointer">
                    </div>
                </div>

                <!-- Guests Selector (Adults & Children in the same box) -->
                <div class="relative" id="home-guest-box-container">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5 flex items-center gap-1.5">
                        <i class="fas fa-users text-amber-500"></i> Guests
                    </label>
                    <div id="home-guest-trigger" onclick="toggleGuestDropdown('home')" class="w-full bg-slate-50 hover:bg-slate-100/80 border border-slate-200 rounded-2xl p-3.5 text-sm font-semibold text-slate-800 focus:bg-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 outline-none transition cursor-pointer flex items-center justify-between select-none shadow-xs">
                        <div class="flex items-center gap-2 truncate">
                            <span id="home-guest-summary-text" class="truncate font-semibold text-slate-800">1 Guest (1 Adult)</span>
                        </div>
                        <i id="home-guest-chevron" class="fas fa-chevron-down text-slate-400 text-xs transition-transform duration-200"></i>
                    </div>

                    <!-- Hidden Inputs for Form Submission / Logic -->
                    <input type="hidden" id="home-guest-count" name="guest_count" value="1">
                    <input type="hidden" id="home-adults-count" name="adults" value="1">
                    <input type="hidden" id="home-children-count" name="children" value="0">

                    <!-- Dropdown Popover (Same Box) -->
                    <div id="home-guest-dropdown" class="hidden absolute left-0 right-0 sm:right-auto sm:w-80 top-full mt-2 bg-white rounded-2xl shadow-2xl border border-slate-200 p-4 z-50 space-y-4">
                        <div class="flex justify-between items-center pb-2 border-b border-slate-100">
                            <div>
                                <h4 class="text-xs font-bold uppercase text-slate-700 tracking-wider">Select Guests</h4>
                                <p class="text-[11px] text-slate-400">No maximum limit</p>
                            </div>
                            <button type="button" onclick="closeGuestDropdown('home')" class="text-slate-400 hover:text-slate-600 text-xs p-1 rounded-lg">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>

                        <!-- Adults: 18+ -->
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="font-bold text-slate-800 text-sm">Adults</div>
                                <div class="text-[11px] text-slate-400 font-medium">Ages 18 and above</div>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" onclick="updateGuestStepper('home', 'adults', -1)" class="w-8 h-8 rounded-full border border-slate-200 hover:border-slate-300 bg-slate-50 hover:bg-slate-100 flex items-center justify-center text-slate-600 font-bold transition disabled:opacity-30 disabled:cursor-not-allowed" id="home-adults-minus" disabled>
                                    <i class="fas fa-minus text-xs"></i>
                                </button>
                                <span id="home-adults-display" class="w-8 text-center font-bold text-slate-800 text-sm">1</span>
                                <button type="button" onclick="updateGuestStepper('home', 'adults', 1)" class="w-8 h-8 rounded-full border border-slate-200 hover:border-amber-400 bg-slate-50 hover:bg-amber-50 flex items-center justify-center text-slate-700 hover:text-amber-600 font-bold transition">
                                    <i class="fas fa-plus text-xs"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Children: 17 & below -->
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="font-bold text-slate-800 text-sm">Children</div>
                                <div class="text-[11px] text-slate-400 font-medium">Ages 17 &amp; below</div>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" onclick="updateGuestStepper('home', 'children', -1)" class="w-8 h-8 rounded-full border border-slate-200 hover:border-slate-300 bg-slate-50 hover:bg-slate-100 flex items-center justify-center text-slate-600 font-bold transition disabled:opacity-30 disabled:cursor-not-allowed" id="home-children-minus" disabled>
                                    <i class="fas fa-minus text-xs"></i>
                                </button>
                                <span id="home-children-display" class="w-8 text-center font-bold text-slate-800 text-sm">0</span>
                                <button type="button" onclick="updateGuestStepper('home', 'children', 1)" class="w-8 h-8 rounded-full border border-slate-200 hover:border-amber-400 bg-slate-50 hover:bg-amber-50 flex items-center justify-center text-slate-700 hover:text-amber-600 font-bold transition">
                                    <i class="fas fa-plus text-xs"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Age Guidance Badge -->
                        <div class="p-2.5 rounded-xl bg-amber-50/70 border border-amber-200/60 text-[11px] text-amber-900 flex items-start gap-2">
                            <i class="fas fa-info-circle text-amber-500 mt-0.5 flex-shrink-0"></i>
                            <span>Age 18+ counts as Adult. Age 17 and below counts as Child.</span>
                        </div>

                        <!-- Apply Button -->
                        <button type="button" onclick="closeGuestDropdown('home')" class="w-full bg-slate-900 hover:bg-amber-500 hover:text-slate-950 text-white font-bold py-2 rounded-xl text-xs transition shadow-sm">
                            Done (<span id="home-total-guest-badge">1 Guest</span>)
                        </button>
                    </div>
                </div>

                <!-- Submit Button -->
                <div>
                    <button type="submit" class="w-full bg-slate-900 hover:bg-slate-800 text-white font-bold py-3.5 px-6 rounded-2xl transition-all duration-300 transform hover:-translate-y-0.5 shadow-lg flex items-center justify-center gap-2 group">
                        <i class="fas fa-search text-amber-400 group-hover:scale-110 transition-transform"></i>
                        <span>Check Availability</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ================= INN HIGHLIGHTS & STATS STRIP ================= -->
    <section class="py-12 bg-gradient-to-b from-slate-50 via-white to-slate-50 px-4">
        <div class="max-w-7xl mx-auto">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
                
                <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-100 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col items-center text-center">
                    <div class="w-14 h-14 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-2xl mb-4 shadow-inner">
                        <i class="fas fa-heart"></i>
                    </div>
                    <h3 class="text-3xl font-bold text-slate-900 mb-1">1,200+</h3>
                    <p class="text-xs sm:text-sm font-semibold text-slate-500 uppercase tracking-wider">Happy Travelers</p>
                    <span class="text-xs text-slate-400 mt-2">From 45+ countries worldwide</span>
                </div>

                <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-100 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col items-center text-center">
                    <div class="w-14 h-14 rounded-2xl bg-sky-50 text-sky-600 flex items-center justify-center text-2xl mb-4 shadow-inner">
                        <i class="fas fa-award"></i>
                    </div>
                    <h3 class="text-3xl font-bold text-slate-900 mb-1">4.9 ★</h3>
                    <p class="text-xs sm:text-sm font-semibold text-slate-500 uppercase tracking-wider">Top Rated Inn</p>
                    <span class="text-xs text-slate-400 mt-2">Verified guest satisfaction</span>
                </div>

                <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-100 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col items-center text-center">
                    <div class="w-14 h-14 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center text-2xl mb-4 shadow-inner">
                        <i class="fas fa-walking"></i>
                    </div>
                    <h3 class="text-3xl font-bold text-slate-900 mb-1">2 Mins</h3>
                    <p class="text-xs sm:text-sm font-semibold text-slate-500 uppercase tracking-wider">Walk to Beach</p>
                    <span class="text-xs text-slate-400 mt-2">Pristine Panglao coast</span>
                </div>

                <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-100 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col items-center text-center">
                    <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl mb-4 shadow-inner">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3 class="text-3xl font-bold text-slate-900 mb-1">24 / 7</h3>
                    <p class="text-xs sm:text-sm font-semibold text-slate-500 uppercase tracking-wider">Security & Desk</p>
                    <span class="text-xs text-slate-400 mt-2">Individual lockers & cameras</span>
                </div>

            </div>
        </div>
    </section>

    <!-- ================= ISLAND GALLERY CAROUSEL SECTION ================= -->
    <section id="gallery-carousel-section" class="py-20 bg-white px-4 sm:px-6 lg:px-8 border-b border-slate-100 relative overflow-hidden">
        <div class="max-w-7xl mx-auto">
            
            <!-- Section Header -->
            <div class="flex flex-col md:flex-row md:items-end justify-between mb-12 gap-6">
                <div>
                    <span class="text-xs font-bold uppercase tracking-widest text-amber-600 bg-amber-50 px-3.5 py-1.5 rounded-full border border-amber-100 inline-flex items-center gap-1.5">
                        <i class="fas fa-camera-retro text-amber-500"></i> Visual Journey
                    </span>
                    <h2 class="text-3xl sm:text-4xl md:text-5xl font-serif font-bold text-slate-900 mt-3">
                        Our Island Gallery
                    </h2>
                    <p class="text-slate-500 text-base sm:text-lg mt-2 max-w-xl">
                        Snapshots of life, chill vibes, tranquil spaces, and friendly tropical adventures at W Island Hub Backpackers Inn.
                    </p>
                </div>

                <!-- Carousel Navigation Controls & CTA -->
                <div class="flex items-center gap-3">
                    <button onclick="prevGallerySlide()" 
                            aria-label="Previous Gallery Photo" 
                            class="w-12 h-12 rounded-full border border-slate-200 hover:border-amber-500 bg-slate-50 hover:bg-amber-500 text-slate-700 hover:text-slate-950 flex items-center justify-center transition-all duration-300 shadow-sm hover:shadow-md cursor-pointer group">
                        <i class="fas fa-chevron-left text-sm group-hover:-translate-x-0.5 transition-transform"></i>
                    </button>
                    <button onclick="nextGallerySlide()" 
                            aria-label="Next Gallery Photo" 
                            class="w-12 h-12 rounded-full border border-slate-200 hover:border-amber-500 bg-slate-50 hover:bg-amber-500 text-slate-700 hover:text-slate-950 flex items-center justify-center transition-all duration-300 shadow-sm hover:shadow-md cursor-pointer group">
                        <i class="fas fa-chevron-right text-sm group-hover:translate-x-0.5 transition-transform"></i>
                    </button>
                    <button onclick="router('rooms')" 
                            class="hidden sm:inline-flex items-center gap-2 text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 px-4 py-3 rounded-full transition-colors ml-2">
                        <span>Explore Rooms</span>
                        <i class="fas fa-arrow-right text-[10px]"></i>
                    </button>
                </div>
            </div>

            <!-- Gallery Carousel Viewport -->
            <div id="gallery-carousel-container" class="relative overflow-hidden rounded-3xl pb-2">
                <!-- Sliding Track -->
                <div id="gallery-carousel-track" class="flex transition-transform duration-500 ease-out">
                    <!-- Populated dynamically by initGalleryCarousel() in carousel.js -->
                </div>
            </div>

            <!-- Carousel Pagination Dots -->
            <div id="gallery-dots" class="flex items-center justify-center gap-2 mt-8">
                <!-- Populated dynamically by carousel.js -->
            </div>

        </div>
    </section>

    <!-- Fullscreen Gallery Lightbox Modal -->
    <div id="gallery-lightbox" class="fixed inset-0 bg-black/90 backdrop-blur-md z-50 hidden items-center justify-center p-4">
        <div class="relative max-w-5xl w-full flex flex-col items-center animate-fadeIn">
            <!-- Close Button -->
            <button onclick="closeGalleryLightbox()" 
                    aria-label="Close Lightbox" 
                    class="absolute -top-12 right-0 sm:right-2 text-white/70 hover:text-white text-2xl p-2 cursor-pointer transition">
                <i class="fas fa-times"></i>
            </button>
            
            <!-- Lightbox Image -->
            <div class="max-h-[80vh] overflow-hidden rounded-2xl shadow-2xl border border-white/10 bg-black flex items-center justify-center">
                <img id="lightbox-image" src="" alt="Enlarged Gallery Photo" class="max-h-[78vh] w-auto object-contain">
            </div>

            <!-- Caption & Details -->
            <div class="mt-4 text-center">
                <h4 id="lightbox-title" class="text-white font-serif font-bold text-lg sm:text-xl"></h4>
                <p class="text-slate-400 text-xs sm:text-sm mt-1">W Island Hub Backpackers Inn &bull; Panglao Island, Bohol</p>
            </div>
        </div>
    </div>

    <!-- ================= SIGNATURE INN AMENITIES & LIFESTYLE ================= -->
    <?php
    // Ensure DB connection is loaded
    if (!isset($pdo) || !$pdo) {
        if (file_exists(__DIR__ . '/../config/db.php')) {
            require_once __DIR__ . '/../config/db.php';
        }
    }
    if ((!isset($pdo) || !$pdo) && function_exists('getDB')) {
        $pdo = getDB();
    }

    // Query active amenities from database (with fallback to default array)
    $activeAmenitiesList = [];
    if (isset($pdo) && $pdo) {
        try {
            $stmtAmen = $pdo->query("SELECT * FROM `amenities` WHERE `is_active` = 1 ORDER BY `sort_order` ASC, `id` ASC");
            $activeAmenitiesList = $stmtAmen->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $activeAmenitiesList = [];
        }
    }
    if (empty($activeAmenitiesList)) {
        $activeAmenitiesList = [
            ['title' => 'Beachfront & Coral Coast', 'icon' => 'fas fa-umbrella-beach', 'category' => 'Location', 'description' => 'Steps away from pristine white sands. Enjoy early morning ocean dips, snorkeling among reef life, and front-row seats to world-famous sunsets.', 'image_url' => 'assets/image/amenity_beachfront.jpg'],
            ['title' => 'Nomad-Grade Starlink WiFi', 'icon' => 'fas fa-wifi', 'category' => 'Connectivity', 'description' => 'Lightning-fast, reliable satellite internet across all dorms, private rooms, and chill lounges. Seamless for remote work, video calls, and streaming.', 'image_url' => 'assets/image/amenity_starlink.jpg'],
            ['title' => 'Sunset Social Lounge & Bar', 'icon' => 'fas fa-cocktail', 'category' => 'Social', 'description' => 'Vibrant open-air communal hub with board games, craft beers, refreshing tropical fruit shakes, and evening acoustic music jams.', 'image_url' => 'assets/image/amenity_lounge_bar.jpg'],
            ['title' => 'Scooter Rentals & Island Tours', 'icon' => 'fas fa-motorcycle', 'category' => 'Adventures', 'description' => 'Rent scooters right from the front desk. Book hassle-free tours to Chocolate Hills, Tarsier Sanctuaries, and dolphin watching expeditions.', 'image_url' => 'assets/image/amenity_scooter_tour.jpg'],
            ['title' => 'Keycard Security & Lockers', 'icon' => 'fas fa-key', 'category' => 'Security', 'description' => 'Travel with total peace of mind. Every dorm guest receives an oversized security locker, personal bedside power socket, and reading lamp.', 'image_url' => 'assets/image/amenity_lockers.jpg'],
            ['title' => 'Free Coffee & Water Refills', 'icon' => 'fas fa-mug-hot', 'category' => 'Dining', 'description' => 'Complimentary fresh-brewed local island coffee every morning. Unlimited filtered cold drinking water stations to keep you energized and eco-friendly.', 'image_url' => 'assets/image/amenity_coffee_water.jpg']
        ];
    }

    $amenityGradients = [
        'Location' => 'from-amber-400 to-yellow-600 text-slate-950',
        'Connectivity' => 'from-sky-400 to-blue-600 text-white',
        'Social' => 'from-purple-400 to-indigo-600 text-white',
        'Adventures' => 'from-teal-400 to-emerald-600 text-white',
        'Security' => 'from-rose-400 to-red-600 text-white',
        'Dining' => 'from-amber-500 to-orange-600 text-white',
        'Comfort' => 'from-indigo-400 to-violet-600 text-white',
        'Leisure' => 'from-cyan-400 to-blue-600 text-white'
    ];
    ?>
    <section id="inn-amenities" class="py-24 bg-slate-950 text-white px-4 sm:px-6 lg:px-8 relative overflow-hidden">
        
        <!-- Ambient Glow Elements -->
        <div class="absolute top-0 right-0 w-96 h-96 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 w-96 h-96 bg-sky-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="max-w-7xl mx-auto relative z-10">
            
            <!-- Section Header with Carousel / Slider Controls -->
            <div class="flex flex-col md:flex-row md:items-end justify-between mb-16 gap-6">
                <div>
                    <span class="text-xs font-bold uppercase tracking-widest text-amber-400 bg-white/10 px-4 py-1.5 rounded-full border border-white/15 inline-flex items-center gap-2 shadow-xs">
                        <i class="fas fa-concierge-bell text-amber-400"></i> The W Island Hub Experience
                    </span>
                    <h2 class="text-3xl sm:text-5xl font-serif font-bold text-white mt-4">
                        Designed for Travelers &amp; Nomads
                    </h2>
                    <p class="text-slate-300 text-base sm:text-lg mt-3 font-light max-w-2xl">
                        Every detail of our inn has been thoughtfully tailored to provide modern convenience, island relaxation, and effortless social connection.
                    </p>
                </div>

                <!-- Slider Controls & CTA -->
                <div class="flex items-center gap-3 self-start md:self-end">
                    <button onclick="slideAmenitiesCarousel(-1)" aria-label="Previous Amenities" class="w-12 h-12 rounded-full border border-white/20 bg-white/10 hover:bg-amber-500 hover:border-amber-500 hover:text-slate-950 text-white flex items-center justify-center transition-all duration-300 cursor-pointer shadow-sm hover:shadow-lg group">
                        <i class="fas fa-chevron-left text-sm group-hover:-translate-x-0.5 transition-transform"></i>
                    </button>
                    <button onclick="slideAmenitiesCarousel(1)" aria-label="Next Amenities" class="w-12 h-12 rounded-full border border-white/20 bg-white/10 hover:bg-amber-500 hover:border-amber-500 hover:text-slate-950 text-white flex items-center justify-center transition-all duration-300 cursor-pointer shadow-sm hover:shadow-lg group">
                        <i class="fas fa-chevron-right text-sm group-hover:translate-x-0.5 transition-transform"></i>
                    </button>
                    <button onclick="router('rooms')" class="hidden sm:inline-flex items-center gap-2 text-xs font-bold text-slate-950 bg-amber-400 hover:bg-amber-300 px-5 py-3 rounded-full transition-all shadow-md ml-2 cursor-pointer">
                        <span>Book Stay</span>
                        <i class="fas fa-arrow-right text-[10px]"></i>
                    </button>
                </div>
            </div>

            <!-- Amenities Scrollable / Carousel Track -->
            <div id="amenities-slider-container" class="relative overflow-x-auto pb-4 scroll-smooth no-scrollbar select-none" style="scrollbar-width: none; -ms-overflow-style: none;">
                <div id="amenities-slider-track" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 min-w-full">
                    <?php 
                    foreach ($activeAmenitiesList as $amenity): 
                        $category = htmlspecialchars($amenity['category'] ?? 'General');
                        $iconClass = htmlspecialchars($amenity['icon'] ?? 'fas fa-star');
                        $title = htmlspecialchars($amenity['title'] ?? '');
                        $desc = htmlspecialchars($amenity['description'] ?? '');
                        $imgUrl = !empty($amenity['image_url']) ? htmlspecialchars($amenity['image_url']) : '';
                        $gradClass = $amenityGradients[$category] ?? 'from-amber-400 to-yellow-600 text-slate-950';
                    ?>
                    <div class="amenity-card-item bg-white/5 backdrop-blur-md rounded-3xl border border-white/10 hover:border-amber-400/50 transition-all duration-300 group hover:-translate-y-1.5 flex flex-col justify-between shadow-xl overflow-hidden">
                        <div>
                            <!-- Card Image Banner -->
                            <?php if ($imgUrl): ?>
                            <div class="h-48 sm:h-52 w-full overflow-hidden relative bg-slate-900">
                                <img src="<?php echo $imgUrl; ?>" alt="<?php echo $title; ?>" class="w-full h-full object-cover group-hover:scale-108 transition-transform duration-700 ease-out">
                                <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/20 to-transparent"></div>
                                <span class="absolute top-4 left-4 text-[11px] font-bold uppercase tracking-wider text-amber-300 bg-slate-950/70 backdrop-blur-md px-3 py-1 rounded-full border border-white/15">
                                    <?php echo $category; ?>
                                </span>
                            </div>
                            <?php endif; ?>

                            <div class="p-6 sm:p-8">
                                <div class="flex items-center justify-between mb-5">
                                    <div class="w-13 h-13 rounded-2xl bg-gradient-to-br <?php echo $gradClass; ?> flex items-center justify-center text-xl shadow-lg group-hover:scale-110 transition-transform duration-300">
                                        <i class="<?php echo $iconClass; ?>"></i>
                                    </div>
                                    <?php if (!$imgUrl): ?>
                                    <span class="text-[11px] font-bold uppercase tracking-wider text-amber-300 bg-white/10 px-3 py-1 rounded-full border border-white/15">
                                        <?php echo $category; ?>
                                    </span>
                                    <?php endif; ?>
                                </div>

                                <h3 class="text-xl font-bold text-white mb-2.5 group-hover:text-amber-300 transition-colors">
                                    <?php echo $title; ?>
                                </h3>
                                <p class="text-slate-400 text-sm leading-relaxed">
                                    <?php echo $desc; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>
    </section>

    <script>
    // Horizontal carousel scroll for amenities section
    function slideAmenitiesCarousel(direction) {
        const container = document.getElementById('amenities-slider-container');
        if (!container) return;
        const scrollAmount = (container.clientWidth * 0.8) * direction;
        container.scrollBy({ left: scrollAmount, behavior: 'smooth' });
    }

    // Dynamic Live Hydration for Amenities (Auto-refreshes if admin updates image/text)
    async function syncLandingAmenities() {
        try {
            const res = await fetch('actions/amenities_action.php?action=get_active&_t=' + Date.now());
            if (!res.ok) return;
            const data = await res.json();
            if (!data.success || !Array.isArray(data.amenities) || data.amenities.length === 0) return;

            const track = document.getElementById('amenities-slider-track');
            if (!track) return;

            const gradients = {
                'Location': 'from-amber-400 to-yellow-600 text-slate-950',
                'Connectivity': 'from-sky-400 to-blue-600 text-white',
                'Social': 'from-purple-400 to-indigo-600 text-white',
                'Adventures': 'from-teal-400 to-emerald-600 text-white',
                'Security': 'from-rose-400 to-red-600 text-white',
                'Dining': 'from-amber-500 to-orange-600 text-white',
                'Comfort': 'from-indigo-400 to-violet-600 text-white',
                'Leisure': 'from-cyan-400 to-blue-600 text-white'
            };

            const esc = (s) => {
                const div = document.createElement('div');
                div.textContent = s || '';
                return div.innerHTML;
            };

            track.innerHTML = data.amenities.map(amenity => {
                const cat = esc(amenity.category || 'General');
                const iconClass = esc(amenity.icon || 'fas fa-star');
                const title = esc(amenity.title || '');
                const desc = esc(amenity.description || '');
                const imgUrl = amenity.image_url ? esc(amenity.image_url) : '';
                const gradClass = gradients[amenity.category] || 'from-amber-400 to-yellow-600 text-slate-950';

                return `
                <div class="amenity-card-item bg-white/5 backdrop-blur-md rounded-3xl border border-white/10 hover:border-amber-400/50 transition-all duration-300 group hover:-translate-y-1.5 flex flex-col justify-between shadow-xl overflow-hidden">
                    <div>
                        ${imgUrl ? `
                        <div class="h-48 sm:h-52 w-full overflow-hidden relative bg-slate-900">
                            <img src="${imgUrl}" alt="${title}" class="w-full h-full object-cover group-hover:scale-108 transition-transform duration-700 ease-out">
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/20 to-transparent"></div>
                            <span class="absolute top-4 left-4 text-[11px] font-bold uppercase tracking-wider text-amber-300 bg-slate-950/70 backdrop-blur-md px-3 py-1 rounded-full border border-white/15">
                                ${cat}
                            </span>
                        </div>` : ''}

                        <div class="p-6 sm:p-8">
                            <div class="flex items-center justify-between mb-5">
                                <div class="w-13 h-13 rounded-2xl bg-gradient-to-br ${gradClass} flex items-center justify-center text-xl shadow-lg group-hover:scale-110 transition-transform duration-300">
                                    <i class="${iconClass}"></i>
                                </div>
                                ${!imgUrl ? `
                                <span class="text-[11px] font-bold uppercase tracking-wider text-amber-300 bg-white/10 px-3 py-1 rounded-full border border-white/15">
                                    ${cat}
                                </span>` : ''}
                            </div>

                            <h3 class="text-xl font-bold text-white mb-2.5 group-hover:text-amber-300 transition-colors">
                                ${title}
                            </h3>
                            <p class="text-slate-400 text-sm leading-relaxed">
                                ${desc}
                            </p>
                        </div>
                    </div>
                </div>`;
            }).join('');
        } catch (e) {
            console.debug('Amenities live sync info:', e);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', syncLandingAmenities);
    } else {
        syncLandingAmenities();
    }
    </script>

    <!-- ================= DISCOVER BOHOL & PANGLAO (TOURIST ATTRACTIONS) ================= -->
    <section id="discovery-bohol" class="py-24 bg-gradient-to-b from-slate-50 via-white to-slate-50 px-4 sm:px-6 lg:px-8 relative overflow-hidden">
        <div class="max-w-7xl mx-auto">
            
            <!-- Section Header -->
            <div class="text-center max-w-3xl mx-auto mb-16">
                <span class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-amber-700 bg-amber-100/80 border border-amber-200/60 px-4 py-1.5 rounded-full shadow-xs">
                    <i class="fas fa-compass text-amber-500"></i> Discover Bohol &bull; Near W Island Hub
                </span>
                <h2 class="text-3xl sm:text-4xl md:text-5xl font-serif font-bold text-slate-900 mt-4 leading-tight">
                    Top Tourist Attractions Just Minutes Away
                </h2>
                <p class="text-slate-600 text-base sm:text-lg mt-3 leading-relaxed font-light">
                    Situated centrally in Panglao Island, <strong class="text-slate-900 font-semibold">W Island Hub Backpackers Inn</strong> is your ultimate basecamp. Experience world-famous white beaches, swimming with giant sea turtles, mystical caves, and legendary countryside wonders with ease.
                </p>
                <div class="flex flex-wrap items-center justify-center gap-2.5 mt-5 text-xs text-slate-600 font-medium">
                    <span class="inline-flex items-center gap-1.5 bg-white border border-slate-200/80 px-3.5 py-1.5 rounded-full shadow-xs"><i class="fas fa-motorcycle text-amber-500"></i> Motorbike Rentals On-Site</span>
                    <span class="inline-flex items-center gap-1.5 bg-white border border-slate-200/80 px-3.5 py-1.5 rounded-full shadow-xs"><i class="fas fa-ticket-alt text-teal-500"></i> Concierge Tour Packages</span>
                    <span class="inline-flex items-center gap-1.5 bg-white border border-slate-200/80 px-3.5 py-1.5 rounded-full shadow-xs"><i class="fas fa-bolt text-brand-blue"></i> Instant Booking (No Login Needed)</span>
                </div>
            </div>

            <!-- Attractions Grid (8 Key Nearby Attractions) -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-16">
                
                <!-- 1. Alona Beach & Nightlife -->
                <div class="bg-white rounded-3xl overflow-hidden border border-slate-100 shadow-sm hover:shadow-2xl transition-all duration-300 flex flex-col group hover:-translate-y-1">
                    <div class="h-48 relative overflow-hidden">
                        <img src="https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=800&q=80" alt="Alona Beach Panglao" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>
                        <span class="absolute top-3 left-3 bg-white/95 backdrop-blur px-3 py-1 rounded-full text-[11px] font-bold text-amber-700 uppercase shadow-sm">
                            Beach &amp; Nightlife
                        </span>
                        <span class="absolute bottom-3 left-3 text-white text-xs font-bold flex items-center gap-1.5 drop-shadow-md">
                            <i class="fas fa-motorcycle text-amber-400"></i> 5 Mins from Inn (1.8 km)
                        </span>
                    </div>
                    <div class="p-5 flex-1 flex flex-col justify-between space-y-4">
                        <div>
                            <h3 class="text-lg font-bold text-slate-900 group-hover:text-brand-blue transition-colors">Alona Beach &amp; Dining Strip</h3>
                            <p class="text-slate-500 text-xs sm:text-sm mt-2 leading-relaxed">
                                Panglao's premier white sand stretch. Lined with beachfront seafood bistros, dive centers, live acoustic bars, and island hopping boats.
                            </p>
                        </div>
                        <div class="pt-3 border-t border-slate-100 space-y-2.5">
                            <div class="text-[11px] text-amber-800 bg-amber-50 p-2.5 rounded-xl border border-amber-200/50 flex items-start gap-2">
                                <i class="fas fa-check-circle text-amber-500 mt-0.5 flex-shrink-0"></i>
                                <span><strong>Inn Perk:</strong> Rent an automatic scooter at front desk or catch a ₱50 quick local tricycle ride.</span>
                            </div>
                            <button onclick="router('rooms')" class="w-full py-2.5 bg-slate-100 hover:bg-brand-dark hover:text-white text-slate-800 font-bold text-xs rounded-xl transition flex items-center justify-center gap-1.5 cursor-pointer shadow-xs">
                                <span>Book Stay Near Alona</span>
                                <i class="fas fa-arrow-right text-[10px]"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- 2. Dumaluan Beach / White Beach -->
                <div class="bg-white rounded-3xl overflow-hidden border border-slate-100 shadow-sm hover:shadow-2xl transition-all duration-300 flex flex-col group hover:-translate-y-1">
                    <div class="h-48 relative overflow-hidden">
                        <img src="https://images.unsplash.com/photo-1540555700478-4be289fbecef?auto=format&fit=crop&w=800&q=80" alt="Dumaluan Beach Panglao" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>
                        <span class="absolute top-3 left-3 bg-white/95 backdrop-blur px-3 py-1 rounded-full text-[11px] font-bold text-teal-700 uppercase shadow-sm">
                            Tranquil White Sand
                        </span>
                        <span class="absolute bottom-3 left-3 text-white text-xs font-bold flex items-center gap-1.5 drop-shadow-md">
                            <i class="fas fa-map-marker-alt text-teal-400"></i> 7 Mins from Inn (3.5 km)
                        </span>
                    </div>
                    <div class="p-5 flex-1 flex flex-col justify-between space-y-4">
                        <div>
                            <h3 class="text-lg font-bold text-slate-900 group-hover:text-brand-blue transition-colors">Dumaluan White Beach</h3>
                            <p class="text-slate-500 text-xs sm:text-sm mt-2 leading-relaxed">
                                A quieter, wider, and more serene white-sand coastline. Shaded by swaying coconut palms with shallow turquoise lagoons ideal for swimming.
                            </p>
                        </div>
                        <div class="pt-3 border-t border-slate-100 space-y-2.5">
                            <div class="text-[11px] text-teal-800 bg-teal-50 p-2.5 rounded-xl border border-teal-200/50 flex items-start gap-2">
                                <i class="fas fa-check-circle text-teal-500 mt-0.5 flex-shrink-0"></i>
                                <span><strong>Inn Perk:</strong> Our backpackers' top choice for peaceful sunrise swims and sunset picnics.</span>
                            </div>
                            <button onclick="router('rooms')" class="w-full py-2.5 bg-slate-100 hover:bg-brand-dark hover:text-white text-slate-800 font-bold text-xs rounded-xl transition flex items-center justify-center gap-1.5 cursor-pointer shadow-xs">
                                <span>Stay Minutes Away</span>
                                <i class="fas fa-arrow-right text-[10px]"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- 3. Napaling Reef & Sardine Run -->
                <div class="bg-white rounded-3xl overflow-hidden border border-slate-100 shadow-sm hover:shadow-2xl transition-all duration-300 flex flex-col group hover:-translate-y-1">
                    <div class="h-48 relative overflow-hidden">
                        <img src="https://images.unsplash.com/photo-1544551763-46a013bb70d5?auto=format&fit=crop&w=800&q=80" alt="Napaling Reef Sardine Run" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>
                        <span class="absolute top-3 left-3 bg-white/95 backdrop-blur px-3 py-1 rounded-full text-[11px] font-bold text-blue-700 uppercase shadow-sm">
                            Sardine Run &amp; Reef
                        </span>
                        <span class="absolute bottom-3 left-3 text-white text-xs font-bold flex items-center gap-1.5 drop-shadow-md">
                            <i class="fas fa-swimmer text-blue-400"></i> 12 Mins from Inn (7 km)
                        </span>
                    </div>
                    <div class="p-5 flex-1 flex flex-col justify-between space-y-4">
                        <div>
                            <h3 class="text-lg font-bold text-slate-900 group-hover:text-brand-blue transition-colors">Napaling Reef &amp; Sardines</h3>
                            <p class="text-slate-500 text-xs sm:text-sm mt-2 leading-relaxed">
                                World-class shore freediving and snorkeling. Swim right into swirling silver bait balls of millions of sardines and dramatic coral drop-off walls.
                            </p>
                        </div>
                        <div class="pt-3 border-t border-slate-100 space-y-2.5">
                            <div class="text-[11px] text-blue-800 bg-blue-50 p-2.5 rounded-xl border border-blue-200/50 flex items-start gap-2">
                                <i class="fas fa-check-circle text-blue-500 mt-0.5 flex-shrink-0"></i>
                                <span><strong>Inn Perk:</strong> Free snorkel mask rentals and tides schedule advice provided at the inn.</span>
                            </div>
                            <button onclick="router('rooms')" class="w-full py-2.5 bg-slate-100 hover:bg-brand-dark hover:text-white text-slate-800 font-bold text-xs rounded-xl transition flex items-center justify-center gap-1.5 cursor-pointer shadow-xs">
                                <span>Book Snorkel Adventure</span>
                                <i class="fas fa-arrow-right text-[10px]"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- 4. Hinagdanan Cave -->
                <div class="bg-white rounded-3xl overflow-hidden border border-slate-100 shadow-sm hover:shadow-2xl transition-all duration-300 flex flex-col group hover:-translate-y-1">
                    <div class="h-48 relative overflow-hidden">
                        <img src="https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=800&q=80" alt="Hinagdanan Cave Panglao" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>
                        <span class="absolute top-3 left-3 bg-white/95 backdrop-blur px-3 py-1 rounded-full text-[11px] font-bold text-purple-700 uppercase shadow-sm">
                            Limestone Cenote
                        </span>
                        <span class="absolute bottom-3 left-3 text-white text-xs font-bold flex items-center gap-1.5 drop-shadow-md">
                            <i class="fas fa-compass text-purple-400"></i> 15 Mins from Inn (9 km)
                        </span>
                    </div>
                    <div class="p-5 flex-1 flex flex-col justify-between space-y-4">
                        <div>
                            <h3 class="text-lg font-bold text-slate-900 group-hover:text-brand-blue transition-colors">Hinagdanan Cave &amp; Pool</h3>
                            <p class="text-slate-500 text-xs sm:text-sm mt-2 leading-relaxed">
                                A mystical subterranean limestone cave naturally illuminated by sunbeams. Dip into the cool, deep freshwater pool surrounded by ancient stalactites.
                            </p>
                        </div>
                        <div class="pt-3 border-t border-slate-100 space-y-2.5">
                            <div class="text-[11px] text-purple-800 bg-purple-50 p-2.5 rounded-xl border border-purple-200/50 flex items-start gap-2">
                                <i class="fas fa-check-circle text-purple-500 mt-0.5 flex-shrink-0"></i>
                                <span><strong>Inn Perk:</strong> A must-visit stop along the popular Panglao scooter coastal road circuit.</span>
                            </div>
                            <button onclick="router('rooms')" class="w-full py-2.5 bg-slate-100 hover:bg-brand-dark hover:text-white text-slate-800 font-bold text-xs rounded-xl transition flex items-center justify-center gap-1.5 cursor-pointer shadow-xs">
                                <span>Reserve Your Stay</span>
                                <i class="fas fa-arrow-right text-[10px]"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- 5. Balicasag Island Marine Sanctuary -->
                <div class="bg-white rounded-3xl overflow-hidden border border-slate-100 shadow-sm hover:shadow-2xl transition-all duration-300 flex flex-col group hover:-translate-y-1">
                    <div class="h-48 relative overflow-hidden">
                        <img src="https://images.unsplash.com/photo-1518467166778-b88f373ffec7?auto=format&fit=crop&w=800&q=80" alt="Balicasag Sea Turtles" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>
                        <span class="absolute top-3 left-3 bg-white/95 backdrop-blur px-3 py-1 rounded-full text-[11px] font-bold text-emerald-700 uppercase shadow-sm">
                            Sea Turtles Reserve
                        </span>
                        <span class="absolute bottom-3 left-3 text-white text-xs font-bold flex items-center gap-1.5 drop-shadow-md">
                            <i class="fas fa-ship text-emerald-400"></i> Daily Boat Departures
                        </span>
                    </div>
                    <div class="p-5 flex-1 flex flex-col justify-between space-y-4">
                        <div>
                            <h3 class="text-lg font-bold text-slate-900 group-hover:text-brand-blue transition-colors">Balicasag Sea Turtles</h3>
                            <p class="text-slate-500 text-xs sm:text-sm mt-2 leading-relaxed">
                                Swim arm-in-arm alongside giant green sea turtles in protected marine waters. Includes sunrise wild dolphin watching and pristine coral reefs.
                            </p>
                        </div>
                        <div class="pt-3 border-t border-slate-100 space-y-2.5">
                            <div class="text-[11px] text-emerald-800 bg-emerald-50 p-2.5 rounded-xl border border-emerald-200/50 flex items-start gap-2">
                                <i class="fas fa-check-circle text-emerald-500 mt-0.5 flex-shrink-0"></i>
                                <span><strong>Inn Perk:</strong> Direct concierge booking with trusted local boat captains &amp; group joiner discounts.</span>
                            </div>
                            <button onclick="router('rooms')" class="w-full py-2.5 bg-slate-100 hover:bg-brand-dark hover:text-white text-slate-800 font-bold text-xs rounded-xl transition flex items-center justify-center gap-1.5 cursor-pointer shadow-xs">
                                <span>Book Island Basecamp</span>
                                <i class="fas fa-arrow-right text-[10px]"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- 6. Virgin Island Sandbar -->
                <div class="bg-white rounded-3xl overflow-hidden border border-slate-100 shadow-sm hover:shadow-2xl transition-all duration-300 flex flex-col group hover:-translate-y-1">
                    <div class="h-48 relative overflow-hidden">
                        <img src="https://images.unsplash.com/photo-1506953823976-52e1fdc0149a?auto=format&fit=crop&w=800&q=80" alt="Virgin Island Sandbar Bohol" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>
                        <span class="absolute top-3 left-3 bg-white/95 backdrop-blur px-3 py-1 rounded-full text-[11px] font-bold text-amber-700 uppercase shadow-sm">
                            Tidal Sandbar
                        </span>
                        <span class="absolute bottom-3 left-3 text-white text-xs font-bold flex items-center gap-1.5 drop-shadow-md">
                            <i class="fas fa-water text-amber-400"></i> 20 Mins Island Hop
                        </span>
                    </div>
                    <div class="p-5 flex-1 flex flex-col justify-between space-y-4">
                        <div>
                            <h3 class="text-lg font-bold text-slate-900 group-hover:text-brand-blue transition-colors">Virgin Island (Pungtud)</h3>
                            <p class="text-slate-500 text-xs sm:text-sm mt-2 leading-relaxed">
                                An iconic crescent of pure powdery white sand emerging in the middle of shallow crystal water at low tide. Taste fresh grilled seafood and coconuts.
                            </p>
                        </div>
                        <div class="pt-3 border-t border-slate-100 space-y-2.5">
                            <div class="text-[11px] text-amber-800 bg-amber-50 p-2.5 rounded-xl border border-amber-200/50 flex items-start gap-2">
                                <i class="fas fa-check-circle text-amber-500 mt-0.5 flex-shrink-0"></i>
                                <span><strong>Inn Perk:</strong> Regularly paired with our morning Balicasag island hopping boat itineraries.</span>
                            </div>
                            <button onclick="router('rooms')" class="w-full py-2.5 bg-slate-100 hover:bg-brand-dark hover:text-white text-slate-800 font-bold text-xs rounded-xl transition flex items-center justify-center gap-1.5 cursor-pointer shadow-xs">
                                <span>Explore Island Trips</span>
                                <i class="fas fa-arrow-right text-[10px]"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- 7. Chocolate Hills & ATV Safari -->
                <div class="bg-white rounded-3xl overflow-hidden border border-slate-100 shadow-sm hover:shadow-2xl transition-all duration-300 flex flex-col group hover:-translate-y-1">
                    <div class="h-48 relative overflow-hidden">
                        <img src="https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?auto=format&fit=crop&w=800&q=80" alt="Chocolate Hills Bohol" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>
                        <span class="absolute top-3 left-3 bg-white/95 backdrop-blur px-3 py-1 rounded-full text-[11px] font-bold text-amber-800 uppercase shadow-sm">
                            UNESCO Geopark
                        </span>
                        <span class="absolute bottom-3 left-3 text-white text-xs font-bold flex items-center gap-1.5 drop-shadow-md">
                            <i class="fas fa-mountain text-amber-400"></i> Countryside Day Tour
                        </span>
                    </div>
                    <div class="p-5 flex-1 flex flex-col justify-between space-y-4">
                        <div>
                            <h3 class="text-lg font-bold text-slate-900 group-hover:text-brand-blue transition-colors">Chocolate Hills &amp; ATVs</h3>
                            <p class="text-slate-500 text-xs sm:text-sm mt-2 leading-relaxed">
                                Over 1,200 conical limestone hills dotting the horizon. Soak in the 360-degree viewpoint or ride thrilling all-terrain quad bikes across rural trails.
                            </p>
                        </div>
                        <div class="pt-3 border-t border-slate-100 space-y-2.5">
                            <div class="text-[11px] text-amber-800 bg-amber-50 p-2.5 rounded-xl border border-amber-200/50 flex items-start gap-2">
                                <i class="fas fa-check-circle text-amber-500 mt-0.5 flex-shrink-0"></i>
                                <span><strong>Inn Perk:</strong> Air-conditioned tour vans pick you up right outside our inn gate after morning breakfast.</span>
                            </div>
                            <button onclick="router('rooms')" class="w-full py-2.5 bg-slate-100 hover:bg-brand-dark hover:text-white text-slate-800 font-bold text-xs rounded-xl transition flex items-center justify-center gap-1.5 cursor-pointer shadow-xs">
                                <span>Book Countryside Base</span>
                                <i class="fas fa-arrow-right text-[10px]"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- 8. Philippine Tarsier Sanctuary & Loboc River -->
                <div class="bg-white rounded-3xl overflow-hidden border border-slate-100 shadow-sm hover:shadow-2xl transition-all duration-300 flex flex-col group hover:-translate-y-1">
                    <div class="h-48 relative overflow-hidden">
                        <img src="https://images.unsplash.com/photo-1476514525535-07fb3b4ae5f1?auto=format&fit=crop&w=800&q=80" alt="Loboc River Cruise Bohol" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>
                        <span class="absolute top-3 left-3 bg-white/95 backdrop-blur px-3 py-1 rounded-full text-[11px] font-bold text-emerald-700 uppercase shadow-sm">
                            Wildlife &amp; Culture
                        </span>
                        <span class="absolute bottom-3 left-3 text-white text-xs font-bold flex items-center gap-1.5 drop-shadow-md">
                            <i class="fas fa-leaf text-emerald-400"></i> Day Tour Highlight
                        </span>
                    </div>
                    <div class="p-5 flex-1 flex flex-col justify-between space-y-4">
                        <div>
                            <h3 class="text-lg font-bold text-slate-900 group-hover:text-brand-blue transition-colors">Tarsiers &amp; Loboc River</h3>
                            <p class="text-slate-500 text-xs sm:text-sm mt-2 leading-relaxed">
                                See the world's smallest nocturnal primates in an ethical natural sanctuary, followed by a musical buffet cruise on the jade-green Loboc River.
                            </p>
                        </div>
                        <div class="pt-3 border-t border-slate-100 space-y-2.5">
                            <div class="text-[11px] text-emerald-800 bg-emerald-50 p-2.5 rounded-xl border border-emerald-200/50 flex items-start gap-2">
                                <i class="fas fa-check-circle text-emerald-500 mt-0.5 flex-shrink-0"></i>
                                <span><strong>Inn Perk:</strong> Seamlessly bundled in our Bohol Island tour packages with guaranteed seats.</span>
                            </div>
                            <button onclick="router('rooms')" class="w-full py-2.5 bg-slate-100 hover:bg-brand-dark hover:text-white text-slate-800 font-bold text-xs rounded-xl transition flex items-center justify-center gap-1.5 cursor-pointer shadow-xs">
                                <span>Reserve Your Stay</span>
                                <i class="fas fa-arrow-right text-[10px]"></i>
                            </button>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Interactive Basecamp Conversion Banner -->
            <div class="bg-gradient-to-r from-slate-950 via-slate-900 to-brand-dark rounded-3xl p-8 sm:p-12 text-white shadow-2xl border border-white/10 relative overflow-hidden">
                <div class="absolute -right-16 -bottom-16 w-64 h-64 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>
                <div class="relative z-10 flex flex-col lg:flex-row items-center justify-between gap-8">
                    <div class="max-w-2xl space-y-4 text-center lg:text-left">
                        <span class="text-xs uppercase font-bold tracking-widest text-amber-400 bg-white/10 px-3 py-1 rounded-full inline-block">
                            Start Your Bohol Getaway
                        </span>
                        <h3 class="text-2xl sm:text-3xl md:text-4xl font-serif font-bold text-white leading-tight">
                            Book Your Room in Under a Minute &bull; No Login Required!
                        </h3>
                        <p class="text-slate-300 text-sm sm:text-base font-light leading-relaxed">
                            Stay right where the adventure begins. Enjoy air-conditioned dorms and boutique private rooms, Starlink high-speed internet, on-site motorbike rentals, and exclusive guest tour packages.
                        </p>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-2 text-xs text-slate-300">
                            <div class="flex items-center gap-1.5"><i class="fas fa-check text-amber-400"></i> Instant Booking</div>
                            <div class="flex items-center gap-1.5"><i class="fas fa-check text-amber-400"></i> No Login Needed</div>
                            <div class="flex items-center gap-1.5"><i class="fas fa-check text-amber-400"></i> PayMongo / GCash</div>
                            <div class="flex items-center gap-1.5"><i class="fas fa-check text-amber-400"></i> Free Tour Advice</div>
                        </div>
                    </div>
                    <div class="flex flex-col sm:flex-row lg:flex-col items-center gap-3 w-full lg:w-auto flex-shrink-0">
                        <button onclick="router('rooms')" class="w-full sm:w-auto bg-gradient-to-r from-amber-500 to-yellow-500 hover:from-amber-600 hover:to-yellow-600 text-slate-950 font-bold px-8 py-4 rounded-2xl shadow-xl hover:shadow-2xl transition-all transform hover:scale-105 flex items-center justify-center gap-3 text-sm sm:text-base cursor-pointer">
                            <i class="fas fa-bed"></i>
                            <span>Check Available Rooms</span>
                            <i class="fas fa-arrow-right text-xs"></i>
                        </button>
                        <a href="tel:+639123456789" class="text-xs text-slate-400 hover:text-amber-300 transition-colors flex items-center gap-1.5">
                            <i class="fas fa-phone-alt text-[10px]"></i> Front Desk Inquiries: +63 912 345 6789
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- ================= GUEST REVIEWS & STORIES SECTION ================= -->
    <!-- Integrated with existing dynamic reviews loader in user/reviews.php & homepage_reviews.js -->
    <?php include 'user/reviews.php'; ?>

    <!-- ================= LOCATION & GETTING HERE ================= -->
    <section class="py-20 bg-slate-900 text-white px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                
                <div>
                    <span class="text-xs font-bold uppercase tracking-widest text-amber-400 bg-white/10 px-3.5 py-1.5 rounded-full">
                        Island Location
                    </span>
                    <h2 class="text-3xl sm:text-4xl font-serif font-bold text-white mt-4 mb-6">
                        Easy to Reach, Hard to Leave
                    </h2>
                    <p class="text-slate-300 text-base leading-relaxed mb-8 font-light">
                        Located conveniently in Panglao Island, Bohol, W Island Hub Backpackers Inn is positioned close to the vibrant beach dining scene while offering a peaceful tropical oasis.
                    </p>

                    <div class="space-y-4">
                        <div class="flex items-start gap-4 p-4 rounded-2xl bg-white/5 border border-white/10">
                            <div class="w-10 h-10 rounded-xl bg-amber-400 text-slate-950 flex items-center justify-center font-bold text-base flex-shrink-0">
                                <i class="fas fa-plane"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-white text-sm">Panglao-Bohol International Airport (TAG)</h4>
                                <p class="text-xs text-slate-400 mt-0.5">Approx. 10 minutes drive &bull; Airport pickup available upon request</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-4 p-4 rounded-2xl bg-white/5 border border-white/10">
                            <div class="w-10 h-10 rounded-xl bg-sky-400 text-slate-950 flex items-center justify-center font-bold text-base flex-shrink-0">
                                <i class="fas fa-ship"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-white text-sm">Tagbilaran Seaport (Ferry Terminal)</h4>
                                <p class="text-xs text-slate-400 mt-0.5">Approx. 25-30 minutes drive &bull; Easy tricycle and van connection</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-4 p-4 rounded-2xl bg-white/5 border border-white/10">
                            <div class="w-10 h-10 rounded-xl bg-teal-400 text-slate-950 flex items-center justify-center font-bold text-base flex-shrink-0">
                                <i class="fas fa-umbrella-beach"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-white text-sm">Alona Beach & Nightlife Hub</h4>
                                <p class="text-xs text-slate-400 mt-0.5">Approx. 5 minutes drive &bull; Quick scooter trip or pleasant stroll</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8">
                        <button onclick="router('about')" class="inline-flex items-center gap-2 text-sm font-bold text-amber-400 hover:text-amber-300 transition-colors">
                            <span>View Full Map & Directions</span>
                            <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </div>

                <!-- Google Maps Preview Box -->
                <div class="h-96 rounded-3xl overflow-hidden shadow-2xl border border-white/10 bg-slate-800">
                    <iframe 
                        width="100%" 
                        height="100%" 
                        frameborder="0" 
                        style="border:0" 
                        src="https://www.google.com/maps/embed?pb=!3m2!1sen!2sph!4v1773839985257!5m2!1sen!2sph!6m8!1m7!1szHZuFCclv2jgA4bBC2k8Lg!2m2!1d9.602317850938597!2d123.8082172682524!3f116.79589647239351!4f-12.777956511199221!5f0.7820865974627469" 
                        loading="lazy" 
                        referrerpolicy="no-referrer-when-downgrade" 
                        allowfullscreen>
                    </iframe>
                </div>

            </div>
        </div>
    </section>

    <!-- ================= FREQUENTLY ASKED QUESTIONS ================= -->
    <section class="py-20 bg-slate-50 px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl mx-auto">
            
            <div class="text-center mb-16">
                <span class="text-xs font-bold uppercase tracking-widest text-amber-600 bg-amber-100/60 px-3.5 py-1.5 rounded-full">
                    Traveler Guidance
                </span>
                <h2 class="text-3xl sm:text-4xl font-serif font-bold text-slate-900 mt-3">
                    Frequently Asked Questions
                </h2>
                <p class="text-slate-500 text-base mt-2">
                    Everything you need to know before checking into your island home.
                </p>
            </div>

            <div class="space-y-4">
                
                <!-- FAQ Item 1 -->
                <details class="group bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm transition-all duration-300">
                    <summary class="cursor-pointer font-bold text-slate-900 flex items-center justify-between gap-4 text-base sm:text-lg">
                        <span>What are the check-in and check-out times?</span>
                        <i class="fas fa-chevron-down text-sm text-slate-400 group-open:rotate-180 transition-transform duration-300"></i>
                    </summary>
                    <div class="mt-4 pt-4 border-t border-slate-100 text-slate-600 text-sm leading-relaxed">
                        Check-in starts from <strong>2:00 PM</strong> onwards, and check-out is by <strong>11:00 AM</strong>. If you arrive early or have a late departure, you are welcome to leave your luggage in our secure storage room and use the communal lounge and beach facilities!
                    </div>
                </details>

                <!-- FAQ Item 2 -->
                <details class="group bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm transition-all duration-300">
                    <summary class="cursor-pointer font-bold text-slate-900 flex items-center justify-between gap-4 text-base sm:text-lg">
                        <span>What payment methods do you accept for booking?</span>
                        <i class="fas fa-chevron-down text-sm text-slate-400 group-open:rotate-180 transition-transform duration-300"></i>
                    </summary>
                    <div class="mt-4 pt-4 border-t border-slate-100 text-slate-600 text-sm leading-relaxed">
                        We offer 2 flexible payment options: <strong>Option A (Full 100% GCash Payment)</strong> for guaranteed reservation with zero front desk balance, and <strong>Option B (Strict Pay-at-Hotel)</strong> for same-day bookings with check-in within 24 hours.
                    </div>
                </details>

                <!-- FAQ Item 3 -->
                <details class="group bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm transition-all duration-300">
                    <summary class="cursor-pointer font-bold text-slate-900 flex items-center justify-between gap-4 text-base sm:text-lg">
                        <span>Is the WiFi fast enough for remote work and digital nomads?</span>
                        <i class="fas fa-chevron-down text-sm text-slate-400 group-open:rotate-180 transition-transform duration-300"></i>
                    </summary>
                    <div class="mt-4 pt-4 border-t border-slate-100 text-slate-600 text-sm leading-relaxed">
                        Yes! We have high-speed <strong>Starlink Satellite WiFi</strong> backed by local high-bandwidth connections covering all dorms, private suites, and social work spaces with power outlets nearby.
                    </div>
                </details>

                <!-- FAQ Item 4 -->
                <details class="group bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm transition-all duration-300">
                    <summary class="cursor-pointer font-bold text-slate-900 flex items-center justify-between gap-4 text-base sm:text-lg">
                        <span>Can I rent motorbikes or book island tours directly?</span>
                        <i class="fas fa-chevron-down text-sm text-slate-400 group-open:rotate-180 transition-transform duration-300"></i>
                    </summary>
                    <div class="mt-4 pt-4 border-t border-slate-100 text-slate-600 text-sm leading-relaxed">
                        Absolutely. We maintain a fleet of well-serviced automatic scooters for rent right at the reception, and our front desk can instantly arrange Balicasag island hopping, whale shark encounters, and Bohol countryside tours at direct local rates.
                    </div>
                </details>

            </div>
        </div>
    </section>

    <!-- ================= HIGH-CONVERSION CTA BANNER ================= -->
    <section class="py-20 bg-gradient-to-r from-slate-950 via-slate-900 to-amber-950 text-white px-4 sm:px-6 lg:px-8 text-center relative overflow-hidden">
        <div class="max-w-4xl mx-auto relative z-10">
            <span class="text-xs font-bold uppercase tracking-widest text-amber-400 bg-white/10 px-4 py-1.5 rounded-full border border-white/20">
                Direct Booking Guarantee
            </span>
            <h2 class="text-3xl sm:text-5xl font-serif font-bold text-white mt-5 mb-4 leading-tight">
                Your Tropical Island Getaway Begins Here
            </h2>
            <p class="text-slate-300 text-base sm:text-lg max-w-2xl mx-auto mb-10 font-light">
                Book directly on our official website for guaranteed best rates, priority room selection, instant GCash confirmation, and flexible policies.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                <button onclick="router('rooms')" class="w-full sm:w-auto bg-gradient-to-r from-amber-500 to-yellow-500 hover:from-amber-600 hover:to-yellow-600 text-slate-950 font-bold py-4 px-10 rounded-full transition-all duration-300 transform hover:scale-105 shadow-2xl text-base">
                    Reserve Your Room Now
                </button>
                <button onclick="router('contact')" class="w-full sm:w-auto bg-white/10 hover:bg-white/20 text-white border border-white/20 font-semibold py-4 px-8 rounded-full transition-all text-base">
                    Contact Inn Concierge
                </button>
            </div>
        </div>
    </section>

</div>
