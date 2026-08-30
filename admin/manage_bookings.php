<?php
/**
 * Admin Booking Management View
 * Features status tabs, side-by-side receipt review modal, and one-click status transitions.
 */
?>
<div class="space-y-6">

    <!-- Header & Expiry Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
        <div>
            <h3 class="text-xl font-bold text-slate-900 flex items-center gap-2">
                <i class="fas fa-receipt text-brand-blue"></i> Reservations & Payment Review
            </h3>
            <p class="text-xs text-slate-500 mt-0.5">
                Verify GCash payments, manage hold timers, collect check-in balances, and handle arrivals.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <button onclick="triggerExpiryWorker()" class="px-4 py-2.5 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 rounded-xl text-xs font-bold transition flex items-center gap-2">
                <i class="fas fa-stopwatch text-amber-600"></i> Run Auto-Expiry Check
            </button>
            <button onclick="loadAdminBookings()" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition flex items-center gap-1.5">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
        </div>
    </div>

    <!-- Status Tabs -->
    <div class="flex flex-wrap gap-2 border-b border-slate-200 pb-3" id="admin-booking-tabs">
        <button onclick="switchAdminFilter('all')" data-tab="all" class="admin-tab-btn active px-4 py-2 rounded-xl text-xs font-bold bg-slate-900 text-white transition flex items-center gap-2 shadow-sm">
            All Reservations <span id="badge-all" class="bg-white/20 text-white px-2 py-0.5 rounded-full text-[10px]">0</span>
        </button>
        <button onclick="switchAdminFilter('pending_verification')" data-tab="pending_verification" class="admin-tab-btn px-4 py-2 rounded-xl text-xs font-bold text-amber-800 bg-amber-50 hover:bg-amber-100 border border-amber-200 transition flex items-center gap-2">
            <i class="fas fa-hourglass-half text-amber-500"></i> Pending Verification 
            <span id="badge-pending-verification" class="bg-amber-500 text-white px-2 py-0.5 rounded-full text-[10px]">0</span>
        </button>
        <button onclick="switchAdminFilter('pending_arrival')" data-tab="pending_arrival" class="admin-tab-btn px-4 py-2 rounded-xl text-xs font-bold text-teal-800 bg-teal-50 hover:bg-teal-100 border border-teal-200 transition flex items-center gap-2">
            <i class="fas fa-hotel text-teal-600"></i> Pay at Hotel (Arrivals)
            <span id="badge-pending-arrival" class="bg-teal-600 text-white px-2 py-0.5 rounded-full text-[10px]">0</span>
        </button>
        <button onclick="switchAdminFilter('deposit_paid')" data-tab="deposit_paid" class="admin-tab-btn px-4 py-2 rounded-xl text-xs font-bold text-purple-800 bg-purple-50 hover:bg-purple-100 border border-purple-200 transition flex items-center gap-2">
            <i class="fas fa-percentage text-purple-600"></i> Deposit Paid (Balance Due)
            <span id="badge-deposit-paid" class="bg-purple-600 text-white px-2 py-0.5 rounded-full text-[10px]">0</span>
        </button>
        <button onclick="switchAdminFilter('confirmed')" data-tab="confirmed" class="admin-tab-btn px-4 py-2 rounded-xl text-xs font-bold text-emerald-800 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 transition flex items-center gap-2">
            <i class="fas fa-check-circle text-emerald-600"></i> Confirmed (Full)
            <span id="badge-confirmed" class="bg-emerald-600 text-white px-2 py-0.5 rounded-full text-[10px]">0</span>
        </button>
        <button onclick="switchAdminFilter('completed')" data-tab="completed" class="admin-tab-btn px-4 py-2 rounded-xl text-xs font-bold text-blue-800 bg-blue-50 hover:bg-blue-100 border border-blue-200 transition flex items-center gap-2">
            <i class="fas fa-check-double text-blue-600"></i> Completed / Checked-In
            <span id="badge-completed" class="bg-blue-600 text-white px-2 py-0.5 rounded-full text-[10px]">0</span>
        </button>
        <button onclick="switchAdminFilter('cancelled_expired')" data-tab="cancelled_expired" class="admin-tab-btn px-4 py-2 rounded-xl text-xs font-bold text-rose-800 bg-rose-50 hover:bg-rose-100 border border-rose-200 transition flex items-center gap-2">
            <i class="fas fa-times-circle text-rose-600"></i> Cancelled / Expired / No-Show
            <span id="badge-cancelled" class="bg-rose-600 text-white px-2 py-0.5 rounded-full text-[10px]">0</span>
        </button>
    </div>

    <!-- Bookings Table Card -->
    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 text-[11px] uppercase tracking-wider text-slate-500 border-b border-slate-100">
                        <th class="p-4 font-bold">Booking Reference</th>
                        <th class="p-4 font-bold">Guest Details</th>
                        <th class="p-4 font-bold">Room & Dates</th>
                        <th class="p-4 font-bold">Payment & Balance</th>
                        <th class="p-4 font-bold">Status / Hold Timer</th>
                        <th class="p-4 font-bold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="admin-bookings-table-body" class="divide-y divide-slate-100 text-xs">
                    <tr>
                        <td colspan="6" class="p-8 text-center text-slate-400">
                            <i class="fas fa-spinner fa-spin text-xl mb-2 text-brand-blue block"></i>
                            Loading reservations...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- ================= MODAL: SIDE-BY-SIDE RECEIPT REVIEW ================= -->
<div id="receipt-review-modal" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-[150] hidden items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-4xl w-full max-h-[90vh] overflow-hidden shadow-2xl border border-slate-100 flex flex-col animate-in fade-in zoom-in duration-200">
        
        <!-- Modal Topbar -->
        <div class="p-5 bg-slate-900 text-white flex justify-between items-center">
            <div>
                <h4 class="text-base font-bold flex items-center gap-2">
                    <i class="fas fa-receipt text-sky-400"></i> Proof of Payment & Reference Verification
                </h4>
                <p class="text-xs text-slate-400">Compare the guest's uploaded receipt image against the submitted GCash reference number.</p>
            </div>
            <button onclick="closeReceiptReviewModal()" class="text-slate-400 hover:text-white text-lg">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <!-- Modal Body (Side-by-Side 2-Columns) -->
        <div class="p-6 overflow-y-auto grid grid-cols-1 md:grid-cols-12 gap-6 items-start">
            
            <!-- LEFT: Receipt Image Viewer (7 cols) -->
            <div class="md:col-span-7 space-y-3">
                <div class="flex items-center justify-between text-xs text-slate-500">
                    <span class="font-bold text-slate-700">Uploaded Receipt Image</span>
                    <a id="modal-receipt-download" href="#" target="_blank" class="text-brand-blue hover:underline text-[11px] font-bold">
                        <i class="fas fa-external-link-alt mr-1"></i> Open Full Size
                    </a>
                </div>
                
                <div class="bg-slate-100 rounded-2xl border border-slate-200 p-2 text-center max-h-[420px] overflow-hidden flex items-center justify-center">
                    <img id="modal-receipt-img" src="" class="max-h-[400px] w-auto max-w-full object-contain rounded-xl shadow-sm" alt="Payment Receipt">
                </div>
            </div>

            <!-- RIGHT: Booking Details & Reference Check (5 cols) -->
            <div class="md:col-span-5 space-y-4">
                
                <div class="bg-sky-50/80 p-4 rounded-2xl border border-sky-100 space-y-2">
                    <span class="text-[10px] text-sky-700 font-bold uppercase tracking-wide block">Submitted GCash Reference</span>
                    <p id="modal-gcash-ref" class="text-lg font-mono font-extrabold text-sky-900 select-all tracking-wider">1002938475891</p>
                    <p class="text-[11px] text-sky-700">Verify this 10-30 digit reference matches the transaction ID on the receipt.</p>
                </div>

                <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100 space-y-2 text-xs">
                    <div class="flex justify-between">
                        <span class="text-slate-500">Guest:</span>
                        <strong id="modal-guest-name" class="text-slate-800">-</strong>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Contact:</span>
                        <span id="modal-guest-contact" class="text-slate-700">-</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Room:</span>
                        <strong id="modal-room-name" class="text-slate-800">-</strong>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Dates:</span>
                        <span id="modal-dates" class="text-slate-700">-</span>
                    </div>
                    <div class="flex justify-between pt-2 border-t border-slate-200">
                        <span class="text-slate-600 font-bold">Total Bill:</span>
                        <strong id="modal-total-bill" class="text-slate-900 font-bold">₱0.00</strong>
                    </div>
                    <div class="flex justify-between text-brand-blue font-bold">
                        <span>Paid Amount:</span>
                        <span id="modal-paid-amount">₱0.00</span>
                    </div>
                    <div class="flex justify-between text-slate-500">
                        <span>Balance Due at Check-in:</span>
                        <span id="modal-balance-due" class="font-bold text-slate-800">₱0.00</span>
                    </div>
                </div>

                <!-- One-Click Decision Buttons -->
                <div class="pt-2 space-y-2">
                    <input type="hidden" id="modal-booking-id" value="">
                    
                    <button type="button" onclick="adminApproveBooking()" class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-md transition flex items-center justify-center gap-2 text-xs">
                        <i class="fas fa-check-circle"></i> Approve Payment & Confirm Hold
                    </button>

                    <button type="button" onclick="adminRejectBooking()" class="w-full py-2.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-bold rounded-xl transition flex items-center justify-center gap-2 text-xs">
                        <i class="fas fa-times-circle"></i> Reject (Invalid Receipt / No Payment)
                    </button>
                </div>

            </div>

        </div>

    </div>
</div>

<script>
let allAdminBookings = [];
let activeAdminFilter = 'all';

document.addEventListener('DOMContentLoaded', function() {
    loadAdminBookings();
});

function loadAdminBookings() {
    fetch('../actions/book_action.php?action=get_all')
        .then(res => res.json())
        .then(data => {
            if (data.success && data.bookings) {
                allAdminBookings = data.bookings;
                updateAdminBadges(data.counts || {});
                renderAdminBookingsTable();
            }
        })
        .catch(err => console.error('Error fetching admin bookings:', err));
}

function updateAdminBadges(counts) {
    document.getElementById('badge-all').innerText = counts.all || 0;
    document.getElementById('badge-pending-verification').innerText = counts.pending_verification || 0;
    document.getElementById('badge-pending-arrival').innerText = counts.pending_arrival || 0;
    document.getElementById('badge-deposit-paid').innerText = counts.deposit_paid || 0;
    document.getElementById('badge-confirmed').innerText = counts.confirmed || 0;
    document.getElementById('badge-completed').innerText = counts.completed || 0;
    document.getElementById('badge-cancelled').innerText = (counts.cancelled_expired || 0) + (counts.no_show || 0);
}

function switchAdminFilter(filter) {
    activeAdminFilter = filter;
    
    document.querySelectorAll('.admin-tab-btn').forEach(btn => {
        if (btn.getAttribute('data-tab') === filter) {
            btn.classList.add('bg-slate-900', 'text-white', 'shadow-sm');
        } else {
            btn.classList.remove('bg-slate-900', 'text-white', 'shadow-sm');
        }
    });

    renderAdminBookingsTable();
}

function renderAdminBookingsTable() {
    const tbody = document.getElementById('admin-bookings-table-body');
    if (!tbody) return;

    let filtered = allAdminBookings;
    if (activeAdminFilter !== 'all') {
        if (activeAdminFilter === 'cancelled_expired') {
            filtered = filtered.filter(b => b.booking_status === 'cancelled_expired' || b.booking_status === 'no_show' || b.status === 'Cancelled');
        } else {
            filtered = filtered.filter(b => b.booking_status === activeAdminFilter);
        }
    }

    if (filtered.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="p-8 text-center text-slate-400">
                    <i class="fas fa-inbox text-3xl mb-2 text-slate-300 block"></i>
                    No reservations found for this status.
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = filtered.map(b => {
        const checkIn = b.check_in_date || b.check_in;
        const checkOut = b.check_out_date || b.check_out;
        const total = parseFloat(b.total_amount || b.total_price || 0);
        const paid = parseFloat(b.paid_amount || 0);
        const balance = parseFloat(b.balance_due || 0);
        const status = b.booking_status || 'pending_verification';

        // Status Badge
        let statusBadge = '';
        if (status === 'confirmed') {
            statusBadge = `<span class="px-2.5 py-1 rounded-full font-bold bg-emerald-100 text-emerald-800 border border-emerald-200"><i class="fas fa-check-circle mr-1"></i> Confirmed (Full)</span>`;
        } else if (status === 'deposit_paid') {
            statusBadge = `<span class="px-2.5 py-1 rounded-full font-bold bg-purple-100 text-purple-800 border border-purple-200"><i class="fas fa-percentage mr-1"></i> Deposit Paid</span>`;
        } else if (status === 'pending_arrival') {
            statusBadge = `<span class="px-2.5 py-1 rounded-full font-bold bg-teal-100 text-teal-800 border border-teal-200"><i class="fas fa-hotel mr-1"></i> Pay at Hotel</span>`;
        } else if (status === 'completed') {
            statusBadge = `<span class="px-2.5 py-1 rounded-full font-bold bg-blue-100 text-blue-800 border border-blue-200"><i class="fas fa-check-double mr-1"></i> Completed</span>`;
        } else if (status === 'cancelled_expired' || status === 'no_show') {
            statusBadge = `<span class="px-2.5 py-1 rounded-full font-bold bg-rose-100 text-rose-800 border border-rose-200"><i class="fas fa-times-circle mr-1"></i> ${status === 'no_show' ? 'No-Show' : 'Cancelled / Expired'}</span>`;
        } else {
            statusBadge = `<span class="px-2.5 py-1 rounded-full font-bold bg-amber-100 text-amber-800 border border-amber-200 animate-pulse"><i class="fas fa-hourglass-half mr-1"></i> Verification Pending</span>`;
        }

        // Hold / Expiry Timer representation
        let timerDisplay = '';
        if (b.expires_at && (status === 'pending_verification' || status === 'pending_arrival')) {
            const expDate = new Date(b.expires_at);
            timerDisplay = `<div class="text-[10px] text-amber-700 mt-1 font-mono font-semibold"><i class="far fa-clock mr-1"></i> Hold until ${expDate.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}</div>`;
        }

        // Action Buttons
        let actionButtons = '';
        if (status === 'pending_verification') {
            actionButtons = `
                <button onclick="openReceiptModal(${b.id})" class="px-3 py-1.5 bg-brand-blue hover:bg-sky-600 text-white rounded-xl font-bold transition flex items-center gap-1.5 shadow-sm">
                    <i class="fas fa-search-plus"></i> Review Receipt
                </button>
            `;
        } else if (status === 'deposit_paid' || status === 'pending_arrival') {
            actionButtons = `
                <div class="flex items-center justify-end gap-2">
                    <button onclick="adminCheckInGuest(${b.id})" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold transition flex items-center gap-1.5 shadow-sm" title="Check-in guest and collect remaining balance">
                        <i class="fas fa-check"></i> Check-in & Collect
                    </button>
                    ${status === 'pending_arrival' ? `
                        <button onclick="adminMarkNoShow(${b.id})" class="px-2.5 py-1.5 bg-slate-100 hover:bg-rose-50 text-rose-600 rounded-xl font-bold transition text-[11px]" title="Mark No-Show and release room">
                            No-Show
                        </button>
                    ` : ''}
                </div>
            `;
        } else {
            actionButtons = `
                <span class="text-slate-400 text-[11px] italic">Processed</span>
            `;
        }

        return `
            <tr class="hover:bg-slate-50/60 transition">
                <td class="p-4">
                    <span class="font-mono font-bold text-slate-800 block">${b.ref_id}</span>
                    <span class="text-[10px] text-slate-400">Booked: ${new Date(b.created_at).toLocaleDateString()}</span>
                </td>
                <td class="p-4">
                    <strong class="text-slate-800 block">${b.guest_name || b.user_name || 'Guest'}</strong>
                    <span class="text-slate-500 text-[11px] block">${b.guest_phone || b.guest_email || b.user_email || 'No phone'}</span>
                </td>
                <td class="p-4">
                    <strong class="text-slate-800 block">${b.room_name || 'Hostel Room'}</strong>
                    <span class="text-slate-500 text-[11px]">${checkIn} &rarr; ${checkOut} (${b.nights}n)</span>
                </td>
                <td class="p-4">
                    <div class="text-slate-800 font-bold">₱${total.toFixed(2)}</div>
                    <div class="text-[11px] text-emerald-700 font-medium">Paid: ₱${paid.toFixed(2)}</div>
                    ${balance > 0 ? `<div class="text-[11px] text-purple-700 font-bold">Due: ₱${balance.toFixed(2)}</div>` : ''}
                </td>
                <td class="p-4">
                    ${statusBadge}
                    ${timerDisplay}
                </td>
                <td class="p-4 text-right">
                    ${actionButtons}
                </td>
            </tr>
        `;
    }).join('');
}

/**
 * Open Side-by-Side Receipt Review Modal
 */
function openReceiptModal(bookingId) {
    const booking = allAdminBookings.find(b => b.id == bookingId);
    if (!booking) return;

    document.getElementById('modal-booking-id').value = booking.id;
    document.getElementById('modal-gcash-ref').innerText = booking.gcash_reference_no || 'No Reference Provided';
    document.getElementById('modal-guest-name').innerText = booking.guest_name || booking.user_name || 'Guest';
    document.getElementById('modal-guest-contact').innerText = `${booking.guest_phone || ''} ${booking.guest_email ? '(' + booking.guest_email + ')' : ''}`;
    document.getElementById('modal-room-name').innerText = booking.room_name || 'Hostel Room';
    document.getElementById('modal-dates').innerText = `${booking.check_in_date || booking.check_in} → ${booking.check_out_date || booking.check_out} (${booking.nights} night${booking.nights > 1 ? 's' : ''})`;
    
    const total = parseFloat(booking.total_amount || booking.total_price || 0);
    const paid = parseFloat(booking.paid_amount || 0);
    const balance = parseFloat(booking.balance_due || 0);

    document.getElementById('modal-total-bill').innerText = `₱${total.toFixed(2)}`;
    document.getElementById('modal-paid-amount').innerText = `₱${paid.toFixed(2)}`;
    document.getElementById('modal-balance-due').innerText = `₱${balance.toFixed(2)}`;

    const receiptImg = document.getElementById('modal-receipt-img');
    const receiptDownload = document.getElementById('modal-receipt-download');
    const imgUrl = booking.receipt_url ? ('../' + booking.receipt_url) : '../assets/image/background.jpg';

    receiptImg.src = imgUrl;
    receiptDownload.href = imgUrl;

    const modal = document.getElementById('receipt-review-modal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeReceiptReviewModal() {
    const modal = document.getElementById('receipt-review-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function adminApproveBooking() {
    const bookingId = document.getElementById('modal-booking-id').value;
    if (!bookingId) return;

    fetch('../actions/book_action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'approve', id: bookingId })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            closeReceiptReviewModal();
            loadAdminBookings();
        } else {
            alert('Approval error: ' + data.message);
        }
    })
    .catch(err => console.error('Approve error:', err));
}

function adminRejectBooking() {
    const bookingId = document.getElementById('modal-booking-id').value;
    if (!bookingId) return;

    if (!confirm('Are you sure you want to reject this payment proof? The room hold will be immediately released.')) {
        return;
    }

    fetch('../actions/book_action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'reject', id: bookingId })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            closeReceiptReviewModal();
            loadAdminBookings();
        } else {
            alert('Reject error: ' + data.message);
        }
    })
    .catch(err => console.error('Reject error:', err));
}

function adminCheckInGuest(bookingId) {
    if (!confirm('Check in this guest and collect any remaining balance?')) return;

    fetch('../actions/book_action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'check_in', id: bookingId })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            loadAdminBookings();
        } else {
            alert('Check-in error: ' + data.message);
        }
    })
    .catch(err => console.error('Check-in error:', err));
}

function adminMarkNoShow(bookingId) {
    if (!confirm('Mark reservation as No-Show and release room inventory?')) return;

    fetch('../actions/book_action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'no_show', id: bookingId })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            loadAdminBookings();
        } else {
            alert('No-show error: ' + data.message);
        }
    })
    .catch(err => console.error('No-show error:', err));
}

function triggerExpiryWorker() {
    fetch('../actions/cron_expire_bookings.php')
        .then(res => res.json())
        .then(data => {
            alert(data.message || 'Expiry check complete.');
            loadAdminBookings();
        })
        .catch(err => console.error('Expiry trigger error:', err));
}
</script>
