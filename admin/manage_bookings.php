<!-- ================= Manage Bookings Page ================= -->
<div id="manage-bookings" class="page-section">
    <div class="p-6 max-w-7xl mx-auto">
        <!-- Multi-Tier Statistics Counter Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-6">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-3.5">
                <span class="text-[10px] uppercase font-bold text-gray-400 block tracking-wider" id="stat-total-label">Total Active</span>
                <div class="text-2xl font-bold text-gray-800 mt-1" id="stat-total">0</div>
            </div>
            <div class="bg-amber-50/70 border border-amber-200 rounded-2xl p-3.5">
                <span class="text-[10px] uppercase font-bold text-amber-700 block tracking-wider">GCash Pending</span>
                <div class="text-2xl font-bold text-amber-700 mt-1" id="stat-pending-verify">0</div>
            </div>
            <div class="bg-blue-50/70 border border-blue-200 rounded-2xl p-3.5">
                <span class="text-[10px] uppercase font-bold text-blue-700 block tracking-wider">Deposit Paid</span>
                <div class="text-2xl font-bold text-blue-700 mt-1" id="stat-deposit-paid">0</div>
            </div>
            <div class="bg-purple-50/70 border border-purple-200 rounded-2xl p-3.5">
                <span class="text-[10px] uppercase font-bold text-purple-700 block tracking-wider">Hotel Arrival</span>
                <div class="text-2xl font-bold text-purple-700 mt-1" id="stat-pending-arrival">0</div>
            </div>
            <div class="bg-emerald-50/70 border border-emerald-200 rounded-2xl p-3.5">
                <span class="text-[10px] uppercase font-bold text-emerald-700 block tracking-wider">Confirmed/Done</span>
                <div class="text-2xl font-bold text-emerald-700 mt-1" id="stat-confirmed">0</div>
            </div>
            <div class="bg-rose-50/70 border border-rose-200 rounded-2xl p-3.5">
                <span class="text-[10px] uppercase font-bold text-rose-700 block tracking-wider">Cancelled/Expired</span>
                <div class="text-2xl font-bold text-rose-700 mt-1" id="stat-cancelled">0</div>
            </div>
        </div>
        
        <!-- Header & Action Controls -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-4">
            <div>
                <p class="text-xs text-gray-500" id="bookings-page-subtitle">Automated reservation management: instant PayMongo confirmations, check-ins, and hotel arrivals.</p>
            </div>
            
            <div class="flex flex-wrap items-center gap-2 w-full md:w-auto">
                <div class="relative flex-1 md:w-56">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    <input type="text" id="booking-search" placeholder="Search ref, guest, phone, room..." 
                        class="w-full pl-9 pr-3 py-2 border border-gray-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-brand-blue/30 bg-white"
                        oninput="filterBookings()">
                </div>
                
                <select id="status-filter" class="px-3 py-2 border border-gray-200 rounded-xl text-xs bg-white focus:outline-none focus:ring-2 focus:ring-brand-blue/30" onchange="filterBookings()">
                    <option value="">All Statuses</option>
                    <option value="pending_verification">Pending Verification / Hold</option>
                    <option value="deposit_paid">Deposit Paid (Balance Due)</option>
                    <option value="confirmed">Confirmed (PayMongo / Paid)</option>
                    <option value="pending_arrival">Pending Arrival (Pay at Hotel)</option>
                    <option value="completed">Completed / Checked-In</option>
                    <option value="cancelled_expired">Cancelled / Expired Hold</option>
                    <option value="no_show">No-Show Released</option>
                </select>

                <select id="payment-method-filter" class="px-3 py-2 border border-gray-200 rounded-xl text-xs bg-white focus:outline-none focus:ring-2 focus:ring-brand-blue/30" onchange="filterBookings()">
                    <option value="">All Payment Types</option>
                    <option value="paymongo">PayMongo Online</option>
                    <option value="full_gcash">Full GCash (100%)</option>
                    <option value="deposit_gcash">Deposit GCash (20%)</option>
                    <option value="pay_at_hotel">Pay at Hotel</option>
                </select>

                <button onclick="loadBookings()" class="bg-brand-blue hover:bg-sky-600 text-white px-3.5 py-2 rounded-xl text-xs font-semibold transition flex items-center gap-1.5 shadow-sm" title="Refresh & trigger auto-expiry">
                    <i class="fas fa-sync-alt" id="refresh-icon"></i> Refresh
                </button>
            </div>
        </div>

        <!-- View Tabs & Batch Actions Toolbar -->
        <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
            <div class="inline-flex bg-gray-100 rounded-xl p-1 shadow-inner">
                <button type="button" data-bookings-view="active" onclick="switchBookingsView('active')"
                        class="bookings-tab flex items-center gap-2 px-4 py-2 rounded-lg text-xs sm:text-sm font-semibold transition bg-white text-brand-blue shadow-sm">
                    <i class="fas fa-calendar-check"></i>
                    Active Bookings
                    <span id="bookings-active-count" class="text-xs font-bold px-2 py-0.5 rounded-full bg-blue-50 text-brand-blue">0</span>
                </button>
                <button type="button" data-bookings-view="archived" onclick="switchBookingsView('archived')"
                        class="bookings-tab flex items-center gap-2 px-4 py-2 rounded-lg text-xs sm:text-sm font-semibold transition text-gray-500 hover:text-gray-700">
                    <i class="fas fa-box-archive"></i>
                    Archive
                    <span id="bookings-archived-count" class="text-xs font-bold px-2 py-0.5 rounded-full bg-white text-gray-500">0</span>
                </button>
            </div>

            <!-- Quick Batch Archive for Active View -->
            <div id="active-batch-tools" class="flex items-center gap-2">
                <button onclick="archiveAllCancelledBookings()" class="px-3.5 py-1.5 rounded-xl border border-rose-200 bg-rose-50/70 hover:bg-rose-100 text-rose-700 text-xs font-semibold transition flex items-center gap-1.5 shadow-sm" title="Move all cancelled and expired hold records to Archive">
                    <i class="fas fa-broom"></i>
                    Archive Cancelled / Expired
                </button>
            </div>

            <!-- Archive View Banner -->
            <div id="archive-info-badge" class="hidden text-xs text-amber-700 bg-amber-50 border border-amber-200 px-3.5 py-1.5 rounded-xl flex items-center gap-2">
                <i class="fas fa-shield-alt text-amber-600"></i>
                <span>Archived records keep receipts, audit trails, and guest history intact. Restore or permanently delete records here.</span>
            </div>
        </div>
        
        <!-- Bookings Master Table -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[1100px]">
                    <thead id="bookings-table-head">
                        <tr class="bg-slate-50 text-[11px] font-bold text-gray-500 uppercase tracking-wider border-b border-gray-100">
                            <th class="p-3.5">Reference ID</th>
                            <th class="p-3.5">Guest Information</th>
                            <th class="p-3.5">Room & Stay</th>
                            <th class="p-3.5">Payment Pathway</th>
                            <th class="p-3.5">Paid / Balance Due</th>
                            <th class="p-3.5">Status & Hold Timer</th>
                            <th class="p-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="bookings-table-body" class="text-xs divide-y divide-gray-100">
                        <tr>
                            <td colspan="7" class="p-8 text-center text-gray-400">
                                <i class="fas fa-spinner fa-spin mr-2"></i> Loading bookings & verifying hold expirations...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ================= SIDE-BY-SIDE RECEIPT VERIFICATION MODAL ================= -->
<div id="receipt-modal" class="hidden fixed inset-0 bg-black/75 flex items-center justify-center z-[120] p-4">
    <div class="bg-white rounded-3xl w-full max-w-4xl max-h-[92vh] flex flex-col shadow-2xl overflow-hidden border border-gray-100">
        <div class="p-5 bg-gradient-to-r from-brand-dark via-slate-900 to-brand-blue text-white flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-white/10 flex items-center justify-center font-bold text-base">
                    <i class="fas fa-receipt text-teal-300"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold">Proof of Payment Review</h3>
                    <p class="text-xs text-white/70">Verify guest GCash transaction and reference number</p>
                </div>
            </div>
            <button onclick="closeReceiptModal()" class="text-white/70 hover:text-white bg-white/10 hover:bg-white/20 p-2 rounded-xl transition">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="p-6 overflow-y-auto flex-1 grid grid-cols-1 md:grid-cols-12 gap-6 bg-slate-50/50">
            <!-- Left Side: Receipt Image Preview -->
            <div class="md:col-span-7 flex flex-col items-center justify-center bg-white p-4 rounded-2xl border border-gray-200 shadow-sm">
                <div class="w-full flex justify-between items-center mb-2 px-1">
                    <span class="text-xs font-bold text-gray-600">Uploaded Receipt Image</span>
                    <a id="modal-receipt-link" href="#" target="_blank" class="text-xs text-brand-blue hover:underline font-semibold flex items-center gap-1">
                        <i class="fas fa-external-link-alt"></i> Open Full Size
                    </a>
                </div>
                <div class="relative w-full h-[360px] bg-gray-100 rounded-xl overflow-hidden flex items-center justify-center border border-gray-100">
                    <img id="modal-receipt-img" src="" alt="Receipt Image" class="max-w-full max-h-full object-contain">
                    <div id="modal-no-receipt" class="hidden text-center text-gray-400">
                        <i class="fas fa-image text-3xl mb-2"></i>
                        <p class="text-xs">No receipt image attached for this booking.</p>
                    </div>
                </div>
            </div>

            <!-- Right Side: Booking Breakdown & Action Buttons -->
            <div class="md:col-span-5 space-y-4">
                <div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-sm space-y-3">
                    <div>
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Booking Reference</span>
                        <div class="text-base font-mono font-bold text-brand-blue" id="modal-ref-id">-</div>
                    </div>

                    <div class="border-t border-gray-100 pt-2">
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Accommodation / Beds</span>
                        <div class="text-xs font-semibold text-gray-800" id="modal-room-info">-</div>
                    </div>

                    <div class="border-t border-gray-100 pt-2 grid grid-cols-2 gap-2">
                        <div>
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Guest Name</span>
                            <strong class="text-gray-800 text-xs block" id="modal-guest-name">-</strong>
                            <span class="text-[11px] text-gray-500 block truncate" id="modal-guest-email">-</span>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Phone Number</span>
                            <span class="text-xs font-medium text-gray-800 block" id="modal-guest-phone">-</span>
                        </div>
                    </div>

                    <!-- Submitted GCash Reference -->
                    <div class="p-3 bg-blue-50 rounded-xl border border-blue-200">
                        <span class="text-[10px] font-bold text-blue-800 uppercase tracking-wider block">Submitted GCash Reference No.</span>
                        <div class="flex items-center justify-between mt-1">
                            <strong class="text-base font-mono font-bold text-blue-900" id="modal-gcash-ref">-</strong>
                            <button onclick="copyModalRef()" class="text-xs bg-white text-blue-700 px-2 py-1 rounded shadow-sm hover:bg-blue-100" title="Copy reference number">
                                <i class="far fa-copy"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Financial Summary -->
                    <div class="border-t border-gray-100 pt-2 space-y-1.5 text-xs">
                        <div class="flex justify-between text-gray-600">
                            <span>Payment Pathway:</span>
                            <strong class="text-gray-800" id="modal-payment-method">-</strong>
                        </div>
                        <div class="flex justify-between text-gray-600">
                            <span>Total Bill:</span>
                            <strong class="text-gray-900" id="modal-total-amount">₱0.00</strong>
                        </div>
                        <div class="flex justify-between text-emerald-700 font-semibold">
                            <span>Paid / Deposit Proof:</span>
                            <strong class="text-emerald-700" id="modal-paid-amount">₱0.00</strong>
                        </div>
                        <div class="flex justify-between text-brand-blue font-semibold">
                            <span>Balance Due at Desk:</span>
                            <strong class="text-brand-blue" id="modal-balance-due">₱0.00</strong>
                        </div>
                    </div>

                    <!-- Special Requests -->
                    <div id="modal-special-requests-box" class="hidden p-2.5 bg-violet-50 rounded-xl text-xs text-violet-900 border border-violet-200 flex items-start gap-2">
                        <i class="fas fa-comment-dots text-violet-500 mt-0.5 flex-shrink-0"></i>
                        <div class="flex-1 min-w-0">
                            <span class="font-bold block text-violet-800 uppercase tracking-wider text-[10px]">Guest Special Requests</span>
                            <span id="modal-special-requests-text" class="block mt-0.5 break-words leading-relaxed">-</span>
                        </div>
                    </div>

                    <div id="modal-hold-expiry-box" class="p-2.5 bg-amber-50 rounded-xl text-xs text-amber-800 border border-amber-200 flex items-start gap-2">
                        <i class="fas fa-stopwatch text-amber-600 mt-0.5"></i>
                        <div>
                            <span class="font-bold block">Hold Expiration:</span>
                            <span id="modal-expires-at">-</span>
                        </div>
                    </div>
                </div>

                <!-- One-Click Decision Actions -->
                <div class="space-y-2" id="modal-action-buttons">
                    <button id="btn-modal-approve" onclick="approveFromModal()" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2.5 rounded-xl shadow transition text-xs flex items-center justify-center gap-2">
                        <i class="fas fa-check-circle"></i> Approve Booking & Lock Reservation
                    </button>
                    <button id="btn-modal-reject" onclick="rejectFromModal()" class="w-full bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold py-2 rounded-xl border border-rose-200 transition text-xs flex items-center justify-center gap-2">
                        <i class="fas fa-times-circle"></i> Reject (Invalid Receipt / Release Hold)
                    </button>
                    <button id="btn-modal-checkin" onclick="checkInFromModal()" class="w-full bg-brand-blue hover:bg-sky-600 text-white font-bold py-2.5 rounded-xl shadow transition text-xs flex items-center justify-center gap-2">
                        <i class="fas fa-concierge-bell"></i> Mark Checked-In & Collect Balance
                    </button>
                    <button id="btn-modal-archive" onclick="archiveFromModal()" class="w-full bg-amber-50 hover:bg-amber-100 text-amber-800 font-bold py-2 rounded-xl border border-amber-200 transition text-xs flex items-center justify-center gap-2">
                        <i class="fas fa-box-archive"></i> Archive Reservation
                    </button>
                    <button id="btn-modal-restore" onclick="restoreFromModal()" class="w-full bg-emerald-50 hover:bg-emerald-100 text-emerald-800 font-bold py-2 rounded-xl border border-emerald-200 transition text-xs flex items-center justify-center gap-2">
                        <i class="fas fa-rotate-left"></i> Restore to Active Bookings
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let currentBookingsView = 'active';
let allBookings = [];
let activeModalBooking = null;
let bookingCounts = { active: 0, archived: 0 };

function formatDate(dateStr) {
    if (!dateStr) return '-';
    const date = new Date(dateStr);
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
}

function formatTime(timeStr) {
    if (!timeStr) return '';
    const [h, m] = timeStr.split(':');
    const hour = parseInt(h, 10);
    const ampm = hour >= 12 ? 'PM' : 'AM';
    const hour12 = hour % 12 || 12;
    return `${hour12}:${m} ${ampm}`;
}

function switchBookingsView(view) {
    if (currentBookingsView === view) return;
    currentBookingsView = (view === 'archived') ? 'archived' : 'active';
    
    // Clear search input on tab switch
    const searchInput = document.getElementById('booking-search');
    if (searchInput) searchInput.value = '';

    const batchTools = document.getElementById('active-batch-tools');
    const archiveInfo = document.getElementById('archive-info-badge');
    const pageSubtitle = document.getElementById('bookings-page-subtitle');

    if (currentBookingsView === 'archived') {
        if (batchTools) batchTools.classList.add('hidden');
        if (archiveInfo) archiveInfo.classList.remove('hidden');
        if (pageSubtitle) pageSubtitle.textContent = 'Archived reservations: historical and completed stays kept safe for auditing, folios, and proof review.';
    } else {
        if (batchTools) batchTools.classList.remove('hidden');
        if (archiveInfo) archiveInfo.classList.add('hidden');
        if (pageSubtitle) pageSubtitle.textContent = 'Automated reservation management: instant PayMongo confirmations, check-ins, and hotel arrivals.';
    }

    loadBookings();
}

function applyBookingsTabStyles() {
    document.querySelectorAll('.bookings-tab').forEach(tab => {
        const isActive = tab.dataset.bookingsView === currentBookingsView;
        tab.classList.toggle('bg-white', isActive);
        tab.classList.toggle('text-brand-blue', isActive);
        tab.classList.toggle('shadow-sm', isActive);
        tab.classList.toggle('text-gray-500', !isActive);
    });
}

function renderTableHead() {
    const headRow = document.querySelector('#bookings-table-head tr');
    if (!headRow) return;

    if (currentBookingsView === 'archived') {
        headRow.innerHTML = `
            <th class="p-3.5">Reference ID</th>
            <th class="p-3.5">Guest Information</th>
            <th class="p-3.5">Room & Stay</th>
            <th class="p-3.5">Payment Pathway</th>
            <th class="p-3.5">Paid / Balance Due</th>
            <th class="p-3.5">Status</th>
            <th class="p-3.5">Archived Date</th>
            <th class="p-3.5 text-right">Actions</th>
        `;
    } else {
        headRow.innerHTML = `
            <th class="p-3.5">Reference ID</th>
            <th class="p-3.5">Guest Information</th>
            <th class="p-3.5">Room & Stay</th>
            <th class="p-3.5">Payment Pathway</th>
            <th class="p-3.5">Paid / Balance Due</th>
            <th class="p-3.5">Status & Hold Timer</th>
            <th class="p-3.5 text-right">Actions</th>
        `;
    }
}

function renderCounts() {
    const activeBadge = document.getElementById('bookings-active-count');
    const archivedBadge = document.getElementById('bookings-archived-count');
    if (activeBadge) activeBadge.textContent = bookingCounts.active;
    if (archivedBadge) archivedBadge.textContent = bookingCounts.archived;
}

function loadBookings(view) {
    if (typeof view === 'string') {
        currentBookingsView = (view === 'archived') ? 'archived' : 'active';
    }
    const refreshIcon = document.getElementById('refresh-icon');
    if (refreshIcon) refreshIcon.classList.add('fa-spin');

    applyBookingsTabStyles();
    renderTableHead();

    const tbody = document.getElementById('bookings-table-body');
    if (tbody && allBookings.length === 0) {
        const colSpan = (currentBookingsView === 'archived') ? 8 : 7;
        tbody.innerHTML = `<tr><td colspan="${colSpan}" class="p-8 text-center text-gray-400"><i class="fas fa-spinner fa-spin mr-2"></i> Loading ${currentBookingsView} bookings...</td></tr>`;
    }

    fetch('../actions/bookings_action.php?view=' + currentBookingsView)
        .then(response => response.json())
        .then(data => {
            if (refreshIcon) refreshIcon.classList.remove('fa-spin');
            if (data.success) {
                allBookings = Array.isArray(data.bookings) ? data.bookings : [];
                bookingCounts = data.counts || { active: allBookings.length, archived: 0 };
                renderCounts();
                updateStats();
                filterBookings();
            } else {
                const colSpan = (currentBookingsView === 'archived') ? 8 : 7;
                if (tbody) tbody.innerHTML = `<tr><td colspan="${colSpan}" class="p-6 text-center text-rose-500">${data.message || 'Error loading bookings.'}</td></tr>`;
            }
        })
        .catch(error => {
            if (refreshIcon) refreshIcon.classList.remove('fa-spin');
            console.error('Error:', error);
            const colSpan = (currentBookingsView === 'archived') ? 8 : 7;
            if (tbody) tbody.innerHTML = `<tr><td colspan="${colSpan}" class="p-6 text-center text-rose-500">Error loading bookings. Database connection issue.</td></tr>`;
        });
}

function updateStats() {
    const stats = {
        total: allBookings.length,
        pending_verify: 0,
        deposit_paid: 0,
        pending_arrival: 0,
        confirmed: 0,
        cancelled: 0
    };

    allBookings.forEach(b => {
        const bs = b.bookingStatus;
        if (bs === 'pending_verification') stats.pending_verify++;
        else if (bs === 'deposit_paid') stats.deposit_paid++;
        else if (bs === 'pending_arrival') stats.pending_arrival++;
        else if (bs === 'confirmed' || bs === 'completed') stats.confirmed++;
        else if (bs === 'cancelled_expired' || bs === 'no_show') stats.cancelled++;
    });

    const statTotalLabel = document.getElementById('stat-total-label');
    if (statTotalLabel) {
        statTotalLabel.textContent = (currentBookingsView === 'archived') ? 'Total Archived' : 'Total Active';
    }

    document.getElementById('stat-total').textContent = stats.total;
    document.getElementById('stat-pending-verify').textContent = stats.pending_verify;
    document.getElementById('stat-deposit-paid').textContent = stats.deposit_paid;
    document.getElementById('stat-pending-arrival').textContent = stats.pending_arrival;
    document.getElementById('stat-confirmed').textContent = stats.confirmed;
    document.getElementById('stat-cancelled').textContent = stats.cancelled;
}

function filterBookings() {
    const search = (document.getElementById('booking-search').value || '').toLowerCase();
    const statusFilter = document.getElementById('status-filter').value;
    const paymentFilter = document.getElementById('payment-method-filter').value;

    const filtered = allBookings.filter(b => {
        const matchesSearch = !search || 
            b.ref_id.toLowerCase().includes(search) ||
            b.guestName.toLowerCase().includes(search) ||
            (b.guestEmail && b.guestEmail.toLowerCase().includes(search)) ||
            (b.guestPhone && b.guestPhone.toLowerCase().includes(search)) ||
            (b.roomName && b.roomName.toLowerCase().includes(search)) ||
            (b.gcashRefNo && b.gcashRefNo.toLowerCase().includes(search));

        const matchesStatus = !statusFilter || b.bookingStatus === statusFilter;
        const matchesPayment = !paymentFilter || 
            b.paymentMethodRaw === paymentFilter || 
            (paymentFilter === 'paymongo' && (b.paymentMethodRaw || '').toLowerCase().includes('paymongo'));

        return matchesSearch && matchesStatus && matchesPayment;
    });

    renderBookings(filtered);
}

function renderBookings(bookings) {
    const tbody = document.getElementById('bookings-table-body');
    if (!tbody) return;

    const colSpan = (currentBookingsView === 'archived') ? 8 : 7;

    if (bookings.length === 0) {
        const emptyMessage = (currentBookingsView === 'archived')
            ? '<div class="py-4 text-center"><i class="fas fa-box-open text-3xl text-gray-300 mb-2 block"></i><p class="text-sm font-semibold text-gray-600">No archived reservations</p><p class="text-xs text-gray-400 mt-1">Archived bookings will appear here when moved out of active management.</p></div>'
            : '<div class="py-4 text-center"><i class="fas fa-calendar-check text-3xl text-gray-300 mb-2 block"></i><p class="text-sm font-semibold text-gray-600">No matching active reservations found</p><p class="text-xs text-gray-400 mt-1">Try adjusting your search criteria or status filter.</p></div>';
        tbody.innerHTML = `<tr><td colspan="${colSpan}" class="p-8 text-center text-gray-400">${emptyMessage}</td></tr>`;
        return;
    }

    const isArchivedView = (currentBookingsView === 'archived');

    tbody.innerHTML = bookings.map(b => {
        // Status Badge Logic
        let statusBadge = '';
        let timerNotice = '';

        if (b.bookingStatus === 'pending_verification') {
            statusBadge = '<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800 border border-amber-200"><i class="fas fa-stopwatch"></i> Pending Verification</span>';
            if (b.expiresAt && !isArchivedView) {
                timerNotice = `<span class="block text-[10px] text-amber-700 font-mono mt-0.5">Hold expires: ${b.expiresAt.substring(11, 16)}</span>`;
            }
        } else if (b.bookingStatus === 'deposit_paid') {
            statusBadge = '<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-blue-100 text-blue-800 border border-blue-200"><i class="fas fa-coins"></i> Deposit Paid</span>';
        } else if (b.bookingStatus === 'pending_arrival') {
            statusBadge = '<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-purple-100 text-purple-800 border border-purple-200"><i class="fas fa-store"></i> Pending Arrival</span>';
            if (b.expectedArrivalTime && !isArchivedView) {
                timerNotice = `<span class="block text-[10px] text-purple-700 font-semibold mt-0.5">ETA: ${formatTime(b.expectedArrivalTime)}</span>`;
            }
        } else if (b.bookingStatus === 'confirmed') {
            statusBadge = '<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200"><i class="fas fa-check-circle"></i> Confirmed (Full)</span>';
        } else if (b.bookingStatus === 'completed') {
            statusBadge = '<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-teal-100 text-teal-800 border border-teal-200"><i class="fas fa-user-check"></i> Checked-In</span>';
        } else if (b.bookingStatus === 'cancelled_expired') {
            statusBadge = '<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-100 text-rose-700 border border-rose-200"><i class="fas fa-times-circle"></i> Expired / Cancelled</span>';
        } else if (b.bookingStatus === 'no_show') {
            statusBadge = '<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800 border border-rose-200"><i class="fas fa-user-slash"></i> No-Show</span>';
        } else {
            statusBadge = `<span class="px-2 py-0.5 rounded text-[11px] font-bold bg-gray-100 text-gray-700">${b.status}</span>`;
        }

        // Payment Method Badge
        let paymentBadge = `<span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-gray-100 text-gray-700">${b.paymentMethod}</span>`;
        if (b.paymentMethodRaw && b.paymentMethodRaw.toLowerCase().includes('paymongo')) {
            paymentBadge = `<span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200 flex items-center gap-1 w-fit"><i class="fas fa-bolt text-emerald-600"></i> ${b.paymentMethod}</span>`;
        } else if (b.paymentMethodRaw === 'full_gcash') {
            paymentBadge = `<span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200 flex items-center gap-1 w-fit"><i class="fas fa-qrcode"></i> Full GCash</span>`;
        } else if (b.paymentMethodRaw === 'deposit_gcash') {
            paymentBadge = `<span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-200 flex items-center gap-1 w-fit"><i class="fas fa-coins"></i> 20% Deposit</span>`;
        } else if (b.paymentMethodRaw === 'pay_at_hotel') {
            paymentBadge = `<span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-purple-50 text-purple-700 border border-purple-200 flex items-center gap-1 w-fit"><i class="fas fa-store"></i> Pay at Hotel</span>`;
        }

        // Build Action Buttons
        let actionButtons = '';
        if (isArchivedView) {
            actionButtons = `
                ${(b.receiptUrl || b.gcashRefNo) ? `
                    <button onclick="openReceiptModal(${b.id})" class="px-2.5 py-1 bg-blue-50 hover:bg-blue-100 text-brand-blue rounded-lg font-bold text-xs transition flex items-center gap-1 shadow-xs" title="Review Proof of Payment">
                        <i class="fas fa-search-dollar"></i> Review
                    </button>
                ` : ''}

                <button onclick="restoreBooking(${b.id}, '${b.ref_id}')" class="px-2.5 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 rounded-lg text-xs font-bold transition flex items-center gap-1 shadow-xs" title="Restore to active reservations">
                    <i class="fas fa-rotate-left"></i> Restore
                </button>

                <button onclick="printAdminInvoice('${b.id}')" class="p-1.5 text-gray-500 hover:text-brand-blue hover:bg-gray-100 rounded-lg transition" title="Print Invoice / Voucher">
                    <i class="fas fa-print"></i>
                </button>

                <button onclick="permanentlyDeleteBooking(${b.id}, '${b.ref_id}')" class="p-1.5 text-gray-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition" title="Permanently delete from database">
                    <i class="fas fa-trash"></i>
                </button>
            `;
        } else {
            actionButtons = `
                ${(b.paymongoSessionId && b.bookingStatus === 'pending_verification') ? `
                    <button onclick="adminVerifyPaymongo('${b.paymongoSessionId}', '${b.ref_id}')" class="px-2.5 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 rounded-lg text-xs font-bold transition flex items-center gap-1" title="Check PayMongo Live Status">
                        <i class="fas fa-sync-alt"></i> Verify
                    </button>
                ` : ''}

                ${(b.receiptUrl || b.gcashRefNo) ? `
                    <button onclick="openReceiptModal(${b.id})" class="px-2.5 py-1 bg-blue-50 hover:bg-blue-100 text-brand-blue rounded-lg font-bold text-xs transition flex items-center gap-1" title="Verify Proof of Payment">
                        <i class="fas fa-search-dollar"></i> Review
                    </button>
                ` : ''}

                ${(b.bookingStatus === 'pending_verification') ? `
                    <button onclick="approveBooking(${b.id})" class="p-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 rounded-lg transition" title="Approve Booking">
                        <i class="fas fa-check"></i>
                    </button>
                    <button onclick="rejectBooking(${b.id})" class="p-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 rounded-lg transition" title="Reject Receipt">
                        <i class="fas fa-times"></i>
                    </button>
                ` : ''}

                ${(b.bookingStatus === 'deposit_paid' || b.bookingStatus === 'pending_arrival') ? `
                    <button onclick="checkInBooking(${b.id})" class="px-2 py-1 bg-brand-blue hover:bg-sky-600 text-white rounded-lg text-[11px] font-bold transition flex items-center gap-1" title="Mark Checked-In & Collect Balance">
                        <i class="fas fa-concierge-bell"></i> Check-In
                    </button>
                ` : ''}

                <button onclick="printAdminInvoice('${b.id}')" class="p-1.5 text-gray-500 hover:text-brand-blue hover:bg-gray-100 rounded-lg transition" title="Print Invoice / Voucher">
                    <i class="fas fa-print"></i>
                </button>
                
                <button onclick="archiveBooking(${b.id}, '${b.ref_id}')" class="p-1.5 text-gray-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition" title="Archive reservation">
                    <i class="fas fa-box-archive"></i>
                </button>
            `;
        }

        return `
            <tr class="hover:bg-slate-50/70 transition ${isArchivedView ? 'bg-slate-50/30' : ''}">
                <!-- Ref ID & Date -->
                <td class="p-3.5">
                    <span class="font-mono font-bold text-gray-800 text-xs block">${b.ref_id}</span>
                    <span class="text-[10px] text-gray-400">${formatDate(b.created_at)}</span>
                </td>

                <!-- Guest Info -->
                <td class="p-3.5">
                    <div class="font-bold text-gray-800 text-xs">${b.guestName}</div>
                    <div class="text-[11px] text-gray-500">${b.guestEmail || '-'}</div>
                    <div class="text-[10px] text-gray-400 font-mono">${b.guestPhone || ''}</div>
                    <div class="text-[10px] text-slate-500 font-medium mt-0.5"><i class="fas fa-users text-amber-500 mr-1"></i>${(b.adults || 1) + (b.children || 0)} Guests (${b.adults || 1}A, ${b.children || 0}C${Array.isArray(b.childrenAges) && b.childrenAges.length > 0 ? ` · Ages: ${b.childrenAges.map(a => a == 0 ? '0' : a).join(', ')}` : ''})</div>
                    ${(b.specialRequests && b.specialRequests.trim()) ? `<div class="mt-1 flex items-start gap-1 max-w-[180px]" title="${b.specialRequests.replace(/"/g, '&quot;')}"><span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-semibold bg-violet-50 text-violet-700 border border-violet-200 truncate max-w-full"><i class="fas fa-comment-dots text-violet-500 flex-shrink-0"></i><span class="truncate">${b.specialRequests}</span></span></div>` : ''}
                </td>

                <!-- Room & Stay -->
                <td class="p-3.5">
                    <div class="font-semibold text-gray-800 flex items-center gap-1.5 flex-wrap">
                        ${b.roomsCount > 1 ? `<span class="bg-indigo-50 text-indigo-700 px-1.5 py-0.5 rounded text-[10px] font-bold border border-indigo-200">${b.roomsCount} Rooms</span>` : ''}
                        ${(b.roomId === 5 || (b.roomType && b.roomType.toLowerCase().includes('dorm')) || (b.roomName && b.roomName.toLowerCase().includes('room 5'))) ? `<span class="bg-teal-50 text-teal-800 px-1.5 py-0.5 rounded text-[10px] font-bold border border-teal-200"><i class="fas fa-bed mr-0.5 text-teal-600"></i>${b.bedsCount || 1} Bed${(b.bedsCount || 1) > 1 ? 's' : ''} (Dorm)</span>` : ''}
                        <span>${b.roomName}</span>
                    </div>
                    <div class="text-[11px] text-gray-500">${formatDate(b.checkIn)} &rarr; ${formatDate(b.checkOut)} (${b.nights}n)</div>
                </td>

                <!-- Payment Pathway -->
                <td class="p-3.5 space-y-1">
                    ${paymentBadge}
                    ${b.gcashRefNo ? `<div class="text-[10px] font-mono text-gray-500 truncate max-w-[140px]" title="GCash Ref: ${b.gcashRefNo}">GCash: ${b.gcashRefNo}</div>` : ''}
                    ${b.paymongoPaymentId ? `<div class="text-[10px] font-mono text-emerald-700 truncate max-w-[140px]" title="PayMongo: ${b.paymongoPaymentId}"><i class="fas fa-receipt mr-1"></i>${b.paymongoPaymentId}</div>` : ''}
                    ${(!b.paymongoPaymentId && b.paymongoSessionId) ? `<div class="text-[9px] font-mono text-gray-400 truncate max-w-[140px]" title="Session: ${b.paymongoSessionId}">${b.paymongoSessionId}</div>` : ''}
                </td>

                <!-- Paid vs Balance -->
                <td class="p-3.5">
                    <div class="font-bold text-gray-900">₱${parseFloat(b.total).toFixed(2)}</div>
                    <div class="text-[11px] text-emerald-600 font-semibold">Paid: ₱${parseFloat(b.paidAmount).toFixed(2)}</div>
                    <div class="text-[11px] text-brand-blue font-semibold">Balance: ₱${parseFloat(b.balanceDue).toFixed(2)}</div>
                </td>

                <!-- Status & Hold Timer (or Status for Archived) -->
                <td class="p-3.5">
                    ${statusBadge}
                    ${timerNotice}
                </td>

                ${isArchivedView ? `
                    <!-- Archived Date -->
                    <td class="p-3.5">
                        <span class="font-semibold text-gray-700 text-xs block">${formatDate(b.archivedAt)}</span>
                        <span class="text-[10px] text-gray-400 font-mono">${b.archivedAt ? b.archivedAt.substring(11, 16) : '-'}</span>
                    </td>
                ` : ''}

                <!-- Actions -->
                <td class="p-3.5 text-right">
                    <div class="flex items-center justify-end gap-1.5 flex-wrap">
                        ${actionButtons}
                    </div>
                </td>
            </tr>
        `;
    }).join('');
}

// Side-by-Side Receipt Modal Operations
function openReceiptModal(bookingId) {
    const booking = allBookings.find(b => b.id == bookingId);
    if (!booking) return;

    activeModalBooking = booking;

    const isDorm = (booking.roomId === 5 || (booking.roomType && booking.roomType.toLowerCase().includes('dorm')) || (booking.roomName && booking.roomName.toLowerCase().includes('room 5')));
    const dormBedText = isDorm ? ` (${booking.bedsCount || 1} Bed${(booking.bedsCount || 1) > 1 ? 's' : ''} in Dormitory)` : (booking.roomsCount > 1 ? ` (${booking.roomsCount} Rooms)` : '');
    const roomInfoEl = document.getElementById('modal-room-info');
    if (roomInfoEl) {
        roomInfoEl.textContent = (booking.roomName || 'Room') + dormBedText;
    }

    document.getElementById('modal-ref-id').textContent = booking.ref_id;
    document.getElementById('modal-guest-name').textContent = booking.guestName;
    document.getElementById('modal-guest-email').textContent = booking.guestEmail || 'No email provided';
    document.getElementById('modal-guest-phone').textContent = booking.guestPhone || 'No phone provided';
    document.getElementById('modal-gcash-ref').textContent = booking.gcashRefNo || 'No Reference Entered';
    document.getElementById('modal-payment-method').textContent = booking.paymentMethod;
    document.getElementById('modal-total-amount').textContent = '₱' + parseFloat(booking.total).toFixed(2);
    document.getElementById('modal-paid-amount').textContent = '₱' + parseFloat(booking.paidAmount).toFixed(2);
    document.getElementById('modal-balance-due').textContent = '₱' + parseFloat(booking.balanceDue).toFixed(2);

    const expiresBox = document.getElementById('modal-hold-expiry-box');
    const expiresText = document.getElementById('modal-expires-at');
    if (booking.expiresAt && booking.bookingStatus === 'pending_verification' && !booking.isArchived) {
        expiresBox.classList.remove('hidden');
        expiresText.textContent = booking.expiresAt + ' (2-Hour Hold Timer)';
    } else {
        expiresBox.classList.add('hidden');
    }

    // Special Requests
    const specialReqBox = document.getElementById('modal-special-requests-box');
    const specialReqText = document.getElementById('modal-special-requests-text');
    const specialReq = (booking.specialRequests || '').trim();
    if (specialReq) {
        specialReqText.textContent = specialReq;
        specialReqBox.classList.remove('hidden');
    } else {
        specialReqBox.classList.add('hidden');
    }

    const imgEl = document.getElementById('modal-receipt-img');
    const linkEl = document.getElementById('modal-receipt-link');
    const noReceiptEl = document.getElementById('modal-no-receipt');

    if (booking.receiptUrl) {
        imgEl.src = '../' + booking.receiptUrl;
        imgEl.classList.remove('hidden');
        linkEl.href = '../' + booking.receiptUrl;
        linkEl.classList.remove('hidden');
        noReceiptEl.classList.add('hidden');
    } else {
        imgEl.classList.add('hidden');
        linkEl.classList.add('hidden');
        noReceiptEl.classList.remove('hidden');
    }

    // Modal Button visibility
    const approveBtn = document.getElementById('btn-modal-approve');
    const rejectBtn = document.getElementById('btn-modal-reject');
    const checkinBtn = document.getElementById('btn-modal-checkin');
    const archiveBtn = document.getElementById('btn-modal-archive');
    const restoreBtn = document.getElementById('btn-modal-restore');

    // Reset visibility
    if (approveBtn) approveBtn.classList.add('hidden');
    if (rejectBtn) rejectBtn.classList.add('hidden');
    if (checkinBtn) checkinBtn.classList.add('hidden');
    if (archiveBtn) archiveBtn.classList.add('hidden');
    if (restoreBtn) restoreBtn.classList.add('hidden');

    if (booking.isArchived) {
        if (restoreBtn) restoreBtn.classList.remove('hidden');
    } else {
        if (booking.bookingStatus === 'pending_verification') {
            if (approveBtn) approveBtn.classList.remove('hidden');
            if (rejectBtn) rejectBtn.classList.remove('hidden');
        } else if (booking.bookingStatus === 'deposit_paid' || booking.bookingStatus === 'pending_arrival') {
            if (checkinBtn) checkinBtn.classList.remove('hidden');
            if (archiveBtn) archiveBtn.classList.remove('hidden');
        } else {
            if (archiveBtn) archiveBtn.classList.remove('hidden');
        }
    }

    document.getElementById('receipt-modal').classList.remove('hidden');
}

function closeReceiptModal() {
    document.getElementById('receipt-modal').classList.add('hidden');
    activeModalBooking = null;
}

function copyModalRef() {
    if (activeModalBooking && activeModalBooking.gcashRefNo) {
        navigator.clipboard.writeText(activeModalBooking.gcashRefNo).then(() => {
            showToast('GCash Reference copied: ' + activeModalBooking.gcashRefNo, 'success');
        });
    }
}

function approveFromModal() {
    if (!activeModalBooking) return;
    approveBooking(activeModalBooking.id);
    closeReceiptModal();
}

function rejectFromModal() {
    if (!activeModalBooking) return;
    rejectBooking(activeModalBooking.id);
    closeReceiptModal();
}

function checkInFromModal() {
    if (!activeModalBooking) return;
    checkInBooking(activeModalBooking.id);
    closeReceiptModal();
}

function archiveFromModal() {
    if (!activeModalBooking) return;
    const bId = activeModalBooking.id;
    const ref = activeModalBooking.ref_id;
    closeReceiptModal();
    archiveBooking(bId, ref);
}

function restoreFromModal() {
    if (!activeModalBooking) return;
    const bId = activeModalBooking.id;
    const ref = activeModalBooking.ref_id;
    closeReceiptModal();
    restoreBooking(bId, ref);
}

// Action Handlers
function approveBooking(bookingId) {
    showConfirm('Approve this GCash payment and lock the room reservation?', () => {
        fetch('../actions/bookings_action.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'approve', id: bookingId })
        })
        .then(res => res.json())
        .then(data => {
            showToast(data.message || 'Booking approved successfully!', data.success ? 'success' : 'error');
            if (data.success) {
                loadBookings();
                if (typeof loadBookingCounts === 'function') loadBookingCounts();
            }
        })
        .catch(err => {
            console.error(err);
            showToast('Error approving booking', 'error');
        });
    });
}

function adminVerifyPaymongo(sessionId, refId) {
    showToast('Checking PayMongo API status...', 'info');
    fetch(`../actions/paymongo_action.php?action=verify_session&session_id=${encodeURIComponent(sessionId)}&ref_id=${encodeURIComponent(refId)}`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                if (data.is_paid) {
                    showToast('Payment verified as PAID on PayMongo! Booking is Confirmed.', 'success');
                    loadBookings();
                    if (typeof loadBookingCounts === 'function') loadBookingCounts();
                } else {
                    showToast(`PayMongo status: ${data.session_status || 'Awaiting Payment'}.`, 'warning');
                }
            } else {
                showToast('PayMongo error: ' + (data.message || 'Verification failed'), 'error');
            }
        })
        .catch(err => {
            console.error(err);
            showToast('Network error verifying PayMongo status', 'error');
        });
}

function rejectBooking(bookingId) {
    showConfirm('Reject this booking? The room lock will be released immediately.', () => {
        fetch('../actions/bookings_action.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'reject', id: bookingId })
        })
        .then(res => res.json())
        .then(data => {
            showToast(data.message || 'Booking rejected.', data.success ? 'info' : 'error');
            if (data.success) {
                loadBookings();
                if (typeof loadBookingCounts === 'function') loadBookingCounts();
            }
        })
        .catch(err => {
            console.error(err);
            showToast('Error rejecting booking', 'error');
        });
    });
}

function checkInBooking(bookingId) {
    showConfirm('Mark guest checked in and collect remaining balance?', () => {
        fetch('../actions/bookings_action.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'check_in', id: bookingId })
        })
        .then(res => res.json())
        .then(data => {
            showToast(data.message || 'Check-in completed!', data.success ? 'success' : 'error');
            if (data.success) {
                loadBookings();
                if (typeof loadBookingCounts === 'function') loadBookingCounts();
            }
        })
        .catch(err => {
            console.error(err);
            showToast('Error checking in booking', 'error');
        });
    });
}

function archiveBooking(bookingId, refId) {
    const label = refId ? `reservation <strong>${refId}</strong>` : 'this reservation';
    showConfirm(
        `Archive ${label}?<br><span class="text-xs text-gray-500 mt-1 block">The record will be safely moved to the Archive tab. All payment information, receipts, and folio history stay intact.</span>`,
        () => {
            fetch('../actions/bookings_action.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'archive', id: bookingId })
            })
            .then(res => res.json())
            .then(data => {
                showToast(data.message || 'Reservation archived successfully.', data.success ? 'success' : 'error');
                if (data.success) {
                    loadBookings();
                    if (typeof loadBookingCounts === 'function') loadBookingCounts();
                }
            })
            .catch(err => {
                console.error(err);
                showToast('Error archiving reservation', 'error');
            });
        },
        { title: 'Archive Reservation', confirmLabel: 'Archive Record', variant: 'primary' }
    );
}

function restoreBooking(bookingId, refId) {
    const label = refId ? `reservation <strong>${refId}</strong>` : 'this reservation';
    showConfirm(
        `Restore ${label} back to the active reservations board?`,
        () => {
            fetch('../actions/bookings_action.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'restore', id: bookingId })
            })
            .then(res => res.json())
            .then(data => {
                showToast(data.message || 'Reservation restored successfully.', data.success ? 'success' : 'error');
                if (data.success) {
                    loadBookings();
                    if (typeof loadBookingCounts === 'function') loadBookingCounts();
                }
            })
            .catch(err => {
                console.error(err);
                showToast('Error restoring reservation', 'error');
            });
        },
        { title: 'Restore Reservation', confirmLabel: 'Restore to Active', variant: 'primary' }
    );
}

function permanentlyDeleteBooking(bookingId, refId) {
    const label = refId ? `reservation <strong>${refId}</strong>` : 'this reservation';
    showConfirm(
        `<span class="text-rose-600 font-bold block mb-1">Permanent Deletion Warning:</span>Are you sure you want to permanently delete ${label}?<br><span class="text-xs text-rose-500 mt-1 block">This permanently deletes the record and its receipt file from the database. This action cannot be undone.</span>`,
        () => {
            fetch('../actions/bookings_action.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'delete', id: bookingId })
            })
            .then(res => res.json())
            .then(data => {
                showToast(data.message || 'Reservation deleted permanently.', data.success ? 'info' : 'error');
                if (data.success) {
                    loadBookings();
                    if (typeof loadBookingCounts === 'function') loadBookingCounts();
                }
            })
            .catch(err => {
                console.error(err);
                showToast('Error deleting reservation', 'error');
            });
        },
        { title: 'Permanent Delete', confirmLabel: 'Delete Forever' }
    );
}

function archiveAllCancelledBookings() {
    showConfirm(
        `Archive all cancelled and expired hold reservations?<br><span class="text-xs text-gray-500 mt-1 block">This will clean up your active dashboard by moving all cancelled/expired bookings into the Archive tab.</span>`,
        () => {
            fetch('../actions/bookings_action.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'batch_archive', type: 'cancelled' })
            })
            .then(res => res.json())
            .then(data => {
                showToast(data.message || 'Cancelled reservations archived.', data.success ? 'success' : 'error');
                if (data.success) {
                    loadBookings();
                    if (typeof loadBookingCounts === 'function') loadBookingCounts();
                }
            })
            .catch(err => {
                console.error(err);
                showToast('Error archiving cancelled reservations', 'error');
            });
        },
        { title: 'Archive Cancelled Reservations', confirmLabel: 'Archive Cancelled', variant: 'primary' }
    );
}

// Backward-compatible delete handler
function deleteBooking(bookingId) {
    if (currentBookingsView === 'archived') {
        permanentlyDeleteBooking(bookingId);
    } else {
        const b = allBookings.find(item => item.id == bookingId);
        archiveBooking(bookingId, b ? b.ref_id : null);
    }
}
// Open print popup in the same tab using an iframe modal
function openSameTabPrintWindow() {
    let modal = document.getElementById('same-tab-print-modal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'same-tab-print-modal';
        modal.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.6);z-index:999999;display:flex;align-items:center;justify-content:center;';
        
        let container = document.createElement('div');
        container.style.cssText = 'width:90%;height:90%;background:#fff;border-radius:8px;box-shadow:0 10px 40px rgba(0,0,0,0.3);display:flex;flex-direction:column;overflow:hidden;';
        
        let header = document.createElement('div');
        header.style.cssText = 'padding:12px 20px;background:#f8fafc;border-bottom:1px solid #e2e8f0;display:flex;justify-content:space-between;align-items:center;';
        header.innerHTML = '<strong style="color:#1e293b;font-family:sans-serif;font-size:16px;">Print Preview</strong><button onclick="document.getElementById(\'same-tab-print-modal\').style.display=\'none\'" style="background:#ef4444;color:#fff;border:none;padding:6px 14px;border-radius:6px;cursor:pointer;font-weight:bold;">Close</button>';
        
        let iframe = document.createElement('iframe');
        iframe.id = 'same-tab-print-iframe';
        iframe.style.cssText = 'flex-grow:1;border:none;width:100%;background:#fff;';
        
        container.appendChild(header);
        container.appendChild(iframe);
        modal.appendChild(container);
        document.body.appendChild(modal);
    }
    
    modal.style.display = 'flex';
    let iframe = document.getElementById('same-tab-print-iframe');
    let win = iframe.contentWindow;
    
    win.document.open();
    win.document.write('');
    win.document.close();
    
    return win;
}

function printAdminInvoice(bookingId) {
    const booking = allBookings.find(b => b.id == bookingId);
    if (!booking) return;

    const dateNow = new Date().toLocaleDateString();
    const guestName = booking.guestName || 'Guest';
    const guestEmail = booking.guestEmail || 'N/A';
    const paymentMethod = booking.paymentMethod || 'Full Payment';
    const pricePerNight = booking.room_price || ((booking.total - 15) / Math.max(1, booking.nights));
    const total = parseFloat(booking.total);
    const paid = parseFloat(booking.paidAmount || 0);
    const balance = parseFloat(booking.balanceDue || (total - paid));

    const invoiceHTML = `
        <html>
            <head>
                <title>Voucher - ${booking.ref_id}</title>
                <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
                <style>
                    body { font-family: sans-serif; padding: 20px; }
                    @media print {
                        .no-print { display: none; }
                        body { padding: 0; }
                    }
                </style>
            </head>
            <body class="bg-gray-50">
                <div class="max-w-2xl mx-auto bg-white p-8 rounded-2xl shadow">
                    <div class="flex justify-between items-start mb-8 border-b pb-6">
                        <div>
                            <h2 class="text-3xl font-serif font-bold text-gray-800 mb-1">W Island Backpackers Inn</h2>
                            <p class="text-xs text-teal-700 font-semibold uppercase tracking-wider mb-1">W Island Hub Hospitality Services</p>
                            <p class="text-xs text-gray-500"><i class="fas fa-map-marker-alt text-red-500 mr-1"></i>Dao, Dauis - Panglao Center Road, Dauis, Panglao Island, Bohol 6339, Philippines</p>
                            <p class="text-[11px] text-gray-400 mt-0.5">DOT Accr. No.: DOT-R7-ACC-2024-0318 &bull; BIR TIN: 432-876-543-00000 &bull; Permit: BP-2024-DAUIS-0521</p>
                        </div>
                        <div class="text-right flex items-center gap-4">
                            <div class="text-right">
                                <div class="text-xl font-bold text-blue-600 mb-1">FOLIO INVOICE</div>
                                <div class="text-sm font-mono font-semibold text-gray-600">Ref: ${booking.ref_id}</div>
                                <div class="text-xs text-gray-500">Issued: ${dateNow}</div>
                            </div>
                            <div class="bg-white p-1 border border-gray-100 rounded-lg shadow-sm">
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=80x80&data=${encodeURIComponent(booking.ref_id)}" alt="QR Code" class="w-20 h-20">
                            </div>
                        </div>
                    </div>
    
                    <div class="grid grid-cols-2 gap-8 mb-8 text-sm">
                        <div>
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Billed To:</h4>
                            <div class="font-bold text-gray-800 text-base">${guestName}</div>
                            <div class="text-gray-600">${guestEmail}</div>
                            <div class="text-gray-500 font-mono text-xs">${booking.guestPhone || ''}</div>
                            <div class="text-gray-600 text-xs mt-1">Guests: ${(booking.adults || 1) + (booking.children || 0)} (${booking.adults || 1} Adult${(booking.adults || 1) > 1 ? 's' : ''}${booking.children > 0 ? `, ${booking.children} Child${booking.children > 1 ? 'ren' : ''}` : ''}${Array.isArray(booking.childrenAges) && booking.childrenAges.length > 0 ? ` · Ages: ${booking.childrenAges.map(a => a == 0 ? '0' : a).join(', ')}` : ''})</div>
                        </div>
                        <div class="text-right">
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Payment Pathway:</h4>
                            <div class="font-bold text-gray-800">${paymentMethod}</div>
                            <div class="text-gray-600">Status: <strong class="text-blue-600">${booking.bookingStatus}</strong></div>
                            ${booking.gcashRefNo ? `<div class="text-xs font-mono text-gray-500">GCash Ref: ${booking.gcashRefNo}</div>` : ''}
                        </div>
                    </div>
    
                    <div class="mb-8">
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Reservation Details:</h4>
                        <div class="bg-gray-50 rounded-xl p-4 border border-gray-100 grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-gray-500 block mb-1">Room</span>
                                <strong class="text-gray-800 text-base">${booking.roomName}</strong>
                            </div>
                            <div>
                                <span class="text-gray-500 block mb-1">Nights</span>
                                <strong class="text-gray-800">${booking.nights}</strong>
                            </div>
                            <div>
                                <span class="text-gray-500 block mb-1">Check-in</span>
                                <strong class="text-gray-800">${formatDate(booking.checkIn)}</strong>
                            </div>
                            <div>
                                <span class="text-gray-500 block mb-1">Check-out</span>
                                <strong class="text-gray-800">${formatDate(booking.checkOut)}</strong>
                            </div>
                        </div>
                    </div>
    
                    <table class="w-full text-left border-collapse mb-8 text-sm">
                        <thead>
                            <tr class="border-b-2 border-gray-200">
                                <th class="py-3 font-bold text-gray-800">Description</th>
                                <th class="py-3 font-bold text-gray-800 text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="border-b border-gray-100">
                                <td class="py-3">
                                    <div class="font-bold text-gray-800">Room Rate</div>
                                    <div class="text-xs text-gray-500">₱${parseFloat(pricePerNight).toFixed(2)} x ${booking.nights} nights</div>
                                </td>
                                <td class="py-3 text-right font-medium">₱${(parseFloat(pricePerNight) * parseInt(booking.nights)).toFixed(2)}</td>
                            </tr>
                            <tr class="border-b border-gray-100">
                                <td class="py-3">
                                    <div class="font-bold text-gray-800">Taxes & Fees</div>
                                </td>
                                <td class="py-3 text-right font-medium">₱15.00</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr class="border-b border-gray-200">
                                <td class="py-3 font-bold text-base text-gray-800 text-right pr-4">Total Bill:</td>
                                <td class="py-3 font-bold text-xl text-gray-900 text-right">₱${total.toFixed(2)}</td>
                            </tr>
                            <tr class="bg-green-50">
                                <td class="py-2 font-bold text-xs text-green-800 text-right pr-4">Paid Amount:</td>
                                <td class="py-2 font-bold text-sm text-green-700 text-right">₱${paid.toFixed(2)}</td>
                            </tr>
                            <tr class="bg-blue-50">
                                <td class="py-2 font-bold text-xs text-blue-800 text-right pr-4">Balance at Front Desk:</td>
                                <td class="py-2 font-bold text-sm text-blue-700 text-right">₱${balance.toFixed(2)}</td>
                            </tr>
                        </tfoot>
                    </table>
                    
                    <div class="text-center text-xs text-gray-500 mt-8 pt-6 border-t border-gray-200 space-y-2">
                        <div class="flex flex-wrap justify-center items-center gap-4 text-[11px] text-gray-700 font-medium bg-gray-50 py-2 px-3 rounded-lg border border-gray-100">
                            <span>Standard Check-in: <strong>02:00 PM</strong></span>
                            <span>Check-out: <strong>11:00 AM</strong></span>
                            <span>Front Desk Hotline: <strong>+63 945 688 2266</strong></span>
                            <span>Email: <strong>wislandhub2025@gmail.com</strong></span>
                        </div>
                        <p class="text-gray-600 text-[11px]">
                            Thank you for staying with W Island Backpackers Inn, Dauis, Panglao Island, Bohol!
                        </p>
                        <p class="text-[10px] text-gray-400">
                            Subject to standard hostel rules & RA 10173 Philippine Data Privacy Act compliance. Official computerized PMS folio record.
                        </p>
                    </div>

                    <div class="mt-8 text-center no-print">
                        <button onclick="window.print();" class="px-6 py-2 bg-blue-600 text-white rounded-xl shadow hover:bg-blue-700 font-semibold">
                            <i class="fas fa-print mr-1"></i> Print Invoice
                        </button>
                    </div>
                </div>
            </body>
        </html>
    `;

    const printWindow = openSameTabPrintWindow();
    printWindow.document.write(invoiceHTML);
    printWindow.document.close();
}

// Load bookings on load
document.addEventListener('DOMContentLoaded', loadBookings);
</script>

