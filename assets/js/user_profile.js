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

    // Update Profile Completeness & Header Stats
    updateCompletenessMeter();
    updateProfileStats();

    // Render Tab Components
    renderOverviewTab();
    renderBookingsList();
    renderFeedbackList();
    renderReviewsList();
}

/**
 * Dynamically computes and updates Profile Header Stats (Total Spent, Upcoming Stays, Total Reservations)
 */
function updateProfileStats() {
    const bookings = (window.appState && Array.isArray(window.appState.bookings)) ? window.appState.bookings : [];
    const todayStr = new Date().toISOString().substring(0, 10);

    let totalSpent = 0.0;
    let upcomingCount = 0;
    let totalReservations = bookings.length;

    bookings.forEach(b => {
        const bStatus = String(b.bookingStatus || b.booking_status || b.status || 'pending_verification').toLowerCase();
        const legStatus = String(b.status || '').toLowerCase();
        const checkIn = b.checkIn || b.check_in_date || b.check_in || '';
        const checkOut = b.checkOut || b.check_out_date || b.check_out || '';

        const isCancelled = ['cancelled_expired', 'cancelled', 'no_show'].includes(bStatus) || legStatus === 'cancelled';
        const isCompleted = bStatus === 'completed' || (legStatus === 'confirmed' && checkOut && checkOut < todayStr);

        if (!isCancelled) {
            if (!isCompleted && checkIn && checkIn > todayStr) {
                upcomingCount++;
            }

            const total = parseFloat(b.total !== undefined ? b.total : (b.total_amount || b.total_price || 0));
            const paid = parseFloat(b.paidAmount !== undefined ? b.paidAmount : (b.paid_amount || 0));

            if (paid > 0) {
                totalSpent += paid;
            } else if (bStatus === 'confirmed' || bStatus === 'completed' || legStatus === 'confirmed') {
                totalSpent += total;
            } else if (bStatus === 'deposit_paid') {
                totalSpent += Math.round(total * 0.20 * 100) / 100;
            }
        }
    });

    const totalSpentEl = document.getElementById('total-spent-display');
    if (totalSpentEl) {
        totalSpentEl.textContent = `₱${totalSpent.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
    }

    const upcomingEl = document.getElementById('upcoming-stays-display');
    if (upcomingEl) {
        upcomingEl.textContent = upcomingCount;
    }

    const totalResEl = document.getElementById('total-reservations-display');
    if (totalResEl) {
        totalResEl.textContent = totalReservations;
    }
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
        if (tabId === 'overview') {
            renderOverviewTab();
        } else if (tabId === 'bookings') {
            renderBookingsList();
        }
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
 * Date and Time Formatting Helpers for Stays & Anti-No-Show Warnings
 */
function getLocalDateStr(d = new Date()) {
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

function formatTime12H(timeStr) {
    if (!timeStr) return '2:00 PM';
    const clean = String(timeStr).trim();
    const parts = clean.split(':');
    let hour = parseInt(parts[0], 10);
    const min = parts[1] ? parts[1].substring(0, 2) : '00';
    if (isNaN(hour)) return timeStr;
    const ampm = hour >= 12 ? 'PM' : 'AM';
    hour = hour % 12;
    hour = hour ? hour : 12;
    return `${hour}:${min} ${ampm}`;
}

function getFormattedDateWithDay(dateStr) {
    if (!dateStr) return '-';
    const parts = String(dateStr).split('-');
    if (parts.length < 3) return dateStr;
    const d = new Date(parseInt(parts[0], 10), parseInt(parts[1], 10) - 1, parseInt(parts[2], 10));
    return d.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' });
}

/**
 * Returns a full accurate datetime string combining a date string (YYYY-MM-DD)
 * and a time string (HH:MM or HH:MM:SS) for display.
 * Example: "Thu, Oct 1, 2026 · 2:00 PM"
 *
 * @param {string} dateStr  - Date in YYYY-MM-DD format.
 * @param {string} timeStr  - Time in HH:MM or HH:MM:SS format (24-hour).
 * @returns {string} Formatted datetime string.
 */
function getFormattedDateTimeStr(dateStr, timeStr) {
    if (!dateStr) return '-';
    const parts = String(dateStr).split('-');
    if (parts.length < 3) return dateStr;
    const timeParts = (timeStr || '00:00:00').split(':');
    const hour = parseInt(timeParts[0], 10) || 0;
    const min  = parseInt(timeParts[1], 10) || 0;

    const d = new Date(
        parseInt(parts[0], 10),
        parseInt(parts[1], 10) - 1,
        parseInt(parts[2], 10),
        hour,
        min,
        0
    );

    const datePart = d.toLocaleDateString('en-US', {
        weekday: 'short', month: 'short', day: 'numeric', year: 'numeric'
    });
    // Zero-pad minutes
    const minStr  = String(min).padStart(2, '0');
    const ampm    = hour >= 12 ? 'PM' : 'AM';
    const hour12  = hour % 12 === 0 ? 12 : hour % 12;
    const timePart = `${hour12}:${minStr} ${ampm}`;

    return `${datePart} · ${timePart}`;
}

function getRelativeDayLabel(dateStr) {
    if (!dateStr) return '';
    const todayStr = getLocalDateStr();
    if (dateStr === todayStr) return 'Today (Check-in Day)';
    
    const parts = String(dateStr).split('-');
    if (parts.length < 3) return '';
    const target = new Date(parseInt(parts[0], 10), parseInt(parts[1], 10) - 1, parseInt(parts[2], 10));
    const now = new Date();
    const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
    
    const diffDays = Math.round((target.getTime() - today.getTime()) / (1000 * 60 * 60 * 24));
    if (diffDays === 1) return 'Tomorrow (1 Day After Book)';
    if (diffDays === -1) return 'Yesterday';
    if (diffDays > 1) return `In ${diffDays} Days`;
    if (diffDays < -1) return `${Math.abs(diffDays)} Days Ago`;
    return 'Today (Check-in Day)';
}


/**
 * Render Overview Tab (Next Upcoming Stay or Recent Activity)
 */
function renderOverviewTab(selectedBookingId = null) {
    const container = document.getElementById('next-stay-container');
    if (!container) return;

    const bookings = (window.appState && window.appState.bookings) ? window.appState.bookings : [];
    const today = getLocalDateStr();

    // Active upcoming bookings:
    // Any booking not cancelled, not expired, not marked no-show, not completed
    // where checkOut >= today OR checkIn >= today
    const activeBookings = bookings.filter(b => {
        const bStatus = (b.bookingStatus || '').toLowerCase();
        const status = (b.status || '').toLowerCase();
        const isCancelled = status === 'cancelled' || bStatus === 'cancelled_expired' || bStatus === 'no_show' || bStatus === 'completed';
        if (isCancelled) return false;

        const checkOut = b.checkOut || '';
        const checkIn = b.checkIn || '';
        
        // If check-in is today, only count as upcoming if it hasn't passed 2:00 PM
        if (checkIn === today && new Date().getHours() >= 14) {
            return false;
        }

        return checkOut >= today || checkIn >= today;
    });

    // Sort upcoming stays by check-in date ascending (soonest stay first)
    activeBookings.sort((a, b) => new Date(a.checkIn || 0) - new Date(b.checkIn || 0));

    let upcoming = null;
    if (selectedBookingId) {
        upcoming = activeBookings.find(b => b.id == selectedBookingId) || activeBookings[0] || null;
    } else {
        upcoming = activeBookings[0] || null;
    }

    if (upcoming) {
        const formattedCheckIn   = getFormattedDateWithDay(upcoming.checkIn);
        const formattedCheckOut  = getFormattedDateWithDay(upcoming.checkOut);
        // Full accurate datetime strings: check-in uses the actual arrival time,
        // check-out is always at 12:00 PM per hostel policy.
        const checkInTime        = upcoming.expectedArrivalTime || '14:00:00';
        const formattedCheckInDT = getFormattedDateTimeStr(upcoming.checkIn, checkInTime);
        const formattedCheckOutDT = getFormattedDateTimeStr(upcoming.checkOut, '12:00:00');
        const relativeDay   = getRelativeDayLabel(upcoming.checkIn);
        const arrivalTime12H = formatTime12H(checkInTime);

        const total = parseFloat(upcoming.total || 0);
        const paid = parseFloat(upcoming.paidAmount !== undefined ? upcoming.paidAmount : (upcoming.status === 'Confirmed' ? total : 0));
        const balance = parseFloat(upcoming.balanceDue !== undefined ? upcoming.balanceDue : (total - paid));
        const safeRoomName = (upcoming.roomName || 'Room').replace(/'/g, "\\'");
        const bStatus = (upcoming.bookingStatus || (upcoming.status === 'Confirmed' ? 'confirmed' : 'pending_verification')).toLowerCase();

        // Status Badge
        let statusBadgeHtml = '';
        if (bStatus === 'confirmed') {
            statusBadgeHtml = `<span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200"><i class="fas fa-check-circle text-emerald-600"></i> Confirmed &amp; Guaranteed</span>`;
        } else if (bStatus === 'deposit_paid') {
            statusBadgeHtml = `<span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800 border border-blue-200"><i class="fas fa-shield-alt text-blue-600"></i> 20% Deposit Paid (No-Show Protected)</span>`;
        } else if (bStatus === 'pending_arrival') {
            statusBadgeHtml = `<span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-purple-100 text-purple-800 border border-purple-200"><i class="fas fa-store text-purple-600"></i> Pay at Hotel (Pending Arrival)</span>`;
        } else {
            statusBadgeHtml = `<span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200"><i class="fas fa-stopwatch text-amber-600"></i> Pending GCash Verification</span>`;
        }

        // Multi-booking quick switcher if guest has more than 1 upcoming reservation
        let multiBookingSwitcher = '';
        if (activeBookings.length > 1) {
            multiBookingSwitcher = `
                <div class="mb-5 pb-4 border-b border-gray-100 flex flex-wrap items-center justify-between gap-3">
                    <span class="text-xs font-bold text-gray-700 flex items-center gap-1.5">
                        <i class="fas fa-layer-group text-brand-blue"></i> You have <strong>${activeBookings.length}</strong> upcoming reservations:
                    </span>
                    <div class="flex flex-wrap gap-1.5">
                        ${activeBookings.map((ab, idx) => `
                            <button onclick="renderOverviewTab(${ab.id})" class="px-2.5 py-1 rounded-lg text-xs font-bold transition ${ab.id === upcoming.id ? 'bg-brand-blue text-white shadow-xs' : 'bg-slate-100 text-gray-600 hover:bg-slate-200'}">
                                Stay #${idx + 1}: ${ab.roomName.substring(0, 15)} (${ab.checkIn})
                            </button>
                        `).join('')}
                    </div>
                </div>
            `;
        }

        container.innerHTML = `
            ${multiBookingSwitcher}
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-6 border-b border-gray-100 mb-6">
                <div>
                    <div class="flex flex-wrap items-center gap-2 mb-1.5">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-teal-50 text-teal-800 border border-teal-200">
                            <i class="fas fa-calendar-check text-teal-600"></i> Next Upcoming Stay
                        </span>
                        ${statusBadgeHtml}
                    </div>
                    <h3 class="text-2xl sm:text-3xl font-bold text-gray-800 tracking-tight">${upcoming.roomName}</h3>
                    <p class="text-xs text-gray-500 mt-1 flex flex-wrap items-center gap-x-3 gap-y-1">
                        <span>Booking Ref: <strong class="font-mono text-gray-700">${upcoming.refId || ('WIH-' + upcoming.id)}</strong></span>
                        ${upcoming.dateBooked ? `<span>• Booked: <strong class="text-gray-600">${upcoming.dateBooked.substring(0, 10)}</strong></span>` : ''}
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <button onclick="openVoucherModal(${upcoming.id})" class="bg-gradient-to-r from-brand-blue to-teal-500 hover:from-sky-600 hover:to-teal-600 text-white font-bold text-xs px-4 py-2.5 rounded-xl shadow-md hover:shadow-lg transition flex items-center gap-2 cursor-pointer">
                        <i class="fas fa-ticket-alt"></i> View Voucher
                    </button>
                    <button onclick="openInvoiceModal(${upcoming.id})" class="bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 font-bold text-xs px-4 py-2.5 rounded-xl shadow-xs hover:shadow transition flex items-center gap-2 cursor-pointer">
                        <i class="fas fa-file-invoice-dollar text-emerald-600"></i> View Invoice
                    </button>
                    ${balance > 0 && bStatus !== 'cancelled_expired' && bStatus !== 'no_show' ? `
                        <button onclick="payBookingWithPayMongo(${upcoming.id}, '${upcoming.ref_id || upcoming.refId}')" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs px-4 py-2.5 rounded-xl shadow-md transition flex items-center gap-1.5 cursor-pointer" title="Settle balance online">
                            <i class="fas fa-bolt"></i> Pay Balance (₱${balance.toFixed(2)})
                        </button>
                    ` : ''}
                </div>
            </div>



            <!-- TIME, DAY, HOUR BOOKING SCHEDULE DETAILS GRID -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <!-- Check-in: Accurate Datetime -->
                <div class="bg-teal-50/70 p-4 rounded-2xl border border-teal-100 relative overflow-hidden sm:col-span-2">
                    <div class="flex justify-between items-start mb-1">
                        <span class="text-[10px] uppercase font-bold text-teal-600 block">Arrival Date &amp; Time</span>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-teal-100 text-teal-800">${relativeDay}</span>
                    </div>
                    <span class="font-bold text-teal-900 text-sm flex items-center gap-2 mt-1">
                        <i class="far fa-calendar-alt text-teal-600"></i>
                        <span class="font-mono">${formattedCheckInDT}</span>
                    </span>
                    <span class="text-[11px] text-teal-700 block mt-1">
                        <i class="fas fa-door-open text-teal-500 mr-1"></i>
                        Check-in opens at <strong>2:00 PM</strong> &mdash; your expected arrival: <strong>${arrivalTime12H}</strong>
                    </span>
                </div>

                <!-- Check-out: Accurate Datetime -->
                <div class="bg-amber-50/70 p-4 rounded-2xl border border-amber-100 sm:col-span-2">
                    <div class="flex justify-between items-start mb-1">
                        <span class="text-[10px] uppercase font-bold text-amber-600 block">Departure Date &amp; Time</span>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-amber-100 text-amber-800">${upcoming.nights} Night(s)</span>
                    </div>
                    <span class="font-bold text-amber-900 text-sm flex items-center gap-2 mt-1">
                        <i class="far fa-calendar-check text-amber-600"></i>
                        <span class="font-mono">${formattedCheckOutDT}</span>
                    </span>
                    <span class="text-[11px] text-amber-700 block mt-1">
                        <i class="fas fa-door-closed text-amber-500 mr-1"></i>
                        Standard check-out: <strong>12:00 PM</strong> &mdash; please vacate by this time
                    </span>
                </div>

                <!-- Duration & Payment Status -->
                <div class="bg-slate-50 p-4 rounded-2xl border border-gray-100 sm:col-span-2">
                    <span class="text-[10px] uppercase font-bold text-gray-400 block mb-1">Stay Duration &amp; Bill</span>
                    <span class="font-bold text-brand-blue text-sm flex items-center gap-2">
                        <i class="fas fa-moon text-indigo-400"></i> ${upcoming.nights} Night(s) • ₱${total.toFixed(2)}
                    </span>
                    <span class="text-[11px] ${balance > 0 ? 'text-amber-700 font-semibold' : 'text-emerald-600 font-semibold'} block mt-1">
                        ${balance > 0 ? `₱${paid.toFixed(2)} Paid • ₱${balance.toFixed(2)} Due at Desk` : `<i class="fas fa-check-circle"></i> 100% Fully Settled`}
                    </span>
                </div>

                <!-- Booking Reference -->
                <div class="bg-slate-50 p-4 rounded-2xl border border-gray-100 sm:col-span-2">
                    <span class="text-[10px] uppercase font-bold text-gray-400 block mb-1">Booking Reference &amp; Code</span>
                    <span class="font-bold text-gray-800 text-sm flex items-center gap-2 font-mono">
                        <i class="fas fa-hashtag text-brand-blue"></i> ${upcoming.refId || ('WIH-' + upcoming.id)}
                    </span>
                    <span class="text-[11px] text-gray-500 block mt-1">
                        <i class="fas fa-info-circle text-gray-400 mr-1"></i>
                        Present this reference at the front desk upon arrival
                    </span>
                </div>
            </div>

            <!-- ANTI-NO-SHOW WARNING & REMINDER BOX -->
            <div class="bg-amber-50/90 border border-amber-200/90 rounded-2xl p-4 sm:p-5 flex flex-col md:flex-row items-start md:items-center justify-between gap-4 mb-4">
                <div class="flex items-start gap-3.5">
                    <div class="w-10 h-10 rounded-xl bg-amber-500/20 text-amber-700 flex items-center justify-center text-lg flex-shrink-0 mt-0.5">
                        <i class="fas fa-exclamation-triangle text-amber-600"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h5 class="text-xs sm:text-sm font-bold text-amber-950">Arrive On Time to Avoid No-Show Cancellation</h5>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-200 text-amber-900 uppercase">Policy Notice</span>
                        </div>
                        <p class="text-xs text-amber-900/80 mt-1 leading-relaxed">
                            Check-in starts at <strong>2:00 PM</strong> (your expected arrival: <strong>${arrivalTime12H}</strong>). Under hotel policy, unconfirmed late arrivals may be released to waitlisted guests as a no-show. If your flight or boat is delayed, please inform the front desk immediately.
                        </p>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-2 w-full md:w-auto flex-shrink-0">
                    <a href="tel:+639456882266" class="w-full sm:w-auto text-center px-4 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs shadow-xs transition flex items-center justify-center gap-2">
                        <i class="fas fa-phone-alt"></i> Call Front Desk
                    </a>
                    <button onclick="openBookingReportsModal(${upcoming.id})" class="w-full sm:w-auto text-center px-4 py-2.5 rounded-xl bg-white hover:bg-brand-blue/10 border border-brand-blue/30 text-brand-blue font-bold text-xs transition flex items-center justify-center gap-1.5 shadow-xs">
                        <i class="fas fa-comments"></i> Messages & Reports
                    </button>
                    <button onclick="openLateArrivalModal(${upcoming.id})" class="w-full sm:w-auto text-center px-4 py-2.5 rounded-xl bg-white hover:bg-amber-100/80 border border-amber-300 text-amber-950 font-bold text-xs transition flex items-center justify-center gap-1.5 shadow-xs">
                        <i class="fas fa-clock text-amber-600"></i> Report Late Arrival
                    </button>
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3 pt-2 text-xs">
                <span class="text-gray-500"><i class="fas fa-map-pin text-rose-500 mr-1"></i> W Island Hub Main Lodge, Island Beachway, Philippines</span>
                <div class="flex gap-2">
                    <button onclick="switchTab('bookings')" class="text-brand-blue hover:underline font-bold">
                        View All Reservations &rarr;
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
    const today = getLocalDateStr();

    // Filter by status
    let filtered = allBookings;
    if (activeBookingFilter !== 'all') {
        const filterLower = activeBookingFilter.toLowerCase();
        if (filterLower === 'upcoming') {
            filtered = filtered.filter(b => {
                const bStatus = (b.bookingStatus || '').toLowerCase();
                const status = (b.status || '').toLowerCase();
                const isCancelled = status === 'cancelled' || bStatus === 'cancelled_expired' || bStatus === 'no_show';
                const checkInDate = b.checkIn || '';
                if (isCancelled) return false;
                
                // If strictly future date
                if (checkInDate > today) return true;
                
                // If it is today, count as upcoming only if before 2:00 PM
                if (checkInDate === today && new Date().getHours() < 14) return true;
                
                return false;
            });
        } else if (filterLower === 'confirmed') {
            filtered = filtered.filter(b => {
                const bStatus = (b.bookingStatus || '').toLowerCase();
                const status = (b.status || '').toLowerCase();
                return status === 'confirmed' || bStatus === 'confirmed' || bStatus === 'deposit_paid';
            });
        } else if (filterLower === 'pending') {
            filtered = filtered.filter(b => {
                const bStatus = (b.bookingStatus || '').toLowerCase();
                const status = (b.status || '').toLowerCase();
                return (status === 'pending' || bStatus === 'pending_arrival' || bStatus === 'pending_verification') && bStatus !== 'cancelled_expired' && bStatus !== 'no_show';
            });
        } else if (filterLower === 'cancelled') {
            filtered = filtered.filter(b => {
                const bStatus = (b.bookingStatus || '').toLowerCase();
                const status = (b.status || '').toLowerCase();
                return status === 'cancelled' || bStatus === 'cancelled_expired' || bStatus === 'no_show';
            });
        } else {
            filtered = filtered.filter(b => (b.status || '').toLowerCase() === filterLower);
        }
    }

    // Filter by search query
    if (currentSearchQuery) {
        filtered = filtered.filter(b => {
            const roomMatch = (b.roomName || '').toLowerCase().includes(currentSearchQuery);
            const refMatch = (b.refId || '').toLowerCase().includes(currentSearchQuery) || (b.ref_id || '').toLowerCase().includes(currentSearchQuery);
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
            statusBadge = `<span class="px-2.5 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800 border border-blue-200"><i class="fas fa-shield-alt text-[10px]"></i> 20% Deposit Paid (Balance Due at Desk)</span>`;
        } else if (bStatus === 'pending_arrival') {
            statusBadge = `<span class="px-2.5 py-1 rounded-full text-xs font-bold bg-purple-100 text-purple-800 border border-purple-200"><i class="fas fa-store text-[10px]"></i> Pending Arrival (Pay at Hotel)</span>`;
            if (b.expectedArrivalTime) {
                timerNotice = `<span class="text-[10px] text-purple-700 font-semibold block mt-0.5">Expected arrival: ${formatTime12H(b.expectedArrivalTime)}</span>`;
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

        const checkInFmt = b.checkIn ? getFormattedDateWithDay(b.checkIn) : '-';
        const checkOutFmt = b.checkOut ? getFormattedDateWithDay(b.checkOut) : '-';
        const safeRoomName = (b.roomName || 'Room').replace(/'/g, "\\'");
        const total = parseFloat(b.total || 0);
        const paid = parseFloat(b.paidAmount !== undefined ? b.paidAmount : (b.status === 'Confirmed' ? total : 0));
        const balance = parseFloat(b.balanceDue !== undefined ? b.balanceDue : (total - paid));
        const arrivalTime12H = formatTime12H(b.expectedArrivalTime || '14:00:00');
        const relativeDay = b.checkIn ? getRelativeDayLabel(b.checkIn) : '';

        // Only show upcoming reminder/actions when check-in date is strictly AFTER today
        // (same-day bookings = guest should already be at the hotel, so no reminder needed)
        const isUpcomingActive = b.status !== 'Cancelled' && bStatus !== 'cancelled_expired' && bStatus !== 'no_show' && bStatus !== 'completed' && ((b.checkIn || '') > today);

        // Review Button for Completed Stays Only
        let reviewBtn = '';
        const isPastStay = (b.checkOut && b.checkOut <= today) || bStatus === 'completed';
        const isEligibleBooking = (b.status === 'Confirmed' || bStatus === 'confirmed' || bStatus === 'completed' || bStatus === 'deposit_paid') && bStatus !== 'cancelled_expired' && bStatus !== 'no_show';

        if (b.roomId && isEligibleBooking) {
            const userReviews = (window.appState && Array.isArray(window.appState.reviews)) ? window.appState.reviews : [];
            const alreadyReviewed = userReviews.some(r => String(r.room_id) === String(b.roomId));

            if (alreadyReviewed) {
                reviewBtn = `
                    <span class="px-3 py-1.5 rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-700 text-xs font-bold inline-flex items-center gap-1.5" title="You have reviewed this room">
                        <i class="fas fa-check-circle text-emerald-500"></i> Reviewed
                    </span>
                `;
            } else if (isPastStay) {
                reviewBtn = `
                    <button onclick="openReviewModal(${b.roomId}, '${safeRoomName}')" class="px-3 py-1.5 rounded-xl border border-amber-300 bg-amber-50 hover:bg-amber-100 text-amber-800 text-xs font-bold transition flex items-center gap-1.5 shadow-xs" title="Share your stay experience">
                        <i class="fas fa-star text-amber-500"></i> Write Review
                    </button>
                `;
            }
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
                        ${isUpcomingActive ? `
                            <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-purple-50 text-purple-800 border border-purple-200 mt-2">
                                <i class="fas fa-clock text-purple-600"></i> Check-in: <strong>${arrivalTime12H}</strong> • ${relativeDay} <span class="text-[11px] text-purple-600 font-normal">(Arrive on time to avoid no-show)</span>
                            </div>
                        ` : ''}
                        <div class="flex flex-wrap items-center gap-3 text-[11px] text-gray-400 mt-1">
                            <span>Ref: <strong class="font-mono text-gray-600">${b.ref_id || b.refId || ('WIH-' + b.id)}</strong></span>
                            ${b.gcashRefNo ? `<span>• GCash Ref: <strong class="font-mono text-gray-600">${b.gcashRefNo}</strong></span>` : ''}
                            ${b.paymongoPaymentId ? `<span>• PayMongo: <strong class="font-mono text-emerald-600">${b.paymongoPaymentId}</strong></span>` : ''}
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
                        ${balance > 0 && bStatus !== 'cancelled_expired' && bStatus !== 'no_show' ? `
                            <button onclick="payBookingWithPayMongo(${b.id}, '${b.ref_id || b.refId}')" class="px-3 py-1.5 rounded-xl border border-emerald-300 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 text-xs font-bold transition flex items-center gap-1.5 shadow-xs" title="Pay balance now with Card, GCash, or Maya via PayMongo">
                                <i class="fas fa-bolt text-emerald-600"></i> Pay Online
                            </button>
                        ` : ''}
                        <button onclick="openVoucherModal(${b.id})" class="px-3 py-1.5 rounded-xl border border-teal-200 bg-teal-50 hover:bg-teal-100 text-teal-800 text-xs font-bold transition flex items-center gap-1.5 shadow-2xs cursor-pointer" title="View Stay Pass & Voucher">
                            <i class="fas fa-ticket-alt text-teal-600"></i> Voucher
                        </button>
                        <button onclick="openInvoiceModal(${b.id})" class="px-3 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold transition flex items-center gap-1.5 shadow-2xs cursor-pointer" title="View Official Folio Invoice / Receipt">
                            <i class="fas fa-file-invoice-dollar text-emerald-600"></i> Invoice
                        </button>
                        ${isUpcomingActive ? `
                            <button onclick="openLateArrivalModal(${b.id})" class="px-3 py-1.5 rounded-xl border border-amber-300 bg-amber-50 hover:bg-amber-100 text-amber-900 text-xs font-bold transition flex items-center gap-1.5 shadow-xs cursor-pointer" title="Report Delay / Late Check-in">
                                <i class="fas fa-clock text-amber-600"></i> Report Delay
                            </button>
                        ` : ''}
                        ${reviewBtn}
                        <button onclick="openBookingReportsModal(${b.id})" class="px-3 py-1.5 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-brand-blue hover:text-sky-600 text-xs font-bold transition cursor-pointer" title="Messages & Reports">
                            <i class="fas fa-comments"></i>
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
    if (typeof window.showVoucherModal === 'function') {
        const booking = ((window.appState && window.appState.bookings) ? window.appState.bookings : []).find(b => String(b.id) === String(bookingId) || String(b.ref_id) === String(bookingId) || String(b.refId) === String(bookingId));
        if (booking) {
            window.showVoucherModal(booking);
            return;
        }
    }
    if (typeof window.fetchBookingAndShow === 'function') {
        window.fetchBookingAndShow(bookingId, 'voucher');
    }
}

/**
 * Open Official Folio Invoice / Receipt Modal
 */
function openInvoiceModal(bookingId) {
    if (typeof window.showInvoiceModal === 'function') {
        const booking = ((window.appState && window.appState.bookings) ? window.appState.bookings : []).find(b => String(b.id) === String(bookingId) || String(b.ref_id) === String(bookingId) || String(b.refId) === String(bookingId));
        if (booking) {
            window.showInvoiceModal(booking);
            return;
        }
    }
    if (typeof window.fetchBookingAndShow === 'function') {
        window.fetchBookingAndShow(bookingId, 'invoice');
    }
}

function closeVoucherModal() {
    const modal = document.getElementById('voucher-modal-wrapper');
    if (modal) modal.remove();
}

function closeInvoiceModal() {
    const modal = document.getElementById('invoice-modal-wrapper');
    if (modal) modal.remove();
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

    const allReports = (window.appState && Array.isArray(window.appState.reports)) ? window.appState.reports : [];
    
    // Hide booking-specific reports and late arrivals from the general support inquiries tab
    const reports = allReports.filter(r => !r.booking_id && r.type !== 'late_arrival');

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
        const isResolved = r.status === 'Resolved';
        const statusClass = isResolved ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800';
        const reply = r.admin_response || r.admin_reply || '';

        return `
            <div class="border border-gray-100 rounded-2xl p-4 bg-slate-50/50 hover:bg-white hover:shadow-sm transition">
                <div class="flex justify-between items-start mb-1.5">
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">${r.type || 'Inquiry'}</span>
                        <h5 class="font-bold text-gray-800 text-sm">${r.subject || 'General Inquiry'}</h5>
                    </div>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold ${statusClass}">${r.status || 'Pending'}</span>
                </div>
                <p class="text-xs text-gray-600 mb-2 leading-relaxed">${r.message}</p>
                <div class="text-[10px] text-gray-400 flex items-center justify-between">
                    <span><i class="far fa-clock mr-1"></i> ${dateFmt}</span>
                </div>
                ${reply ? `
                    <div class="mt-3 p-3 bg-blue-50/80 border-l-4 border-brand-blue rounded-r-xl">
                        <div class="text-[11px] font-bold text-brand-blue mb-1 flex items-center gap-1.5">
                            <i class="fas fa-reply"></i> Response from Inn Staff:
                        </div>
                        <p class="text-xs text-gray-700">${reply}</p>
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

    const reviews = (window.appState && Array.isArray(window.appState.reviews)) ? window.appState.reviews : [];

    if (reviews.length === 0) {
        container.innerHTML = `
            <div class="text-center py-8 text-gray-400 bg-slate-50 rounded-2xl border border-dashed border-gray-200">
                <i class="far fa-star text-3xl text-gray-300 mb-2"></i>
                <p class="text-xs text-gray-600 font-medium">You haven't written any room reviews yet.</p>
                <p class="text-[11px] text-gray-400 mt-1">Review your room after completing your stay to help other travelers!</p>
            </div>
        `;
        return;
    }

    container.innerHTML = reviews.map(rev => {
        const rating = Math.max(1, Math.min(5, Math.round(parseFloat(rev.rating || 5))));
        let stars = '';
        for (let i = 0; i < 5; i++) {
            stars += `<i class="fas fa-star ${i < rating ? 'text-amber-400' : 'text-gray-200'}"></i>`;
        }

        const dateFmt = rev.created_at ? new Date(rev.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : 'Recently';
        const reply = rev.admin_reply || rev.admin_response || '';

        return `
            <div class="border border-gray-100 rounded-2xl p-4 bg-slate-50/50 hover:bg-white hover:shadow-sm transition">
                <div class="flex justify-between items-start mb-2">
                    <div>
                        <h5 class="font-bold text-gray-800 text-sm">${rev.room_name || 'Room Stay'}</h5>
                        <div class="flex items-center gap-1 text-xs mt-0.5">${stars} <span class="font-bold text-gray-700 text-xs ml-1">${parseFloat(rev.rating || 5).toFixed(1)}</span></div>
                    </div>
                    <span class="text-[10px] text-gray-400">${dateFmt}</span>
                </div>
                <p class="text-xs text-gray-600 leading-relaxed italic">"${rev.comment}"</p>
                ${reply ? `
                    <div class="mt-3 p-3 bg-amber-50/70 border-l-4 border-amber-400 rounded-r-xl">
                        <div class="text-[11px] font-bold text-amber-800 mb-0.5"><i class="fas fa-reply mr-1"></i> Inn Host Reply:</div>
                        <p class="text-xs text-gray-700">${reply}</p>
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
    const roomIdInput = document.getElementById('review-room-id');
    const roomTitleSpan = document.getElementById('review-modal-room-title');
    const commentInput = document.getElementById('review-comment');

    if (roomIdInput) roomIdInput.value = roomId;
    if (roomTitleSpan) roomTitleSpan.innerText = `Reviewing: ${roomTitle}`;
    if (commentInput) commentInput.value = '';
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
    const form = document.getElementById('review-form');
    if (form) form.reset();
}

function setModalRating(rating) {
    const ratingInput = document.getElementById('review-rating-val');
    if (ratingInput) ratingInput.value = rating;

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
    const roomIdInput = document.getElementById('review-room-id');
    const ratingInput = document.getElementById('review-rating-val');
    const commentInput = document.getElementById('review-comment');

    const roomId = roomIdInput ? roomIdInput.value : '';
    const rating = ratingInput ? ratingInput.value : '5';
    const comment = commentInput ? commentInput.value.trim() : '';
    const currentUser = (window.appState && window.appState.currentUser) ? window.appState.currentUser : {};

    if (!roomId) {
        if (typeof showToast === 'function') showToast('Please select a room to review.', 'error');
        else alert('Please select a room to review.');
        return;
    }

    if (!comment) {
        if (typeof showToast === 'function') showToast('Please write a brief comment sharing your experience.', 'error');
        else alert('Please write a brief comment sharing your experience.');
        return;
    }

    const submitBtn = document.querySelector('#review-form button[type="submit"]') || document.getElementById('review-submit-btn');
    const originalBtnText = submitBtn ? submitBtn.innerHTML : 'Post Review';
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Submitting...';
    }

    fetch('../actions/reviews_action.php?action=submit_review', {
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
                showToast(data.message || 'Thank you for your review!', 'success');
            } else {
                alert(data.message || 'Thank you for your review!');
            }

            // Find room name for immediate display in UI
            const booking = (window.appState && Array.isArray(window.appState.bookings))
                ? window.appState.bookings.find(b => b.roomId == roomId)
                : null;
            const roomName = booking ? (booking.roomName || 'Room Stay') : 'Room Stay';

            // Add new review to appState
            if (!window.appState.reviews) window.appState.reviews = [];
            window.appState.reviews.unshift({
                id: data.id || Date.now(),
                user_id: currentUser.id,
                room_id: roomId,
                room_name: roomName,
                rating: rating,
                comment: comment,
                status: 'approved',
                visible: 1,
                created_at: new Date().toISOString()
            });

            closeReviewModal();
            renderReviewsList();
            renderBookingsList();
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
    })
    .finally(() => {
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
        }
    });
}

/**
 * Open Dedicated Late Arrival Reporting Modal
 */
function openLateArrivalModal(bookingId = null) {
    const allBookings = (window.appState && Array.isArray(window.appState.bookings)) ? window.appState.bookings : [];
    let booking = null;
    if (bookingId) {
        booking = allBookings.find(b => b.id == bookingId);
    }
    if (!booking) {
        const today = getLocalDateStr();
        booking = allBookings.find(b => {
            const bStatus = (b.bookingStatus || '').toLowerCase();
            const status = (b.status || '').toLowerCase();
            return status !== 'cancelled' && bStatus !== 'cancelled_expired' && bStatus !== 'no_show' && ((b.checkOut || '') >= today || (b.checkIn || '') >= today);
        }) || allBookings[0] || null;
    }

    if (!booking) {
        if (typeof showToast === 'function') {
            showToast('No active reservation found to report late arrival.', 'warning');
        } else {
            alert('No active reservation found to report late arrival.');
        }
        return;
    }

    const bIdInput = document.getElementById('late-arrival-booking-id');
    const roomNameEl = document.getElementById('late-modal-room-name');
    const refIdEl = document.getElementById('late-modal-ref-id');
    const checkInDateEl = document.getElementById('late-modal-checkin-date');
    const currentTimeEl = document.getElementById('late-modal-current-time');
    const newTimeInput = document.getElementById('late-arrival-new-time');
    const phoneInput = document.getElementById('late-arrival-contact-phone');
    const notesInput = document.getElementById('late-arrival-notes');
    const reasonSelect = document.getElementById('late-arrival-reason');

    if (bIdInput) bIdInput.value = booking.id;
    if (roomNameEl) roomNameEl.textContent = booking.roomName || 'Island Room';
    if (refIdEl) refIdEl.textContent = `Ref: ${booking.ref_id || booking.refId || ('WIH-' + booking.id)}`;
    if (checkInDateEl) checkInDateEl.textContent = getFormattedDateWithDay(booking.checkIn);
    if (currentTimeEl) currentTimeEl.textContent = `Current: ${formatTime12H(booking.expectedArrivalTime || '14:00:00')}`;

    // Set default new arrival time to 2 hours after scheduled or 17:00
    if (newTimeInput) {
        const cur = (booking.expectedArrivalTime || '14:00:00').substring(0, 5);
        const parts = cur.split(':');
        let h = (parseInt(parts[0], 10) || 14) + 2;
        if (h > 23) h = 23;
        newTimeInput.value = `${String(h).padStart(2, '0')}:00`;
    }

    const user = (window.appState && window.appState.currentUser) ? window.appState.currentUser : {};
    if (phoneInput) {
        phoneInput.value = user.phone || booking.guestPhone || '';
    }

    if (notesInput) notesInput.value = '';
    if (reasonSelect) reasonSelect.selectedIndex = 0;

    const modal = document.getElementById('late-arrival-modal');
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
}

function closeLateArrivalModal() {
    const modal = document.getElementById('late-arrival-modal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
}

function submitLateArrival(e) {
    e.preventDefault();
    const bookingId = document.getElementById('late-arrival-booking-id').value;
    const newTime = document.getElementById('late-arrival-new-time').value;
    const reason = document.getElementById('late-arrival-reason').value;
    const phone = document.getElementById('late-arrival-contact-phone').value.trim();
    const notes = document.getElementById('late-arrival-notes').value.trim();
    const currentUser = (window.appState && window.appState.currentUser) ? window.appState.currentUser : {};

    if (!newTime) {
        if (typeof showToast === 'function') {
            showToast('Please select your new estimated arrival time.', 'warning');
        } else {
            alert('Please select your new estimated arrival time.');
        }
        return;
    }

    const allBookings = (window.appState && Array.isArray(window.appState.bookings)) ? window.appState.bookings : [];
    const booking = allBookings.find(b => b.id == bookingId) || {};
    const roomName = booking.roomName || 'Reservation';
    const refCode = booking.ref_id || booking.refId || ('WIH-' + booking.id);
    const newTime12H = formatTime12H(newTime);

    const fullMessage = [
        `=== LATE ARRIVAL NOTIFICATION ===`,
        `Reservation: ${roomName} (Ref: ${refCode})`,
        `Reason for Delay: ${reason}`,
        `New Estimated Arrival Time: ${newTime12H} (${newTime})`,
        `Contact Phone / WhatsApp: ${phone || 'Not provided'}`,
        notes ? `Guest Notes: ${notes}` : `Guest requested room hold extension due to travel delay.`
    ].join('\n');

    const submitBtn = document.getElementById('btn-submit-late-arrival');
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Submitting...';
    }

    fetch('../actions/reports_action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            user_id: currentUser.id,
            booking_id: bookingId ? parseInt(bookingId, 10) : null,
            type: 'late_arrival',
            subject: `Late Arrival: ${roomName} [ETA ${newTime12H}] (${refCode})`,
            message: fullMessage,
            new_arrival_time: newTime + (newTime.length === 5 ? ':00' : ''),
            contact_phone: phone
        })
    })
    .then(res => res.json())
    .then(data => {
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Send Arrival Notice';
        }

        if (data.success) {
            // Update local state so countdown and UI refresh immediately without page reload!
            if (booking) {
                booking.expectedArrivalTime = newTime + (newTime.length === 5 ? ':00' : '');
                if (phone) booking.guestPhone = phone;
            }

            if (typeof showToast === 'function') {
                showToast(`Late arrival recorded! Your new arrival time is set to ${newTime12H}. Room hold is maintained.`, 'success');
            } else {
                alert(`Late arrival recorded! Your new arrival time is set to ${newTime12H}. Room hold is maintained.`);
            }

            closeLateArrivalModal();

            // Refresh overview tab and bookings list with new arrival time
            renderOverviewTab(bookingId);
            renderBookingsList();
        } else {
            if (typeof showToast === 'function') {
                showToast('Error: ' + data.message, 'error');
            } else {
                alert('Error: ' + data.message);
            }
        }
    })
    .catch(err => {
        console.error('Late arrival submission error:', err);
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Send Arrival Notice';
        }
        if (typeof showToast === 'function') {
            showToast('Network error while reporting late arrival. Please call front desk.', 'error');
        } else {
            alert('Network error while reporting late arrival. Please call front desk directly.');
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
    localStorage.removeItem('w_currentUser');
    window.location.href = '../logout.php?type=' + role;
}

/**
 * Pay remaining balance for a booking online via PayMongo
 */
async function payBookingWithPayMongo(bookingId, refId) {
    if (typeof showToast === 'function') {
        showToast('Initiating secure PayMongo checkout...', 'info');
    }

    try {
        const formData = new FormData();
        formData.append('action', 'create_checkout');
        formData.append('booking_id', bookingId);
        formData.append('ref_id', refId);

        const res = await fetch('../actions/paymongo_action.php', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();

        if (data.success && data.checkout_url) {
            if (typeof showToast === 'function') {
                showToast('Redirecting to PayMongo Checkout...', 'success');
            }
            setTimeout(() => {
                window.location.href = data.checkout_url;
            }, 600);
        } else {
            alert(data.message || 'Unable to open PayMongo checkout.');
        }
    } catch (e) {
        console.error('PayMongo error:', e);
        alert('Network connection error. Please try again.');
    }
}

// Global window binding
window.payBookingWithPayMongo = payBookingWithPayMongo;

// Check for paymongo_success or payment_status in URL parameters
document.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('paymongo_success')) {
        const refId = urlParams.get('ref_id');
        if (typeof showToast === 'function') {
            showToast(`Payment Confirmed via PayMongo! Reservation Ref: ${refId || ''}`, 'success');
        }
        if (typeof switchTab === 'function') {
            switchTab('bookings');
        }
        // Auto-open voucher if booking found
        if (refId && window.appState && window.appState.bookings) {
            const b = window.appState.bookings.find(item => (item.ref_id === refId || item.refId === refId));
            if (b) {
                setTimeout(() => {
                    openVoucherModal(b.id);
                }, 400);
            }
        }
        // Clean URL
        window.history.replaceState({}, document.title, window.location.pathname);
    }
});




/**
 * Booking Messages & Reports Modal Logic
 */
function openBookingReportsModal(bookingId) {
    const allBookings = (window.appState && Array.isArray(window.appState.bookings)) ? window.appState.bookings : [];
    const booking = allBookings.find(b => b.id == bookingId);
    if (!booking) return;

    const roomName = booking.roomName || 'Reservation';
    const refCode = booking.ref_id || booking.refId || ('WIH-' + booking.id);
    
    document.getElementById('br-booking-id').value = bookingId;
    document.getElementById('br-modal-room-name').textContent = `${roomName} (Ref: ${refCode})`;
    
    // Reset form
    document.getElementById('br-report-type').value = 'complaint';
    document.getElementById('br-report-subject').value = '';
    document.getElementById('br-report-message').value = '';

    // Render Messages
    renderBookingMessages(bookingId);

    const modal = document.getElementById('booking-reports-modal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeBookingReportsModal() {
    const modal = document.getElementById('booking-reports-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function renderBookingMessages(bookingId) {
    const container = document.getElementById('br-messages-container');
    const allReports = (window.appState && Array.isArray(window.appState.reports)) ? window.appState.reports : [];
    
    // Filter reports for this specific booking
    const bookingReports = allReports.filter(r => r.booking_id == bookingId);
    
    // Sort by created_at ascending so newest is at bottom (chat style)
    bookingReports.sort((a, b) => new Date(a.created_at || 0) - new Date(b.created_at || 0));

    if (bookingReports.length === 0) {
        container.innerHTML = `
            <div class="text-center py-6 text-gray-400 flex flex-col items-center">
                <i class="fas fa-inbox text-2xl mb-2 text-gray-300"></i>
                <p class="text-xs">No previous messages or reports for this booking.</p>
            </div>
        `;
        return;
    }

    container.innerHTML = bookingReports.map(r => {
        const dateFmt = r.created_at ? new Date(r.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '';
        const reply = r.admin_response || r.admin_reply || '';
        const isLateArrival = r.type === 'late_arrival';
        
        let typeBadge = '';
        if (isLateArrival) {
            typeBadge = '<span class="bg-amber-100 text-amber-800 px-2 py-0.5 rounded text-[10px] font-bold uppercase">Late Arrival</span>';
        } else if (r.type === 'complaint') {
            typeBadge = '<span class="bg-rose-100 text-rose-800 px-2 py-0.5 rounded text-[10px] font-bold uppercase">Problem/Issue</span>';
        } else {
            typeBadge = `<span class="bg-slate-100 text-slate-700 px-2 py-0.5 rounded text-[10px] font-bold uppercase">${r.type || 'Message'}</span>`;
        }

        let html = `
            <div class="bg-slate-50 border border-gray-200 rounded-2xl p-3 shadow-xs">
                <div class="flex justify-between items-start mb-1.5">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-user-circle text-gray-400"></i>
                        <span class="font-bold text-gray-800 text-xs">You</span>
                    </div>
                    ${typeBadge}
                </div>
                <h5 class="font-bold text-gray-700 text-[11px] mb-1">${r.subject || ''}</h5>
                <p class="text-xs text-gray-600 whitespace-pre-line leading-relaxed">${r.message}</p>
                <div class="text-[9px] text-gray-400 text-right mt-1">${dateFmt}</div>
            </div>
        `;

        if (reply) {
            html += `
                <div class="bg-blue-50/60 border border-brand-blue/30 rounded-2xl p-3 ml-6 mt-2 relative shadow-xs">
                    <div class="absolute -left-3 top-4 text-brand-blue/30"><i class="fas fa-reply fa-flip-horizontal"></i></div>
                    <div class="flex justify-between items-start mb-1.5">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-headset text-brand-blue"></i>
                            <span class="font-bold text-brand-blue text-xs">Management</span>
                        </div>
                        <span class="bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded text-[10px] font-bold uppercase">Response</span>
                    </div>
                    <p class="text-xs text-gray-700 whitespace-pre-line leading-relaxed">${reply}</p>
                </div>
            `;
        }
        return html;
    }).join('');
    
    // Scroll to bottom
    setTimeout(() => {
        container.scrollTop = container.scrollHeight;
    }, 10);
}

function submitBookingReport(e) {
    e.preventDefault();
    const btn = document.getElementById('br-submit-btn');
    const originalBtnHtml = btn.innerHTML;
    
    const bookingId = document.getElementById('br-booking-id').value;
    const type = document.getElementById('br-report-type').value;
    const subject = document.getElementById('br-report-subject').value.trim();
    const message = document.getElementById('br-report-message').value.trim();
    const currentUser = (window.appState && window.appState.currentUser) ? window.appState.currentUser : {};

    if (!message || !subject) return;

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Sending...';

    fetch('../actions/reports_action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            user_id: currentUser.id,
            booking_id: bookingId,
            type: type,
            subject: subject,
            message: message
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            if (typeof showToast === 'function') {
                showToast('Your message has been sent to management.', 'success');
            }
            
            // Add to local state so it appears immediately
            if (!window.appState.reports) window.appState.reports = [];
            window.appState.reports.push({
                id: data.report_id || Date.now(),
                booking_id: bookingId,
                type: type,
                subject: subject,
                message: message,
                status: 'Pending',
                created_at: new Date().toISOString()
            });
            
            // Clear form and re-render messages
            document.getElementById('br-report-subject').value = '';
            document.getElementById('br-report-message').value = '';
            renderBookingMessages(bookingId);
            if (typeof renderFeedbackList === 'function') renderFeedbackList();
        } else {
            if (typeof showToast === 'function') {
                showToast(data.message || 'Error sending message', 'error');
            } else {
                alert(data.message || 'Error sending message');
            }
        }
    })
    .catch(err => {
        console.error('Error submitting report:', err);
        if (typeof showToast === 'function') showToast('Network error', 'error');
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalBtnHtml;
    });
}
