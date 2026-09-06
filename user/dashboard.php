
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
                <a href="#inn-amenities" class="w-full sm:w-auto bg-white/10 hover:bg-white/20 backdrop-blur-md text-white border border-white/25 font-semibold py-4 px-8 rounded-full transition-all duration-300 flex items-center justify-center gap-2 text-base pointer-events-auto">
                    <i class="fas fa-compass text-amber-400"></i>
                    <span>Discover Island Life</span>
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

                <!-- Guests Count -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1.5 flex items-center gap-1.5">
                        <i class="fas fa-user-friends text-amber-500"></i> Guests (Max 5)
                    </label>
                    <div class="relative">
                        <input type="number" id="home-guest-count" min="1" max="5" value="1" placeholder="1-5 Guests" 
                               class="w-full bg-slate-50 border border-slate-200 rounded-2xl p-3.5 text-sm font-semibold text-slate-800 focus:bg-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 outline-none transition">
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
    <section id="inn-amenities" class="py-24 bg-slate-950 text-white px-4 sm:px-6 lg:px-8 relative overflow-hidden">
        
        <!-- Ambient Glow Elements -->
        <div class="absolute top-0 right-0 w-96 h-96 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute bottom-0 left-0 w-96 h-96 bg-sky-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="max-w-7xl mx-auto relative z-10">
            
            <!-- Section Header -->
            <div class="text-center max-w-3xl mx-auto mb-20">
                <span class="text-xs font-bold uppercase tracking-widest text-amber-400 bg-white/10 px-4 py-1.5 rounded-full border border-white/15">
                    The W Island Hub Experience
                </span>
                <h2 class="text-3xl sm:text-5xl font-serif font-bold text-white mt-4">
                    Designed for Travelers & Nomads
                </h2>
                <p class="text-slate-300 text-base sm:text-lg mt-3 font-light">
                    Every detail of our inn has been thoughtfully tailored to provide modern convenience, island relaxation, and effortless social connection.
                </p>
            </div>

            <!-- Amenities Grid (6 Modern Cards) -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                
                <!-- Feature 1 -->
                <div class="bg-white/5 backdrop-blur-md p-8 rounded-3xl border border-white/10 hover:border-amber-400/50 transition-all duration-300 group hover:-translate-y-1">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-amber-400 to-yellow-600 text-slate-950 flex items-center justify-center text-2xl mb-6 shadow-lg group-hover:scale-110 transition-transform">
                        <i class="fas fa-umbrella-beach"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2.5">Beachfront & Coral Coast</h3>
                    <p class="text-slate-400 text-sm leading-relaxed">
                        Steps away from pristine white sands. Enjoy early morning ocean dips, snorkeling among reef life, and front-row seats to world-famous sunsets.
                    </p>
                </div>

                <!-- Feature 2 -->
                <div class="bg-white/5 backdrop-blur-md p-8 rounded-3xl border border-white/10 hover:border-sky-400/50 transition-all duration-300 group hover:-translate-y-1">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-sky-400 to-blue-600 text-white flex items-center justify-center text-2xl mb-6 shadow-lg group-hover:scale-110 transition-transform">
                        <i class="fas fa-wifi"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2.5">Nomad-Grade Starlink WiFi</h3>
                    <p class="text-slate-400 text-sm leading-relaxed">
                        Lightning-fast, reliable satellite internet across all dorms, private rooms, and chill lounges. Seamless for remote work, video calls, and streaming.
                    </p>
                </div>

                <!-- Feature 3 -->
                <div class="bg-white/5 backdrop-blur-md p-8 rounded-3xl border border-white/10 hover:border-purple-400/50 transition-all duration-300 group hover:-translate-y-1">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-purple-400 to-indigo-600 text-white flex items-center justify-center text-2xl mb-6 shadow-lg group-hover:scale-110 transition-transform">
                        <i class="fas fa-cocktail"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2.5">Sunset Social Lounge & Bar</h3>
                    <p class="text-slate-400 text-sm leading-relaxed">
                        Vibrant open-air communal hub with board games, craft beers, refreshing tropical fruit shakes, and evening acoustic music jams.
                    </p>
                </div>

                <!-- Feature 4 -->
                <div class="bg-white/5 backdrop-blur-md p-8 rounded-3xl border border-white/10 hover:border-teal-400/50 transition-all duration-300 group hover:-translate-y-1">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-teal-400 to-emerald-600 text-white flex items-center justify-center text-2xl mb-6 shadow-lg group-hover:scale-110 transition-transform">
                        <i class="fas fa-motorcycle"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2.5">Scooter Rentals & Island Tours</h3>
                    <p class="text-slate-400 text-sm leading-relaxed">
                        Rent scooters right from the front desk. Book hassle-free tours to Chocolate Hills, Tarsier Sanctuaries, and dolphin watching expeditions.
                    </p>
                </div>

                <!-- Feature 5 -->
                <div class="bg-white/5 backdrop-blur-md p-8 rounded-3xl border border-white/10 hover:border-rose-400/50 transition-all duration-300 group hover:-translate-y-1">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-rose-400 to-red-600 text-white flex items-center justify-center text-2xl mb-6 shadow-lg group-hover:scale-110 transition-transform">
                        <i class="fas fa-key"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2.5">Keycard Security & Lockers</h3>
                    <p class="text-slate-400 text-sm leading-relaxed">
                        Travel with total peace of mind. Every dorm guest receives an oversized security locker, personal bedside power socket, and reading lamp.
                    </p>
                </div>

                <!-- Feature 6 -->
                <div class="bg-white/5 backdrop-blur-md p-8 rounded-3xl border border-white/10 hover:border-amber-400/50 transition-all duration-300 group hover:-translate-y-1">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-amber-500 to-amber-700 text-white flex items-center justify-center text-2xl mb-6 shadow-lg group-hover:scale-110 transition-transform">
                        <i class="fas fa-mug-hot"></i>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2.5">Free Coffee & Water Refills</h3>
                    <p class="text-slate-400 text-sm leading-relaxed">
                        Complimentary fresh-brewed local island coffee every morning. Unlimited filtered cold drinking water stations to keep you energized and eco-friendly.
                    </p>
                </div>

            </div>
        </div>
    </section>

    <!-- ================= CURATED ISLAND ADVENTURES ================= -->
    <section class="py-20 bg-slate-50 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            
            <div class="text-center max-w-3xl mx-auto mb-16">
                <span class="text-xs font-bold uppercase tracking-widest text-amber-600 bg-amber-100/60 px-3.5 py-1.5 rounded-full">
                    Discover Bohol & Panglao
                </span>
                <h2 class="text-3xl sm:text-4xl md:text-5xl font-serif font-bold text-slate-900 mt-3">
                    Unforgettable Island Adventures
                </h2>
                <p class="text-slate-500 text-base sm:text-lg mt-2">
                    Our front desk concierge will help you organize the ultimate tropical itinerary.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                
                <!-- Adventure 1 -->
                <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-between">
                    <div>
                        <div class="w-12 h-12 rounded-2xl bg-sky-50 text-sky-600 flex items-center justify-center text-xl mb-4">
                            <i class="fas fa-fish"></i>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 mb-2">Balicasag Sea Turtles</h3>
                        <p class="text-slate-500 text-xs sm:text-sm leading-relaxed">
                            World-class marine sanctuary snorkeling. Swim alongside friendly sea turtles and thousands of tropical fish.
                        </p>
                    </div>
                    <div class="mt-6 pt-4 border-t border-slate-100 text-xs font-bold text-amber-600 flex items-center gap-1">
                        <span>Boat tours available daily</span>
                    </div>
                </div>

                <!-- Adventure 2 -->
                <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-between">
                    <div>
                        <div class="w-12 h-12 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center text-xl mb-4">
                            <i class="fas fa-sun"></i>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 mb-2">Virgin Island Sandbar</h3>
                        <p class="text-slate-500 text-xs sm:text-sm leading-relaxed">
                            A mesmerizing crescent of powdery white sand appearing and disappearing with the tropical ocean tides.
                        </p>
                    </div>
                    <div class="mt-6 pt-4 border-t border-slate-100 text-xs font-bold text-amber-600 flex items-center gap-1">
                        <span>15 mins island hop</span>
                    </div>
                </div>

                <!-- Adventure 3 -->
                <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-between">
                    <div>
                        <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl mb-4">
                            <i class="fas fa-mountain"></i>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 mb-2">Chocolate Hills Safari</h3>
                        <p class="text-slate-500 text-xs sm:text-sm leading-relaxed">
                            Marvel at over 1,000 geological hills and visit the famous Tarsier sanctuary in the heart of Bohol.
                        </p>
                    </div>
                    <div class="mt-6 pt-4 border-t border-slate-100 text-xs font-bold text-amber-600 flex items-center gap-1">
                        <span>Full day countryside tour</span>
                    </div>
                </div>

                <!-- Adventure 4 -->
                <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-between">
                    <div>
                        <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl mb-4">
                            <i class="fas fa-fire"></i>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 mb-2">Sunset Paddle & Bonfire</h3>
                        <p class="text-slate-500 text-xs sm:text-sm leading-relaxed">
                            Rent stand-up paddleboards right on the beach, followed by evening acoustic music around the beach fire.
                        </p>
                    </div>
                    <div class="mt-6 pt-4 border-t border-slate-100 text-xs font-bold text-amber-600 flex items-center gap-1">
                        <span>Nightly inn community event</span>
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
