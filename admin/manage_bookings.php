<!-- ================= Manage Bookings Page ================= -->
<div id="manage-bookings" class="page-section">
    <div class="p-6 max-w-7xl mx-auto">
        <!-- View Tabs & Batch Actions Toolbar -->
        <div class="flex flex-wrap items-center justify-between gap-4 mb-5">
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

            <!-- Archive View Indicator -->
            <div id="archive-info-badge" class="hidden text-xs font-semibold text-amber-700 bg-amber-50 border border-amber-200 px-3.5 py-1.5 rounded-xl inline-flex items-center gap-1.5 shadow-2xs">
                <i class="fas fa-box-archive text-amber-600"></i>
                <span>Archived Reservations</span>
            </div>
        </div>

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
        
        <!-- Action Controls & Filters Bar -->
        <div class="flex flex-wrap items-center gap-2.5 mb-4 w-full">
            <div class="relative flex-1 min-w-[240px]">
                <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                <input type="text" id="booking-search" placeholder="Search ref, guest, phone, room..." 
                    class="w-full h-10 pl-9 pr-3.5 border border-gray-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-brand-blue/30 bg-white font-medium shadow-2xs"
                    oninput="filterBookings()">
            </div>
            
            <select id="status-filter" class="h-10 px-3.5 border border-gray-200 rounded-xl text-xs bg-white font-medium text-gray-700 focus:outline-none focus:ring-2 focus:ring-brand-blue/30 shadow-2xs shrink-0 cursor-pointer" onchange="filterBookings()">
                <option value="">All Statuses</option>
                <option value="pending_verification">Pending Verification / Hold</option>
                <option value="deposit_paid">Deposit Paid (Balance Due)</option>
                <option value="confirmed">Confirmed (Full Payment)</option>
                <option value="pending_arrival">Pending Arrival (Pay at Hotel)</option>
                <option value="completed">Completed / Checked-In</option>
                <option value="cancelled_expired">Cancelled / Expired Hold</option>
                <option value="no_show">No-Show Released</option>
            </select>

            <select id="payment-method-filter" class="h-10 px-3.5 border border-gray-200 rounded-xl text-xs bg-white font-medium text-gray-700 focus:outline-none focus:ring-2 focus:ring-brand-blue/30 shadow-2xs shrink-0 cursor-pointer" onchange="filterBookings()">
                <option value="">All Payment Types</option>
                <option value="full_gcash">📱 Full GCash (100%)</option>
                <option value="deposit_hotel">🪙 Deposit / Pay at Hotel</option>
                <option value="walkin">💵 Walk-In / Cash (Front Desk)</option>
                <option value="other_payment">💳 Other Payment Method</option>
            </select>

            <button type="button" onclick="openWalkinModal()" class="h-10 bg-emerald-600 hover:bg-emerald-700 text-white px-4 rounded-xl text-xs font-bold transition inline-flex items-center gap-2 shadow-xs cursor-pointer shrink-0" title="Record physical walk-in booking at front desk">
                <i class="fas fa-user-plus"></i>
                <span>Walk-In Booking</span>
            </button>

            <button onclick="loadBookings()" class="h-10 bg-brand-blue hover:bg-sky-600 text-white px-4 rounded-xl text-xs font-semibold transition inline-flex items-center gap-2 shadow-xs cursor-pointer shrink-0" title="Refresh & trigger auto-expiry">
                <i class="fas fa-sync-alt" id="refresh-icon"></i>
                <span>Refresh</span>
            </button>
        </div>
        
        <!-- Bookings Master Table -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[1100px]">
                    <thead id="bookings-table-head">
                        <tr class="bg-slate-50 text-[11px] font-bold text-gray-500 uppercase tracking-wider border-b border-gray-100">
                            <th class="p-3.5 align-middle whitespace-nowrap">Reference ID</th>
                            <th class="p-3.5 align-middle whitespace-nowrap">Guest Information</th>
                            <th class="p-3.5 align-middle whitespace-nowrap">Room & Stay</th>
                            <th class="p-3.5 align-middle whitespace-nowrap">Payment Pathway</th>
                            <th class="p-3.5 align-middle whitespace-nowrap">Paid / Balance Due</th>
                            <th class="p-3.5 align-middle whitespace-nowrap">Status & Hold Timer</th>
                            <th class="p-3.5 align-middle text-right whitespace-nowrap">Actions</th>
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
                    <button id="btn-modal-noshow" onclick="markNoShowFromModal()" class="w-full bg-rose-600 hover:bg-rose-700 text-white font-bold py-2.5 rounded-xl shadow transition text-xs flex items-center justify-center gap-2">
                        <i class="fas fa-user-slash"></i> Mark as No-Show & Release Room
                    </button>
                    <button id="btn-modal-archive" onclick="archiveFromModal()" class="w-full bg-amber-50 hover:bg-amber-100 text-amber-800 font-bold py-2 rounded-xl border border-amber-200 transition text-xs flex items-center justify-center gap-2">
                        <i class="fas fa-box-archive"></i> Archive Reservation
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ================= PHYSICAL WALK-IN BOOKING MODAL ================= -->
<div id="walkin-modal" class="hidden fixed inset-0 bg-black/75 flex items-center justify-center z-[130] p-4 overflow-y-auto">
    <div class="bg-white rounded-3xl w-full max-w-3xl my-8 flex flex-col shadow-2xl overflow-hidden border border-gray-100 max-h-[92vh]">
        <!-- Header -->
        <div class="p-5 bg-gradient-to-r from-slate-900 via-brand-dark to-emerald-800 text-white flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-white/10 flex items-center justify-center font-bold text-lg text-emerald-300">
                    <i class="fas fa-user-plus"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold">Front Desk Walk-In Booking</h3>
                    <p class="text-xs text-emerald-200">Record on-site guest reservation, collect payment, and update room occupancy</p>
                </div>
            </div>
            <button type="button" onclick="closeWalkinModal()" class="text-white/70 hover:text-white hover:bg-white/10 p-2 rounded-xl transition">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <!-- Body Form -->
        <form id="walkin-booking-form" onsubmit="submitWalkinBooking(event)" class="overflow-y-auto p-6 space-y-5">
            <!-- Guest Information -->
            <div>
                <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2.5 flex items-center gap-1.5">
                    <i class="fas fa-id-card text-emerald-600"></i> Guest Information
                </h4>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="sm:col-span-1">
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Full Name <span class="text-rose-500">*</span></label>
                        <input type="text" id="walkin-guest-name" required placeholder="e.g. Juan Dela Cruz" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500/30 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Phone Number <span class="text-rose-500">*</span></label>
                        <input type="text" id="walkin-guest-phone" required placeholder="e.g. 09171234567" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500/30 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Email <span class="text-gray-400 font-normal">(Optional)</span></label>
                        <input type="email" id="walkin-guest-email" placeholder="e.g. guest@example.com" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500/30 focus:outline-none">
                    </div>
                </div>
            </div>

            <!-- Room & Stay Details -->
            <div>
                <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2.5 flex items-center gap-1.5">
                    <i class="fas fa-bed text-emerald-600"></i> Room & Stay Details
                </h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-2">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Select Room <span class="text-rose-500">*</span></label>
                        <select id="walkin-room-id" required onchange="handleWalkinRoomChange()" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-xs bg-white font-medium focus:ring-2 focus:ring-emerald-500/30 focus:outline-none">
                            <option value="">Loading available rooms...</option>
                        </select>
                        <p id="walkin-room-hint" class="text-[11px] text-gray-500 mt-1"></p>
                    </div>

                    <div id="walkin-dorm-beds-container" class="hidden sm:col-span-2 bg-teal-50 border border-teal-200 rounded-xl p-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <label class="block text-xs font-bold text-teal-900 mb-0.5">Dormitory Bed Selection</label>
                                <p class="text-[11px] text-teal-700">Dorm rate: ₱460.00 per bed / night</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <label class="text-xs font-semibold text-teal-900">Beds count:</label>
                                <input type="number" id="walkin-beds-count" min="1" max="8" value="1" onchange="calculateWalkinBill()" class="w-16 px-2.5 py-1.5 border border-teal-300 rounded-lg text-xs font-bold text-teal-900 bg-white text-center focus:outline-none">
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Check-in Date <span class="text-rose-500">*</span></label>
                        <input type="date" id="walkin-check-in" required onchange="calculateWalkinBill()" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-emerald-500/30 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Check-out Date <span class="text-rose-500">*</span></label>
                        <input type="date" id="walkin-check-out" required onchange="calculateWalkinBill()" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-emerald-500/30 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Guests Count</label>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <span class="text-[10px] text-gray-500">Adults:</span>
                                <input type="number" id="walkin-adults" min="1" value="1" class="w-full px-2 py-1.5 border border-gray-200 rounded-lg text-xs">
                            </div>
                            <div>
                                <span class="text-[10px] text-gray-500">Children:</span>
                                <input type="number" id="walkin-children" min="0" value="0" class="w-full px-2 py-1.5 border border-gray-200 rounded-lg text-xs">
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Arrival Time</label>
                        <input type="time" id="walkin-arrival-time" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-emerald-500/30 focus:outline-none">
                    </div>
                </div>
            </div>

            <!-- Payment & Front Desk Status -->
            <div>
                <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2.5 flex items-center gap-1.5">
                    <i class="fas fa-hand-holding-dollar text-emerald-600"></i> Payment & Front Desk Status
                </h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-2">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Payment Method</label>
                        <select id="walkin-payment-method" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-xs bg-white focus:ring-2 focus:ring-emerald-500/30 focus:outline-none font-medium">
                            <option value="Cash (Front Desk)">💵 Cash (Front Desk Counter)</option>
                            <option value="GCash (Front Desk)">📱 GCash (Front Desk QR / Transfer)</option>
                            <option value="Maya (Front Desk)">🟢 Maya (Front Desk QR / E-Wallet)</option>
                            <option value="Card / POS">💳 Credit / Debit Card (Terminal / POS)</option>
                            <option value="Pay at Hotel">🏨 Pay at Hotel</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Reservation & Occupancy Status</label>
                        <select id="walkin-booking-status" onchange="handleWalkinStatusChange()" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-xs bg-white focus:ring-2 focus:ring-emerald-500/30 focus:outline-none font-bold text-emerald-800">
                            <option value="completed" selected>✅ Checked-In & Room Occupied (Immediate Check-In)</option>
                            <option value="confirmed">🟢 Confirmed Reservation (Full Payment Collected)</option>
                            <option value="deposit_paid">🔵 Deposit Paid (Balance Due at Check-In)</option>
                            <option value="pending_arrival">🟣 Pending Arrival (Pay Later at Counter)</option>
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Front Desk Notes / Special Requests</label>
                        <input type="text" id="walkin-special-requests" placeholder="e.g. Physical walk-in guest, payment received at counter" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500/30 focus:outline-none">
                    </div>
                </div>

                <!-- Financial Calculation Card -->
                <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4">
                    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-gray-200 pb-3 mb-3">
                        <div class="text-xs text-gray-600">
                            Duration: <span id="walkin-nights-display" class="font-bold text-gray-800">1 night</span>
                            <span class="text-gray-300 mx-1">|</span>
                            Nightly Rate: <span id="walkin-rate-display" class="font-bold text-gray-800">₱0.00</span>
                            <span class="text-gray-300 mx-1">|</span>
                            Fee: <span class="font-bold text-gray-800">₱15.00</span>
                        </div>
                        <div class="text-xs font-bold text-gray-700 flex items-center gap-1">
                            <span>Total Bill:</span>
                            <span id="walkin-total-display" class="text-base text-emerald-700 font-extrabold">₱0.00</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-center">
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="text-xs font-semibold text-gray-700">Amount Paid / Collected Now</label>
                                <div class="flex items-center gap-1">
                                    <button type="button" onclick="setWalkinPaymentRatio(1.0)" class="text-[10px] bg-emerald-100 hover:bg-emerald-200 text-emerald-800 font-bold px-2 py-0.5 rounded transition">100% Full</button>
                                    <button type="button" onclick="setWalkinPaymentRatio(0.2)" class="text-[10px] bg-blue-100 hover:bg-blue-200 text-blue-800 font-bold px-2 py-0.5 rounded transition">20% Deposit</button>
                                </div>
                            </div>
                            <div class="relative">
                                <span class="absolute left-3 top-2 text-xs font-bold text-gray-400">₱</span>
                                <input type="number" step="0.01" min="0" id="walkin-paid-amount" oninput="updateWalkinBalance()" class="w-full pl-7 pr-3 py-2 border border-gray-300 rounded-xl text-xs font-bold text-gray-800 focus:ring-2 focus:ring-emerald-500/30 focus:outline-none">
                            </div>
                        </div>

                        <div class="bg-white rounded-xl p-3 border border-gray-200 flex justify-between items-center">
                            <div>
                                <span class="text-[10px] text-gray-400 uppercase font-bold block">Remaining Balance Due</span>
                                <span id="walkin-balance-display" class="text-sm font-extrabold text-brand-blue">₱0.00</span>
                            </div>
                            <div id="walkin-occupancy-indicator" class="text-right">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                    <i class="fas fa-door-closed"></i> Will Occupy Room
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Buttons -->
            <div class="pt-3 flex items-center justify-end gap-3 border-t border-gray-100">
                <button type="button" onclick="closeWalkinModal()" class="px-4 py-2 border border-gray-200 rounded-xl text-xs font-semibold text-gray-600 hover:bg-gray-50 transition cursor-pointer">
                    Cancel
                </button>
                <button type="submit" id="btn-submit-walkin" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-md transition flex items-center gap-2 cursor-pointer">
                    <i class="fas fa-check-circle"></i> Register Booking & Occupy Room
                </button>
            </div>
        </form>
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

/**
 * Checks if the guest has not arrived by their scheduled check-in date and expected arrival time.
 * Standard check-in time starts at 2:00 PM (14:00) onwards.
 */
function isPastCheckInTime(b) {
    if (!b || !b.checkIn) return false;
    
    // If the booking is already completed (checked in), no-show, or cancelled/expired, do not show
    const bs = b.bookingStatus;
    if (bs === 'completed' || bs === 'no_show' || bs === 'cancelled_expired' || b.status === 'Cancelled') {
        return false;
    }

    // Determine target check-in date (YYYY-MM-DD)
    const checkInDate = (b.checkIn || '').substring(0, 10);
    if (!checkInDate || !checkInDate.includes('-')) return false;

    // Determine arrival time (HH:mm) - standard check-in begins at 14:00 (2:00 PM) onwards
    let timeStr = '14:00';
    if (b.expectedArrivalTime && b.expectedArrivalTime.trim()) {
        timeStr = b.expectedArrivalTime.trim().substring(0, 5);
    }
    if (timeStr < '14:00') {
        timeStr = '14:00';
    }

    try {
        const parts = checkInDate.split('-');
        const timeParts = timeStr.split(':');
        const targetDateTime = new Date(
            parseInt(parts[0], 10),
            parseInt(parts[1], 10) - 1,
            parseInt(parts[2], 10),
            parseInt(timeParts[0], 10),
            parseInt(timeParts[1], 10),
            0
        );
        const now = new Date();
        return now >= targetDateTime;
    } catch (err) {
        return false;
    }
}

/**
 * Checks if a booking's payment method is Pay at Hotel.
 */
function isPayAtHotel(b) {
    if (!b) return false;
    const pMethod = (b.paymentMethod || '').toLowerCase();
    const raw = (b.paymentMethodRaw || '').toLowerCase();
    const channel = (b.paymentChannel || '').toLowerCase();
    const type = (b.paymentType || '').toLowerCase();

    return channel === 'hotel' ||
           raw === 'pay_at_hotel' ||
           raw.includes('hotel') ||
           pMethod.includes('hotel') ||
           type === 'pay_at_hotel';
}

/**
 * Checks if a booking is in pending status (e.g. pending verification or arrival)
 */
function isBookingPending(b) {
    if (!b) return false;
    const status = String(b.status || '').toLowerCase();
    const bStatus = String(b.bookingStatus || b.booking_status || '').toLowerCase();
    return bStatus === 'pending_verification' || bStatus === 'pending' || (status === 'pending' && bStatus !== 'pending_arrival');
}

function switchBookingsView(view) {
    if (currentBookingsView === view) return;
    currentBookingsView = (view === 'archived') ? 'archived' : 'active';
    
    // Clear search input on tab switch
    const searchInput = document.getElementById('booking-search');
    if (searchInput) searchInput.value = '';

    const batchTools = document.getElementById('active-batch-tools');
    const archiveInfo = document.getElementById('archive-info-badge');

    if (currentBookingsView === 'archived') {
        if (batchTools) batchTools.classList.add('hidden');
        if (archiveInfo) archiveInfo.classList.remove('hidden');
    } else {
        if (batchTools) batchTools.classList.remove('hidden');
        if (archiveInfo) archiveInfo.classList.add('hidden');
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
            <th class="p-3.5 align-middle whitespace-nowrap">Reference ID</th>
            <th class="p-3.5 align-middle whitespace-nowrap">Guest Information</th>
            <th class="p-3.5 align-middle whitespace-nowrap">Room & Stay</th>
            <th class="p-3.5 align-middle whitespace-nowrap">Payment Pathway</th>
            <th class="p-3.5 align-middle whitespace-nowrap">Paid / Balance Due</th>
            <th class="p-3.5 align-middle whitespace-nowrap">Status</th>
            <th class="p-3.5 align-middle whitespace-nowrap">Archived Date</th>
            <th class="p-3.5 align-middle text-center whitespace-nowrap">Actions</th>
        `;
    } else {
        headRow.innerHTML = `
            <th class="p-3.5 align-middle whitespace-nowrap">Reference ID</th>
            <th class="p-3.5 align-middle whitespace-nowrap">Guest Information</th>
            <th class="p-3.5 align-middle whitespace-nowrap">Room & Stay</th>
            <th class="p-3.5 align-middle whitespace-nowrap">Payment Pathway</th>
            <th class="p-3.5 align-middle whitespace-nowrap">Paid / Balance Due</th>
            <th class="p-3.5 align-middle whitespace-nowrap">Status & Hold Timer</th>
            <th class="p-3.5 align-middle text-right whitespace-nowrap">Actions</th>
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

function matchesPaymentFilter(b, paymentFilter) {
    if (!paymentFilter) return true;

    // Primary: use the server-computed canonical paymentType key
    const pType = b.paymentType || '';
    if (pType && pType === paymentFilter) return true;

    // Secondary: inspect paymentMethod, paymentChannel, and raw value strings
    const pMethod = (b.paymentMethod || '').toLowerCase();
    const pChannel = (b.paymentChannel || '').toLowerCase();
    const raw = (b.paymentMethodRaw || '').toLowerCase();
    const ref = (b.ref_id || '').toUpperCase();

    switch (paymentFilter) {
        case 'other_payment':
        case 'paymongo':
            return pType === 'other_payment' ||
                   pType === 'paymongo' ||
                   pChannel === 'maya' ||
                   pChannel === 'card' ||
                   pChannel === 'grab_pay' ||
                   pMethod.includes('card') ||
                   pMethod.includes('credit') ||
                   pMethod.includes('debit') ||
                   pMethod.includes('maya') ||
                   pMethod.includes('grab') ||
                   pMethod.includes('other') ||
                   raw.includes('card') ||
                   raw.includes('credit') ||
                   raw.includes('debit') ||
                   raw.includes('maya') ||
                   raw.includes('grab') ||
                   raw.includes('other') ||
                   (raw.includes('paymongo') && !raw.includes('gcash')) ||
                   (b.paymongoSessionId && b.paymongoSessionId.length > 0 && !pMethod.includes('gcash') && !raw.includes('gcash')) ||
                   (b.paymongoPaymentId && b.paymongoPaymentId.length > 0 && !pMethod.includes('gcash') && !raw.includes('gcash'));

        case 'full_gcash':
            return pType === 'full_gcash' ||
                   pChannel === 'gcash' ||
                   pMethod.includes('gcash') ||
                   raw === 'full_gcash' ||
                   raw === 'gcash' ||
                   (raw.includes('gcash') &&
                    !raw.includes('deposit') &&
                    !raw.includes('front desk') &&
                    !raw.includes('counter'));

        case 'deposit_hotel':
        case 'deposit_gcash':
        case 'pay_at_hotel':
            // Combines 20% Deposit (GCash) and Pay at Hotel
            return pType === 'deposit_hotel' ||
                   pType === 'deposit_gcash' ||
                   pType === 'pay_at_hotel' ||
                   pChannel === 'hotel' ||
                   pChannel === 'deposit' ||
                   pMethod.includes('deposit') ||
                   pMethod.includes('hotel') ||
                   raw === 'deposit_gcash' ||
                   raw === 'pay_at_hotel' ||
                   raw.includes('deposit') ||
                   raw.includes('hotel') ||
                   b.bookingStatus === 'deposit_paid' ||
                   b.bookingStatus === 'pending_arrival';

        case 'walkin':
            return pType === 'walkin' ||
                   ref.startsWith('WK-') ||
                   pChannel === 'cash' ||
                   pMethod.includes('front desk') ||
                   pMethod.includes('cash') ||
                   pMethod.includes('pos') ||
                   raw.includes('cash') ||
                   raw.includes('front desk') ||
                   raw.includes('counter') ||
                   raw.includes('walk') ||
                   raw === 'card / pos' ||
                   raw.includes('pos');

        default:
            return raw === paymentFilter.toLowerCase() || pType === paymentFilter.toLowerCase();
    }
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
        const matchesPayment = matchesPaymentFilter(b, paymentFilter);

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

        if (!isArchivedView && isPastCheckInTime(b) && isPayAtHotel(b)) {
            timerNotice += `<span class="inline-flex items-center gap-1 text-[10px] text-rose-700 font-bold bg-rose-100/90 px-2 py-0.5 rounded border border-rose-200 mt-1"><i class="fas fa-clock text-rose-600"></i> Past Check-In Time</span>`;
        }

        // Payment Method Badge — accurately displays the specific payment method used (GCash, Maya, Credit Card, Cash, etc.)
        let paymentBadge;
        const pMethod = b.paymentMethod || b.paymentMethodRaw || 'Standard';
        const pMethodLower = pMethod.toLowerCase();
        const pType = b.paymentType || '';
        const rawLower = (b.paymentMethodRaw || '').toLowerCase();
        const pChannel = (b.paymentChannel || '').toLowerCase();

        // 1. GCash (Full GCash, GCash Deposit, GCash PayMongo, Front Desk GCash)
        if (pChannel === 'gcash' || pMethodLower.includes('gcash') || rawLower.includes('gcash') || (b.gcashRefNo && !rawLower.includes('card') && !rawLower.includes('maya'))) {
            paymentBadge = `<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200 shadow-2xs w-fit" title="${pMethod}"><span class="w-4 h-4 rounded-full bg-blue-600 text-white flex items-center justify-center text-[9px] font-black leading-none">G</span> ${pMethod}</span>`;
        }
        // 2. Maya (Maya / PayMaya E-wallet via PayMongo or Front Desk)
        else if (pChannel === 'maya' || pMethodLower.includes('maya') || rawLower.includes('maya')) {
            paymentBadge = `<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200 shadow-2xs w-fit" title="${pMethod}"><span class="w-4 h-4 rounded-full bg-emerald-600 text-white flex items-center justify-center text-[9px] font-black leading-none">M</span> ${pMethod}</span>`;
        }
        // 3. Credit / Debit Card (Visa, Mastercard, POS)
        else if (
            pChannel === 'card' ||
            pMethodLower.includes('card') || pMethodLower.includes('credit') || pMethodLower.includes('debit') ||
            pMethodLower.includes('visa') || pMethodLower.includes('mastercard') || pMethodLower.includes('pos') ||
            rawLower.includes('card') || rawLower.includes('credit') || rawLower.includes('debit') ||
            rawLower.includes('visa') || rawLower.includes('mastercard') || rawLower.includes('pos')
        ) {
            paymentBadge = `<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-indigo-50 text-indigo-800 border border-indigo-200 shadow-2xs w-fit" title="${pMethod}"><i class="fas fa-credit-card text-indigo-600"></i> ${pMethod}</span>`;
        }
        // 4. GrabPay E-wallet
        else if (pChannel === 'grab_pay' || pMethodLower.includes('grab') || rawLower.includes('grab')) {
            paymentBadge = `<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-green-50 text-green-800 border border-green-200 shadow-2xs w-fit" title="${pMethod}"><i class="fas fa-mobile-screen-button text-green-600"></i> ${pMethod}</span>`;
        }
        // 5. Cash / Front Desk Walk-In
        else if (
            pChannel === 'cash' ||
            pMethodLower.includes('cash') || rawLower.includes('cash') ||
            (pType === 'walkin' && !pMethodLower.includes('hotel')) ||
            (b.ref_id && b.ref_id.startsWith('WK-') && !pMethodLower.includes('hotel'))
        ) {
            paymentBadge = `<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-teal-50 text-teal-800 border border-teal-200 shadow-2xs w-fit" title="${pMethod}"><i class="fas fa-money-bill-wave text-teal-600"></i> ${pMethod}</span>`;
        }
        // 6. Pay at Hotel / Over-the-counter
        else if (pChannel === 'hotel' || pMethodLower.includes('hotel') || rawLower.includes('hotel') || rawLower === 'pay_at_hotel') {
            paymentBadge = `<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-purple-50 text-purple-700 border border-purple-200 shadow-2xs w-fit" title="${pMethod}"><i class="fas fa-store text-purple-600"></i> ${pMethod}</span>`;
        }
        // 7. Security Deposit Paid (20%)
        else if (pChannel === 'deposit' || pMethodLower.includes('deposit') || rawLower.includes('deposit') || b.bookingStatus === 'deposit_paid' || pType === 'deposit_hotel') {
            paymentBadge = `<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-800 border border-amber-200 shadow-2xs w-fit" title="${pMethod}"><i class="fas fa-coins text-amber-600"></i> ${pMethod}</span>`;
        }
        // 8. Other Online Payment / PayMongo checkout
        else if (pType === 'other_payment' || rawLower.includes('paymongo') || b.paymongoSessionId || b.paymongoPaymentId) {
            paymentBadge = `<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-100 text-slate-800 border border-slate-200 shadow-2xs w-fit" title="${pMethod}"><i class="fas fa-globe text-slate-600"></i> ${pMethod}</span>`;
        }
        // 9. Standard Fallback
        else {
            paymentBadge = `<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-gray-100 text-gray-700 border border-gray-200 w-fit" title="${pMethod}"><i class="fas fa-receipt text-gray-500"></i> ${pMethod}</span>`;
        }

        // Build Action Buttons
        let actionButtons = '';
        if (isArchivedView) {
            actionButtons = `
                ${(b.receiptUrl || b.gcashRefNo) ? `
                    <button onclick="openReceiptModal(${b.id})" class="w-8 h-8 text-brand-blue hover:bg-blue-50 rounded-lg transition inline-flex items-center justify-center shadow-2xs shrink-0 cursor-pointer" title="Review Proof of Payment">
                        <i class="fas fa-search-dollar"></i>
                    </button>
                ` : ''}

                ${!isBookingPending(b) ? `
                    <button onclick="printAdminInvoice('${b.id}')" class="w-8 h-8 text-gray-500 hover:text-brand-blue hover:bg-gray-100 rounded-lg transition inline-flex items-center justify-center shadow-2xs shrink-0 cursor-pointer" title="Print Invoice / Voucher">
                        <i class="fas fa-print"></i>
                    </button>
                ` : ''}

                <button onclick="permanentlyDeleteBooking(${b.id}, '${b.ref_id}')" class="w-8 h-8 text-gray-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition inline-flex items-center justify-center shadow-2xs shrink-0 cursor-pointer" title="Permanently delete from database">
                    <i class="fas fa-trash"></i>
                </button>
            `;
        } else {
            actionButtons = `
                ${(b.paymongoSessionId && b.bookingStatus === 'pending_verification') ? `
                    <button onclick="adminVerifyPaymongo('${b.paymongoSessionId}', '${b.ref_id}')" class="h-8 px-2.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 rounded-lg text-xs font-bold transition inline-flex items-center gap-1.5 shadow-2xs shrink-0 cursor-pointer" title="Check PayMongo Live Status">
                        <i class="fas fa-sync-alt"></i> Verify
                    </button>
                ` : ''}

                ${(b.receiptUrl || b.gcashRefNo) ? `
                    <button onclick="openReceiptModal(${b.id})" class="h-8 px-2.5 bg-blue-50 hover:bg-blue-100 text-brand-blue rounded-lg font-bold text-xs transition inline-flex items-center gap-1.5 shadow-2xs shrink-0 cursor-pointer" title="Verify Proof of Payment">
                        <i class="fas fa-search-dollar"></i> Review
                    </button>
                ` : ''}

                ${(b.bookingStatus === 'pending_verification') ? `
                    <button onclick="approveBooking(${b.id})" class="w-8 h-8 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 rounded-lg transition inline-flex items-center justify-center shadow-2xs shrink-0 cursor-pointer" title="Approve Booking">
                        <i class="fas fa-check"></i>
                    </button>
                    <button onclick="rejectBooking(${b.id})" class="w-8 h-8 bg-rose-50 hover:bg-rose-100 text-rose-700 rounded-lg transition inline-flex items-center justify-center shadow-2xs shrink-0 cursor-pointer" title="Reject Receipt">
                        <i class="fas fa-times"></i>
                    </button>
                ` : ''}

                ${(b.bookingStatus === 'deposit_paid' || b.bookingStatus === 'pending_arrival') ? `
                    <button onclick="checkInBooking(${b.id})" class="h-8 px-2.5 bg-brand-blue hover:bg-sky-600 text-white rounded-lg text-xs font-bold transition inline-flex items-center gap-1.5 shadow-2xs shrink-0 cursor-pointer" title="Mark Checked-In & Collect Balance">
                        <i class="fas fa-concierge-bell"></i> Check-In
                    </button>
                ` : ''}

                ${(isPastCheckInTime(b) && isPayAtHotel(b)) ? `
                    <button onclick="markNoShow(${b.id}, '${b.ref_id}')" class="h-8 px-2.5 bg-rose-50 hover:bg-rose-100 text-rose-700 rounded-lg text-xs font-bold transition inline-flex items-center gap-1.5 shadow-2xs shrink-0 cursor-pointer border border-rose-200" title="Guest not arrived at scheduled check-in time — Mark as No-Show">
                        <i class="fas fa-user-slash"></i> No-Show
                    </button>
                ` : ''}

                ${!isBookingPending(b) ? `
                    <button onclick="printAdminInvoice('${b.id}')" class="w-8 h-8 text-gray-500 hover:text-brand-blue hover:bg-gray-100 rounded-lg transition inline-flex items-center justify-center shadow-2xs shrink-0 cursor-pointer" title="Print Invoice / Voucher">
                        <i class="fas fa-print"></i>
                    </button>
                ` : ''}
                
                <button onclick="archiveBooking(${b.id}, '${b.ref_id}')" class="w-8 h-8 text-gray-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition inline-flex items-center justify-center shadow-2xs shrink-0 cursor-pointer" title="Archive reservation">
                    <i class="fas fa-box-archive"></i>
                </button>
            `;
        }

        return `
            <tr class="hover:bg-slate-50/70 transition ${isArchivedView ? 'bg-slate-50/30' : ''}">
                <!-- Ref ID & Date -->
                <td class="p-3.5 align-middle">
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <span class="font-mono font-bold text-gray-800 text-xs">${b.ref_id}</span>
                        ${(b.ref_id && b.ref_id.startsWith('WK-')) ? `<span class="bg-emerald-100 text-emerald-800 text-[9px] font-extrabold px-1.5 py-0.5 rounded uppercase tracking-wider">Walk-In</span>` : ''}
                    </div>
                    <span class="text-[10px] text-gray-400 block mt-0.5">${formatDate(b.created_at)}</span>
                </td>

                <!-- Guest Info -->
                <td class="p-3.5 align-middle">
                    <div class="font-bold text-gray-800 text-xs">${b.guestName}</div>
                    <div class="text-[11px] text-gray-500 truncate max-w-[200px]">${b.guestEmail || '-'}</div>
                    <div class="text-[10px] text-gray-400 font-mono">${b.guestPhone || ''}</div>
                    <div class="text-[10px] text-slate-500 font-medium mt-0.5 flex items-center gap-1"><i class="fas fa-users text-amber-500 shrink-0"></i>${(b.adults || 1) + (b.children || 0)} Guests (${b.adults || 1}A, ${b.children || 0}C${Array.isArray(b.childrenAges) && b.childrenAges.length > 0 ? ` · Ages: ${b.childrenAges.map(a => a == 0 ? '0' : a).join(', ')}` : ''})</div>
                    ${(b.specialRequests && b.specialRequests.trim()) ? `<div class="mt-1 flex items-start gap-1 max-w-[180px]" title="${b.specialRequests.replace(/"/g, '&quot;')}"><span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-semibold bg-violet-50 text-violet-700 border border-violet-200 truncate max-w-full"><i class="fas fa-comment-dots text-violet-500 flex-shrink-0"></i><span class="truncate">${b.specialRequests}</span></span></div>` : ''}
                </td>

                <!-- Room & Stay -->
                <td class="p-3.5 align-middle">
                    <div class="font-semibold text-gray-800 flex items-center gap-1.5 flex-wrap">
                        ${b.roomsCount > 1 ? `<span class="bg-indigo-50 text-indigo-700 px-1.5 py-0.5 rounded text-[10px] font-bold border border-indigo-200">${b.roomsCount} Rooms</span>` : ''}
                        ${(b.roomId === 5 || (b.roomType && b.roomType.toLowerCase().includes('dorm')) || (b.roomName && b.roomName.toLowerCase().includes('room 5'))) ? `<span class="bg-teal-50 text-teal-800 px-1.5 py-0.5 rounded text-[10px] font-bold border border-teal-200"><i class="fas fa-bed mr-0.5 text-teal-600"></i>${b.bedsCount || 1} Bed${(b.bedsCount || 1) > 1 ? 's' : ''} (Dorm)</span>` : ''}
                        <span>${b.roomName}</span>
                    </div>
                    <div class="text-[11px] text-gray-500 mt-0.5">${formatDate(b.checkIn)} &rarr; ${formatDate(b.checkOut)} (${b.nights}n)</div>
                </td>

                <!-- Payment Pathway -->
                <td class="p-3.5 align-middle space-y-1">
                    ${paymentBadge}
                    ${b.gcashRefNo ? `<div class="text-[10px] font-mono text-gray-500 truncate max-w-[150px]" title="GCash Ref: ${b.gcashRefNo}">GCash: ${b.gcashRefNo}</div>` : ''}
                    ${b.paymongoPaymentId ? `<div class="text-[10px] font-mono text-emerald-700 truncate max-w-[150px]" title="PayMongo: ${b.paymongoPaymentId}"><i class="fas fa-receipt mr-1"></i>${b.paymongoPaymentId}</div>` : ''}
                    ${(!b.paymongoPaymentId && b.paymongoSessionId) ? `<div class="text-[9px] font-mono text-gray-400 truncate max-w-[150px]" title="Session: ${b.paymongoSessionId}">${b.paymongoSessionId}</div>` : ''}
                </td>

                <!-- Paid vs Balance -->
                <td class="p-3.5 align-middle">
                    <div class="font-bold text-gray-900 text-xs">₱${parseFloat(b.total).toFixed(2)}</div>
                    <div class="text-[11px] text-emerald-600 font-semibold">Paid: ₱${parseFloat(b.paidAmount).toFixed(2)}</div>
                    <div class="text-[11px] text-brand-blue font-semibold">Balance: ₱${parseFloat(b.balanceDue).toFixed(2)}</div>
                </td>

                <!-- Status & Hold Timer (or Status for Archived) -->
                <td class="p-3.5 align-middle">
                    <div class="space-y-0.5">
                        ${statusBadge}
                        ${timerNotice}
                    </div>
                </td>

                ${isArchivedView ? `
                    <!-- Archived Date -->
                    <td class="p-3.5 align-middle">
                        <span class="font-semibold text-gray-700 text-xs block">${formatDate(b.archivedAt)}</span>
                        <span class="text-[10px] text-gray-400 font-mono">${b.archivedAt ? b.archivedAt.substring(11, 16) : '-'}</span>
                    </td>
                ` : ''}

                <!-- Actions -->
                <td class="p-3.5 align-middle ${isArchivedView ? 'text-center' : 'text-right'}">
                    <div class="flex items-center ${isArchivedView ? 'justify-center' : 'justify-end'} gap-1.5 flex-nowrap">
                        ${actionButtons}
                    </div>
                </td>
            </tr>
        `;
    }).join('');
}

// ================= WALK-IN BOOKING CONTROLLER =================
let walkinRoomsList = [];

function loadWalkinRooms() {
    const roomSelect = document.getElementById('walkin-room-id');
    if (!roomSelect) return;
    
    roomSelect.innerHTML = '<option value="">Loading rooms...</option>';
    
    fetch('../actions/rooms_action.php')
        .then(res => res.json())
        .then(data => {
            if (data.success && Array.isArray(data.rooms)) {
                walkinRoomsList = data.rooms;
                renderWalkinRoomOptions();
            } else {
                roomSelect.innerHTML = '<option value="">Failed to load rooms</option>';
            }
        })
        .catch(err => {
            console.error('Error loading rooms for walk-in:', err);
            roomSelect.innerHTML = '<option value="">Error connecting to rooms server</option>';
        });
}

function renderWalkinRoomOptions() {
    const roomSelect = document.getElementById('walkin-room-id');
    if (!roomSelect) return;
    
    if (walkinRoomsList.length === 0) {
        roomSelect.innerHTML = '<option value="">No rooms configured</option>';
        return;
    }
    
    const prevVal = roomSelect.value;
    let html = '<option value="">-- Choose a Room --</option>';
    
    walkinRoomsList.forEach(r => {
        const isDorm = (r.type === 'Dorm' || r.id == 5);
        const price = parseFloat(r.price || 0).toFixed(2);
        let badge = '';
        if (r.status === 'Maintenance') {
            badge = ' [Under Maintenance]';
        } else if (r.status === 'Occupied' || r.is_occupied) {
            badge = ' [Currently Occupied]';
        } else {
            badge = ' [Available]';
        }
        
        const disabled = (r.status === 'Maintenance') ? 'disabled' : '';
        html += `<option value="${r.id}" ${disabled} ${r.id == prevVal ? 'selected' : ''}>
            ${r.name} (${r.type || 'Room'}) - ₱${price}/nt${badge}
        </option>`;
    });
    
    roomSelect.innerHTML = html;
    
    if (prevVal) {
        roomSelect.value = prevVal;
    } else if (walkinRoomsList.length > 0) {
        const firstAvail = walkinRoomsList.find(r => r.status !== 'Maintenance');
        if (firstAvail) {
            roomSelect.value = firstAvail.id;
        }
    }
    
    handleWalkinRoomChange();
}

function openWalkinModal() {
    const modal = document.getElementById('walkin-modal');
    if (!modal) return;
    
    // Reset inputs
    document.getElementById('walkin-guest-name').value = '';
    document.getElementById('walkin-guest-phone').value = '';
    document.getElementById('walkin-guest-email').value = '';
    document.getElementById('walkin-adults').value = '1';
    document.getElementById('walkin-children').value = '0';
    document.getElementById('walkin-beds-count').value = '1';
    document.getElementById('walkin-payment-method').value = 'Cash (Front Desk)';
    document.getElementById('walkin-booking-status').value = 'completed';
    document.getElementById('walkin-special-requests').value = 'Walk-in guest (Front desk counter)';
    
    // Set default dates: checkIn = today, checkOut = tomorrow
    const today = new Date();
    const tomorrow = new Date();
    tomorrow.setDate(today.getDate() + 1);
    
    const yyyy = today.getFullYear();
    const mm = String(today.getMonth() + 1).padStart(2, '0');
    const dd = String(today.getDate()).padStart(2, '0');
    const todayStr = `${yyyy}-${mm}-${dd}`;
    
    const tyyyy = tomorrow.getFullYear();
    const tmm = String(tomorrow.getMonth() + 1).padStart(2, '0');
    const tdd = String(tomorrow.getDate()).padStart(2, '0');
    const tomorrowStr = `${tyyyy}-${tmm}-${tdd}`;
    
    document.getElementById('walkin-check-in').value = todayStr;
    document.getElementById('walkin-check-out').value = tomorrowStr;
    
    // Current time for arrival
    const curHours = String(today.getHours()).padStart(2, '0');
    const curMins = String(today.getMinutes()).padStart(2, '0');
    document.getElementById('walkin-arrival-time').value = `${curHours}:${curMins}`;
    
    loadWalkinRooms();
    modal.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

function closeWalkinModal() {
    const modal = document.getElementById('walkin-modal');
    if (modal) modal.classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}

function handleWalkinRoomChange() {
    const roomId = document.getElementById('walkin-room-id').value;
    const room = walkinRoomsList.find(r => r.id == roomId);
    const dormContainer = document.getElementById('walkin-dorm-beds-container');
    const hint = document.getElementById('walkin-room-hint');
    
    if (!room) {
        if (dormContainer) dormContainer.classList.add('hidden');
        if (hint) hint.textContent = '';
        calculateWalkinBill();
        return;
    }
    
    const isDorm = (room.type === 'Dorm' || room.id == 5);
    if (isDorm) {
        if (dormContainer) dormContainer.classList.remove('hidden');
        if (hint) {
            const availBeds = (room.available_beds !== undefined) ? room.available_beds : 8;
            hint.innerHTML = `<span class="text-teal-700 font-semibold"><i class="fas fa-bed mr-1"></i>Dormitory Bed Selection: ${availBeds} of 8 bed(s) available today. Rate is ₱460 per bed/night.</span>`;
        }
    } else {
        if (dormContainer) dormContainer.classList.add('hidden');
        if (hint) {
            if (room.status === 'Occupied' || room.is_occupied) {
                hint.innerHTML = `<span class="text-amber-700 font-medium"><i class="fas fa-exclamation-triangle mr-1"></i>Notice: This room is currently occupied today. Check if previous guest has checked out before booking today.</span>`;
            } else {
                hint.innerHTML = `<span class="text-emerald-700 font-medium"><i class="fas fa-check-circle mr-1"></i>Max capacity: ${room.capacity || 2} persons. Base rate: ₱${parseFloat(room.price).toFixed(2)}/night.</span>`;
            }
        }
    }
    
    calculateWalkinBill();
}

function calculateWalkinBill() {
    const roomId = document.getElementById('walkin-room-id').value;
    const room = walkinRoomsList.find(r => r.id == roomId);
    const checkInVal = document.getElementById('walkin-check-in').value;
    const checkOutVal = document.getElementById('walkin-check-out').value;
    
    let nights = 1;
    if (checkInVal && checkOutVal) {
        const d1 = new Date(checkInVal);
        const d2 = new Date(checkOutVal);
        const diffTime = d2.getTime() - d1.getTime();
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        nights = diffDays > 0 ? diffDays : 1;
    }
    
    const nightsDisplay = document.getElementById('walkin-nights-display');
    if (nightsDisplay) nightsDisplay.textContent = `${nights} night${nights > 1 ? 's' : ''}`;
    
    let ratePerNight = 0;
    let standardTotal = 0;
    const isDorm = room && (room.type === 'Dorm' || room.id == 5);
    
    if (isDorm) {
        const beds = Math.max(1, parseInt(document.getElementById('walkin-beds-count').value || 1, 10));
        ratePerNight = 460 * beds;
        standardTotal = (ratePerNight * nights) + 15.00;
        const rateDisplay = document.getElementById('walkin-rate-display');
        if (rateDisplay) rateDisplay.textContent = `₱460 × ${beds} bed${beds > 1 ? 's' : ''} = ₱${ratePerNight.toFixed(2)}`;
    } else if (room) {
        ratePerNight = parseFloat(room.price || 0);
        standardTotal = (ratePerNight * nights) + 15.00;
        const rateDisplay = document.getElementById('walkin-rate-display');
        if (rateDisplay) rateDisplay.textContent = `₱${ratePerNight.toFixed(2)}/night`;
    } else {
        const rateDisplay = document.getElementById('walkin-rate-display');
        if (rateDisplay) rateDisplay.textContent = '₱0.00';
    }
    
    const totalDisplay = document.getElementById('walkin-total-display');
    if (totalDisplay) totalDisplay.textContent = `₱${standardTotal.toFixed(2)}`;
    
    handleWalkinStatusChange(standardTotal);
}

function handleWalkinStatusChange(calculatedTotal) {
    const status = document.getElementById('walkin-booking-status').value;
    const total = (typeof calculatedTotal === 'number') 
        ? calculatedTotal 
        : parseFloat((document.getElementById('walkin-total-display').textContent || '0').replace(/[^0-9.]/g, '')) || 0;
    
    const paidInput = document.getElementById('walkin-paid-amount');
    const occIndicator = document.getElementById('walkin-occupancy-indicator');
    
    if (status === 'completed' || status === 'confirmed') {
        paidInput.value = total.toFixed(2);
        if (occIndicator) {
            occIndicator.innerHTML = `<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200"><i class="fas fa-door-closed"></i> Will Occupy Room</span>`;
        }
    } else if (status === 'deposit_paid') {
        const deposit = Math.round(total * 0.20 * 100) / 100;
        paidInput.value = deposit.toFixed(2);
        if (occIndicator) {
            occIndicator.innerHTML = `<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-200"><i class="fas fa-coins"></i> 20% Deposit Held</span>`;
        }
    } else if (status === 'pending_arrival') {
        paidInput.value = '0.00';
        if (occIndicator) {
            occIndicator.innerHTML = `<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-purple-100 text-purple-800 border border-purple-200"><i class="fas fa-clock"></i> Pending Arrival</span>`;
        }
    }
    
    updateWalkinBalance();
}

function updateWalkinBalance() {
    const total = parseFloat((document.getElementById('walkin-total-display').textContent || '0').replace(/[^0-9.]/g, '')) || 0;
    const paid = parseFloat(document.getElementById('walkin-paid-amount').value || 0) || 0;
    const balance = Math.max(0, total - paid);
    
    const balanceDisplay = document.getElementById('walkin-balance-display');
    if (balanceDisplay) {
        balanceDisplay.textContent = `₱${balance.toFixed(2)}`;
    }
}

function setWalkinPaymentRatio(ratio) {
    const total = parseFloat((document.getElementById('walkin-total-display').textContent || '0').replace(/[^0-9.]/g, '')) || 0;
    const paid = Math.round(total * ratio * 100) / 100;
    document.getElementById('walkin-paid-amount').value = paid.toFixed(2);
    updateWalkinBalance();
}

function submitWalkinBooking(event) {
    event.preventDefault();
    
    const roomId = document.getElementById('walkin-room-id').value;
    const guestName = document.getElementById('walkin-guest-name').value.trim();
    const guestPhone = document.getElementById('walkin-guest-phone').value.trim();
    const guestEmail = document.getElementById('walkin-guest-email').value.trim();
    const checkIn = document.getElementById('walkin-check-in').value;
    const checkOut = document.getElementById('walkin-check-out').value;
    const adults = document.getElementById('walkin-adults').value;
    const children = document.getElementById('walkin-children').value;
    const bedsCount = document.getElementById('walkin-beds-count').value;
    const arrivalTime = document.getElementById('walkin-arrival-time').value;
    const paymentMethod = document.getElementById('walkin-payment-method').value;
    const bookingStatus = document.getElementById('walkin-booking-status').value;
    const specialRequests = document.getElementById('walkin-special-requests').value.trim();
    const paidAmount = parseFloat(document.getElementById('walkin-paid-amount').value || 0);
    const totalAmount = parseFloat((document.getElementById('walkin-total-display').textContent || '0').replace(/[^0-9.]/g, '')) || 0;
    
    if (!roomId) {
        showToast('Please select a room for this walk-in guest.', 'warning');
        return;
    }
    if (!guestName || !guestPhone) {
        showToast('Guest Name and Phone Number are required.', 'warning');
        return;
    }
    if (!checkIn || !checkOut) {
        showToast('Please select valid check-in and check-out dates.', 'warning');
        return;
    }
    if (checkOut <= checkIn) {
        showToast('Check-out date must be strictly after check-in date.', 'error');
        return;
    }
    
    const btnSubmit = document.getElementById('btn-submit-walkin');
    const originalBtnText = btnSubmit.innerHTML;
    btnSubmit.disabled = true;
    btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Registering Booking...';
    
    const payload = {
        action: 'create_walkin',
        room_id: roomId,
        guest_name: guestName,
        guest_phone: guestPhone,
        guest_email: guestEmail,
        check_in: checkIn,
        check_out: checkOut,
        adults: adults,
        children: children,
        beds_count: bedsCount,
        expected_arrival_time: arrivalTime,
        payment_method: paymentMethod,
        booking_status: bookingStatus,
        special_requests: specialRequests,
        total_amount: totalAmount,
        paid_amount: paidAmount
    };
    
    fetch('../actions/bookings_action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(res => res.json())
    .then(data => {
        btnSubmit.disabled = false;
        btnSubmit.innerHTML = originalBtnText;
        
        if (data.success) {
            showToast(data.message || `Walk-in booking ${data.ref_id} created successfully!`, 'success');
            closeWalkinModal();
            loadBookings();
            if (typeof loadBookingCounts === 'function') loadBookingCounts();
        } else {
            showToast(data.message || 'Failed to create walk-in booking', 'error');
        }
    })
    .catch(err => {
        btnSubmit.disabled = false;
        btnSubmit.innerHTML = originalBtnText;
        console.error('Walk-in booking error:', err);
        showToast('Network error while saving walk-in booking', 'error');
    });
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
    const noshowBtn = document.getElementById('btn-modal-noshow');
    const archiveBtn = document.getElementById('btn-modal-archive');

    // Reset visibility
    if (approveBtn) approveBtn.classList.add('hidden');
    if (rejectBtn) rejectBtn.classList.add('hidden');
    if (checkinBtn) checkinBtn.classList.add('hidden');
    if (noshowBtn) noshowBtn.classList.add('hidden');
    if (archiveBtn) archiveBtn.classList.add('hidden');

    if (!booking.isArchived) {
        if (isPastCheckInTime(booking) && isPayAtHotel(booking)) {
            if (noshowBtn) noshowBtn.classList.remove('hidden');
        }
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

function markNoShow(bookingId, refId) {
    const label = refId ? `reservation <strong>${refId}</strong>` : 'this reservation';
    showConfirm(
        `Mark ${label} as <strong>No-Show</strong>?<br><span class="text-xs text-rose-600 mt-1 block">The guest has not arrived on their scheduled check-in time. This will cancel the reservation, forfeit payment/deposit, and release the room back to available inventory.</span>`,
        () => {
            fetch('../actions/bookings_action.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'mark_no_show', id: bookingId })
            })
            .then(res => res.json())
            .then(data => {
                showToast(data.message || 'Marked as No-Show. Room inventory released.', data.success ? 'success' : 'error');
                if (data.success) {
                    loadBookings();
                    if (typeof loadBookingCounts === 'function') loadBookingCounts();
                }
            })
            .catch(err => {
                console.error(err);
                showToast('Error marking booking as No-Show', 'error');
            });
        }
    );
}

function markNoShowFromModal() {
    if (!activeModalBooking) return;
    const bId = activeModalBooking.id;
    const rId = activeModalBooking.ref_id;
    closeReceiptModal();
    markNoShow(bId, rId);
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

    if (isBookingPending(booking)) {
        showToast('Invoice and Voucher cannot be printed while reservation is pending approval.', 'error');
        return;
    }

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

