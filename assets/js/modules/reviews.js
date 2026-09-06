/**
 * REVIEWS MODULE
 * Manages fetching, rendering, and submitting room-specific guest reviews,
 * interactive star-picker widgets, and host replies.
 */

/**
 * Initializes and fetches guest reviews for an individual room card in the accommodations catalog.
 * Populates average star ratings, review counters, and customer comments.
 * 
 * @param {number|string} roomId - The ID of the target room.
 */
async function initReviewsSection(roomId) {
    const content = document.getElementById(`reviews-${roomId}`);
    if (!content) return;

    try {
        const res = await fetch(`actions/reviews_action.php?action=get_reviews&room_id=${roomId}`);
        const data = await res.json();

        if (data.success) {
            const countEl = document.querySelector(`.room-card[data-room-id="${roomId}"] .reviews-count`);
            if (countEl) countEl.textContent = `(${data.total_reviews})`;

            const avg = Math.max(0, Math.min(5, Math.round(data.avg_rating || 0)));
            const avgVal = parseFloat(data.avg_rating || 5.0).toFixed(1);

            let starsHtml = '';
            for (let i = 0; i < 5; i++) {
                starsHtml += `<i class="fas fa-star ${i < avg ? 'text-amber-400' : 'text-slate-200'}"></i>`;
            }

            const headerHtml = `
                <div class="p-4 bg-gradient-to-r from-amber-50/80 via-white to-amber-50/40 rounded-2xl border border-amber-100 flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="text-2xl sm:text-3xl font-serif font-bold text-slate-900">${avgVal}</div>
                        <div>
                            <div class="flex gap-0.5 text-xs text-amber-400 mb-0.5">${starsHtml}</div>
                            <span class="text-xs text-slate-500 font-medium">Based on ${data.total_reviews} verified reviews</span>
                        </div>
                    </div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-amber-700 bg-amber-100/70 border border-amber-200/60 px-2.5 py-1 rounded-full">
                        Room Rating
                    </span>
                </div>
            `;

            let reviewsListHtml = '';
            if (data.reviews && data.reviews.length > 0) {
                reviewsListHtml = data.reviews.map((review, idx) => {
                    const rRating = Math.max(1, Math.min(5, Math.round(parseFloat(review.rating || 5))));
                    let rStars = '';
                    for (let i = 0; i < 5; i++) {
                        rStars += `<i class="fas fa-star ${i < rRating ? 'text-amber-400' : 'text-slate-200'} text-[11px]"></i>`;
                    }
                    const initial = (review.user_name || 'G').charAt(0).toUpperCase();

                    const replyHtml = review.admin_reply ? `
                        <div class="mt-3 p-3 bg-amber-50/70 border-l-2 border-amber-500 rounded-r-xl text-xs">
                            <div class="flex items-center gap-1.5 font-bold text-slate-800 mb-0.5">
                                <i class="fas fa-reply text-amber-600 text-[10px]"></i>
                                <span>Host Response</span>
                            </div>
                            <p class="text-slate-600 font-normal leading-relaxed">${review.admin_reply}</p>
                        </div>
                    ` : '';

                    return `
                        <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm hover:border-slate-200 transition-colors">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-slate-800 to-slate-900 text-amber-400 font-bold text-xs flex items-center justify-center shadow-sm">
                                        ${initial}
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-1.5">
                                            <span class="font-bold text-xs text-slate-900">${review.user_name}</span>
                                            <i class="fas fa-check-circle text-emerald-500 text-[10px]" title="Verified Guest"></i>
                                        </div>
                                        <span class="text-[10px] text-slate-400">${review.formatted_date}</span>
                                    </div>
                                </div>
                                <div class="flex gap-0.5">
                                    ${rStars}
                                </div>
                            </div>
                            <p class="text-slate-600 text-xs sm:text-sm leading-relaxed pl-10">${review.comment}</p>
                            ${replyHtml}
                        </div>
                    `;
                }).join('');
            } else {
                reviewsListHtml = `
                    <div class="text-center py-8 bg-slate-50/60 rounded-2xl border border-dashed border-slate-200">
                        <i class="far fa-comment-dots text-slate-300 text-3xl mb-2"></i>
                        <p class="text-slate-500 text-xs sm:text-sm font-medium">No reviews yet for this room.</p>
                        <p class="text-slate-400 text-xs mt-0.5">Be the first guest to share your experience!</p>
                    </div>
                `;
            }

            content.innerHTML = `
                <div class="space-y-3">
                    ${headerHtml}
                    <div class="max-h-72 overflow-y-auto pr-1 space-y-3" style="scrollbar-width: thin; scrollbar-color: #cbd5e1 transparent;">
                        ${reviewsListHtml}
                    </div>
                </div>
            `;
        }
    } catch (e) {
        content.innerHTML = '<p class="text-red-500 text-xs text-center py-4">Failed to load room reviews.</p>';
    }
}

/**
 * Checks if review submission button should be enabled based on star rating and comment input.
 * 
 * @param {number|string} roomId - Room ID.
 */
function checkSubmitReady(roomId) {
    const picker = document.querySelector(`.star-picker[data-room-id="${roomId}"]`);
    const comment = document.getElementById(`review-comment-${roomId}`);
    const btn = document.querySelector(`.room-card[data-room-id="${roomId}"] .submit-review-btn`);
    
    if (picker && picker.dataset.rating && comment && comment.value.trim() && btn) {
        btn.disabled = false;
    } else if (btn) {
        btn.disabled = true;
    }
}

/**
 * Loads and renders room reviews into dedicated review container elements
 * when viewing a room detail page.
 * 
 * @param {number|string} roomId - The ID of the target room.
 */
async function loadRoomReviews(roomId) {
    const reviewsContainer = document.getElementById('room-reviews-container');
    const reviewFormContainer = document.getElementById('room-review-form-container');
    const reviewStatsContainer = document.getElementById('room-review-stats');
    const roomNameEl = document.getElementById('room-page-name');

    if (!reviewsContainer || !reviewFormContainer || !reviewStatsContainer || !roomNameEl) return;

    const room = (window.appState && window.appState.rooms) 
        ? window.appState.rooms.find(r => r.id == roomId) 
        : null;
    if (room) {
        roomNameEl.textContent = room.name;
    }

    try {
        const response = await fetch(`actions/reviews_action.php?action=get_reviews&room_id=${roomId}`);
        const data = await response.json();

        if (data.success) {
            // Render modern average rating stats
            const avg = Math.max(0, Math.min(5, Math.round(data.avg_rating || 0)));
            const avgVal = parseFloat(data.avg_rating || 5.0).toFixed(1);
            let stars = '';
            for (let i = 0; i < 5; i++) {
                stars += `<i class="fas fa-star ${i < avg ? 'text-amber-400' : 'text-slate-200'}"></i>`;
            }
            
            reviewStatsContainer.innerHTML = `
                <div class="flex items-center gap-4 p-5 bg-gradient-to-r from-amber-50/70 via-white to-amber-50/30 rounded-3xl border border-amber-100/80 shadow-sm">
                    <div class="w-16 h-16 rounded-2xl bg-slate-900 text-amber-400 font-serif font-bold text-2xl flex items-center justify-center shadow-md">
                        ${avgVal}
                    </div>
                    <div>
                        <div class="flex items-center gap-1 text-base text-amber-400 mb-1">${stars}</div>
                        <span class="text-xs text-slate-500 font-medium">Based on ${data.total_reviews} verified guest reviews</span>
                    </div>
                </div>
            `;

            // Render review list
            if (data.reviews && data.reviews.length > 0) {
                reviewsContainer.innerHTML = `
                    <div class="max-h-[480px] overflow-y-auto pr-2 space-y-4" style="scrollbar-width: thin; scrollbar-color: #cbd5e1 transparent;">
                        ${data.reviews.map(review => {
                            const uName = review.user_name || 'Anonymous';
                            const rRating = Math.max(0, Math.min(5, Math.round(review.rating || 5)));
                            let rStars = '';
                            for (let i = 0; i < 5; i++) {
                                rStars += `<i class="fas fa-star ${i < rRating ? 'text-amber-400' : 'text-slate-200'} text-xs"></i>`;
                            }
                            const initial = uName.charAt(0).toUpperCase();

                            return `
                            <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm hover:border-slate-200 transition-colors">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-amber-400 to-amber-600 text-slate-950 flex items-center justify-center font-bold text-xs shadow-sm">${initial}</div>
                                        <div>
                                            <div class="font-bold text-slate-900 text-sm flex items-center gap-1.5">
                                                <span>${uName}</span>
                                                <i class="fas fa-check-circle text-emerald-500 text-[10px]" title="Verified Guest"></i>
                                            </div>
                                            <div class="text-xs text-slate-400">${review.formatted_date}</div>
                                        </div>
                                    </div>
                                    <div class="flex gap-0.5">${rStars}</div>
                                </div>
                                <p class="text-slate-600 text-sm pl-13 leading-relaxed">${review.comment}</p>
                                ${review.admin_reply ? `
                                    <div class="mt-3 ml-13 p-3 bg-amber-50/70 border-l-2 border-amber-500 rounded-r-xl text-xs">
                                        <strong class="text-slate-800 block mb-0.5 font-bold"><i class="fas fa-reply mr-1 text-amber-600"></i> Host Response:</strong>
                                        <span class="text-slate-600">${review.admin_reply}</span>
                                    </div>
                                ` : ''}
                            </div>
                            `;
                        }).join('')}
                    </div>
                `;
            } else {
                reviewsContainer.innerHTML = `
                    <div class="text-center py-10 bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                        <p class="text-slate-500 text-sm font-medium">No reviews yet for this room.</p>
                        <p class="text-slate-400 text-xs mt-1">Be the first to write a review!</p>
                    </div>
                `;
            }
        }

        // Render review submission form if user is authenticated
        const currentUser = window.appState ? window.appState.currentUser : null;
        if (currentUser) {
            reviewFormContainer.innerHTML = `
                <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm">
                    <h3 class="text-lg font-serif font-bold text-slate-900 mb-4 flex items-center gap-2">
                        <i class="fas fa-pen-fancy text-amber-500 text-sm"></i> Leave a Review
                    </h3>
                    <form id="room-review-form" onsubmit="submitRoomReview(event, ${roomId})">
                        <div class="mb-4">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Your Rating</label>
                            <div class="flex items-center gap-2">
                                <div class="flex items-center gap-1 text-2xl text-slate-300" id="star-rating">
                                    ${[1,2,3,4,5].map(i => `<i class="fas fa-star cursor-pointer hover:scale-110 transition-transform" data-value="${i}"></i>`).join('')}
                                </div>
                                <span id="star-rating-label" class="text-xs font-bold text-amber-600 ml-2">Select rating</span>
                            </div>
                            <input type="hidden" id="review-rating" name="rating" required>
                        </div>
                        <div class="mb-4">
                            <label for="review-comment" class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Your Comment</label>
                            <textarea id="review-comment" name="comment" rows="4" class="w-full bg-slate-50 border border-slate-200 rounded-2xl p-3.5 text-sm focus:bg-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 outline-none transition resize-vertical" placeholder="Share your experience with this room... What did you like? Any suggestions?" required maxlength="500"></textarea>
                        </div>
                        <button type="submit" class="bg-slate-900 hover:bg-amber-500 hover:text-slate-950 text-white font-bold py-3 px-6 rounded-2xl transition-all duration-300 shadow-md flex items-center gap-2 text-xs">
                            <span>Submit Review</span>
                            <i class="fas fa-arrow-right text-[10px]"></i>
                        </button>
                    </form>
                </div>
            `;
            
            // Add interactive star rating hover and select behavior
            const starElements = reviewFormContainer.querySelectorAll('#star-rating .fa-star');
            const ratingLabel = reviewFormContainer.querySelector('#star-rating-label');
            const starLabels = { 1: '1 Star - Poor', 2: '2 Stars - Fair', 3: '3 Stars - Good', 4: '4 Stars - Very Good', 5: '5 Stars - Exceptional!' };

            starElements.forEach(star => {
                star.addEventListener('mouseover', () => {
                    const rating = star.dataset.value;
                    starElements.forEach(s => {
                        s.classList.toggle('text-amber-400', s.dataset.value <= rating);
                        s.classList.toggle('text-slate-300', s.dataset.value > rating);
                    });
                    if (ratingLabel) ratingLabel.textContent = starLabels[rating] || `${rating} Stars`;
                });
                star.addEventListener('click', () => {
                    const rating = star.dataset.value;
                    document.getElementById('review-rating').value = rating;
                    if (ratingLabel) ratingLabel.textContent = starLabels[rating] || `${rating} Stars`;
                });
            });
        } else {
            reviewFormContainer.innerHTML = '<div class="text-center p-6 bg-slate-50 rounded-2xl border border-slate-200/60"><p class="text-xs text-slate-600">Please <a onclick="router(\'login\')" class="text-amber-600 font-bold hover:underline cursor-pointer">log in</a> to leave a review.</p></div>';
        }

    } catch (error) {
        console.error("Failed to load reviews:", error);
        reviewsContainer.innerHTML = '<p class="text-red-500 text-xs text-center py-4">Could not load reviews at this time.</p>';
    }
}

/**
 * Submits a new guest review to the backend.
 * 
 * @param {Event} event - Form submission event.
 * @param {number|string} roomId - Target room ID.
 */
async function submitRoomReview(event, roomId) {
    if (event && event.preventDefault) event.preventDefault();
    const ratingInput = document.getElementById('review-rating');
    const commentInput = document.getElementById('review-comment');
    const rating = ratingInput ? ratingInput.value : '';
    const comment = commentInput ? commentInput.value.trim() : '';

    if (!rating) {
        if (typeof window.showToast === 'function') window.showToast('Please select a star rating.', 'warning');
        return;
    }

    const currentUser = window.appState ? window.appState.currentUser : null;
    if (!currentUser) {
        if (typeof window.showToast === 'function') window.showToast('Please login to post a review.', 'warning');
        return;
    }

    try {
        const res = await fetch('actions/reviews_action.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'submit_review',
                user_id: currentUser.id,
                room_id: roomId,
                rating: rating,
                comment: comment
            })
        });
        const data = await res.json();

        if (data.success) {
            if (typeof window.showToast === 'function') window.showToast('Review submitted successfully!', 'success');
            loadRoomReviews(roomId);
            initReviewsSection(roomId);
        } else {
            if (typeof window.showToast === 'function') window.showToast(data.message || 'Failed to submit review.', 'error');
        }
    } catch (err) {
        console.error('Review submit error:', err);
        if (typeof window.showToast === 'function') window.showToast('Network error submitting review.', 'error');
    }
}

// Attach event listeners for room catalog card star picker and review submit
if (!window._reviewsListenersBound) {
    window._reviewsListenersBound = true;

    // Star rating picker click
    document.addEventListener('click', (e) => {
        if (e.target.classList.contains('review-star')) {
            const picker = e.target.closest('.star-picker');
            if (!picker) return;
            const stars = picker.querySelectorAll('.review-star');
            const rating = parseInt(e.target.dataset.rating, 10);
            const ratingEl = picker.querySelector('.selected-rating');
            
            const ratingDescriptions = {
                1: '1 Star - Poor',
                2: '2 Stars - Fair',
                3: '3 Stars - Good',
                4: '4 Stars - Very Good',
                5: '5 Stars - Exceptional!'
            };

            stars.forEach((star, index) => {
                if (index < rating) {
                    star.className = 'review-star fas fa-star text-2xl text-amber-400 cursor-pointer transform scale-110 transition-all';
                } else {
                    star.className = 'review-star far fa-star text-2xl text-slate-300 hover:text-amber-300 cursor-pointer transition-all';
                }
            });
            
            if (ratingEl) {
                ratingEl.textContent = ratingDescriptions[rating] || `${rating} Stars`;
                ratingEl.className = 'selected-rating ml-3 text-xs font-bold text-amber-600';
            }
            picker.dataset.rating = rating;
            
            const section = e.target.closest('.submit-review-section');
            if (section && section.dataset.roomId) {
                checkSubmitReady(section.dataset.roomId);
            }
        }
    });

    // Textarea input
    document.addEventListener('input', (e) => {
        if (e.target.matches && e.target.matches('[id^="review-comment-"]')) {
            const roomId = e.target.id.split('-')[2];
            if (roomId) checkSubmitReady(roomId);
        }
    });

    // Submit review click
    document.addEventListener('click', async (e) => {
        const btn = e.target.closest('.submit-review-btn:not(:disabled)');
        if (btn) {
            const section = btn.closest('.submit-review-section');
            if (!section) return;
            const roomId = section.dataset.roomId;
            const picker = document.querySelector(`.star-picker[data-room-id="${roomId}"]`);
            const rating = picker ? picker.dataset.rating : null;
            const commentEl = document.getElementById(`review-comment-${roomId}`);
            const comment = commentEl ? commentEl.value.trim() : '';
            const spinner = btn.querySelector('.fa-spinner');
            const messageEl = btn.parentNode.querySelector('.submit-message');
            
            if (!rating) {
                if (typeof window.showToast === 'function') window.showToast('Please select a star rating.', 'warning');
                return;
            }

            btn.disabled = true;
            if (spinner) spinner.classList.remove('hidden');
            const btnText = btn.querySelector('.btn-text');
            if (btnText) btnText.textContent = 'Submitting...';
            
            try {
                const res = await fetch('actions/reviews_action.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({
                        action: 'submit_review',
                        room_id: roomId,
                        rating: rating,
                        comment: comment
                    })
                });
                const data = await res.json();
                
                if (data.success) {
                    if (messageEl) {
                        messageEl.classList.remove('hidden');
                        messageEl.className = 'submit-message mt-2 text-green-600 font-medium';
                        messageEl.textContent = data.message;
                    }
                    if (commentEl) commentEl.value = '';
                    if (picker) delete picker.dataset.rating;
                    setTimeout(() => initReviewsSection(roomId), 1000);
                } else {
                    if (messageEl) {
                        messageEl.classList.remove('hidden');
                        messageEl.className = 'submit-message mt-2 text-red-600 font-medium';
                        messageEl.textContent = data.message || 'Submission failed';
                    }
                }
            } catch (err) {
                if (messageEl) {
                    messageEl.classList.remove('hidden');
                    messageEl.className = 'submit-message mt-2 text-red-600 font-medium';
                    messageEl.textContent = 'Network error';
                }
            } finally {
                btn.disabled = false;
                if (spinner) spinner.classList.add('hidden');
                if (btnText) btnText.textContent = 'Submit Review';
            }
        }
    });
}

// Global window bindings for inline HTML and cross-module access
window.initReviewsSection = initReviewsSection;
window.checkSubmitReady = checkSubmitReady;
window.loadRoomReviews = loadRoomReviews;
window.submitRoomReview = submitRoomReview;

