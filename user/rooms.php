<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/db.php';

$pdo = getDB();
$rooms = [];
$error = '';

if ($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM rooms ORDER BY created_at DESC");
        $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error = 'Failed to load rooms: ' . $e->getMessage();
    }
} else {
    $error = 'Database connection failed';
}
?>

<!-- Rooms Grid -->
<div id="rooms-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
    <?php if ($error): ?>
        <div class="col-span-full text-center text-red-500 py-8">
            <p><?php echo htmlspecialchars($error); ?></p>
        </div>
    <?php elseif (empty($rooms)): ?>
        <div class="col-span-full text-center text-gray-500 py-8">
            <p>No rooms available at the moment.</p>
        </div>
    <?php else: ?>
        <?php foreach ($rooms as $room): 
            $amenities = is_array($room['amenities']) ? $room['amenities'] : json_decode($room['amenities'] ?? '[]', true);
            if (!is_array($amenities)) {
                $amenities = array_map('trim', explode(',', $room['amenities'] ?? ''));
            }
            $rating = floatval($room['rating'] ?? 5.0);
            $stars = round($rating);
            $room_id = intval($room['id']);
        ?>
            <div class="room-card bg-white rounded-xl shadow-sm hover:shadow-lg transition overflow-hidden border border-gray-100 flex flex-col" data-room-id="<?php echo $room_id; ?>">
                <div class="h-48 overflow-hidden relative">
                    <img src="<?php echo htmlspecialchars($room['image']); ?>" alt="<?php echo htmlspecialchars($room['name']); ?>" class="w-full h-full object-cover transition duration-500 hover:scale-110">
                    <div class="absolute top-2 right-2 bg-white/90 backdrop-blur px-2 py-1 rounded text-xs font-bold text-gray-800">
                        <?php for ($i = 0; $i < 5; $i++): ?>
                            <i class="fas fa-star text-yellow-400 text-xs <?php echo $i < $stars ? '' : 'opacity-30'; ?>"></i>
                        <?php endfor; ?>
                        <span class="ml-1 text-gray-600">(<?php echo $rating; ?>)</span>
                    </div>
                </div>
                <div class="p-6 flex-1 flex flex-col">
                    <div class="flex justify-between items-start mb-2">
                        <h3 class="text-xl font-bold text-brand-dark"><?php echo htmlspecialchars($room['name']); ?></h3>
                        <div>
                            <span class="room-status-badge bg-green-100 text-green-800 text-xs font-bold px-2 py-1 rounded uppercase mr-1" data-room-id="<?php echo $room_id; ?>">Loading...</span>
                            <span class="bg-blue-50 text-brand-blue text-xs font-bold px-2 py-1 rounded uppercase"><?php echo htmlspecialchars($room['type']); ?></span>
                        </div>
                    </div>
                    <p class="text-gray-500 text-sm mb-4 line-clamp-2"><?php echo htmlspecialchars($room['description']); ?></p>
                    
                    <div class="flex flex-wrap gap-2 mb-4">
                        <?php foreach (array_slice($amenities, 0, 3) as $amenity): ?>
                            <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded"><?php echo htmlspecialchars($amenity); ?></span>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Reviews Section -->
                    <details class="reviews-section group mt-4 border-t pt-4">
                        <summary class="cursor-pointer font-semibold text-brand-dark mb-3 flex items-center gap-2 hover:text-brand-blue transition group-open:rotate-180">
                            <i class="fas fa-star text-yellow-400"></i>
                            <span>Reviews <span class="reviews-count text-gray-500 ml-1">(Loading...)</span></span>
                            <i class="fas fa-chevron-down text-sm transition-transform duration-200"></i>
                        </summary>
                        
                        <div class="reviews-content space-y-4 mt-3" id="reviews-<?php echo $room_id; ?>">
                            <!-- Dynamic content loaded by JS -->
                        </div>

                        <?php if (isset($_SESSION['user_id'])): ?>
                        <div class="submit-review-section mt-6 p-4 bg-blue-50/50 rounded-lg border" data-room-id="<?php echo $room_id; ?>">
                            <h4 class="font-bold text-brand-dark mb-4 flex items-center gap-2">
                                <i class="fas fa-edit text-blue-500"></i>
                                Write Your Review
                            </h4>
                            <div class="star-picker mb-4 flex gap-1" data-room-id="<?php echo $room_id; ?>">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="review-star far fa-star text-2xl text-gray-400 cursor-pointer hover:text-yellow-400 transition-all duration-200" data-rating="<?php echo $i; ?>" title="Rate <?php echo $i; ?> star<?php echo $i > 1 ? 's' : ''; ?>"></i>
                                <?php endfor; ?>
                                <span class="ml-3 font-medium text-gray-700 selected-rating">Select rating</span>
                            </div>
                            <textarea id="review-comment-<?php echo $room_id; ?>" placeholder="Share your experience with this room... What did you like? Any suggestions?" class="w-full p-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-brand-blue focus:border-transparent resize-vertical min-h-[100px]" maxlength="500"></textarea>
                            <div class="text-right mt-2 text-xs text-gray-500">Max 500 characters</div>
                            <button class="submit-review-btn mt-4 bg-brand-blue hover:bg-blue-700 text-white px-6 py-2 rounded-xl font-medium transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed w-full md:w-auto" disabled>
                                <span class="btn-text">Submit Review</span>
                                <i class="fas fa-spinner fa-spin hidden ml-2"></i>
                            </button>
                            <div class="submit-message mt-2 hidden"></div>
                        </div>
                        <?php endif; ?>
                    </details>

                    <div class="mt-auto pt-4 border-t flex justify-between items-center">

                        <div>
                            <span class="text-2xl font-bold text-brand-blue">₱<?php echo number_format($room['price'], 2); ?></span>
                            <span class="text-gray-400 text-sm">/ night</span>
                        </div>
                        <button onclick="initiateBooking(<?php echo $room_id; ?>)" class="bg-brand-dark hover:bg-gray-800 text-white px-4 py-2 rounded-lg font-medium transition text-sm">
                            Book Now
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
// Existing room status updates...
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.room-card').forEach(card => {
        const roomId = card.dataset.roomId;
        updateRoomStatus(roomId);
        initReviewsSection(roomId);
    });
});

// Update room status (existing)
async function updateRoomStatus(roomId) {
    try {
        const res = await fetch('actions/book_action.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({action: 'get_room_availability', room_id: roomId})
        });
        const data = await res.json();
        const badge = document.querySelector(`[data-room-id="${roomId}"] .room-status-badge`);
        if (badge && data.success) {
            if (data.is_currently_available) {
                badge.textContent = 'Available';
                badge.className = 'room-status-badge bg-green-100 text-green-800 text-xs font-bold px-2 py-1 rounded uppercase mr-1';
            } else {
                badge.textContent = data.next_vacant ? `Occupied until ${data.next_vacant}` : 'Occupied';
                badge.className = 'room-status-badge bg-orange-100 text-orange-800 text-xs font-bold px-2 py-1 rounded uppercase mr-1';
            }
        }
    } catch (e) {
        console.error('Status update failed:', e);
    }
}

// New: Initialize reviews for room
async function initReviewsSection(roomId) {
    const content = document.getElementById(`reviews-${roomId}`);
    if (!content) return;

    try {
        const res = await fetch(`actions/reviews_action.php?action=get_reviews&room_id=${roomId}`);
        const data = await res.json();

        if (data.success) {
            const countEl = document.querySelector(`.room-card[data-room-id="${roomId}"] .reviews-count`);
            if (countEl) countEl.textContent = `(${data.total_reviews})`;

            content.innerHTML = `
                <div class="avg-rating flex items-center gap-2 mb-4 p-3 bg-white rounded-lg shadow-sm">
                    <div class="flex gap-0.5">
                        ${[...Array(5)].map((_, i) => 
                            `<i class="fas fa-star text-yellow-400 ${i < Math.round(data.avg_rating) ? '' : 'opacity-40'}"></i>`
                        ).join('')}
                    </div>
                    <span class="font-bold text-xl ml-2">${data.avg_rating}</span>
                    <span class="text-gray-500 text-sm ml-1">from ${data.total_reviews} reviews</span>
                </div>
                ${data.reviews.length ? data.reviews.map(review => `
                    <div class="review-item bg-white p-4 rounded-lg shadow-sm border">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="flex gap-0.5 text-sm">
                                ${[...Array(5)].map((_, i) => 
                                    `<i class="fas fa-star text-yellow-400 ${i < Math.round(review.rating) ? '' : 'opacity-40'}"></i>`
                                ).join('')}
                            </div>
                            <span class="font-semibold text-gray-800">${review.user_name || 'Anonymous'}</span>
                            <span class="text-xs text-gray-400">${review.formatted_date}</span>
                        </div>
                        <p class="text-gray-700">${review.comment}</p>
                        ${review.admin_reply ? `
                            <div class="mt-3 p-3 bg-blue-50 border-l-4 border-brand-blue rounded-r text-sm">
                                <strong class="text-brand-blue block mb-1"><i class="fas fa-reply mr-1"></i> Response from Host:</strong>
                                <span class="text-gray-700">${review.admin_reply}</span>
                            </div>
                        ` : ''}
                    </div>
                `).join('') : '<p class="text-gray-500 italic text-center py-8">No reviews yet. Be the first!</p>'}
            `;
        }
    } catch (e) {
        content.innerHTML = '<p class="text-red-500 text-center py-8">Failed to load reviews.</p>';
    }
}

// Star rating picker
document.addEventListener('click', (e) => {
    if (e.target.classList.contains('review-star')) {
        const stars = e.target.parentNode.querySelectorAll('.review-star');
        const rating = parseInt(e.target.dataset.rating);
        const ratingEl = e.target.parentNode.nextElementSibling;
        
        stars.forEach((star, index) => {
            if (index < rating) {
                star.classList.remove('far', 'fa-star');
                star.classList.add('fas', 'fa-star', 'text-yellow-400');
            } else {
                star.classList.remove('fas', 'fa-star', 'text-yellow-400');
                star.classList.add('far', 'fa-star', 'text-gray-400');
            }
        });
        
        ratingEl.textContent = `Selected: ${rating} star${rating > 1 ? 's' : ''}`;
        e.target.closest('.star-picker').dataset.rating = rating;
        
        // Enable submit if comment also filled
        const roomId = e.target.closest('.submit-review-section').dataset.roomId;
        checkSubmitReady(roomId);
    }
});

// Textarea change
document.addEventListener('input', (e) => {
    if (e.target.matches('[id^="review-comment-"]')) {
        const roomId = e.target.id.split('-')[2];
        checkSubmitReady(roomId);
    }
});

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

// Submit review
document.addEventListener('click', async (e) => {
    const btn = e.target.closest('.submit-review-btn:not(:disabled)');
    if (btn) {
        const section = btn.closest('.submit-review-section');
        const roomId = section.dataset.roomId;
        const rating = document.querySelector(`.star-picker[data-room-id="${roomId}"]`).dataset.rating;
        const comment = document.getElementById(`review-comment-${roomId}`).value.trim();
        const spinner = btn.querySelector('.fa-spinner');
        const messageEl = btn.parentNode.querySelector('.submit-message');
        
        console.log('Submitting review:', {roomId, rating, comment});
        
        btn.disabled = true;
        spinner.classList.remove('hidden');
        btn.querySelector('.btn-text').textContent = 'Submitting...';
        
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
                messageEl.classList.remove('hidden');
                messageEl.className = 'submit-message mt-2 text-green-600 font-medium';
                messageEl.textContent = data.message;
                document.getElementById(`review-comment-${roomId}`).value = '';
                const pickerReset = document.querySelector(`.star-picker[data-room-id="${roomId}"]`);
                if (pickerReset) delete pickerReset.dataset.rating;
                // Refresh reviews
                setTimeout(() => initReviewsSection(roomId), 1000);
            } else {
                messageEl.classList.remove('hidden');
                messageEl.className = 'submit-message mt-2 text-red-600 font-medium';
                messageEl.textContent = data.message || 'Submission failed';
            }
        } catch (err) {
            messageEl.classList.remove('hidden');
            messageEl.className = 'submit-message mt-2 text-red-600 font-medium';
            messageEl.textContent = 'Network error';
        } finally {
            btn.disabled = false;
            spinner.classList.add('hidden');
            btn.querySelector('.btn-text').textContent = 'Submit Review';
        }
    }
});
</script>
