   <div class="homepage-reviews-only">
    <!-- Guest Reviews Section - Homepage Only -->

    <section id="guest-reviews" class="py-20 bg-gradient-to-b from-gray-50 to-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl md:text-5xl font-bold text-gray-900 mb-6">
                    What Our Guests Say
                </h2>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">Real reviews from real guests. See why they love staying at W Island Hub.</p>
            </div>
            <div id="reviews-carousel" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Reviews loaded dynamically -->
                <div class="text-center p-8 bg-white rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 border border-gray-100">
                    <div class="flex justify-center gap-1 mb-4">
                        <i class="fas fa-star text-2xl text-yellow-400"></i>
                        <i class="fas fa-star text-2xl text-yellow-400"></i>
                        <i class="fas fa-star text-2xl text-yellow-400"></i>
                        <i class="fas fa-star text-2xl text-yellow-400"></i>
                        <i class="far fa-star text-2xl text-yellow-400 opacity-50"></i>
                    </div>
                    <blockquote class="text-gray-700 mb-6 italic">"Loading reviews..."</blockquote>
                    <div class="flex items-center justify-center gap-2 text-sm text-gray-500">
                        <div class="w-8 h-8 bg-gray-200 rounded-full"></div>
                        <span>Guest Name</span>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
// Update homepage_reviews.js selector to target homepage-only section
(function() {
    if (document.querySelector('.homepage-reviews-only')) {
        const observer = new MutationObserver(() => {
            const carousel = document.querySelector('.homepage-reviews-only #reviews-carousel');
            if (carousel && !carousel.hasAttribute('data-loaded')) {
                carousel.setAttribute('data-loaded', 'true');
                // Trigger reviews load by dispatching event
                carousel.dispatchEvent(new CustomEvent('reviewsLoadTrigger'));
            }
        });
        observer.observe(document.body, { childList: true, subtree: true });
    }
})();
</script>

