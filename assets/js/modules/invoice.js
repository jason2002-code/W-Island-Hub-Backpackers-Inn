/**
 * INVOICE & VOUCHER MODULE
 * Manages separate, dedicated modals and print workflows for:
 * 1. Official Booking Voucher (Guest Check-In Pass & Stay Itinerary)
 * 2. Official Folio Invoice / Receipt (Official Billing Statement & Proof of Payment)
 */

// Active modal state to facilitate instant switching between Voucher and Invoice
let currentActiveBookingContext = null;

/**
 * Normalizes booking data into a robust, consistent object structure.
 */
function normalizeBookingData(refIdOrBooking, bookingDetails = {}, apiResponse = {}) {
    let booking = {};

    if (typeof refIdOrBooking === 'object' && refIdOrBooking !== null) {
        booking = { ...refIdOrBooking };
    } else if (typeof refIdOrBooking === 'string' || typeof refIdOrBooking === 'number') {
        booking.refId = String(refIdOrBooking);
        booking.ref_id = String(refIdOrBooking);
    }

    // Merge bookingDetails and apiResponse
    booking = {
        ...booking,
        ...bookingDetails,
        ...apiResponse
    };

    // Reference ID
    const refId = booking.ref_id || booking.refId || (booking.id ? `WIH-${booking.id}` : 'WIH-000000');
    booking.refId = refId;
    booking.ref_id = refId;

    // Room Details
    const roomName = booking.roomName || booking.room_name || 'Standard Island Room';
    const roomId = booking.roomId || booking.room_id || null;
    const isDorm = Boolean(
        booking.isDorm || 
        roomId == 5 || 
        (typeof roomName === 'string' && roomName.toLowerCase().includes('room 5')) || 
        (typeof roomName === 'string' && roomName.toLowerCase().includes('dorm'))
    );
    booking.isDorm = isDorm;
    booking.roomName = roomName;

    // Guest details
    const guestName = (booking.guestName && String(booking.guestName).trim()) ? booking.guestName : 
        ((booking.guest_name && String(booking.guest_name).trim()) ? booking.guest_name : 
        ((window.appState && window.appState.currentUser && window.appState.currentUser.name) ? window.appState.currentUser.name : 
        (document.getElementById('book-name') ? document.getElementById('book-name').value : 'Valued Guest')));

    const guestEmail = (booking.guestEmail && String(booking.guestEmail).trim()) ? booking.guestEmail : 
        ((booking.guest_email && String(booking.guest_email).trim()) ? booking.guest_email : 
        ((window.appState && window.appState.currentUser && window.appState.currentUser.email) ? window.appState.currentUser.email : 
        (document.getElementById('book-email') ? document.getElementById('book-email').value : '')));

    const guestPhone = (booking.guestPhone && String(booking.guestPhone).trim()) ? booking.guestPhone : 
        ((booking.guest_phone && String(booking.guest_phone).trim()) ? booking.guest_phone : 
        ((window.appState && window.appState.currentUser && window.appState.currentUser.phone) ? window.appState.currentUser.phone : 
        (document.getElementById('book-phone') ? document.getElementById('book-phone').value : '')));

    booking.guestName = guestName;
    booking.guestEmail = guestEmail;
    booking.guestPhone = guestPhone;

    // Headcount & beds
    const adults = parseInt(booking.adults || (booking.guests ? booking.guests : 1), 10) || 1;
    const children = parseInt(booking.children || 0, 10) || 0;
    const totalGuests = adults + children;
    const bedsCount = parseInt(booking.bedsCount || booking.beds_count || (isDorm ? adults : 1), 10) || 1;
    const roomsCount = parseInt(booking.roomsCount || booking.rooms_count || 1, 10) || 1;

    booking.adults = adults;
    booking.children = children;
    booking.totalGuests = totalGuests;
    booking.bedsCount = bedsCount;
    booking.roomsCount = roomsCount;

    // Dates & Times
    booking.checkIn = booking.checkIn || booking.check_in_date || booking.check_in || '';
    booking.checkOut = booking.checkOut || booking.check_out_date || booking.check_out || '';
    booking.nights = parseInt(booking.nights || 1, 10) || 1;
    booking.expectedArrivalTime = booking.expectedArrivalTime || booking.expected_arrival_time || '14:00:00';

    // Pricing & Payments
    const total = parseFloat(booking.total !== undefined ? booking.total : (booking.total_amount || booking.total_price || 0));
    booking.total = total;

    const paymentMethodVal = booking.payment_method || booking.paymentMethod || 
        (document.querySelector('input[name="payment"]:checked') ? document.querySelector('input[name="payment"]:checked').value : 'paymongo');
    booking.paymentMethod = paymentMethodVal;

    const isHotelPayment = (
        paymentMethodVal === 'pay_at_hotel' || 
        (typeof paymentMethodVal === 'string' && (paymentMethodVal.toLowerCase().includes('hotel') || paymentMethodVal.toLowerCase().includes('deposit'))) ||
        booking.booking_status === 'deposit_paid' || 
        booking.bookingStatus === 'deposit_paid' || 
        booking.is_deposit
    );
    booking.isHotelPayment = isHotelPayment;

    let paidAmount;
    let balanceDue;
    const bStatus = (booking.booking_status || booking.bookingStatus || booking.status || 'pending_verification').toLowerCase();

    if (bStatus === 'deposit_paid' || (isHotelPayment && parseFloat(booking.paid_amount || booking.paidAmount) > 0)) {
        paidAmount = parseFloat(booking.paid_amount || booking.paidAmount || (Math.round(total * 0.20 * 100) / 100));
        balanceDue = parseFloat(booking.balance_due !== undefined ? booking.balance_due : (booking.balanceDue !== undefined ? booking.balanceDue : Math.round((total - paidAmount) * 100) / 100));
    } else if (isHotelPayment && (bStatus === 'pending_verification' || parseFloat(booking.paid_amount || booking.paidAmount || 0) === 0)) {
        paidAmount = 0.00;
        balanceDue = total;
    } else if (booking.paid_amount !== undefined || booking.paidAmount !== undefined) {
        paidAmount = parseFloat(booking.paid_amount !== undefined ? booking.paid_amount : booking.paidAmount);
        balanceDue = parseFloat(booking.balance_due !== undefined ? booking.balance_due : (booking.balanceDue !== undefined ? booking.balanceDue : Math.max(0, total - paidAmount)));
    } else if (bStatus === 'confirmed' || bStatus === 'completed') {
        paidAmount = total;
        balanceDue = 0.00;
    } else {
        paidAmount = isHotelPayment ? Math.round(total * 0.20 * 100) / 100 : total;
        balanceDue = isHotelPayment ? Math.round((total - paidAmount) * 100) / 100 : 0.00;
    }

    booking.paidAmount = paidAmount;
    booking.balanceDue = balanceDue;
    booking.bookingStatus = bStatus;

    // Calculate unit accommodation rate
    if (isDorm) {
        booking.unitPrice = 460.00;
    } else if (booking.pricePerNight || booking.price_per_night || booking.price) {
        booking.unitPrice = parseFloat(booking.pricePerNight || booking.price_per_night || booking.price);
    } else {
        booking.unitPrice = Math.max(0, (total - 15.00) / Math.max(1, (booking.nights * roomsCount)));
    }

    return booking;
}

/**
 * Formats time from 24-hour to 12-hour AM/PM format
 */
function formatTime12H(timeStr) {
    if (!timeStr) return '2:00 PM';
    const clean = String(timeStr).trim();
    const parts = clean.split(':');
    if (parts.length < 2) return timeStr;
    let hours = parseInt(parts[0], 10);
    const minutes = parts[1].substring(0, 2);
    const ampm = hours >= 12 ? 'PM' : 'AM';
    hours = hours % 12;
    hours = hours ? hours : 12;
    return `${hours}:${minutes} ${ampm}`;
}

/**
 * Formats date into readable string (e.g., "Mon, Oct 12, 2026")
 */
function formatReadableDate(dateStr) {
    if (!dateStr) return '-';
    try {
        const parts = String(dateStr).split('-');
        if (parts.length === 3) {
            const dateObj = new Date(parseInt(parts[0], 10), parseInt(parts[1], 10) - 1, parseInt(parts[2], 10));
            return dateObj.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' });
        }
        const d = new Date(dateStr);
        if (!isNaN(d.getTime())) {
            return d.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' });
        }
    } catch(e) {}
    return dateStr;
}

// =========================================================================
// 1. OFFICIAL BOOKING VOUCHER (GUEST STAY PASS & CHECK-IN ITINERARY)
// =========================================================================

/**
 * Displays the Official Booking Voucher Modal (Guest Stay Pass & Check-In Pass)
 */
function showVoucherModal(refIdOrBooking, bookingDetails = {}, apiResponse = {}) {
    closeAllBookingModals();

    const data = normalizeBookingData(refIdOrBooking, bookingDetails, apiResponse);
    currentActiveBookingContext = data;

    const checkInFormatted = formatReadableDate(data.checkIn);
    const checkOutFormatted = formatReadableDate(data.checkOut);
    const arrivalTime12 = formatTime12H(data.expectedArrivalTime);
    const dateIssued = new Date().toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });

    // Status Badge
    let statusBadgeHtml = '';
    const bStatus = data.bookingStatus;
    if (bStatus === 'confirmed' || bStatus === 'completed') {
        statusBadgeHtml = `<span class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-100 text-emerald-800 border border-emerald-200 rounded-full font-bold text-xs shadow-xs"><i class="fas fa-check-circle text-emerald-600"></i> Confirmed (100% Paid)</span>`;
    } else if (bStatus === 'deposit_paid' || (data.isHotelPayment && data.paidAmount > 0)) {
        statusBadgeHtml = `<span class="inline-flex items-center gap-1.5 px-3 py-1 bg-blue-100 text-blue-800 border border-blue-200 rounded-full font-bold text-xs shadow-xs"><i class="fas fa-shield-alt text-blue-600"></i> Confirmed (20% Deposit Paid)</span>`;
    } else if (bStatus === 'pending_arrival') {
        statusBadgeHtml = `<span class="inline-flex items-center gap-1.5 px-3 py-1 bg-purple-100 text-purple-800 border border-purple-200 rounded-full font-bold text-xs shadow-xs"><i class="fas fa-clock text-purple-600"></i> Pending Arrival (Pay at Desk)</span>`;
    } else if (bStatus === 'cancelled_expired' || bStatus === 'cancelled' || bStatus === 'no_show') {
        statusBadgeHtml = `<span class="inline-flex items-center gap-1.5 px-3 py-1 bg-rose-100 text-rose-800 border border-rose-200 rounded-full font-bold text-xs shadow-xs"><i class="fas fa-times-circle text-rose-600"></i> Cancelled / Expired</span>`;
    } else {
        statusBadgeHtml = `<span class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-100 text-amber-800 border border-amber-200 rounded-full font-bold text-xs shadow-xs"><i class="fas fa-hourglass-half text-amber-600"></i> Pending Verification</span>`;
    }

    const modal = document.createElement('div');
    modal.id = 'voucher-modal-wrapper';
    modal.className = 'fixed inset-0 bg-black/75 flex items-center justify-center z-[100] p-4 overflow-y-auto';
    modal.onclick = function(e) {
        if (e.target === modal) closeVoucherModal();
    };

    modal.innerHTML = `
        <div class="bg-white rounded-3xl w-full max-w-2xl max-h-[92vh] flex flex-col shadow-2xl overflow-hidden border border-slate-200 my-auto" onclick="event.stopPropagation()">
            <!-- Modal Header -->
            <div class="p-5 sm:p-6 border-b border-white/10 flex justify-between items-center bg-gradient-to-r from-teal-800 via-teal-700 to-cyan-700 text-white shrink-0">
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-2xl bg-white/15 text-white flex items-center justify-center text-2xl shadow-inner border border-white/20">
                        <i class="fas fa-ticket-alt text-teal-200"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-[11px] uppercase font-bold text-teal-200 tracking-wider bg-teal-900/40 px-2.5 py-0.5 rounded-md border border-teal-300/30">Official Guest Stay Pass</span>
                        </div>
                        <h3 class="text-xl sm:text-2xl font-serif font-bold mt-0.5 tracking-tight">Booking Confirmation Voucher</h3>
                    </div>
                </div>
                <button type="button" onclick="closeVoucherModal()" class="text-white/80 hover:text-white bg-white/10 hover:bg-white/20 p-2 rounded-xl transition cursor-pointer" title="Close Voucher">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>

            <!-- Printable Voucher Body -->
            <div id="voucher-print-content" class="p-5 sm:p-8 overflow-y-auto flex-1 bg-white text-gray-800 space-y-6">
                <!-- Top Brand & Reference Banner -->
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-gray-100 pb-5">
                    <div class="flex items-center gap-3.5">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-teal-500 to-sky-600 text-white flex items-center justify-center text-2xl font-bold shadow-md shrink-0">
                            <i class="fas fa-hotel"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-serif font-bold text-gray-900 leading-tight">W Island Backpackers Inn</h2>
                            <p class="text-xs text-teal-700 font-semibold flex items-center gap-1 mt-0.5">
                                <i class="fas fa-map-marker-alt text-red-500"></i> Dao, Dauis - Panglao Center Road, Dauis, Panglao Island, Bohol 6339
                            </p>
                            <p class="text-[11px] text-gray-500 mt-0.5">
                                <i class="fas fa-phone-alt text-teal-600 mr-1"></i> +63 945 688 2266 &bull; <i class="fas fa-envelope text-teal-600 mr-1"></i> wislandhub2025@gmail.com
                            </p>
                        </div>
                    </div>

                    <!-- Voucher Barcode / Code Stamp -->
                    <div class="text-left sm:text-right bg-teal-50 p-3 rounded-2xl border border-teal-200 shrink-0 w-full sm:w-auto">
                        <div class="text-[10px] uppercase font-bold text-teal-700 tracking-wider mb-0.5">Voucher Reference</div>
                        <div class="font-mono text-base font-extrabold text-teal-950 tracking-wider">${data.refId}</div>
                        <div class="text-[10px] text-teal-600 mt-0.5">Issued: ${dateIssued}</div>
                    </div>
                </div>

                <!-- Primary Stay Pass Card -->
                <div class="bg-gradient-to-br from-slate-50 to-teal-50/50 rounded-2xl p-5 border border-teal-100 grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block mb-1">Primary Guest</span>
                        <strong class="text-gray-900 text-base block font-bold">${data.guestName}</strong>
                        ${data.guestEmail ? `<span class="text-gray-600 block text-xs mt-0.5"><i class="far fa-envelope text-teal-600 mr-1"></i>${data.guestEmail}</span>` : ''}
                        ${data.guestPhone ? `<span class="text-gray-600 block text-xs mt-0.5"><i class="fas fa-phone-alt text-teal-600 mr-1"></i>${data.guestPhone}</span>` : ''}
                        
                        <div class="mt-2.5 inline-flex items-center gap-1.5 text-xs font-semibold text-teal-800 bg-white px-2.5 py-1 rounded-lg border border-teal-200 shadow-xs">
                            <i class="fas ${data.isDorm ? 'fa-bed' : 'fa-users'} text-teal-600"></i>
                            ${data.isDorm ? `${data.bedsCount} Reserved Bed${data.bedsCount > 1 ? 's' : ''} in Shared Dorm` : `${data.totalGuests} Guest${data.totalGuests > 1 ? 's' : ''} (${data.adults} Adult${data.adults > 1 ? 's' : ''}${data.children > 0 ? `, ${data.children} Child${data.children > 1 ? 'ren' : ''}` : ''})`}
                        </div>
                    </div>

                    <div class="sm:text-right flex flex-col justify-between items-start sm:items-end">
                        <div>
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block mb-1">Reservation Status</span>
                            <div>${statusBadgeHtml}</div>
                        </div>
                        <div class="mt-3 sm:mt-0 text-left sm:text-right">
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Total Accommodation</span>
                            <div class="text-lg font-bold text-teal-900 font-mono">₱${data.total.toFixed(2)}</div>
                            <span class="text-[11px] text-gray-500 block">${data.paidAmount >= data.total ? 'Paid in Full' : `Paid: ₱${data.paidAmount.toFixed(2)} &bull; Due at Desk: ₱${data.balanceDue.toFixed(2)}`}</span>
                        </div>
                    </div>
                </div>

                <!-- Schedule & Check-In Details -->
                <div>
                    <span class="text-[11px] font-bold text-gray-500 uppercase tracking-wider block mb-2.5 flex items-center gap-1.5">
                        <i class="fas fa-calendar-alt text-teal-600"></i> Itinerary & Check-In Schedule
                    </span>
                    <div class="bg-white rounded-2xl p-4 border border-gray-200 grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs shadow-xs">
                        <div class="p-2.5 bg-slate-50 rounded-xl">
                            <span class="text-gray-400 block text-[10px] font-bold uppercase">Room / Bunk</span>
                            <strong class="text-gray-900 text-xs block mt-0.5">${!data.isDorm && data.roomsCount > 1 ? `${data.roomsCount}x ` : ''}${data.roomName}</strong>
                            ${data.isDorm ? `<span class="text-[10px] text-teal-600 block font-semibold mt-0.5">(${data.bedsCount} Bed${data.bedsCount > 1 ? 's' : ''})</span>` : ''}
                        </div>
                        <div class="p-2.5 bg-slate-50 rounded-xl">
                            <span class="text-gray-400 block text-[10px] font-bold uppercase">Duration</span>
                            <strong class="text-gray-900 text-xs block mt-0.5">${data.nights} Night${data.nights > 1 ? 's' : ''}</strong>
                            <span class="text-[10px] text-gray-500 block mt-0.5">Stay Length</span>
                        </div>
                        <div class="p-2.5 bg-teal-50 border border-teal-100 rounded-xl">
                            <span class="text-teal-700 block text-[10px] font-bold uppercase">Check-In</span>
                            <strong class="text-teal-950 text-xs block mt-0.5">${checkInFormatted}</strong>
                            <span class="text-[10px] text-teal-700 font-semibold block mt-0.5">From 2:00 PM (Est: ${arrivalTime12})</span>
                        </div>
                        <div class="p-2.5 bg-slate-50 rounded-xl">
                            <span class="text-gray-400 block text-[10px] font-bold uppercase">Check-Out</span>
                            <strong class="text-gray-900 text-xs block mt-0.5">${checkOutFormatted}</strong>
                            <span class="text-[10px] text-gray-500 block mt-0.5">By 11:00 AM</span>
                        </div>
                    </div>
                </div>

                <!-- Essential Guest Guidelines Card -->
                <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 text-xs space-y-2.5">
                    <div class="flex items-center gap-2 font-bold text-amber-900 text-xs uppercase tracking-wide">
                        <i class="fas fa-info-circle text-amber-600 text-sm"></i> Important Check-In & Hostel Guidelines
                    </div>
                    <ul class="space-y-1.5 text-[11px] text-amber-900 list-disc list-inside leading-relaxed">
                        <li><strong>Valid ID Required:</strong> Please present a valid government-issued photo ID (Passport, Driver's License, National ID) along with this voucher reference at the reception.</li>
                        <li><strong>Luggage Storage:</strong> Early arrival before 2:00 PM? Complimentary secure luggage drop is available at the front desk from 10:00 AM.</li>
                        <li><strong>Fiber Wi-Fi:</strong> High-speed fiber internet is accessible across all communal areas and dorms. Wi-Fi credentials will be provided upon desk check-in.</li>
                        <li><strong>Quiet Hours:</strong> Strictly observed from <strong>10:00 PM to 7:00 AM</strong> to ensure a restful atmosphere for all backpackers.</li>
                    </ul>
                </div>

                <!-- Verification Footer Notice -->
                <div class="pt-3 border-t border-gray-100 text-center text-gray-500 text-[10px] space-y-1">
                    <p class="font-medium text-gray-600">
                        W Island Backpackers Inn &bull; Dauis - Panglao Island, Bohol 6339 &bull; Reg. LGU Dauis (BP-2024-DAUIS-0521)
                    </p>
                    <p class="text-gray-400">
                        This voucher serves as proof of accommodation reservation. For billing tax breakdown, view your Official Folio Invoice.
                    </p>
                </div>
            </div>

            <!-- Modal Action Footer -->
            <div class="p-5 bg-slate-50 border-t border-gray-100 flex flex-wrap gap-2.5 justify-between items-center shrink-0">
                <button type="button" onclick="switchModalToInvoice()" class="px-4 py-2.5 bg-white border border-slate-200 hover:bg-slate-100 text-slate-700 rounded-xl font-bold text-xs transition shadow-xs flex items-center gap-2 cursor-pointer">
                    <i class="fas fa-file-invoice-dollar text-emerald-600"></i> View Official Invoice / Receipt
                </button>

                <div class="flex items-center gap-2.5">
                    <button type="button" onclick="printVoucher()" class="px-5 py-2.5 bg-gray-900 hover:bg-black text-white rounded-xl font-bold transition shadow-sm flex items-center gap-2 text-xs cursor-pointer">
                        <i class="fas fa-print"></i> Print Voucher
                    </button>
                    <button type="button" onclick="closeVoucherModal()" class="px-5 py-2.5 bg-teal-600 hover:bg-teal-700 text-white rounded-xl font-bold transition shadow-sm text-xs cursor-pointer">
                        Done
                    </button>
                </div>
            </div>
        </div>
    `;

    document.body.appendChild(modal);
}

/**
 * Closes the Voucher modal
 */
function closeVoucherModal() {
    const modal = document.getElementById('voucher-modal-wrapper');
    if (modal) modal.remove();
    const oldV = document.getElementById('voucher-modal');
    if (oldV) {
        oldV.classList.add('hidden');
        oldV.classList.remove('flex');
    }
}

// =========================================================================
// 2. OFFICIAL FOLIO INVOICE / RECEIPT (BILLING STATEMENT & TAX RECEIPT)
// =========================================================================

/**
 * Displays the Official Folio Invoice / Receipt Modal (Billing Breakdown & Proof of Payment)
 */
function showInvoiceModal(refIdOrBooking, bookingDetails = {}, apiResponse = {}) {
    closeAllBookingModals();

    const data = normalizeBookingData(refIdOrBooking, bookingDetails, apiResponse);
    currentActiveBookingContext = data;

    const checkInFormatted = formatReadableDate(data.checkIn);
    const checkOutFormatted = formatReadableDate(data.checkOut);
    const dateNow = new Date().toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });

    let paymentPathwayLabel = 'Online Payment (Full PayMongo)';
    let paymentBadge = '<span class="px-2.5 py-1 bg-emerald-100 text-emerald-800 rounded-full font-bold text-xs"><i class="fas fa-check-circle mr-1"></i> 100% Paid Full</span>';

    if (data.isHotelPayment) {
        if (data.bookingStatus === 'deposit_paid' || data.paidAmount > 0) {
            paymentPathwayLabel = 'Pay at Hotel (20% Security Deposit Paid)';
            paymentBadge = '<span class="px-2.5 py-1 bg-blue-100 text-blue-800 rounded-full font-bold text-xs"><i class="fas fa-shield-alt mr-1"></i> 20% Deposit Paid</span>';
        } else {
            paymentPathwayLabel = 'Pay at Hotel (20% Deposit Pending)';
            paymentBadge = '<span class="px-2.5 py-1 bg-amber-100 text-amber-800 rounded-full font-bold text-xs"><i class="fas fa-clock mr-1"></i> Pending 20% Deposit</span>';
        }
    } else if (data.gcashRefNo) {
        paymentPathwayLabel = `GCash Direct (Ref: ${data.gcashRefNo})`;
        paymentBadge = '<span class="px-2.5 py-1 bg-teal-100 text-teal-800 rounded-full font-bold text-xs"><i class="fas fa-mobile-alt mr-1"></i> GCash Transfer</span>';
    }

    const roomSubtotal = data.isDorm 
        ? (460.00 * data.bedsCount * data.nights)
        : (data.unitPrice * data.nights * data.roomsCount);
    const taxesAndFees = 15.00;

    const modal = document.createElement('div');
    modal.id = 'invoice-modal-wrapper';
    modal.className = 'fixed inset-0 bg-black/75 flex items-center justify-center z-[100] p-4 overflow-y-auto';
    modal.onclick = function(e) {
        if (e.target === modal) closeInvoiceModal();
    };

    modal.innerHTML = `
        <div class="bg-white rounded-3xl w-full max-w-2xl max-h-[92vh] flex flex-col shadow-2xl overflow-hidden border border-slate-200 my-auto" onclick="event.stopPropagation()">
            <!-- Modal Header -->
            <div class="p-5 sm:p-6 border-b border-white/10 flex justify-between items-center bg-gradient-to-r from-slate-900 via-slate-800 to-blue-950 text-white shrink-0">
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-2xl bg-white/10 text-white flex items-center justify-center text-2xl shadow-inner border border-white/20">
                        <i class="fas fa-file-invoice-dollar text-teal-300"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-[11px] uppercase font-bold text-teal-300 tracking-wider bg-white/10 px-2.5 py-0.5 rounded-md border border-white/10">Official Folio Invoice</span>
                        </div>
                        <h3 class="text-xl sm:text-2xl font-serif font-bold mt-0.5 tracking-tight">Billing & Payment Receipt</h3>
                    </div>
                </div>
                <button type="button" onclick="closeInvoiceModal()" class="text-white/80 hover:text-white bg-white/10 hover:bg-white/20 p-2 rounded-xl transition cursor-pointer" title="Close Invoice">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>

            <!-- Printable Invoice Body -->
            <div id="invoice-print-content" class="p-5 sm:p-8 overflow-y-auto flex-1 bg-white text-gray-800 space-y-6">
                <!-- Legal Business Header -->
                <div class="flex flex-col sm:flex-row justify-between items-start gap-4 border-b border-gray-100 pb-5">
                    <div class="flex items-start gap-3.5">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-brand-blue to-teal-500 text-white flex items-center justify-center text-xl font-bold shadow-md shrink-0">
                            <i class="fas fa-receipt"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-serif font-bold text-gray-900 leading-tight">W Island Hub Backpackers Inn</h2>
                            <p class="text-xs text-brand-blue font-bold tracking-wide">W Island Hub Hospitality Services</p>
                            <p class="text-[11px] text-gray-600 mt-0.5"><i class="fas fa-map-marker-alt text-red-500 mr-1"></i>Dao, Dauis - Panglao Center Road, Dauis, Panglao Island, Bohol 6339, Philippines</p>
                            <p class="text-[10px] text-gray-500 mt-0.5 font-medium">DOT Accr. No.: DOT-R7-ACC-2024-0318 &bull; BIR TIN: 432-876-543-00000 &bull; LGU Permit: BP-2024-DAUIS-0521</p>
                        </div>
                    </div>
                    <div class="text-left sm:text-right shrink-0 bg-slate-50 p-3 rounded-2xl border border-slate-200 w-full sm:w-auto">
                        <span class="inline-block px-2.5 py-0.5 rounded bg-blue-50 text-brand-blue font-mono font-bold text-[10px] uppercase tracking-wider mb-1 border border-blue-200">
                            FOLIO INVOICE
                        </span>
                        <div class="font-mono text-sm font-extrabold text-gray-900">${data.refId}</div>
                        <div class="text-[10px] text-gray-500 mt-0.5">Date: ${dateNow}</div>
                    </div>
                </div>

                <!-- Billed To & Payment Pathway Grid -->
                <div class="bg-slate-50 rounded-2xl p-4 border border-slate-200 grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block mb-1">Billed To (Guest)</span>
                        <strong class="text-gray-900 text-sm block font-bold">${data.guestName}</strong>
                        ${data.guestEmail ? `<span class="text-gray-600 block text-xs mt-0.5">${data.guestEmail}</span>` : ''}
                        ${data.guestPhone ? `<span class="text-gray-600 block text-xs mt-0.5">${data.guestPhone}</span>` : ''}
                        <div class="text-[11px] text-brand-blue font-semibold mt-1">
                            <i class="fas ${data.isDorm ? 'fa-bed' : 'fa-users'} mr-1"></i>
                            ${data.isDorm ? `${data.bedsCount} Bed${data.bedsCount > 1 ? 's' : ''} in Shared Dormitory` : `${data.totalGuests} Guests (${data.adults} Adults${data.children > 0 ? `, ${data.children} Children` : ''})`}
                        </div>
                    </div>
                    <div class="sm:text-right">
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block mb-1">Payment Method & Status</span>
                        <strong class="text-gray-800 block text-xs mb-1.5">${paymentPathwayLabel}</strong>
                        <div>${paymentBadge}</div>
                        ${data.paymongoPaymentId ? `<div class="text-[10px] font-mono text-emerald-700 mt-1">PayMongo ID: ${data.paymongoPaymentId}</div>` : ''}
                    </div>
                </div>

                <!-- Stay Details summary line -->
                <div class="bg-white rounded-xl p-3.5 border border-gray-200 grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs shadow-xs">
                    <div>
                        <span class="text-gray-400 block text-[10px] uppercase font-semibold">Accommodation</span>
                        <strong class="text-gray-800 text-xs">${!data.isDorm && data.roomsCount > 1 ? `${data.roomsCount}x ` : ''}${data.roomName}</strong>
                    </div>
                    <div>
                        <span class="text-gray-400 block text-[10px] uppercase font-semibold">Stay Period</span>
                        <strong class="text-gray-800 text-xs">${data.nights} Night${data.nights > 1 ? 's' : ''}</strong>
                    </div>
                    <div>
                        <span class="text-gray-400 block text-[10px] uppercase font-semibold">Check-In</span>
                        <strong class="text-gray-800 text-xs">${checkInFormatted}</strong>
                    </div>
                    <div>
                        <span class="text-gray-400 block text-[10px] uppercase font-semibold">Check-Out</span>
                        <strong class="text-gray-800 text-xs">${checkOutFormatted}</strong>
                    </div>
                </div>

                <!-- Itemized Billing Table -->
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b-2 border-slate-200 text-gray-500 uppercase text-[10px] tracking-wider">
                            <th class="py-2.5 font-bold">Item Description</th>
                            <th class="py-2.5 font-bold text-right">Amount (PHP)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr>
                            <td class="py-3">
                                <div class="font-bold text-gray-900">${data.isDorm ? 'Dormitory Bed Accommodation Rate' : 'Room Accommodation Rate'}</div>
                                <div class="text-[10px] text-gray-500 mt-0.5 font-mono">
                                    ${data.isDorm 
                                        ? `₱460.00 × ${data.bedsCount} bed(s) × ${data.nights} night(s)` 
                                        : `₱${data.unitPrice.toFixed(2)} × ${data.nights} night(s)${data.roomsCount > 1 ? ` × ${data.roomsCount} rooms` : ''}`}
                                </div>
                            </td>
                            <td class="py-3 text-right font-bold text-gray-800 font-mono">₱${roomSubtotal.toFixed(2)}</td>
                        </tr>
                        <tr>
                            <td class="py-3">
                                <div class="font-bold text-gray-900">Taxes, Service & Eco Fees</div>
                                <div class="text-[10px] text-gray-500 mt-0.5">Local tourism development & environment preservation fees</div>
                            </td>
                            <td class="py-3 text-right font-bold text-gray-800 font-mono">₱${taxesAndFees.toFixed(2)}</td>
                        </tr>
                    </tbody>
                    <tfoot class="border-t-2 border-slate-200">
                        <tr class="border-b border-gray-200">
                            <td class="py-3 font-bold text-sm text-gray-900 text-right pr-4">Total Amount Due:</td>
                            <td class="py-3 font-bold text-base text-gray-950 text-right font-mono">₱${data.total.toFixed(2)}</td>
                        </tr>
                        <tr class="bg-emerald-50 border-b border-emerald-100">
                            <td class="py-2.5 font-bold text-xs text-emerald-900 text-right pr-4">
                                ${data.isHotelPayment ? (data.paidAmount > 0 ? 'Paid Online (20% Security Deposit):' : 'Online Security Deposit (20%):') : 'Paid in Full Online (100%):'}
                            </td>
                            <td class="py-2.5 font-bold text-sm text-emerald-700 text-right font-mono">₱${data.paidAmount.toFixed(2)}</td>
                        </tr>
                        <tr class="bg-blue-50">
                            <td class="py-2.5 font-bold text-xs text-brand-blue text-right pr-4">
                                ${data.isHotelPayment ? (data.paidAmount > 0 ? 'Remaining Balance at Hotel Desk (80%):' : 'Balance Payable at Front Desk (80%):') : 'Remaining Balance at Front Desk:'}
                            </td>
                            <td class="py-2.5 font-bold text-sm text-brand-blue text-right font-mono">₱${data.balanceDue.toFixed(2)}</td>
                        </tr>
                    </tfoot>
                </table>

                <!-- Hotel Front Desk & Support info -->
                <div class="pt-3 border-t border-gray-100 space-y-2 text-center text-xs text-gray-600">
                    <div class="flex flex-wrap items-center justify-center gap-x-4 gap-y-1 text-[11px] text-gray-700 font-medium bg-slate-50 py-2 px-3 rounded-xl border border-gray-200">
                        <span><i class="fas fa-clock text-teal-600 mr-1"></i>Check-in: <strong>02:00 PM</strong></span>
                        <span><i class="fas fa-sign-out-alt text-teal-600 mr-1"></i>Check-out: <strong>11:00 AM</strong></span>
                        <span><i class="fas fa-phone-alt text-teal-600 mr-1"></i>+63 945 688 2266</span>
                        <span><i class="fas fa-envelope text-teal-600 mr-1"></i>wislandhub2025@gmail.com</span>
                    </div>
                    <p class="text-[10px] text-gray-400">
                        W Island Backpackers Inn &bull; Dauis, Panglao Island, Bohol 6339 &bull; BIR TIN: 432-876-543-00000 &bull; Reg. LGU Dauis (BP-2024-DAUIS-0521).
                    </p>
                </div>
            </div>

            <!-- Modal Action Footer -->
            <div class="p-5 bg-slate-50 border-t border-gray-100 flex flex-wrap gap-2.5 justify-between items-center shrink-0">
                <button type="button" onclick="switchModalToVoucher()" class="px-4 py-2.5 bg-white border border-slate-200 hover:bg-slate-100 text-slate-700 rounded-xl font-bold text-xs transition shadow-xs flex items-center gap-2 cursor-pointer">
                    <i class="fas fa-ticket-alt text-teal-600"></i> View Guest Stay Voucher
                </button>

                <div class="flex items-center gap-2.5">
                    <button type="button" onclick="printInvoice()" class="px-5 py-2.5 bg-gray-900 hover:bg-black text-white rounded-xl font-bold transition shadow-sm flex items-center gap-2 text-xs cursor-pointer">
                        <i class="fas fa-print"></i> Print Invoice
                    </button>
                    <button type="button" onclick="closeInvoiceModal()" class="px-5 py-2.5 bg-brand-blue hover:bg-sky-600 text-white rounded-xl font-bold transition shadow-sm text-xs cursor-pointer">
                        Done
                    </button>
                </div>
            </div>
        </div>
    `;

    document.body.appendChild(modal);
}

/**
 * Closes the Invoice modal
 */
function closeInvoiceModal() {
    const modal = document.getElementById('invoice-modal-wrapper');
    if (modal) modal.remove();
    const oldI = document.getElementById('invoice-modal');
    if (oldI) oldI.remove();
}

/**
 * Helper to close any currently open voucher or invoice modal safely
 */
function closeAllBookingModals() {
    const vModal = document.getElementById('voucher-modal-wrapper');
    if (vModal) vModal.remove();
    const iModal = document.getElementById('invoice-modal-wrapper');
    if (iModal) iModal.remove();
    const oldV = document.getElementById('voucher-modal');
    if (oldV) {
        oldV.classList.add('hidden');
        oldV.classList.remove('flex');
    }
    const oldI = document.getElementById('invoice-modal');
    if (oldI) oldI.remove();
}

/**
 * Switches from current active modal to Voucher modal
 */
function switchModalToVoucher() {
    if (currentActiveBookingContext) {
        showVoucherModal(currentActiveBookingContext);
    }
}

/**
 * Switches from current active modal to Invoice modal
 */
function switchModalToInvoice() {
    if (currentActiveBookingContext) {
        showInvoiceModal(currentActiveBookingContext);
    }
}

// =========================================================================
// 3. ID-BASED HANDLERS FOR USER PROFILE & BOOKINGS LIST
// =========================================================================

/**
 * Opens Voucher Modal given a booking ID (from appState or via fetch)
 */
function openVoucherModal(bookingId) {
    if (bookingId === undefined || bookingId === null || bookingId === '') {
        console.warn('openVoucherModal: No bookingId provided');
        return;
    }
    const booking = findBookingById(bookingId);
    if (booking) {
        showVoucherModal(booking);
        return;
    }

    // Fallback: Fetch booking from server
    fetchBookingAndShow(bookingId, 'voucher');
}

/**
 * Opens Invoice / Receipt Modal given a booking ID (from appState or via fetch)
 */
function openInvoiceModal(bookingId) {
    if (bookingId === undefined || bookingId === null || bookingId === '') {
        console.warn('openInvoiceModal: No bookingId provided');
        return;
    }
    const booking = findBookingById(bookingId);
    if (booking) {
        showInvoiceModal(booking);
        return;
    }

    // Fallback: Fetch booking from server
    fetchBookingAndShow(bookingId, 'invoice');
}

/**
 * Finds booking in window.appState or sessionStorage
 */
function findBookingById(bookingId) {
    let bookingsList = [];
    if (window.appState && Array.isArray(window.appState.bookings) && window.appState.bookings.length > 0) {
        bookingsList = window.appState.bookings;
    } else {
        try {
            const stored = sessionStorage.getItem('w_bookings');
            if (stored) bookingsList = JSON.parse(stored);
        } catch(e) {}
    }
    const target = String(bookingId).trim();
    return bookingsList.find(b => String(b.id) === target || String(b.ref_id) === target || String(b.refId) === target);
}

/**
 * Fetches single booking from server if not found in memory
 */
function fetchBookingAndShow(bookingId, modalType = 'voucher') {
    const basePath = (window.location.pathname.includes('/user/') || window.location.pathname.includes('/admin/')) ? '../' : '';
    fetch(`${basePath}actions/bookings_action.php?action=get_single&id=${encodeURIComponent(bookingId)}`)
        .then(res => res.json())
        .then(data => {
            if (data.success && data.booking) {
                const b = data.booking;
                const formatted = {
                    id: b.id,
                    refId: b.ref_id,
                    ref_id: b.ref_id,
                    roomName: b.room_name || 'Standard Room',
                    roomId: b.room_id,
                    checkIn: b.check_in_date || b.check_in,
                    checkOut: b.check_out_date || b.check_out,
                    nights: parseInt(b.nights || 1, 10),
                    adults: parseInt(b.adults || 1, 10),
                    children: parseInt(b.children || 0, 10),
                    guests: (parseInt(b.adults || 1, 10) + parseInt(b.children || 0, 10)),
                    bedsCount: parseInt(b.beds_count || 1, 10),
                    roomsCount: parseInt(b.rooms_count || 1, 10),
                    total: parseFloat(b.total_amount || b.total_price || 0),
                    paidAmount: parseFloat(b.paid_amount !== undefined ? b.paid_amount : 0),
                    balanceDue: parseFloat(b.balance_due !== undefined ? b.balance_due : 0),
                    bookingStatus: b.booking_status || 'pending_verification',
                    paymentMethod: b.payment_method || 'paymongo',
                    expectedArrivalTime: b.expected_arrival_time || '14:00:00',
                    guestName: b.guest_name || '',
                    guestEmail: b.guest_email || '',
                    guestPhone: b.guest_phone || '',
                    gcashRefNo: b.gcash_reference_no || null,
                    paymongoPaymentId: b.paymongo_payment_id || null
                };

                if (modalType === 'voucher') {
                    showVoucherModal(formatted);
                } else {
                    showInvoiceModal(formatted);
                }
            } else {
                if (typeof window.showToast === 'function') {
                    window.showToast('Unable to load booking details.', 'error');
                } else {
                    alert('Unable to load booking details.');
                }
            }
        })
        .catch(err => {
            console.error('Error fetching booking:', err);
            if (typeof window.showToast === 'function') {
                window.showToast('Network error while loading booking details.', 'error');
            }
        });
}

// =========================================================================
// 4. PRINT PREVIEW WINDOW GENERATOR
// =========================================================================

/**
 * Opens print preview iframe modal in current tab (safely bypasses popup blockers)
 */
function openSameTabPrintWindow() {
    let modal = document.getElementById('same-tab-print-modal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'same-tab-print-modal';
        modal.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.7);z-index:999999;display:flex;align-items:center;justify-content:center;padding:16px;';
        
        let container = document.createElement('div');
        container.style.cssText = 'width:92%;max-width:850px;height:90%;background:#fff;border-radius:16px;box-shadow:0 20px 60px rgba(0,0,0,0.3);display:flex;flex-direction:column;overflow:hidden;';
        
        let header = document.createElement('div');
        header.style.cssText = 'padding:14px 20px;background:#0f172a;color:#fff;display:flex;justify-content:space-between;align-items:center;';
        header.innerHTML = `
            <div style="display:flex;align-items:center;gap:10px;">
                <i class="fas fa-print" style="color:#2dd4bf;"></i>
                <strong style="font-family:-apple-system,sans-serif;font-size:15px;letter-spacing:0.3px;">Document Print Preview</strong>
            </div>
            <div style="display:flex;gap:8px;">
                <button type="button" onclick="document.getElementById('same-tab-print-iframe').contentWindow.print()" style="background:#0d9488;color:#fff;border:none;padding:6px 14px;border-radius:8px;cursor:pointer;font-weight:bold;font-size:12px;">Print Now</button>
                <button type="button" onclick="document.getElementById('same-tab-print-modal').style.display='none'" style="background:#ef4444;color:#fff;border:none;padding:6px 14px;border-radius:8px;cursor:pointer;font-weight:bold;font-size:12px;">Close</button>
            </div>
        `;
        
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

/**
 * Prints the Official Guest Stay Voucher
 */
function printVoucher() {
    const content = document.getElementById('voucher-print-content');
    if (!content) return;
    
    const printWindow = openSameTabPrintWindow();
    if (!printWindow) {
        window.print();
        return;
    }

    const refId = currentActiveBookingContext ? currentActiveBookingContext.refId : 'Voucher';

    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
            <head>
                <title>Official Booking Voucher - ${refId} - W Island Backpackers Inn</title>
                <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
                <style>
                    body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; padding: 24px; color: #0f172a; }
                    @media print {
                        body { padding: 8px; }
                        .no-print { display: none !important; }
                        -webkit-print-color-adjust: exact !important;
                        print-color-adjust: exact !important;
                    }
                </style>
            </head>
            <body>
                <div class="max-w-2xl mx-auto">
                    ${content.innerHTML}
                </div>
                <script>
                    window.onload = function() { 
                        setTimeout(function() {
                            window.print(); 
                        }, 400);
                    }
                </script>
            </body>
        </html>
    `);
    printWindow.document.close();
}

/**
 * Prints the Official Folio Invoice / Receipt
 */
function printInvoice() {
    const content = document.getElementById('invoice-print-content');
    if (!content) return;
    
    const printWindow = openSameTabPrintWindow();
    if (!printWindow) {
        window.print();
        return;
    }

    const refId = currentActiveBookingContext ? currentActiveBookingContext.refId : 'Invoice';

    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
            <head>
                <title>Official Folio Invoice - ${refId} - W Island Backpackers Inn</title>
                <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
                <style>
                    body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; padding: 24px; color: #0f172a; }
                    @media print {
                        body { padding: 8px; }
                        .no-print { display: none !important; }
                        -webkit-print-color-adjust: exact !important;
                        print-color-adjust: exact !important;
                    }
                </style>
            </head>
            <body>
                <div class="max-w-2xl mx-auto">
                    ${content.innerHTML}
                </div>
                <script>
                    window.onload = function() { 
                        setTimeout(function() {
                            window.print(); 
                        }, 400);
                    }
                </script>
            </body>
        </html>
    `);
    printWindow.document.close();
}

// Global keydown listener to close modals with ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeAllBookingModals();
        const printModal = document.getElementById('same-tab-print-modal');
        if (printModal) printModal.style.display = 'none';
    }
});

// =========================================================================
// GLOBAL WINDOW BINDINGS
// =========================================================================
window.showVoucherModal = showVoucherModal;
window.closeVoucherModal = closeVoucherModal;
window.openVoucherModal = openVoucherModal;
window.printVoucher = printVoucher;

window.showInvoiceModal = showInvoiceModal;
window.closeInvoiceModal = closeInvoiceModal;
window.openInvoiceModal = openInvoiceModal;
window.printInvoice = printInvoice;

window.switchModalToVoucher = switchModalToVoucher;
window.switchModalToInvoice = switchModalToInvoice;
window.closeAllBookingModals = closeAllBookingModals;
window.fetchBookingAndShow = fetchBookingAndShow;
