// Homepage Reviews Loader with Rating Summary
document.addEventListener('DOMContentLoaded', () => {
let carousel = document.getElementById('reviews-carousel');
// Support homepage-only wrapper
if (!carousel && document.querySelector('.homepage-reviews-only')) {
    carousel = document.querySelector('.homepage-reviews-only #reviews-carousel');
}
    const reviewsSection = carousel?.closest('section') || document.querySelector('#guest-reviews');
    if (!carousel || !reviewsSection) return;

    fetch('actions/reviews_action.php?action=get_recent_reviews')
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Add rating summary above carousel
                const summaryHTML = data.avg_rating ? `
                    <div class="text-center mb-12 p-8 bg-white/50 backdrop-blur-sm rounded-3xl shadow-xl border border-white/30 max-w-4xl mx-auto">
                        <div class="flex items-center justify-center gap-3 mb-4 flex-wrap">
                            <div class="flex gap-1">
                                ${[...Array(5)].map((_, i) => 
                                    `<i class="fas fa-star text-3xl text-yellow-400 ${i < Math.round(data.avg_rating) ? '' : 'opacity-40'}"></i>`
                                ).join('')}
                            </div>
                            <div class="text-4xl font-bold text-gray-900">${data.avg_rating}</div>
                        </div>
                        <p class="text-2xl font-semibold text-gray-700">${data.total_reviews} ${data.total_reviews === 1 ? 'Review' : 'Reviews'}</p>
                        <p class="text-lg text-gray-600 mt-2">Overall guest rating</p>
                    </div>
                ` : '';

                if (data.reviews?.length) {
                    carousel.innerHTML = summaryHTML + data.reviews.map(review => `
                        <div class="review-card bg-white rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 border border-gray-100 p-8 text-center">
                            <div class="flex justify-center gap-1 mb-6">
                                ${[...Array(5)].map((_, i) => 
                                    `<i class="fas fa-star text-2xl text-yellow-400 ${i < Math.round(review.rating) ? '' : 'opacity-50'}"></i>`
                                ).join('')}
                            </div>
                            <blockquote class="text-gray-700 mb-8 italic text-lg leading-relaxed">"${review.comment}"</blockquote>
                            <div class="flex items-center justify-center gap-3">
                                <div class="w-12 h-12 bg-gradient-to-r from-brand-purple to-brand-blue rounded-full flex items-center justify-center text-white font-semibold">${review.user_name.charAt(0).toUpperCase()}</div>
                                <div>
                                    <div class="font-semibold text-gray-900">${review.user_name}</div>
                                    <div class="text-sm text-gray-500">${review.formatted_date}</div>
                                </div>
                            </div>
                        </div>
                    `).join('');
                } else {
                    carousel.innerHTML = summaryHTML || '<div class="col-span-full text-center py-16 text-gray-500 md:col-span-3">No reviews yet. Be the first to share your experience!</div>';
                }
            } else {
                carousel.innerHTML = '<div class="col-span-full text-center py-16 text-gray-500 md:col-span-3">Failed to load reviews.</div>';
            }
        })
        .catch(() => {
            carousel.innerHTML = '<div class="col-span-full text-center py-16 text-gray-500 md:col-span-3">Reviews temporarily unavailable.</div>';
        });
});


