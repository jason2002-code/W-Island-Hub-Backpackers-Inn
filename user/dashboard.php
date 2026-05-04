
 <!-- ================= HOME PAGE ================= -->
        <div id="home" class="page-section active">
            
            <!-- Hero Carousel Section -->
            <div id="hero-carousel" class="relative h-[85vh] w-full overflow-hidden bg-brand-dark group">
                <!-- Slides Container -->
                <div id="hero-slides" class="absolute inset-0 w-full h-full">
                    <!-- Populated by JS -->
                </div>

                <!-- Overlay Content -->
                <div class="relative z-20 h-full flex flex-col justify-center items-center text-center text-white px-4 pointer-events-none">
                    <h2 class="gsap-hero-text text-xl md:text-2xl font-light mb-4 tracking-[0.2em] uppercase text-brand-sand drop-shadow-md">Paradise Awaits</h2>
                    <h1 class="gsap-hero-text text-4xl md:text-7xl font-serif font-bold mb-6 leading-tight drop-shadow-lg">Welcome to<br>W Island Hub</h1>
                    <p class="gsap-hero-text max-w-2xl mx-auto text-lg mb-8 text-gray-100 drop-shadow-md">Experience the perfect blend of modern comfort and tropical serenity at the premier backpackers inn.</p>
                    <button onclick="router('rooms')" class="gsap-hero-btn pointer-events-auto bg-brand-blue hover:bg-sky-600 text-white font-bold py-4 px-10 rounded-full transition transform hover:scale-105 shadow-lg">
                        Book Your Stay Now
                    </button>
                </div>

                <!-- Navigation Controls -->
                <button onclick="prevSlide()" class="absolute left-4 top-1/2 -translate-y-1/2 z-30 text-white/30 hover:text-white transition p-4 rounded-full hover:bg-black/20 opacity-0 group-hover:opacity-100 duration-300 cursor-pointer">
                    <i class="fas fa-chevron-left text-3xl"></i>
                </button>
                <button onclick="nextSlide()" class="absolute right-4 top-1/2 -translate-y-1/2 z-30 text-white/30 hover:text-white transition p-4 rounded-full hover:bg-black/20 opacity-0 group-hover:opacity-100 duration-300 cursor-pointer">
                    <i class="fas fa-chevron-right text-3xl"></i>
                </button>
                
                <!-- Dots -->
                <div id="hero-dots" class="absolute bottom-8 left-1/2 -translate-x-1/2 z-30 flex space-x-3">
                    <!-- Dots injected via JS -->
                </div>
            </div>
            
            <!-- Guest Reviews Section -->
            <?php include 'user/reviews.php'; ?>

            <!-- Auto-Scroll Marquee (Left to Right) -->
            <div class="py-12 bg-white overflow-hidden">
                <div class="text-center mb-8">
                    <h3 class="text-2xl font-serif font-bold text-brand-dark">Our Gallery</h3>
                    <p class="text-gray-500">Swipe through paradise</p>
                </div>
                <div class="marquee-container">
                    <div class="marquee-content reverse" id="marquee-track">
                        <!-- Populated by JS -->
                    </div>
                </div>
            </div>

            <!-- Features Grid -->
            <section class="py-20 bg-slate-50 px-4">
                <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-8 text-center">
                    <div class="bg-white p-8 rounded-2xl shadow-sm hover:shadow-xl transition duration-300">
                        <div class="text-brand-blue text-4xl mb-4"><i class="fas fa-umbrella-beach"></i></div>
                        <h3 class="text-xl font-bold mb-2">Beachfront Access</h3>
                        <p class="text-gray-600">Steps away from the pristine white sands and crystal clear waters.</p>
                    </div>
                    <div class="bg-white p-8 rounded-2xl shadow-sm hover:shadow-xl transition duration-300">
                        <div class="text-brand-blue text-4xl mb-4"><i class="fas fa-wifi"></i></div>
                        <h3 class="text-xl font-bold mb-2">High-Speed WiFi</h3>
                        <p class="text-gray-600">Stay connected with the world while enjoying your island getaway.</p>
                    </div>
                    <div class="bg-white p-8 rounded-2xl shadow-sm hover:shadow-xl transition duration-300">
                        <div class="text-brand-blue text-4xl mb-4"><i class="fas fa-cocktail"></i></div>
                        <h3 class="text-xl font-bold mb-2">Social Hub</h3>
                        <p class="text-gray-600">Meet fellow travelers at our vibrant communal lounge and bar.</p>
                    </div>
                </div>
            </section>
        </div>
