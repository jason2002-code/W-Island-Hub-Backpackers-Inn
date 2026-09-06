<div class="homepage-reviews-only">
    <!-- ================= GUEST REVIEWS & STORIES SECTION ================= -->
    <section id="guest-reviews" class="py-24 bg-gradient-to-b from-slate-50 via-white to-slate-50 border-t border-slate-100 relative overflow-hidden">
        
        <!-- Ambient Decorative Glows -->
        <div class="absolute top-0 right-1/4 w-96 h-96 bg-amber-500/5 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute bottom-0 left-1/4 w-96 h-96 bg-sky-500/5 rounded-full blur-3xl pointer-events-none"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            
            <!-- Section Header & Carousel Navigation Controls -->
            <div class="flex flex-col md:flex-row md:items-end justify-between mb-10 gap-6">
                <div>
                    <span class="text-xs font-bold uppercase tracking-widest text-amber-600 bg-amber-50 px-4 py-1.5 rounded-full border border-amber-100 inline-flex items-center gap-1.5 shadow-sm">
                        <i class="fas fa-heart text-amber-500"></i> Guest Testimonials
                    </span>
                    <h2 class="text-3xl sm:text-4xl md:text-5xl font-serif font-bold text-slate-900 mt-4 mb-3">
                        Stories & Island Memories
                    </h2>
                    <p class="text-slate-500 text-base sm:text-lg font-light leading-relaxed max-w-2xl">
                        Real stories and verified experiences from backpackers, couples, and digital nomads who found their home in paradise.
                    </p>
                </div>

                <!-- Sleek Scroll Controls -->
                <div class="flex items-center gap-3 self-start md:self-end shrink-0">
                    <span id="reviews-swipe-hint" class="text-xs font-medium text-slate-400 bg-white/80 backdrop-blur px-3 py-1.5 rounded-full border border-slate-200/80 shadow-xs hidden sm:inline-flex items-center gap-1.5">
                        <i class="fas fa-arrows-left-right text-amber-500 text-[10px]"></i> Swipe or drag
                    </span>
                    <button id="reviews-scroll-prev" class="w-12 h-12 rounded-full border border-slate-200 bg-white/95 backdrop-blur text-slate-700 hover:text-amber-600 hover:border-amber-400 hover:bg-white flex items-center justify-center transition-all duration-300 shadow-sm hover:shadow-md active:scale-95 disabled:opacity-30 disabled:cursor-not-allowed" aria-label="Previous review">
                        <i class="fas fa-arrow-left text-sm"></i>
                    </button>
                    <button id="reviews-scroll-next" class="w-12 h-12 rounded-full border border-slate-200 bg-white/95 backdrop-blur text-slate-700 hover:text-amber-600 hover:border-amber-400 hover:bg-white flex items-center justify-center transition-all duration-300 shadow-sm hover:shadow-md active:scale-95 disabled:opacity-30 disabled:cursor-not-allowed" aria-label="Next review">
                        <i class="fas fa-arrow-right text-sm"></i>
                    </button>
                </div>
            </div>

            <!-- Dynamic Rating Summary Dashboard Container -->
            <div id="reviews-rating-summary" class="mb-10">
                <!-- Populated dynamically by homepage_reviews.js -->
            </div>

            <!-- Scrollable Reviews Carousel Viewport -->
            <div class="relative group/carousel">
                <!-- Left & Right Soft Ambient Edge Fades on Desktop -->
                <div id="reviews-left-fade" class="pointer-events-none absolute left-0 top-0 bottom-6 w-16 bg-gradient-to-r from-slate-50 via-slate-50/80 to-transparent z-10 opacity-0 transition-opacity duration-300 hidden md:block"></div>
                <div id="reviews-right-fade" class="pointer-events-none absolute right-0 top-0 bottom-6 w-16 bg-gradient-to-l from-slate-50 via-slate-50/80 to-transparent z-10 transition-opacity duration-300 hidden md:block"></div>

                <!-- Horizontal Scroll Track Container -->
                <div id="reviews-scroll-container" class="overflow-x-auto scroll-smooth snap-x snap-mandatory flex gap-6 pb-6 pt-2 select-none no-scrollbar cursor-grab active:cursor-grabbing px-1" style="scrollbar-width: none; -ms-overflow-style: none;">
                    <div id="reviews-carousel" class="flex gap-6 min-w-full items-stretch">
                        <!-- Clean Modern Fallback Cards while loading -->
                        <div class="w-[85vw] sm:w-[380px] md:w-[410px] shrink-0 snap-start bg-white rounded-3xl p-8 border border-slate-100 shadow-sm flex flex-col justify-between animate-pulse">
                            <div>
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex gap-1 text-amber-400 text-sm">
                                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                                    </div>
                                    <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2.5 py-0.5 rounded-full">Verified Stay</span>
                                </div>
                                <p class="text-slate-600 text-sm leading-relaxed mb-6 italic">"The social vibe, clean dorms, and lightning-fast Starlink internet made my remote work stay in Panglao unforgettable."</p>
                            </div>
                            <div class="flex items-center gap-3 pt-4 border-t border-slate-100">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-amber-400 to-amber-600 text-slate-950 font-bold flex items-center justify-center text-xs">J</div>
                                <div>
                                    <h4 class="font-bold text-slate-900 text-sm">Jason C.</h4>
                                    <p class="text-xs text-slate-400">Digital Nomad &bull; 5.0 Rating</p>
                                </div>
                            </div>
                        </div>

                        <div class="w-[85vw] sm:w-[380px] md:w-[410px] shrink-0 snap-start bg-white rounded-3xl p-8 border border-slate-100 shadow-sm flex flex-col justify-between animate-pulse">
                            <div>
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex gap-1 text-amber-400 text-sm">
                                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                                    </div>
                                    <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2.5 py-0.5 rounded-full">Verified Stay</span>
                                </div>
                                <p class="text-slate-600 text-sm leading-relaxed mb-6 italic">"Super clean rooms, friendly staff, and just 2 minutes walk to the beach. Best backpackers inn on the island!"</p>
                            </div>
                            <div class="flex items-center gap-3 pt-4 border-t border-slate-100">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-sky-400 to-blue-600 text-white font-bold flex items-center justify-center text-xs">M</div>
                                <div>
                                    <h4 class="font-bold text-slate-900 text-sm">Maria S.</h4>
                                    <p class="text-xs text-slate-400">Solo Traveler &bull; 5.0 Rating</p>
                                </div>
                            </div>
                        </div>

                        <div class="w-[85vw] sm:w-[380px] md:w-[410px] shrink-0 snap-start bg-white rounded-3xl p-8 border border-slate-100 shadow-sm flex flex-col justify-between animate-pulse">
                            <div>
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex gap-1 text-amber-400 text-sm">
                                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                                    </div>
                                    <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2.5 py-0.5 rounded-full">Verified Stay</span>
                                </div>
                                <p class="text-slate-600 text-sm leading-relaxed mb-6 italic">"Quiet AC, comfortable beds with personal privacy curtains and reading lights. Highly recommend to everyone!"</p>
                            </div>
                            <div class="flex items-center gap-3 pt-4 border-t border-slate-100">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-teal-400 to-emerald-600 text-white font-bold flex items-center justify-center text-xs">L</div>
                                <div>
                                    <h4 class="font-bold text-slate-900 text-sm">Lucas B.</h4>
                                    <p class="text-xs text-slate-400">Backpacker &bull; 5.0 Rating</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modern Pagination Dots & Track Indicators -->
            <div id="reviews-dots" class="flex items-center justify-center gap-2 mt-2">
                <!-- Dots populated dynamically by homepage_reviews.js -->
            </div>

        </div>
    </section>
</div>

<style>
/* Custom hide scrollbars for review carousel track */
#reviews-scroll-container::-webkit-scrollbar {
    display: none;
}
#reviews-scroll-container {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>

