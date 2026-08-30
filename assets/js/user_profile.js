/**
 * Enhanced Guest Portal & Profile Management
 * Handles Tabs, Extended Profile Details, Booking Vouchers, Reviews, and Account Security
 */

let activeBookingFilter = 'all';
let currentSearchQuery = '';

// Initialize Guest Profile on DOM Load
function initGuestProfile() {
    const user = (window.appState && window.appState.currentUser) ? window.appState.currentUser : null;
    
    if (!user) {
        window.location.href = '../index.php#login';
        return;
    }

    // Update Profile Completeness
    updateCompletenessMeter();

    // Render Tab Components
    renderOverviewTab();
    renderBookingsList();
    renderFeedbackList();
    renderReviewsList();
}

// Backward compatibility alias
function renderUserProfile() {
    initGuestProfile();
}

/**
 * Switch Navigation Tab
 */
function switchTab(tabId) {
    // Update button styling
    document.querySelectorAll('.profile-tab-btn').forEach(btn => {
        btn.classList.remove('active', 'bg-brand-blue', 'text-white', 'shadow-md');
        btn.classList.add('text-gray-600');
    });

    const activeBtn = document.getElementById(`tab-btn-${tabId}`);
    if (activeBtn) {
        activeBtn.classList.add('active', 'bg-brand-blue', 'text-white', 'shadow-md');
        activeBtn.classList.remove('text-gray-600');
    }

    // Hide all tab content sections
    document.querySelectorAll('.profile-tab-content').forEach(content => {
        content.classList.add('hidden');
    });

    // Show target tab
    const targetContent = document.getElementById(`tab-content-${tabId}`);
    if (targetContent) {
        targetContent.classList.remove('hidden');
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
}

/**
 * Calculate and Update Profile Completeness Progress Bar
 */
function updateCompletenessMeter() {
    const user = (window.appState && window.appState.currentUser) ? window.appState.currentUser : null;
    if (!user) return;

    let score = 0;
    let missingItems = [];

    // Basic required info (20%)
    if (user.name && user.email) score += 20;

    // Avatar (20%)
    if (user.avatar) {
        score += 20;
    } else {
        missingItems.push('profile photo');
    }

    // Phone / Contact (20%)
    if (user.phone) {
        score += 20;
    } else {
        missingItems.push('phone number');
    }

    // Emergency Contact (20%)
    if (user.emergency_name && user.emergency_phone) {
        score += 20;
    } else {
        missingItems.push('emergency contact');
    }

    // Stay Preferences or Bio (20%)
    if (user.travel_preferences || user.bio || user.nationality) {
        score += 20;
    } else {
        missingItems.push('stay preferences');
    }

    const pctElem = document.getElementById('completeness-percentage');
    const barElem = document.getElementById('completeness-bar');
    const hintElem = document.getElementById('completeness-hint');

    if (pctElem) pctElem.innerText = `${score}%`;
    if (barElem) barElem.style.width = `${score}%`;

    if (hintElem) {
        if (score === 100) {
            hintElem.innerHTML = `<span class="text-emerald-300 font-semibold"><i class="fas fa-check-circle mr-1"></i> Your profile is complete and ready for fast check-in!</span>`;
        } else if (missingItems.length > 0) {
            hintElem.innerText = `Tip: Add ${missingItems.slice(0, 2).join(' and ')} to reach 100%.`;
        }
    }
}

/**
 * Render Overview Tab (Next Upcoming Stay or Recent Activity)
 */
function renderOverviewTab() {
    const container = document.getElementById('next-stay-container');
    if (!container) return;

    const bookings = (window.appState && window.appState.bookings) ? window.appState.bookings : [];
    const confirmedBookings = bookings.filter(b => b.status === 'Confirmed');

    // Find next upcoming stay
    const today = new Date().toISOString().split('T')[0];
    const upcoming = confirmedBookings.find(b => b.checkOut >= today);

    if (upcoming) {
        const checkInDate = new Date(upcoming.checkIn);
        const formattedCheckIn = checkInDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        const formattedCheckOut = new Date(upcoming.checkOut).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });

        container.innerHTML = `
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-6 border-b border-gray-100 mb-6">
                <div>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <i class="fas fa-calendar-check text-emerald-500"></i> Next Upcoming Stay
                    </span>
                    <h3 class="text-2xl font-bold text-gray-800 mt-2">${upcoming.roomName}</h3>
                    <p class="text-xs text-gray-500">Booking Ref: <strong class="font-mono text-gray-700">${upcoming.refId || ('WIH-' + upcoming.id)}</strong></p>
                </div>
                <button onclick="openVoucherModal(${upcoming.id})" class="bg-gradient-to-r from-brand-blue to-teal-500 text-white font-bold text-xs px-5 py-2.5 rounded-xl shadow-md hover:shadow-lg transition flex items-center gap-2">
                    <i class="fas fa-ticket-alt"></i> View Voucher
                </button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                <div class="bg-slate-50 p-4 rounded-2xl border border-gray-100">
                    <span class="text-[10px] uppercase font-bold text-gray-400 block mb-1">Check-in Date</span>
                    <span class="font-bold text-gray-800 text-sm flex items-center gap-2">
                        <i class="far fa-calendar-alt text-brand-blue"></i> ${formattedCheckIn}
                    </span>
                    <span class="text-[10px] text-gray-500 block mt-0.5">Standard time: 2:00 PM</span>
                </div>

                <div class="bg-slate-50 p-4 rounded-2xl border border-gray-100">
                    <span class="text-[10px] uppercase font-bold text-gray-400 block mb-1">Check-out Date</span>
                    <span class="font-bold text-gray-800 text-sm flex items-center gap-2">
                        <i class="far fa-calendar-check text-amber-500"></i> ${formattedCheckOut}
                    </span>
                    <span class="text-[10px] text-gray-500 block mt-0.5">Standard time: 12:00 PM</span>
                </div>

                <div class="bg-slate-50 p-4 rounded-2xl border border-gray-100">
                    <span class="text-[10px] uppercase font-bold text-gray-400 block mb-1">Stay Duration & Total</span>
                    <span class="font-bold text-brand-blue text-sm flex items-center gap-2">
                        <i class="fas fa-moon text-indigo-400"></i> ${upcoming.nights} Night(s) • ₱${parseFloat(upcoming.total).toFixed(2)}
                    </span>
                    <span class="text-[10px] text-emerald-600 font-semibold block mt-0.5"><i class="fas fa-check-circle"></i> Paid via ${upcoming.paymentMethod}</span>
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3 pt-2 text-xs">
                <span class="text-gray-500"><i class="fas fa-map-pin text-rose-500 mr-1"></i> W Island Hub Main Lodge, Island Beachway</span>
                <div class="flex gap-2">
                    <button onclick="openReportModal(${upcoming.id}, '${upcoming.roomName.replace(/'/g, "\\'")}')" class="text-gray-600 hover:text-rose-600 hover:bg-rose-50 px-3 py-1.5 rounded-lg border border-gray-200 transition font-semibold">
                        <i class="fas fa-exclamation-circle mr-1"></i> Need Help with this Stay?
                    </button>
                </div>
            </div>
        `;
    } else {
        container.innerHTML = `
            <div class="text-center py-10">
                <div class="w-16 h-16 rounded-2xl bg-sky-50 text-brand-blue mx-auto flex items-center justify-center text-2xl mb-4">
                    <i class="fas fa-umbrella-beach"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-800 mb-1">No Active Upcoming Trips</h3>
                <p class="text-xs text-gray-500 max-w-md mx-auto mb-6">You don't have any upcoming reservations at W Island Hub. Ready for your next island adventure?</p>
                <a href="../index.php#rooms" class="inline-flex items-center gap-2 bg-gradient-to-r from-brand-blue to-teal-500 text-white px-6 py-3 rounded-xl font-bold text-xs shadow-md hover:shadow-lg transition">
                    <i class="fas fa-bed"></i> Browse Island Dorms & Rooms
                </a>
            </div>
        `;
    }
}

/**
 * Filter Bookings by Status
 */
function filterBookings(status) {
    activeBookingFilter = status;

    // Update filter pills
    document.querySelectorAll('.booking-filter-pill').forEach(btn => {
        if (btn.getAttribute('data-filter') === status) {
            btn.classList.add('bg-gray-900', 'text-white');
            btn.classList.remove('text-gray-600', 'hover:bg-gray-100');
        } else {
            btn.classList.remove('bg-gray-900', 'text-white');
            btn.classList.add('text-gray-600', 'hover:bg-gray-100');
        }
    });

    renderBookingsList();
}

/**
 * Search Bookings
 */
function searchBookings(query) {
    currentSearchQuery = query.toLowerCase().trim();
    renderBookingsList();
}

/**
 * Render Bookings List with Filters and Search
 */
function renderBookingsList() {
    const container = document.getElementById('user-bookings-container');
    if (!container) return;

    const allBookings = (window.appState && window.appState.bookings) ? window.appState.bookings : [];

    // Filter by status
    let filtered = allBookings;
    if (activeBookingFilter !== 'all') {
        filtered = filtered.filter(b => (b.status || '').toLowerCase() === activeBookingFilter.toLowerCase());
    }

    // Filter by search query
    if (currentSearchQuery) {
        filtered = filtered.filter(b => {
            const roomMatch = (b.roomName || '').toLowerCase().includes(currentSearchQuery);
            const refMatch = (b.refId || '').toLowerCase().includes(currentSearchQuery);
            const idMatch = String(b.id).includes(currentSearchQuery);
            return roomMatch || refMatch || idMatch;
        });
    }

    if (filtered.length === 0) {
        container.innerHTML = `
            <div class="text-center py-12 bg-slate-50 rounded-2xl border border-dashed border-gray-200">
                <i class="fas fa-bed text-3xl text-gray-300 mb-3"></i>
                <h4 class="font-bold text-gray-700 text-sm">No reservations found</h4>
                <p class="text-xs text-gray-400 mt-1">${currentSearchQuery ? 'Try a different search term.' : 'You have no bookings matching this status.'}</p>
                <a href="../index.php#rooms" class="mt-4 inline-flex items-center gap-1.5 text-xs font-bold text-brand-blue hover:underline">
                    <i class="fas fa-search"></i> Find a Room
                </a>
            </div>
        `;
        return;
    }

    container.innerHTML = filtered.map(b => {
        const bStatus = b.bookingStatus || (b.status === 'Confirmed' ? 'confirmed' : (b.status === 'Cancelled' ? 'cancelled_expired' : 'pending_verification'));
        let statusBadge = '';
        let timerNotice = '';

        if (bStatus === 'pending_verification') {
            statusBadge = `<span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200"><i class="fas fa-stopwatch text-[10px]"></i> Pending GCash Verification</span>`;
            if (b.expiresAt) {
                timerNotice = `<span class="text-[10px] text-amber-700 font-semibold block mt-0.5">2-Hour hold expires at ${b.expiresAt.substring(11, 16)}</span>`;
            }
        } else if (bStatus === 'deposit_paid') {
            statusBadge = `<span class="px-2.5 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800 border border-blue-200"><i class="fas fa-coins text-[10px]"></i> Deposit Paid (Balance Due at Desk)</span>`;
        } else if (bStatus === 'pending_arrival') {
            statusBadge = `<span class="px-2.5 py-1 rounded-full text-xs font-bold bg-purple-100 text-purple-800 border border-purple-200"><i class="fas fa-store text-[10px]"></i> Pending Arrival (Pay at Hotel)</span>`;
            if (b.expectedArrivalTime) {
                timerNotice = `<span class="text-[10px] text-purple-700 font-semibold block mt-0.5">Expected arrival: ${b.expectedArrivalTime}</span>`;
            }
        } else if (bStatus === 'confirmed') {
            statusBadge = `<span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200"><i class="fas fa-check-circle text-[10px]"></i> Confirmed (Paid Full)</span>`;
        } else if (bStatus === 'completed') {
            statusBadge = `<span class="px-2.5 py-1 rounded-full text-xs font-bold bg-teal-100 text-teal-800 border border-teal-200"><i class="fas fa-user-check text-[10px]"></i> Checked-In</span>`;
        } else if (bStatus === 'no_show') {
            statusBadge = `<span class="px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-200"><i class="fas fa-user-slash text-[10px]"></i> No-Show / Released</span>`;
        } else {
            statusBadge = `<span class="px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-200"><i class="fas fa-times-circle text-[10px]"></i> Expired / Cancelled</span>`;
        }

        const checkInFmt = b.checkIn ? new Date(b.checkIn).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : '-';
        const checkOutFmt = b.checkOut ? new Date(b.checkOut).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : '-';
        const safeRoomName = (b.roomName || 'Room').replace(/'/g, "\\'");
        const total = parseFloat(b.total || 0);
        const paid = parseFloat(b.paidAmount !== undefined ? b.paidAmount : (b.status === 'Confirmed' ? total : 0));
        const balance = parseFloat(b.balanceDue !== undefined ? b.balanceDue : (total - paid));

        // Review Button for Completed/Confirmed Stays
        let reviewBtn = '';
        if ((b.status === 'Confirmed' || bStatus === 'confirmed' || bStatus === 'completed') && b.roomId) {
            reviewBtn = `
                <button onclick="openReviewModal(${b.roomId}, '${safeRoomName}')" class="px-3 py-1.5 rounded-xl border border-amber-200 bg-amber-50 hover:bg-amber-100 text-amber-800 text-xs font-bold transition flex items-center gap-1.5">
                    <i class="fas fa-star text-amber-500"></i> Write Review
                </button>
            `;
        }

        return `
            <div class="border border-gray-100 hover:border-gray-200 bg-white p-5 rounded-2xl shadow-sm hover:shadow-md transition-all flex flex-col md:flex-row justify-between md:items-center gap-4">
                <div class="flex items-start gap-4">
                    <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-xl overflow-hidden bg-gray-100 flex-shrink-0 border border-gray-100">
                        <img src="../${b.roomImage || 'assets/image/background.jpg'}" class="w-full h-full object-cover" alt="${b.roomName}" onerror="this.src='../assets/image/background.jpg'">
                    </div>
                    <div>
                        <div class="flex flex-wrap items-center gap-2 mb-1">
                            <h4 class="font-bold text-gray-800 text-base">${b.roomName}</h4>
                            ${statusBadge}
                        </div>
                        ${timerNotice}
                        <p class="text-xs text-gray-500 flex items-center gap-2 mt-1">
                            <i class="far fa-calendar-alt text-brand-blue"></i> ${checkInFmt} &rarr; ${checkOutFmt} (${b.nights} night${b.nights > 1 ? 's' : ''})
                        </p>
                        <div class="flex flex-wrap items-center gap-3 text-[11px] text-gray-400 mt-1">
                            <span>Ref: <strong class="font-mono text-gray-600">${b.ref_id || b.refId || ('WIH-' + b.id)}</strong></span>
                            ${b.gcashRefNo ? `<span>• GCash Ref: <strong class="font-mono text-gray-600">${b.gcashRefNo}</strong></span>` : ''}
                        </div>
                    </div>
                </div>

                <div class="flex flex-col md:items-end justify-between gap-3 pt-3 md:pt-0 border-t md:border-t-0 border-gray-100">
                    <div class="text-left md:text-right">
                        <span class="text-[10px] text-gray-400 uppercase font-bold block">Total Bill: ₱${total.toFixed(2)}</span>
                        <div class="text-sm font-bold text-emerald-600">Paid: ₱${paid.toFixed(2)}</div>
                        ${balance > 0 ? `<div class="text-xs font-bold text-brand-blue">Due at Desk: ₱${balance.toFixed(2)}</div>` : ''}
                        <span class="text-[10px] text-gray-400 block mt-0.5">Via ${b.paymentMethod || 'GCash'}</span>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <button onclick="openVoucherModal(${b.id})" class="px-3 py-1.5 rounded-xl border border-gray-200 bg-gray-50 hover:bg-gray-100 text-gray-700 text-xs font-bold transition flex items-center gap-1.5">
                            <i class="fas fa-ticket-alt text-brand-blue"></i> Voucher
                        </button>
                        ${reviewBtn}
                        <button onclick="openReportModal(${b.id}, '${safeRoomName}')" class="px-3 py-1.5 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-gray-500 hover:text-rose-600 text-xs font-bold transition" title="Report Issue / Question">
                            <i class="fas fa-question-circle"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
    }).join('');
}

/**
 * Open Booking Voucher Modal
 */
function openVoucherModal(bookingId) {
    const booking = ((window.appState && window.appState.bookings) ? window.appState.bookings : []).find(b => b.id == bookingId);
    if (!booking) {
        if (typeof showToast === 'function') {
            showToast('Booking details not found', 'error');
        } else {
            alert('Booking details not found');
        }
        return;
    }

    const checkInFmt = booking.checkIn ? new Date(booking.checkIn).toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' }) : '-';
    const checkOutFmt = booking.checkOut ? new Date(booking.checkOut).toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' }) : '-';

    const guestName = (window.appState && window.appState.currentUser && window.appState.currentUser.name) ? window.appState.currentUser.name : (booking.guestName || 'Valued Guest');
    const total = parseFloat(booking.total || 0);
    const paid = parseFloat(booking.paidAmount !== undefined ? booking.paidAmount : (booking.status === 'Confirmed' ? total : 0));
    const balance = parseFloat(booking.balanceDue !== undefined ? booking.balanceDue : (total - paid));

    document.getElementById('voucher-guest-name').innerText = guestName;
    document.getElementById('voucher-room-name').innerText = booking.roomName || 'Standard Room';
    document.getElementById('voucher-check-in').innerText = checkInFmt;
    document.getElementById('voucher-check-out').innerText = checkOutFmt;
    document.getElementById('voucher-nights').innerText = `${booking.nights} Night${booking.nights > 1 ? 's' : ''}`;
    document.getElementById('voucher-ref-code').innerText = `Ref: ${booking.ref_id || booking.refId || ('WIH-' + booking.id)}`;
    document.getElementById('voucher-payment-method').innerText = booking.paymentMethod || 'GCash';
    document.getElementById('voucher-total-price').innerText = `₱${total.toFixed(2)}`;

    const badge = document.getElementById('voucher-status-badge');
    if (badge) {
        const bStatus = booking.bookingStatus || booking.status;
        if (bStatus === 'confirmed' || bStatus === 'Confirmed' || bStatus === 'completed') {
            badge.innerText = 'Confirmed (Paid Full)';
            badge.className = 'inline-block px-3 py-1 bg-emerald-100 text-emerald-800 rounded-full text-xs font-bold';
        } else if (bStatus === 'deposit_paid') {
            badge.innerText = `Deposit Paid (₱${paid.toFixed(2)} / Due: ₱${balance.toFixed(2)})`;
            badge.className = 'inline-block px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-bold';
        } else if (bStatus === 'pending_arrival') {
            badge.innerText = `Pending Arrival (Due at Desk: ₱${balance.toFixed(2)})`;
            badge.className = 'inline-block px-3 py-1 bg-purple-100 text-purple-800 rounded-full text-xs font-bold';
        } else if (bStatus === 'cancelled_expired' || bStatus === 'Cancelled' || bStatus === 'no_show') {
            badge.innerText = 'Cancelled / Expired';
            badge.className = 'inline-block px-3 py-1 bg-rose-100 text-rose-800 rounded-full text-xs font-bold';
        } else {
            badge.innerText = 'Pending GCash Verification';
            badge.className = 'inline-block px-3 py-1 bg-amber-100 text-amber-800 rounded-full text-xs font-bold';
        }
    }

    const modal = document.getElementById('voucher-modal');
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
}

function closeVoucherModal() {
    const modal = document.getElementById('voucher-modal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
}

/**
 * Live Avatar Photo Preview
 */
function previewAvatar(event) {
    const input = event.target;
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const previewImg = document.getElementById('edit-avatar-preview');
            const placeholder = document.getElementById('edit-avatar-placeholder');
            
            if (previewImg) {
                previewImg.src = e.target.result;
                previewImg.classList.remove('hidden');
            }
            if (placeholder) {
                placeholder.classList.add('hidden');
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

/**
 * Save Guest Profile Changes
 */
function saveProfile(e) {
    e.preventDefault();

    const saveBtn = document.getElementById('save-profile-btn');
    if (saveBtn) {
        saveBtn.disabled = true;
        saveBtn.innerHTML = `<i class="fas fa-spinner fa-spin mr-2"></i> Saving Changes...`;
    }

    const currentUser = (window.appState && window.appState.currentUser) ? window.appState.currentUser : {};
    const formData = new FormData();
    formData.append('id', currentUser.id);
    formData.append('name', document.getElementById('edit-name').value.trim());
    formData.append('email', document.getElementById('edit-email').value.trim());
    formData.append('phone', document.getElementById('edit-phone').value.trim());
    formData.append('nationality', document.getElementById('edit-nationality').value.trim());
    formData.append('gender', document.getElementById('edit-gender').value);
    formData.append('birthdate', document.getElementById('edit-birthdate').value);
    formData.append('address', document.getElementById('edit-address').value.trim());
    formData.append('bio', document.getElementById('edit-bio').value.trim());

    // Emergency details
    formData.append('emergency_name', document.getElementById('edit-emergency-name').value.trim());
    formData.append('emergency_relation', document.getElementById('edit-emergency-relation').value.trim());
    formData.append('emergency_phone', document.getElementById('edit-emergency-phone').value.trim());

    // Preferences
    formData.append('bunk', document.getElementById('edit-pref-bunk').value);
    formData.append('dietary', document.getElementById('edit-pref-dietary').value);
    formData.append('arrival_time', document.getElementById('edit-pref-arrival').value);
    formData.append('notes', document.getElementById('edit-pref-notes').value.trim());

    // Avatar upload
    const avatarInput = document.getElementById('edit-avatar');
    if (avatarInput && avatarInput.files[0]) {
        formData.append('avatar', avatarInput.files[0]);
    }

    fetch('../actions/profile_action.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            if (typeof showToast === 'function') {
                showToast('Profile updated successfully!', 'success');
            } else {
                alert('Profile updated successfully!');
            }

            // Update appState
            if (data.user && window.appState) {
                window.appState.currentUser = Object.assign(window.appState.currentUser, data.user);
                if (typeof saveState === 'function') saveState();
            }

            // Update UI elements on header
            const headerName = document.getElementById('header-user-name');
            const headerEmail = document.getElementById('header-user-email');
            const headerPhone = document.getElementById('header-user-phone');
            const headerAvatar = document.getElementById('header-avatar-img');

            if (headerName && window.appState && window.appState.currentUser) headerName.innerText = window.appState.currentUser.name;
            if (headerEmail && window.appState && window.appState.currentUser) headerEmail.innerText = window.appState.currentUser.email;
            if (headerPhone && window.appState && window.appState.currentUser && window.appState.currentUser.phone) {
                headerPhone.innerText = window.appState.currentUser.phone;
            }

            if (data.user && data.user.avatar && headerAvatar) {
                headerAvatar.src = `../${data.user.avatar}`;
            }

            updateCompletenessMeter();
            setTimeout(() => switchTab('overview'), 1000);
        } else {
            if (typeof showToast === 'function') {
                showToast(data.message || 'Failed to update profile', 'error');
            } else {
                alert(data.message || 'Failed to update profile');
            }
        }
    })
    .catch(err => {
        console.error('Profile update error:', err);
        if (typeof showToast === 'function') {
            showToast('Network error while saving profile', 'error');
        } else {
            alert('Network error while saving profile');
        }
    })
    .finally(() => {
        if (saveBtn) {
            saveBtn.disabled = false;
            saveBtn.innerHTML = `<i class="fas fa-check"></i> Save Profile Details`;
        }
    });
}

/**
 * Check Password Strength Live
 */
function checkPasswordStrength(password) {
    const bar = document.getElementById('pwd-strength-bar');
    const text = document.getElementById('pwd-strength-text');
    if (!bar || !text) return;

    if (!password) {
        bar.style.width = '0%';
        bar.className = 'h-full bg-gray-300 transition-all duration-300';
        text.innerText = 'None';
        return;
    }

    let strength = 0;
    if (password.length >= 8) strength += 25;
    if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength += 25;
    if (password.match(/\d/)) strength += 25;
    if (password.match(/[^a-zA-Z\d]/)) strength += 25;

    bar.style.width = `${strength}%`;

    if (strength <= 25) {
        bar.className = 'h-full bg-rose-500 transition-all duration-300';
        text.innerText = 'Weak';
        text.className = 'text-rose-500 font-bold';
    } else if (strength <= 50) {
        bar.className = 'h-full bg-amber-500 transition-all duration-300';
        text.innerText = 'Fair';
        text.className = 'text-amber-500 font-bold';
    } else if (strength <= 75) {
        bar.className = 'h-full bg-blue-500 transition-all duration-300';
        text.innerText = 'Good';
        text.className = 'text-brand-blue font-bold';
    } else {
        bar.className = 'h-full bg-emerald-500 transition-all duration-300';
        text.innerText = 'Strong';
        text.className = 'text-emerald-500 font-bold';
    }
}

/**
 * Toggle Password Field Visibility
 */
function togglePassword(inputId, iconElem) {
    const input = document.getElementById(inputId);
    if (!input || !iconElem) return;

    if (input.type === 'password') {
        input.type = 'text';
        iconElem.classList.remove('fa-eye');
        iconElem.classList.add('fa-eye-slash', 'text-brand-blue');
    } else {
        input.type = 'password';
        iconElem.classList.remove('fa-eye-slash', 'text-brand-blue');
        iconElem.classList.add('fa-eye');
    }
}

/**
 * Update User Password
 */
function updateUserPassword(e) {
    e.preventDefault();

    const currentPwd = document.getElementById('current-password').value;
    const newPwd = document.getElementById('new-password').value;
    const confirmPwd = document.getElementById('confirm-password').value;

    if (newPwd !== confirmPwd) {
        if (typeof showToast === 'function') {
            showToast('New password and confirmation password do not match', 'error');
        } else {
            alert('New password and confirmation password do not match');
        }
        return;
    }

    if (newPwd.length < 8) {
        if (typeof showToast === 'function') {
            showToast('Password must be at least 8 characters long', 'error');
        } else {
            alert('Password must be at least 8 characters long');
        }
        return;
    }

    const btn = document.getElementById('update-pwd-btn');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = `<i class="fas fa-spinner fa-spin mr-2"></i> Updating...`;
    }

    const currentUser = (window.appState && window.appState.currentUser) ? window.appState.currentUser : {};

    fetch('../actions/profile_action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            action: 'update_password',
            id: currentUser.id,
            current_password: currentPwd,
            new_password: newPwd,
            confirm_password: confirmPwd
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            if (typeof showToast === 'function') {
                showToast('Password updated successfully!', 'success');
            } else {
                alert('Password updated successfully!');
            }
            document.getElementById('password-change-form').reset();
            checkPasswordStrength('');
        } else {
            if (typeof showToast === 'function') {
                showToast(data.message || 'Failed to update password', 'error');
            } else {
                alert(data.message || 'Failed to update password');
            }
        }
    })
    .catch(err => {
        console.error('Password update error:', err);
        if (typeof showToast === 'function') {
            showToast('Network error while updating password', 'error');
        } else {
            alert('Network error while updating password');
        }
    })
    .finally(() => {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = `<i class="fas fa-lock"></i> Update Password`;
        }
    });
}

/**
 * Render Feedback & Support Inquiries List
 */
function renderFeedbackList() {
    const container = document.getElementById('user-reports-container');
    if (!container) return;

    const reports = (window.appState && window.appState.reports) ? window.appState.reports : [];

    if (reports.length === 0) {
        container.innerHTML = `
            <div class="text-center py-8 text-gray-400">
                <i class="fas fa-comments text-3xl text-gray-200 mb-2"></i>
                <p class="text-xs">You have not submitted any inquiries or feedback yet.</p>
                <button onclick="openReportModal()" class="mt-3 text-xs font-bold text-brand-blue hover:underline">
                    + Submit your first inquiry
                </button>
            </div>
        `;
        return;
    }

    container.innerHTML = reports.map(r => {
        const dateFmt = r.created_at ? new Date(r.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : '';
        const statusClass = r.status === 'Resolved' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800';

        return `
            <div class="border border-gray-100 rounded-2xl p-4 bg-slate-50/50 hover:bg-white hover:shadow-sm transition">
                <div class="flex justify-between items-start mb-1.5">
                    <h5 class="font-bold text-gray-800 text-sm">${r.subject || 'General Inquiry'}</h5>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold ${statusClass}">${r.status}</span>
                </div>
                <p class="text-xs text-gray-600 mb-2 leading-relaxed">${r.message}</p>
                <div class="text-[10px] text-gray-400 flex items-center justify-between">
                    <span><i class="far fa-clock mr-1"></i> ${dateFmt}</span>
                    <span class="capitalize font-semibold text-gray-500">${r.type || 'feedback'}</span>
                </div>
                ${r.admin_response ? `
                    <div class="mt-3 p-3 bg-blue-50/80 border-l-4 border-brand-blue rounded-r-xl">
                        <div class="text-[11px] font-bold text-brand-blue mb-1 flex items-center gap-1.5">
                            <i class="fas fa-reply"></i> Response from Inn Staff:
                        </div>
                        <p class="text-xs text-gray-700">${r.admin_response}</p>
                    </div>
                ` : ''}
            </div>
        `;
    }).join('');
}

/**
 * Render User Room Reviews List
 */
function renderReviewsList() {
    const container = document.getElementById('user-reviews-container');
    if (!container) return;

    const reviews = (window.appState && window.appState.reviews) ? window.appState.reviews : [];

    if (reviews.length === 0) {
        container.innerHTML = `
            <div class="text-center py-8 text-gray-400">
                <i class="fas fa-star text-3xl text-gray-200 mb-2"></i>
                <p class="text-xs">You haven't written any room reviews yet.</p>
                <p class="text-[11px] text-gray-400 mt-1">Review your room after your stay to help other travelers!</p>
            </div>
        `;
        return;
    }

    container.innerHTML = reviews.map(rev => {
        const stars = Array.from({ length: 5 }, (_, i) => {
            return `<i class="fas fa-star ${i < Math.round(rev.rating) ? 'text-amber-400' : 'text-gray-200'}"></i>`;
        }).join('');

        const dateFmt = rev.created_at ? new Date(rev.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : '';

        return `
            <div class="border border-gray-100 rounded-2xl p-4 bg-slate-50/50 hover:bg-white hover:shadow-sm transition">
                <div class="flex justify-between items-start mb-2">
                    <div>
                        <h5 class="font-bold text-gray-800 text-sm">${rev.room_name || 'Room Stay'}</h5>
                        <div class="flex items-center gap-1 text-xs mt-0.5">${stars} <span class="font-bold text-gray-700 text-xs ml-1">${parseFloat(rev.rating).toFixed(1)}</span></div>
                    </div>
                    <span class="text-[10px] text-gray-400">${dateFmt}</span>
                </div>
                <p class="text-xs text-gray-600 leading-relaxed italic">"${rev.comment}"</p>
                ${rev.admin_reply ? `
                    <div class="mt-3 p-3 bg-amber-50/70 border-l-4 border-amber-400 rounded-r-xl">
                        <div class="text-[11px] font-bold text-amber-800 mb-0.5"><i class="fas fa-reply mr-1"></i> Inn Host Reply:</div>
                        <p class="text-xs text-gray-700">${rev.admin_reply}</p>
                    </div>
                ` : ''}
            </div>
        `;
    }).join('');
}

/**
 * Open Review Modal
 */
function openReviewModal(roomId, roomTitle) {
    document.getElementById('review-room-id').value = roomId;
    document.getElementById('review-modal-room-title').innerText = `Reviewing: ${roomTitle}`;
    document.getElementById('review-comment').value = '';
    setModalRating(5);

    const modal = document.getElementById('review-modal');
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
}

function closeReviewModal() {
    const modal = document.getElementById('review-modal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
}

function setModalRating(rating) {
    document.getElementById('review-rating-val').value = rating;
    const stars = document.querySelectorAll('#star-rating-picker i');
    stars.forEach((star, index) => {
        if (index < rating) {
            star.className = 'fas fa-star text-amber-400';
        } else {
            star.className = 'far fa-star text-gray-300';
        }
    });

    const label = document.getElementById('rating-text-label');
    const descriptions = {
        1: '1.0 - Poor Experience',
        2: '2.0 - Below Expectations',
        3: '3.0 - Decent Stay',
        4: '4.0 - Very Good Stay',
        5: '5.0 - Excellent Experience!'
    };
    if (label) label.innerText = descriptions[rating] || `${rating}.0`;
}

function submitReviewFromModal(e) {
    e.preventDefault();
    const roomId = document.getElementById('review-room-id').value;
    const rating = document.getElementById('review-rating-val').value;
    const comment = document.getElementById('review-comment').value.trim();
    const currentUser = (window.appState && window.appState.currentUser) ? window.appState.currentUser : {};

    fetch('../actions/profile_action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            action: 'submit_review',
            user_id: currentUser.id,
            room_id: roomId,
            rating: rating,
            comment: comment
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            if (typeof showToast === 'function') {
                showToast('Thank you for your review!', 'success');
            } else {
                alert('Thank you for your review!');
            }
            closeReviewModal();
            setTimeout(() => window.location.reload(), 1200);
        } else {
            if (typeof showToast === 'function') {
                showToast(data.message || 'Failed to submit review', 'error');
            } else {
                alert(data.message || 'Failed to submit review');
            }
        }
    })
    .catch(err => {
        console.error('Review submit error:', err);
        if (typeof showToast === 'function') {
            showToast('Network error while submitting review', 'error');
        } else {
            alert('Network error while submitting review');
        }
    });
}

/**
 * Open Support / Feedback Modal
 */
function openReportModal(bookingId = null, roomName = null) {
    const bIdInput = document.getElementById('report-booking-id');
    const subjInput = document.getElementById('report-subject');
    const msgInput = document.getElementById('report-message');

    if (bIdInput) bIdInput.value = bookingId || '';
    if (subjInput) {
        subjInput.value = (roomName && roomName !== 'undefined') ? `Inquiry regarding ${roomName}` : '';
    }
    if (msgInput) msgInput.value = '';

    const modal = document.getElementById('report-modal');
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
}

function closeReportModal() {
    const modal = document.getElementById('report-modal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
}

function submitReport(e) {
    e.preventDefault();
    const bookingId = document.getElementById('report-booking-id').value;
    const type = document.getElementById('report-type').value;
    const subject = document.getElementById('report-subject').value.trim();
    const message = document.getElementById('report-message').value.trim();
    const currentUser = (window.appState && window.appState.currentUser) ? window.appState.currentUser : {};

    fetch('../actions/reports_action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            user_id: currentUser.id,
            booking_id: bookingId ? bookingId : null,
            type: type,
            subject: subject,
            message: message
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            if (typeof showToast === 'function') {
                showToast('Feedback submitted successfully! Our team will get back to you shortly.', 'success');
            } else {
                alert('Feedback submitted successfully! Our team will get back to you shortly.');
            }
            closeReportModal();
            setTimeout(() => window.location.reload(), 1500);
        } else {
            if (typeof showToast === 'function') {
                showToast('Error: ' + data.message, 'error');
            } else {
                alert('Error: ' + data.message);
            }
        }
    })
    .catch(err => {
        console.error('Report submission error:', err);
        if (typeof showToast === 'function') {
            showToast('Network error while submitting ticket', 'error');
        } else {
            alert('Network error while submitting ticket');
        }
    });
}

/**
 * Logout Helper
 */
function logout() {
    const role = (window.appState && window.appState.currentUser) ? window.appState.currentUser.role : 'user';
    if (window.appState) {
        window.appState.currentUser = null;
    }
    sessionStorage.removeItem('w_currentUser');
    window.location.href = '../logout.php?type=' + role;
}

