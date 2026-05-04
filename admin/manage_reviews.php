<div id="manage-reviews" class="page-section">
    <div class="flex items-center justify-between mb-8">
        <h2 class="text-3xl font-bold text-brand-dark">Reviews & Ratings</h2>
        <div class="flex gap-3">
            <div class="relative">
                <input id="reviews-search" type="text" placeholder="Search reviews..." class="pl-10 pr-4 py-2 border rounded-lg focus:ring-2 focus:ring-brand-purple w-64">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table id="reviews-table" class="w-full table-auto">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">User</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Room</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Rating</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Comment</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Visible</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Reply</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody id="reviews-tbody" class="divide-y divide-gray-100">
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                            <i class="fas fa-spinner fa-spin text-xl mb-2 block mx-auto"></i>
                            Loading reviews...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', initReviewsManagement);

function initReviewsManagement() {
    loadReviews();
    document.getElementById('reviews-search').addEventListener('input', debounce(loadReviews, 300));
}

let allReviews = [];

function loadReviews() {
    const search = document.getElementById('reviews-search').value.toLowerCase();
    const tbody = document.getElementById('reviews-tbody');

    fetch('../actions/admin_reviews_action.php?action=get_reviews')
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                allReviews = data.reviews;
                renderReviews(search);
            } else {
                tbody.innerHTML = '<tr><td colspan="7" class="px-6 py-12 text-center text-red-500">Failed to load reviews</td></tr>';
            }
        })
        .catch(() => {
            tbody.innerHTML = '<tr><td colspan="7" class="px-6 py-12 text-center text-red-500">Network error</td></tr>';
        });
}

function renderReviews(search = '') {
    const tbody = document.getElementById('reviews-tbody');
    const filtered = allReviews.filter(r => 
        r.user_name.toLowerCase().includes(search) || 
        r.room_name.toLowerCase().includes(search) || 
        r.comment.toLowerCase().includes(search)
    );

    if (filtered.length) {
        tbody.innerHTML = filtered.map(review => `
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 font-medium text-gray-900">${review.user_name || 'Anonymous'}</td>
                <td class="px-6 py-4 text-sm text-gray-700">${review.room_name || 'N/A'}</td>
                <td class="px-6 py-4">
                    <div class="flex gap-0.5">
                        ${[...Array(5)].map((_, i) => `<i class="fas fa-star text-yellow-400 ${i < Math.round(review.rating) ? '' : 'opacity-30'}"></i>`).join('')}
                    </div>
                    <span class="text-sm text-gray-500">${review.rating}</span>
                </td>
                <td class="px-6 py-4 max-w-md">
                    <p class="text-sm text-gray-900">${review.comment}</p>
                </td>
                <td class="px-6 py-4">
                    <button onclick="toggleVisible(${review.id}, ${review.visible})" class="visible-toggle p-2 rounded-lg ${review.visible ? 'bg-green-100 text-green-800 hover:bg-green-200' : 'bg-red-100 text-red-800 hover:bg-red-200'} transition">
                        <i class="fas ${review.visible ? 'fa-eye' : 'fa-eye-slash'}"></i>
                    </button>
                </td>
                <td class="px-6 py-4">
                    ${review.admin_reply ? `
                        <div class="bg-blue-50 p-3 rounded-lg mb-2">
                            <p class="text-sm text-blue-900">${review.admin_reply}</p>
                            <button onclick="showReply(${review.id})" class="text-xs text-blue-600 hover:underline">Edit</button>
                        </div>
                    ` : ''}
                    <textarea id="reply-${review.id}" placeholder="Reply to this review..." class="reply-textarea w-full p-2 border rounded-lg resize-vertical hidden" rows="2">${review.admin_reply || ''}</textarea>
                    <button onclick="saveReply(${review.id})" id="save-reply-btn-${review.id}" class="save-reply-btn bg-brand-purple hover:bg-purple-700 text-white px-3 py-1 rounded text-sm hidden ml-2">Save</button>
                    <button onclick="showReply(${review.id})" id="show-reply-btn-${review.id}" class="show-reply-btn text-sm text-gray-600 hover:text-brand-purple ${review.admin_reply ? 'hidden' : ''}">Reply</button>
                </td>
                <td class="px-6 py-4 text-sm font-medium">
                    <button onclick="deleteReview(${review.id})" class="text-red-600 hover:text-red-800">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </td>
            </tr>
        `).join('');
    } else {
        tbody.innerHTML = '<tr><td colspan="7" class="px-6 py-12 text-center text-gray-500">No reviews found</td></tr>';
    }
}

function toggleVisible(id, currentVisible) {
    fetch('../actions/admin_reviews_action.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'toggle_visible', review_id: id})
    }).then(res => res.json()).then(data => {
        if (data.success) {
            if (typeof showToast === 'function') showToast(currentVisible ? 'Review hidden' : 'Review is now visible', 'success');
            loadReviews();
        } else {
            if (typeof showToast === 'function') showToast('Failed to update visibility', 'error');
        }
    });
}

function showReply(id) {
    document.getElementById(`reply-${id}`).classList.remove('hidden');
    const showBtn = document.getElementById(`show-reply-btn-${id}`);
    if (showBtn) showBtn.classList.add('hidden');
    const saveBtn = document.getElementById(`save-reply-btn-${id}`);
    if (saveBtn) saveBtn.classList.remove('hidden');
}

function saveReply(id) {
    const reply = document.getElementById(`reply-${id}`).value.trim();
    fetch('../actions/admin_reviews_action.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'save_reply', review_id: id, reply: reply})
    }).then(res => res.json()).then(data => {
        if (data.success) {
            if (typeof showToast === 'function') showToast('Reply saved successfully', 'success');
            loadReviews();
        } else {
            if (typeof showToast === 'function') showToast('Failed to save reply', 'error');
        }
    });
}

function deleteReview(id) {
    if (typeof showConfirm === 'function') {
        showConfirm('Are you sure you want to delete this review?', () => {
            executeDelete(id);
        });
    } else if (confirm('Are you sure you want to delete this review?')) {
        executeDelete(id);
    }
}

function executeDelete(id) {
    fetch('../actions/admin_reviews_action.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'delete_review', review_id: id})
    }).then(res => res.json()).then(data => {
        if (data.success) {
            if (typeof showToast === 'function') showToast('Review deleted successfully', 'success');
            loadReviews();
        } else {
            if (typeof showToast === 'function') showToast(data.message || 'Failed to delete review', 'error');
        }
    });
}

function debounce(func, delay) {
    let timeout;
    return (...args) => {
        clearTimeout(timeout);
        timeout = setTimeout(() => func(...args), delay);
    };
}
</script>
