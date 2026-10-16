
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
                    <i class="fas fa-map-marker-alt text-teal-400"></i>
                    <span><strong>Dao, Dauis</strong> Bohol Gateway</span>
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
                    <input type="hidden" id="home-children-ages" name="children_ages" value="[]">

                    <!-- Dropdown Popover (Same Box) -->
                    <div id="home-guest-dropdown" class="hidden absolute left-0 right-0 sm:right-auto sm:w-80 top-full mt-2 bg-white rounded-2xl shadow-2xl border border-slate-200 p-4 z-50 space-y-4 max-h-[85vh] overflow-y-auto">
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
                                <div class="text-[11px] text-slate-400 font-medium">Ages 0 to 17</div>
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

                        <!-- Dynamic Child Age Selection (0 - 17 years old) -->
                        <div id="home-child-ages-container" class="hidden space-y-2 pt-2 border-t border-slate-100">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                                    <i class="fas fa-child text-amber-500"></i> Child Ages at Check-In
                                </span>
                                <span class="text-[10px] text-amber-700 font-semibold bg-amber-50 px-2 py-0.5 rounded-full border border-amber-200">Ages 0 – 17</span>
                            </div>
                            <p class="text-[11px] text-slate-400">Please select the age of each child:</p>
                            <div id="home-child-ages-list" class="grid grid-cols-2 gap-2 max-h-48 overflow-y-auto pr-1">
                                <!-- Populated dynamically based on child count -->
                            </div>
                        </div>

                        <!-- Age Guidance Badge -->
                        <div class="p-2.5 rounded-xl bg-amber-50/70 border border-amber-200/60 text-[11px] text-amber-900 flex items-start gap-2">
                            <i class="fas fa-info-circle text-amber-500 mt-0.5 flex-shrink-0"></i>
                            <span>Age 18+ counts as Adult. Ages 0 to 17 count as Children.</span>
                        </div>

                        <!-- Capacity Overlimit Warning in Dropdown -->
                        <div id="home-stepper-capacity-warning" class="hidden p-2.5 rounded-xl bg-rose-50 border border-rose-200 text-[11px] text-rose-800 flex items-start gap-2">
                            <i class="fas fa-exclamation-triangle text-rose-500 mt-0.5 flex-shrink-0"></i>
                            <span id="home-stepper-capacity-warning-text">The hotel does not accommodate this party size as room capacity is limited.</span>
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
                        <i class="fas fa-tags"></i>
                    </div>
                    <h3 class="text-3xl font-bold text-slate-900 mb-1">Best Rates</h3>
                    <p class="text-xs sm:text-sm font-semibold text-slate-500 uppercase tracking-wider">Direct Booking Hook</p>
                    <span class="text-xs text-slate-400 mt-2">Zero third-party markup fees</span>
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
    <div id="gallery-lightbox" class="fixed inset-0 bg-black/90 backdrop-blur-md z-50 hidden items-center justify-center p-4 cursor-pointer" onclick="closeGalleryLightbox()">
        <div class="relative max-w-5xl w-full flex flex-col items-center animate-fadeIn cursor-default" onclick="event.stopPropagation()">
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
            ['title' => 'Central Island Gateway', 'icon' => 'fas fa-map-marked-alt', 'category' => 'Location', 'description' => 'Strategically located in Dauis between Tagbilaran seaport and Panglao white-sand beaches. Enjoy easy access across Bohol without noisy beach crowds.', 'image_url' => 'assets/image/background.jpg'],
            ['title' => 'Nomad-Grade Starlink WiFi', 'icon' => 'fas fa-wifi', 'category' => 'Connectivity', 'description' => 'Lightning-fast, reliable satellite internet across all dorms, private rooms, and chill lounges. Seamless for remote work, video calls, and streaming.', 'image_url' => 'assets/image/background.jpg'],
            ['title' => 'Fresh & Easy Laundry Hub', 'icon' => 'fas fa-soap', 'category' => 'Comfort', 'description' => 'Travel light and stay fresh! Enjoy our fast, hassle-free laundry service designed for adventurers and island explorers. Drop off beachwear and pick up clean clothes ready for your journey.', 'image_url' => 'assets/image/amenity_6aa79d1dbec73.jpg'],
            ['title' => 'Island Pickleball Court', 'icon' => 'fas fa-table-tennis-paddle-ball', 'category' => 'Leisure', 'description' => 'Stay active on your island getaway with friendly games and rallies on our dedicated pickleball court right here at the inn.', 'image_url' => 'assets/image/amenity_6aa7997251058.jpg'],
            ['title' => 'Keycard Security & Lockers', 'icon' => 'fas fa-key', 'category' => 'Security', 'description' => 'Travel with total peace of mind. Every dorm guest receives an oversized security locker, personal bedside power socket, and reading lamp.', 'image_url' => 'assets/image/background.jpg'],
            ['title' => 'Island Grind Coffee & Brunch', 'icon' => 'fas fa-mug-hot', 'category' => 'Dining', 'description' => 'Complimentary fresh-brewed local island coffee every morning. Unlimited filtered cold drinking water stations to keep you energized and eco-friendly.', 'image_url' => 'assets/image/amenity_6aa7984429fa0.jpg']
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
                    <div class="amenity-card-item bg-white/5 backdrop-blur-md rounded-3xl border border-white/10 hover:border-amber-400/50 transition-all duration-300 group hover:-translate-y-1.5 flex flex-col justify-between shadow-xl overflow-hidden cursor-pointer"
                         data-img="<?php echo htmlspecialchars($imgUrl, ENT_QUOTES); ?>"
                         data-title="<?php echo htmlspecialchars($title, ENT_QUOTES); ?>"
                         data-cat="<?php echo htmlspecialchars($category, ENT_QUOTES); ?>"
                         data-desc="<?php echo htmlspecialchars($desc, ENT_QUOTES); ?>"
                         data-icon="<?php echo htmlspecialchars($iconClass, ENT_QUOTES); ?>"
                         data-grad="<?php echo htmlspecialchars($gradClass, ENT_QUOTES); ?>"
                         onclick="openAmenityFromCard(this)"
                         title="Click to view photo & details">
                        <div>
                            <!-- Card Image Banner -->
                            <?php if ($imgUrl): ?>
                            <div class="h-48 sm:h-52 w-full overflow-hidden relative bg-slate-900 group/img">
                                <img src="<?php echo $imgUrl; ?>" alt="<?php echo $title; ?>" class="w-full h-full object-cover group-hover/img:scale-108 transition-transform duration-700 ease-out">
                                <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/20 to-transparent"></div>
                                <span class="absolute top-4 left-4 text-[11px] font-bold uppercase tracking-wider text-amber-300 bg-slate-950/70 backdrop-blur-md px-3 py-1 rounded-full border border-white/15">
                                    <?php echo $category; ?>
                                </span>
                                <!-- Zoom Icon Overlay -->
                                <div class="absolute top-4 right-4 z-10 w-9 h-9 rounded-full bg-slate-950/70 backdrop-blur-md text-white flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-300 shadow-md">
                                    <i class="fas fa-expand text-xs text-amber-400"></i>
                                </div>
                            </div>
                            <?php endif; ?>

                            <div class="p-6 sm:p-8">
                                <div class="flex items-center justify-between mb-5">
                                    <div class="w-13 h-13 rounded-2xl bg-gradient-to-br <?php echo $gradClass; ?> flex items-center justify-center text-xl shadow-lg group-hover:scale-110 transition-transform duration-300">
                                        <i class="<?php echo $iconClass; ?>"></i>
                                    </div>
                                    <span class="text-xs font-semibold text-amber-400 group-hover:text-amber-300 flex items-center gap-1.5 transition-colors bg-white/5 group-hover:bg-white/10 px-3 py-1.5 rounded-full border border-white/10">
                                        <i class="fas fa-image text-xs"></i>
                                        <span>View Photo</span>
                                    </span>
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

    <!-- ================= AMENITY IMAGE & DETAILS MODAL ================= -->
    <div id="amenity-image-modal" class="fixed inset-0 bg-black/85 backdrop-blur-md z-50 hidden items-center justify-center p-3 sm:p-6 transition-all duration-300 opacity-0 cursor-pointer" onclick="closeAmenityImageModal(event)">
        <div class="bg-slate-900 border border-white/15 rounded-3xl shadow-2xl max-w-2xl w-full overflow-hidden my-auto transform transition-all duration-300 scale-95 flex flex-col max-h-[92vh] cursor-default" onclick="event.stopPropagation()">
            
            <!-- Image Header Banner -->
            <div class="relative h-64 sm:h-80 w-full bg-slate-950 overflow-hidden flex-shrink-0 group/modalimg">
                <img id="amenity-modal-img" src="" alt="Amenity Photo" class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-transparent to-black/40"></div>
                
                <!-- Close Button -->
                <button onclick="closeAmenityImageModal()" type="button" class="absolute top-4 right-4 bg-black/60 hover:bg-black/90 text-white rounded-full w-9 h-9 flex items-center justify-center backdrop-blur-md transition-all duration-200 border border-white/20 shadow-lg cursor-pointer z-20" aria-label="Close modal">
                    <i class="fas fa-times text-sm"></i>
                </button>
                
                <!-- Category Badge -->
                <div class="absolute top-4 left-4 z-20">
                    <span id="amenity-modal-category" class="text-xs font-bold uppercase tracking-wider text-amber-300 bg-slate-950/80 backdrop-blur-md px-3.5 py-1.5 rounded-full border border-white/15 shadow-md">
                        Amenity
                    </span>
                </div>

                <!-- Fullscreen Hint in Image Banner -->
                <button type="button" onclick="if(window.openGalleryLightbox && document.getElementById('amenity-modal-img').src) { closeAmenityImageModal(); window.openGalleryLightbox(document.getElementById('amenity-modal-img').src, document.getElementById('amenity-modal-title').textContent); }" class="absolute bottom-4 right-4 z-20 text-[11px] font-semibold text-white/80 hover:text-white bg-black/50 hover:bg-black/80 backdrop-blur-md px-3 py-1.5 rounded-full border border-white/15 transition-all flex items-center gap-1.5 cursor-pointer">
                    <i class="fas fa-expand text-amber-400 text-xs"></i>
                    <span>Fullscreen</span>
                </button>
            </div>

            <!-- Content Details Area -->
            <div class="p-6 sm:p-8 overflow-y-auto">
                <div class="flex items-center gap-4 mb-4">
                    <div id="amenity-modal-icon-wrap" class="w-12 h-12 rounded-2xl bg-gradient-to-br from-amber-400 to-yellow-600 text-slate-950 flex items-center justify-center text-xl shadow-lg flex-shrink-0">
                        <i id="amenity-modal-icon" class="fas fa-star"></i>
                    </div>
                    <div>
                        <h3 id="amenity-modal-title" class="text-2xl font-serif font-bold text-white leading-tight"></h3>
                        <p class="text-xs text-amber-400 font-medium mt-0.5">W Island Hub Signature Feature</p>
                    </div>
                </div>

                <p id="amenity-modal-desc" class="text-slate-300 text-sm sm:text-base leading-relaxed bg-white/5 p-4 rounded-2xl border border-white/10"></p>
            </div>

            <!-- Modal Footer -->
            <div class="px-6 py-4 bg-slate-950/60 border-t border-white/10 flex items-center justify-between gap-3 flex-shrink-0">
                <span class="text-xs text-slate-400 flex items-center gap-1.5">
                    <i class="fas fa-map-marker-alt text-amber-400"></i> Dauis, Panglao Island, Bohol
                </span>
                <button onclick="closeAmenityImageModal()" type="button" class="px-5 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-white font-semibold text-xs transition cursor-pointer border border-white/10">
                    Close
                </button>
            </div>

        </div>
    </div>

    <script>
    // Horizontal carousel scroll for amenities section
    function slideAmenitiesCarousel(direction) {
        const container = document.getElementById('amenities-slider-container');
        if (!container) return;
        const scrollAmount = (container.clientWidth * 0.8) * direction;
        container.scrollBy({ left: scrollAmount, behavior: 'smooth' });
    }

    // Modal Functions for Amenities
    function openAmenityImageModal(imgSrc, title, category, description, iconClass, gradClass) {
        const modal = document.getElementById('amenity-image-modal');
        const modalImg = document.getElementById('amenity-modal-img');
        const modalTitle = document.getElementById('amenity-modal-title');
        const modalCat = document.getElementById('amenity-modal-category');
        const modalDesc = document.getElementById('amenity-modal-desc');
        const modalIcon = document.getElementById('amenity-modal-icon');
        const modalIconWrap = document.getElementById('amenity-modal-icon-wrap');

        if (!modal) return;

        if (modalImg) modalImg.src = imgSrc || 'assets/image/background.jpg';
        if (modalTitle) modalTitle.textContent = title || 'Inn Amenity';
        if (modalCat) modalCat.textContent = category || 'Amenity';
        if (modalDesc) modalDesc.textContent = description || 'Experience modern comfort and authentic island living at W Island Hub.';
        if (modalIcon) modalIcon.className = iconClass || 'fas fa-star';
        if (modalIconWrap && gradClass) {
            modalIconWrap.className = 'w-12 h-12 rounded-2xl bg-gradient-to-br ' + gradClass + ' flex items-center justify-center text-xl shadow-lg flex-shrink-0';
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        void modal.offsetWidth;
        modal.classList.remove('opacity-0');

        const dialog = modal.querySelector('div');
        if (dialog) {
            dialog.classList.remove('scale-95');
            dialog.classList.add('scale-100');
        }

        document.body.style.overflow = 'hidden';
    }

    function closeAmenityImageModal(e) {
        if (e && e.target) {
            const dialogBox = e.target.closest && e.target.closest('#amenity-image-modal > div');
            if (dialogBox && !e.target.closest('button[onclick*="closeAmenityImageModal"]')) {
                return;
            }
        }

        const modal = document.getElementById('amenity-image-modal');
        if (!modal) return;

        modal.classList.add('opacity-0');
        const dialog = modal.querySelector('div');
        if (dialog) {
            dialog.classList.remove('scale-100');
            dialog.classList.add('scale-95');
        }

        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = '';
        }, 200);
    }

    function openAmenityFromCard(el) {
        const card = el.closest ? el.closest('.amenity-card-item') : el;
        if (!card) return;
        const img = card.dataset.img || 'assets/image/background.jpg';
        const title = card.dataset.title || '';
        const cat = card.dataset.cat || 'Amenity';
        const desc = card.dataset.desc || '';
        const icon = card.dataset.icon || 'fas fa-star';
        const grad = card.dataset.grad || 'from-amber-400 to-yellow-600 text-slate-950';
        openAmenityImageModal(img, title, cat, desc, icon, grad);
    }

    window.openAmenityImageModal = openAmenityImageModal;
    window.closeAmenityImageModal = closeAmenityImageModal;
    window.openAmenityFromCard = openAmenityFromCard;

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            const modal = document.getElementById('amenity-image-modal');
            if (modal && !modal.classList.contains('hidden')) {
                closeAmenityImageModal();
            }
        }
    });

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

            const escAttr = (s) => {
                return (s || '').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
            };

            track.innerHTML = data.amenities.map(amenity => {
                const cat = esc(amenity.category || 'General');
                const iconClass = esc(amenity.icon || 'fas fa-star');
                const title = esc(amenity.title || '');
                const desc = esc(amenity.description || '');
                const imgUrl = amenity.image_url ? esc(amenity.image_url) : 'assets/image/background.jpg';
                const gradClass = gradients[amenity.category] || 'from-amber-400 to-yellow-600 text-slate-950';

                const attrImg = escAttr(amenity.image_url || 'assets/image/background.jpg');
                const attrTitle = escAttr(amenity.title || '');
                const attrCat = escAttr(amenity.category || 'General');
                const attrDesc = escAttr(amenity.description || '');
                const attrIcon = escAttr(amenity.icon || 'fas fa-star');
                const attrGrad = escAttr(gradClass);

                return `
                <div class="amenity-card-item bg-white/5 backdrop-blur-md rounded-3xl border border-white/10 hover:border-amber-400/50 transition-all duration-300 group hover:-translate-y-1.5 flex flex-col justify-between shadow-xl overflow-hidden cursor-pointer"
                     data-img="${attrImg}"
                     data-title="${attrTitle}"
                     data-cat="${attrCat}"
                     data-desc="${attrDesc}"
                     data-icon="${attrIcon}"
                     data-grad="${attrGrad}"
                     onclick="openAmenityFromCard(this)"
                     title="Click to view photo & details">
                    <div>
                        ${imgUrl ? `
                        <div class="h-48 sm:h-52 w-full overflow-hidden relative bg-slate-900 group/img">
                            <img src="${imgUrl}" alt="${title}" class="w-full h-full object-cover group-hover/img:scale-108 transition-transform duration-700 ease-out">
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/20 to-transparent"></div>
                            <span class="absolute top-4 left-4 text-[11px] font-bold uppercase tracking-wider text-amber-300 bg-slate-950/70 backdrop-blur-md px-3 py-1 rounded-full border border-white/15">
                                ${cat}
                            </span>
                            <div class="absolute top-4 right-4 z-10 w-9 h-9 rounded-full bg-slate-950/70 backdrop-blur-md text-white flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-300 shadow-md">
                                <i class="fas fa-expand text-xs text-amber-400"></i>
                            </div>
                        </div>` : ''}

                        <div class="p-6 sm:p-8">
                            <div class="flex items-center justify-between mb-5">
                                <div class="w-13 h-13 rounded-2xl bg-gradient-to-br ${gradClass} flex items-center justify-center text-xl shadow-lg group-hover:scale-110 transition-transform duration-300">
                                    <i class="${iconClass}"></i>
                                </div>
                                <span class="text-xs font-semibold text-amber-400 group-hover:text-amber-300 flex items-center gap-1.5 transition-colors bg-white/5 group-hover:bg-white/10 px-3 py-1.5 rounded-full border border-white/10">
                                    <i class="fas fa-image text-xs"></i>
                                    <span>View Photo</span>
                                </span>
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
                    Centrally situated in Dauis, <strong class="text-slate-900 font-semibold">W Island Hub Backpackers Inn</strong> is your serene, high-value island basecamp. Avoid overpriced beachfront resort markups while staying just minutes away from pristine white shores, marine sanctuaries, caves, and Bohol countryside wonders on your own schedule.
                </p>
                <div class="flex flex-wrap items-center justify-center gap-2.5 mt-5 text-xs text-slate-600 font-medium">
                    <span class="inline-flex items-center gap-1.5 bg-white border border-slate-200/80 px-3.5 py-1.5 rounded-full shadow-xs"><i class="fas fa-map-marker-alt text-amber-500"></i> Central Island Basecamp</span>
                    <span class="inline-flex items-center gap-1.5 bg-white border border-slate-200/80 px-3.5 py-1.5 rounded-full shadow-xs"><i class="fas fa-tags text-teal-500"></i> Best Direct Rates &bull; Zero Fees</span>
                    <span class="inline-flex items-center gap-1.5 bg-white border border-slate-200/80 px-3.5 py-1.5 rounded-full shadow-xs"><i class="fas fa-bolt text-brand-blue"></i> Instant Booking (No Login Needed)</span>
                    <span class="inline-flex items-center gap-1.5 bg-white border border-slate-200/80 px-3.5 py-1.5 rounded-full shadow-xs"><i class="fas fa-wifi text-sky-500"></i> Nomad-Grade Starlink WiFi</span>
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
                            <i class="fas fa-route text-amber-400"></i> 15-20 Mins from Inn (13 km)
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
                                <span><strong>Why Stay With Us:</strong> Enjoy vibrant Alona beach bars and dining, then retreat to W Island Hub for a peaceful, noise-free night's sleep at half the beachfront price!</span>
                            </div>
                            <button onclick="router('rooms')" class="w-full py-2.5 bg-slate-100 hover:bg-brand-dark hover:text-white text-slate-800 font-bold text-xs rounded-xl transition flex items-center justify-center gap-1.5 cursor-pointer shadow-xs">
                                <i class="fas fa-bed text-amber-500"></i>
                                <span>Book Room at W Island Hub</span>
                                <i class="fas fa-arrow-right text-[10px]"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- 2. Dumaluan Beach / White Beach -->
                <div class="bg-white rounded-3xl overflow-hidden border border-slate-100 shadow-sm hover:shadow-2xl transition-all duration-300 flex flex-col group hover:-translate-y-1">
                    <div class="h-48 relative overflow-hidden">
                        <img src="https://pix10.agoda.net/hotelImages/240/240062/240062_15062417390030699657.jpg?va=1&ca=4&ce=1&s=414x232" alt="Dumaluan Beach Panglao" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>
                        <span class="absolute top-3 left-3 bg-white/95 backdrop-blur px-3 py-1 rounded-full text-[11px] font-bold text-teal-700 uppercase shadow-sm">
                            Tranquil White Sand
                        </span>
                        <span class="absolute bottom-3 left-3 text-white text-xs font-bold flex items-center gap-1.5 drop-shadow-md">
                            <i class="fas fa-map-marker-alt text-teal-400"></i> 12-15 Mins from Inn (8.5 km)
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
                                <span><strong>Why Stay With Us:</strong> Only 12-15 minutes to Panglao's most tranquil white sands. Head back to our on-site laundry hub to easily wash and refresh your beach clothes!</span>
                            </div>
                            <button onclick="router('rooms')" class="w-full py-2.5 bg-slate-100 hover:bg-brand-dark hover:text-white text-slate-800 font-bold text-xs rounded-xl transition flex items-center justify-center gap-1.5 cursor-pointer shadow-xs">
                                <i class="fas fa-bed text-teal-500"></i>
                                <span>Book Room at W Island Hub</span>
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
                                <span><strong>Why Stay With Us:</strong> Snorkel or freedive the world-famous sardine run, then refresh with our hot/cold showers, cold fruit shakes, and high-speed Starlink WiFi to upload your footage.</span>
                            </div>
                            <button onclick="router('rooms')" class="w-full py-2.5 bg-slate-100 hover:bg-brand-dark hover:text-white text-slate-800 font-bold text-xs rounded-xl transition flex items-center justify-center gap-1.5 cursor-pointer shadow-xs">
                                <i class="fas fa-bed text-blue-500"></i>
                                <span>Book Room at W Island Hub</span>
                                <i class="fas fa-arrow-right text-[10px]"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- 4. Hinagdanan Cave -->
                <div class="bg-white rounded-3xl overflow-hidden border border-slate-100 shadow-sm hover:shadow-2xl transition-all duration-300 flex flex-col group hover:-translate-y-1">
                    <div class="h-48 relative overflow-hidden">
                        <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSSD-mzqavTt8VXfhejq8BYT9e-myQBoiMLC1pxGieUlhDnFlDA6bleU1Cc&s=10" alt="Hinagdanan Cave Panglao" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>
                        <span class="absolute top-3 left-3 bg-white/95 backdrop-blur px-3 py-1 rounded-full text-[11px] font-bold text-purple-700 uppercase shadow-sm">
                            Limestone Cenote
                        </span>
                        <span class="absolute bottom-3 left-3 text-white text-xs font-bold flex items-center gap-1.5 drop-shadow-md">
                            <i class="fas fa-compass text-purple-400"></i> 6-8 Mins from Inn (3.7 km in Dauis)
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
                                <span><strong>Why Stay With Us:</strong> Right here in our home municipality of Dauis! Beat the tour crowds by visiting early in the morning just 6-8 minutes from our front door.</span>
                            </div>
                            <button onclick="router('rooms')" class="w-full py-2.5 bg-slate-100 hover:bg-brand-dark hover:text-white text-slate-800 font-bold text-xs rounded-xl transition flex items-center justify-center gap-1.5 cursor-pointer shadow-xs">
                                <i class="fas fa-bed text-purple-500"></i>
                                <span>Book Room at W Island Hub</span>
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
                            <i class="fas fa-water text-emerald-400"></i> World-Class Marine Sanctuary
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
                                <span><strong>Why Stay With Us:</strong> Early morning adventure friendly! Enjoy restful beds for early starts, freshly brewed morning coffee, and secure luggage storage while you explore.</span>
                            </div>
                            <button onclick="router('rooms')" class="w-full py-2.5 bg-slate-100 hover:bg-brand-dark hover:text-white text-slate-800 font-bold text-xs rounded-xl transition flex items-center justify-center gap-1.5 cursor-pointer shadow-xs">
                                <i class="fas fa-bed text-emerald-500"></i>
                                <span>Book Room at W Island Hub</span>
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
                                <span><strong>Why Stay With Us:</strong> Relax in cool air-conditioned comfort after a sunny day on the tidal sandbars, recharge your devices, and share your photos instantly over fast Starlink internet.</span>
                            </div>
                            <button onclick="router('rooms')" class="w-full py-2.5 bg-slate-100 hover:bg-brand-dark hover:text-white text-slate-800 font-bold text-xs rounded-xl transition flex items-center justify-center gap-1.5 cursor-pointer shadow-xs">
                                <i class="fas fa-bed text-amber-500"></i>
                                <span>Book Room at W Island Hub</span>
                                <i class="fas fa-arrow-right text-[10px]"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- 7. Chocolate Hills & ATV Safari -->
                <div class="bg-white rounded-3xl overflow-hidden border border-slate-100 shadow-sm hover:shadow-2xl transition-all duration-300 flex flex-col group hover:-translate-y-1">
                    <div class="h-48 relative overflow-hidden">
                        <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQRsmph_l6fp7QbSHqEGfiLpgjNj1k_XefHTE7GZhqTzK01we5N-9pNGOHN&s=10" alt="Chocolate Hills Bohol" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>
                        <span class="absolute top-3 left-3 bg-white/95 backdrop-blur px-3 py-1 rounded-full text-[11px] font-bold text-amber-800 uppercase shadow-sm">
                            UNESCO Geopark
                        </span>
                        <span class="absolute bottom-3 left-3 text-white text-xs font-bold flex items-center gap-1.5 drop-shadow-md">
                            <i class="fas fa-mountain text-amber-400"></i> Bohol Landmark &bull; Main Highway Access
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
                                <span><strong>Why Stay With Us:</strong> Prime position on the Dauis-Panglao main highway makes traveling inland to Chocolate Hills fast and direct, bypassing resort peninsula traffic bottlenecks.</span>
                            </div>
                            <button onclick="router('rooms')" class="w-full py-2.5 bg-slate-100 hover:bg-brand-dark hover:text-white text-slate-800 font-bold text-xs rounded-xl transition flex items-center justify-center gap-1.5 cursor-pointer shadow-xs">
                                <i class="fas fa-bed text-amber-500"></i>
                                <span>Book Room at W Island Hub</span>
                                <i class="fas fa-arrow-right text-[10px]"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- 8. Philippine Tarsier Sanctuary & Loboc River -->
                <div class="bg-white rounded-3xl overflow-hidden border border-slate-100 shadow-sm hover:shadow-2xl transition-all duration-300 flex flex-col group hover:-translate-y-1">
                    <div class="h-48 relative overflow-hidden">
                        <img src="https://dynamic-media-cdn.tripadvisor.com/media/photo-o/16/9e/51/b3/photo1jpg.jpg?w=1200&h=1200&s=1" alt="Loboc River Cruise Bohol" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>
                        <span class="absolute top-3 left-3 bg-white/95 backdrop-blur px-3 py-1 rounded-full text-[11px] font-bold text-emerald-700 uppercase shadow-sm">
                            Wildlife &amp; Culture
                        </span>
                        <span class="absolute bottom-3 left-3 text-white text-xs font-bold flex items-center gap-1.5 drop-shadow-md">
                            <i class="fas fa-leaf text-emerald-400"></i> Ethical Wildlife Sanctuary
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
                                <span><strong>Why Stay With Us:</strong> Backpacker-friendly room rates maximize your travel budget for unforgettable Bohol cultural sights, river buffet cruises, and eco-sanctuary experiences!</span>
                            </div>
                            <button onclick="router('rooms')" class="w-full py-2.5 bg-slate-100 hover:bg-brand-dark hover:text-white text-slate-800 font-bold text-xs rounded-xl transition flex items-center justify-center gap-1.5 cursor-pointer shadow-xs">
                                <i class="fas fa-bed text-emerald-500"></i>
                                <span>Book Room at W Island Hub</span>
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
                            Stay right where island comfort meets value. Enjoy whisper-quiet air-conditioned dorms and boutique private rooms, Starlink high-speed internet, an on-site laundry hub, pickleball court, and guaranteed best rates.
                        </p>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-2 text-xs text-slate-300">
                            <div class="flex items-center gap-1.5"><i class="fas fa-check text-amber-400"></i> Instant Direct Booking</div>
                            <div class="flex items-center gap-1.5"><i class="fas fa-check text-amber-400"></i> No Login Needed</div>
                            <div class="flex items-center gap-1.5"><i class="fas fa-check text-amber-400"></i> Secure GCash / Pay Later</div>
                            <div class="flex items-center gap-1.5"><i class="fas fa-check text-amber-400"></i> Best Price Guarantee</div>
                        </div>
                    </div>
                    <div class="flex flex-col sm:flex-row lg:flex-col items-center gap-3 w-full lg:w-auto flex-shrink-0">
                        <button onclick="router('rooms')" class="w-full sm:w-auto bg-gradient-to-r from-amber-500 to-yellow-500 hover:from-amber-600 hover:to-yellow-600 text-slate-950 font-bold px-8 py-4 rounded-2xl shadow-xl hover:shadow-2xl transition-all transform hover:scale-105 flex items-center justify-center gap-3 text-sm sm:text-base cursor-pointer">
                            <i class="fas fa-bed"></i>
                            <span>Book Room at W Island Hub</span>
                            <i class="fas fa-arrow-right text-xs"></i>
                        </button>
                        <a href="tel:+639456882266" class="text-xs text-slate-400 hover:text-amber-300 transition-colors flex items-center gap-1.5">
                            <i class="fas fa-phone-alt text-[10px]"></i> Front Desk Inquiries: +63 945 688 2266
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
                        Island Location &bull; Dauis, Bohol
                    </span>
                    <h2 class="text-3xl sm:text-4xl font-serif font-bold text-white mt-4 mb-3">
                        Easy to Reach, Hard to Leave
                    </h2>
                    <p class="text-slate-300 text-sm sm:text-base leading-relaxed mb-4 font-light">
                        Strategically situated along the <strong class="text-white font-medium">Dauis - Panglao Center Road in Dao, Dauis</strong>, W Island Hub Backpackers Inn is positioned perfectly between Tagbilaran City's transport hubs and Panglao's world-famous beaches.
                    </p>
                    
                    <div class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-amber-400/10 border border-amber-400/20 text-amber-300 text-xs font-medium mb-6">
                        <i class="fas fa-map-pin text-amber-400 flex-shrink-0"></i>
                        <span>Landmark: Directly across <strong>Wilcon Depot Dauis</strong> &bull; Adjacent to <strong>Royal Palms 2</strong></span>
                    </div>

                    <div class="space-y-3.5">
                        <div class="flex items-start gap-4 p-3.5 rounded-2xl bg-white/5 border border-white/10 hover:border-white/20 transition">
                            <div class="w-10 h-10 rounded-xl bg-sky-400 text-slate-950 flex items-center justify-center font-bold text-base flex-shrink-0">
                                <i class="fas fa-ship"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-white text-sm">Tagbilaran Seaport (Ferry Terminal)</h4>
                                <p class="text-xs text-slate-400 mt-0.5">Approx. 10-15 minutes drive (6 km) &bull; Direct access via Dauis bridge for Cebu/Siquijor ferry arrivals</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-4 p-3.5 rounded-2xl bg-white/5 border border-white/10 hover:border-white/20 transition">
                            <div class="w-10 h-10 rounded-xl bg-amber-400 text-slate-950 flex items-center justify-center font-bold text-base flex-shrink-0">
                                <i class="fas fa-plane"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-white text-sm">Panglao-Bohol International Airport (TAG)</h4>
                                <p class="text-xs text-slate-400 mt-0.5">Approx. 15 minutes drive (10 km) &bull; Quick, scenic drive via Dauis-Panglao Road</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-4 p-3.5 rounded-2xl bg-white/5 border border-white/10 hover:border-white/20 transition">
                            <div class="w-10 h-10 rounded-xl bg-purple-400 text-slate-950 flex items-center justify-center font-bold text-base flex-shrink-0">
                                <i class="fas fa-mountain"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-white text-sm">Local Caves & Dauis Attractions</h4>
                                <p class="text-xs text-slate-400 mt-0.5">Cambagat Cave (10-min walk) &bull; Hinagdanan Cave (6-8 mins / 3.7 km) &bull; Dauis Church (5 mins)</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-4 p-3.5 rounded-2xl bg-white/5 border border-white/10 hover:border-white/20 transition">
                            <div class="w-10 h-10 rounded-xl bg-teal-400 text-slate-950 flex items-center justify-center font-bold text-base flex-shrink-0">
                                <i class="fas fa-umbrella-beach"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-white text-sm">Panglao Beaches & Nightlife Strip</h4>
                                <p class="text-xs text-slate-400 mt-0.5">White Beach / Libaong (8-10 mins) &bull; Dumaluan (12-15 mins) &bull; Alona Beach (15-20 mins)</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-7 flex flex-wrap items-center gap-4">
                        <a href="https://maps.google.com/?q=Dao,+Dauis+-+Panglao+Center+Road,+Dauis,+Bohol" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 bg-gradient-to-r from-amber-500 to-yellow-500 hover:from-amber-600 hover:to-yellow-600 text-slate-950 font-bold px-5 py-2.5 rounded-xl text-xs transition shadow-md">
                            <i class="fas fa-directions"></i>
                            <span>Get Driving Directions</span>
                            <i class="fas fa-external-link-alt text-[10px]"></i>
                        </a>
                        <button onclick="router('contact')" class="inline-flex items-center gap-2 text-xs font-bold text-amber-400 hover:text-amber-300 transition-colors">
                            <span>Contact Reception &bull; Front Desk</span>
                            <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </div>

                <!-- Google Maps Preview Box -->
                <div class="flex flex-col gap-3">
                    <div class="h-96 rounded-3xl overflow-hidden shadow-2xl border border-white/10 bg-slate-800 relative">
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
                    <div class="flex items-center justify-between px-2 text-[11px] text-slate-400">
                        <span class="flex items-center gap-1.5"><i class="fas fa-map-marker-alt text-amber-400"></i> Dao, Dauis - Panglao Center Road, Dauis, Bohol 6339</span>
                        <a href="https://maps.google.com/?q=Dao,+Dauis+-+Panglao+Center+Road,+Dauis,+Bohol" target="_blank" rel="noopener noreferrer" class="text-amber-400 hover:underline">Open in Google Maps &rarr;</a>
                    </div>
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
                        <span>How do I get around Bohol, and why is your Dauis location ideal?</span>
                        <i class="fas fa-chevron-down text-sm text-slate-400 group-open:rotate-180 transition-transform duration-300"></i>
                    </summary>
                    <div class="mt-4 pt-4 border-t border-slate-100 text-slate-600 text-sm leading-relaxed space-y-2">
                        <p>While we focus exclusively on delivering clean, quiet, and affordable boutique accommodations (we do not operate vehicle rentals or tour services), our position right along the Dauis-Panglao main road means local tricycles, multicabs, and airport transfers pass right outside our inn!</p>
                        <p>Our friendly front desk team is always delighted to share honest local travel advice and fair fare estimates so you can explore Bohol independently without tourist markups. Plus, our central location lets you easily reach both Tagbilaran Seaport and Panglao beaches in just 10-15 minutes.</p>
                    </div>
                </details>

                <!-- FAQ Item 5 -->
                <details class="group bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm transition-all duration-300">
                    <summary class="cursor-pointer font-bold text-slate-900 flex items-center justify-between gap-4 text-base sm:text-lg">
                        <span>Why should I book directly on this official website?</span>
                        <i class="fas fa-chevron-down text-sm text-slate-400 group-open:rotate-180 transition-transform duration-300"></i>
                    </summary>
                    <div class="mt-4 pt-4 border-t border-slate-100 text-slate-600 text-sm leading-relaxed">
                        Booking directly on our website gives you our <strong>guaranteed best room rates</strong> with zero third-party platform markups or hidden fees. You get <strong>instant room confirmation in under 60 seconds with no login required</strong>, priority room selection, access to our on-site pickleball court and laundry hub, and direct support from our on-site team!
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
                    Contact Front Desk
                </button>
            </div>
        </div>
    </section>

</div>
