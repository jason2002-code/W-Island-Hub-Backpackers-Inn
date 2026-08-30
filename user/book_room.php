<!-- ================= MULTI-TIER RESERVATION & CHECKOUT VIEW ================= -->
<section id="book" class="py-20 bg-slate-50 min-h-screen">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Breadcrumb / Header -->
        <div class="mb-8 text-center sm:text-left">
            <span class="inline-block px-3 py-1 bg-brand-blue/10 text-brand-blue rounded-full text-xs font-bold uppercase tracking-wider mb-2">
                Fast & Secure Reservations
            </span>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight font-serif">
                Complete Your Stay Booking
            </h2>
            <p class="text-slate-500 text-sm mt-1">
                Select your preferred payment pathway, confirm your room dates, and secure your island getaway.
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <!-- LEFT FORM COLUMN (7 cols) -->
            <div class="lg:col-span-7 space-y-6">
                
                <!-- 1. Guest & Room Details Card -->
                <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-100">
                    <h3 class="text-lg font-bold text-slate-900 mb-4 flex items-center gap-2.5">
                        <span class="w-7 h-7 rounded-full bg-brand-blue text-white flex items-center justify-center text-xs font-bold">1</span>
                        Guest & Stay Dates
                    </h3>

                    <form id="booking-form" onsubmit="handleBookingSubmission(event)" class="space-y-4">
                        <input type="hidden" id="book-room-id" name="room_id" value="">
                        
                        <!-- Guest Info -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-600 mb-1 uppercase tracking-wide">Primary Guest Name</label>
                                <input type="text" id="book-guest-name" name="guest_name" required placeholder="Full Name" 
                                    class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-brand-blue outline-none transition">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-600 mb-1 uppercase tracking-wide">Contact Phone (Required)</label>
                                <input type="tel" id="book-guest-phone" name="guest_phone" required placeholder="+63 900 000 0000" 
                                    class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-brand-blue outline-none transition">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-600 mb-1 uppercase tracking-wide">Email Address (For Voucher)</label>
                            <input type="email" id="book-guest-email" name="guest_email" required placeholder="guest@example.com" 
                                class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-brand-blue outline-none transition">
                        </div>

                        <!-- Date Selection -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                            <div>
                                <label class="block text-xs font-bold text-slate-600 mb-1 uppercase tracking-wide">Check-In Date</label>
                                <input type="date" id="book-checkin-date" name="check_in" required onchange="onBookingDatesChanged()"
                                    class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-brand-blue outline-none transition">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-600 mb-1 uppercase tracking-wide">Check-Out Date</label>
                                <input type="date" id="book-checkout-date" name="check_out" required onchange="onBookingDatesChanged()"
                                    class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-brand-blue outline-none transition">
                            </div>
                        </div>
                    </form>
                </div>

                <!-- 2. Payment Pathway Selector -->
                <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-100">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2.5">
                            <span class="w-7 h-7 rounded-full bg-brand-blue text-white flex items-center justify-center text-xs font-bold">2</span>
                            Select Payment Pathway
                        </h3>
                        <span id="tier-badge" class="text-xs px-2.5 py-0.5 rounded-full font-semibold bg-slate-100 text-slate-600">
                            3 Options
                        </span>
                    </div>

                    <div class="space-y-3" id="payment-options-container">
                        
                        <!-- Option A: Full GCash -->
                        <label class="payment-card relative flex items-start p-4 rounded-2xl border-2 border-brand-blue bg-blue-50/40 cursor-pointer transition hover:shadow-sm" onclick="selectPaymentPathway('full_gcash')">
                            <input type="radio" name="payment_pathway" value="full_gcash" checked class="mt-1 text-brand-blue focus:ring-brand-blue">
                            <div class="ml-3 flex-1">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-bold text-slate-900 flex items-center gap-2">
                                        <i class="fas fa-wallet text-sky-500"></i> Option A: Full Payment (GCash)
                                    </span>
                                    <span class="text-xs font-bold px-2 py-0.5 bg-emerald-100 text-emerald-800 rounded-full">100% Upfront</span>
                                </div>
                                <p class="text-xs text-slate-500 mt-1">Pay 100% upfront via GCash. Room is held for 2 hours while proof is verified by receptionist.</p>
                                <div class="mt-2 text-xs font-semibold text-sky-700">
                                    Amount Due Today: <strong id="opt-a-amount" class="text-sm font-bold text-slate-900">₱0.00</strong>
                                </div>
                            </div>
                        </label>

                        <!-- Option B: 30% Downpayment -->
                        <label class="payment-card relative flex items-start p-4 rounded-2xl border-2 border-slate-200 bg-white cursor-pointer transition hover:border-brand-blue hover:shadow-sm" onclick="selectPaymentPathway('deposit_gcash')">
                            <input type="radio" name="payment_pathway" value="deposit_gcash" class="mt-1 text-brand-blue focus:ring-brand-blue">
                            <div class="ml-3 flex-1">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-bold text-slate-900 flex items-center gap-2">
                                        <i class="fas fa-percentage text-purple-500"></i> Option B: Partial Downpayment (GCash)
                                    </span>
                                    <span class="text-xs font-bold px-2 py-0.5 bg-purple-100 text-purple-800 rounded-full">30% Deposit</span>
                                </div>
                                <p class="text-xs text-slate-500 mt-1">Pay 30% deposit now to hold room. Remaining 70% balance collected at front desk upon check-in.</p>
                                <div class="mt-2 flex flex-wrap items-center justify-between text-xs text-slate-700">
                                    <span>Due Today: <strong id="opt-b-deposit" class="text-sm font-bold text-purple-700">₱0.00</strong></span>
                                    <span>Balance at Check-in: <strong id="opt-b-balance" class="font-bold text-slate-800">₱0.00</strong></span>
                                </div>
                            </div>
                        </label>

                        <!-- Option C: Pay at Hotel (Conditional <= 24h) -->
                        <div id="opt-c-wrapper" class="relative">
                            <label id="opt-c-label" class="payment-card relative flex items-start p-4 rounded-2xl border-2 border-slate-200 bg-white cursor-pointer transition hover:border-brand-blue hover:shadow-sm" onclick="selectPaymentPathway('pay_at_hotel')">
                                <input type="radio" id="opt-c-radio" name="payment_pathway" value="pay_at_hotel" class="mt-1 text-brand-blue focus:ring-brand-blue">
                                <div class="ml-3 flex-1">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-bold text-slate-900 flex items-center gap-2">
                                            <i class="fas fa-hotel text-teal-600"></i> Option C: Strict "Pay at Hotel"
                                        </span>
                                        <span id="opt-c-badge" class="text-xs font-bold px-2 py-0.5 bg-teal-100 text-teal-800 rounded-full">Same-Day / &le;24h</span>
                                    </div>
                                    <p class="text-xs text-slate-500 mt-1">Reserve without upfront payment. Held strictly until <strong>Expected Arrival Time + 1 hour</strong>.</p>
                                    <div class="mt-2 text-xs font-semibold text-teal-800">
                                        Total Due on Arrival: <strong id="opt-c-total" class="text-sm font-bold text-slate-900">₱0.00</strong>
                                    </div>
                                </div>
                            </label>

                            <!-- Locked Overlay for > 24 Hours -->
                            <div id="opt-c-locked-overlay" class="hidden absolute inset-0 bg-slate-100/90 backdrop-blur-[2px] rounded-2xl flex items-center justify-center p-4 border border-dashed border-slate-300 text-center">
                                <div>
                                    <i class="fas fa-lock text-slate-400 text-lg mb-1"></i>
                                    <p class="text-xs font-bold text-slate-700">Option C Locked (Advance Booking)</p>
                                    <p class="text-[11px] text-slate-500">Pay at Hotel is only available for check-ins within 24 hours.</p>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Option C Mandatory Expected Arrival Picker -->
                    <div id="arrival-time-section" class="hidden mt-5 p-4 bg-teal-50/70 border border-teal-200 rounded-2xl space-y-2">
                        <label class="block text-xs font-bold text-teal-900 uppercase tracking-wide">
                            <i class="far fa-clock text-teal-600 mr-1"></i> Mandatory: Expected Arrival Time
                        </label>
                        <p class="text-[11px] text-teal-700">
                            Your reservation will be held strictly until <strong>1 hour past your expected arrival time</strong>. Unclaimed rooms will auto-cancel as No-Show.
                        </p>
                        <div class="grid grid-cols-2 gap-3 pt-1">
                            <select id="book-expected-arrival" class="w-full px-3 py-2 bg-white border border-teal-300 rounded-xl text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-teal-500 outline-none">
                                <option value="14:00:00">2:00 PM (Standard Check-in)</option>
                                <option value="15:00:00">3:00 PM</option>
                                <option value="16:00:00">4:00 PM</option>
                                <option value="17:00:00">5:00 PM</option>
                                <option value="18:00:00">6:00 PM</option>
                                <option value="19:00:00">7:00 PM</option>
                                <option value="20:00:00">8:00 PM (Evening Arrival)</option>
                                <option value="21:00:00">9:00 PM (Late Arrival)</option>
                                <option value="22:00:00">10:00 PM (Night Arrival)</option>
                            </select>
                            <div class="flex items-center text-xs text-teal-800 font-medium">
                                <i class="fas fa-shield-alt text-teal-600 mr-1.5"></i> 1-Hour Hold Guarantee
                            </div>
                        </div>
                    </div>

                </div>

                <!-- 3. GCash Proof of Payment Section (For Option A & B) -->
                <div id="gcash-proof-section" class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-100 space-y-5">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2.5">
                            <span class="w-7 h-7 rounded-full bg-brand-blue text-white flex items-center justify-center text-xs font-bold">3</span>
                            GCash Payment & Proof
                        </h3>
                        <span class="text-xs text-sky-600 font-semibold flex items-center gap-1">
                            <i class="fas fa-shield-alt"></i> Official Inn Merchant
                        </span>
                    </div>

                    <!-- Inn GCash Account Card -->
                    <div class="bg-gradient-to-br from-sky-50 to-blue-50 border border-sky-100 rounded-2xl p-4 sm:p-5 flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <!-- QR Thumbnail with Click-to-Zoom -->
                            <div class="relative group cursor-pointer" onclick="openQrZoomModal()">
                                <img src="assets/image/background.jpg" id="gcash-qr-thumb" class="w-20 h-20 rounded-xl object-cover border-2 border-white shadow-sm" alt="GCash QR">
                                <div class="absolute inset-0 bg-black/40 rounded-xl opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white text-xs">
                                    <i class="fas fa-search-plus"></i>
                                </div>
                            </div>
                            <div>
                                <p class="text-[11px] text-sky-700 font-bold uppercase tracking-wider">Scan or Send To GCash</p>
                                <h4 class="text-base font-bold text-slate-900 mt-0.5">W Island Hub Backpackers</h4>
                                <p class="text-xs font-mono font-bold text-sky-900 mt-1 flex items-center gap-2">
                                    <span id="inn-gcash-number">0917-888-9999</span>
                                    <button type="button" onclick="copyGcashNumber()" class="text-[10px] bg-white px-2 py-0.5 rounded-md border border-sky-200 text-sky-700 hover:bg-sky-100 transition">
                                        <i class="far fa-copy"></i> Copy
                                    </button>
                                </p>
                            </div>
                        </div>

                        <div class="text-center sm:text-right w-full sm:w-auto">
                            <span class="text-[10px] text-slate-400 block uppercase font-bold">Amount To Transfer</span>
                            <span id="transfer-amount-due" class="text-2xl font-extrabold text-brand-blue">₱0.00</span>
                        </div>
                    </div>

                    <!-- GCash Reference Number Input -->
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1 uppercase tracking-wide">
                            GCash Reference Number (10 - 30 digits)
                        </label>
                        <input type="text" id="book-gcash-ref" placeholder="E.g. 1002938475891" 
                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-mono focus:bg-white focus:ring-2 focus:ring-brand-blue outline-none transition">
                        <p class="text-[11px] text-slate-400 mt-1">Found in your GCash SMS confirmation or Transaction Receipt.</p>
                    </div>

                    <!-- Receipt Image Upload Box -->
                    <div>
                        <label class="block text-xs font-bold text-slate-600 mb-1 uppercase tracking-wide">
                            Upload Proof of Payment (Receipt Screenshot)
                        </label>
                        
                        <div id="receipt-dropzone" class="border-2 border-dashed border-slate-200 hover:border-brand-blue rounded-2xl p-6 text-center bg-slate-50/50 hover:bg-blue-50/20 transition cursor-pointer relative" onclick="document.getElementById('receipt-file-input').click()">
                            <input type="file" id="receipt-file-input" accept="image/jpeg,image/png,image/webp" class="hidden" onchange="handleReceiptFileSelect(event)">
                            
                            <div id="dropzone-prompt">
                                <div class="w-12 h-12 rounded-full bg-blue-100 text-brand-blue flex items-center justify-center mx-auto mb-2 text-lg">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                </div>
                                <p class="text-xs font-bold text-slate-700">Click to upload or drag & drop receipt screenshot</p>
                                <p class="text-[11px] text-slate-400 mt-0.5">JPG, PNG, or WebP (Max 5MB)</p>
                            </div>

                            <div id="receipt-preview-container" class="hidden flex items-center justify-center gap-3">
                                <img id="receipt-preview-img" src="" class="h-20 w-20 object-cover rounded-xl border border-slate-200 shadow-sm" alt="Receipt Preview">
                                <div class="text-left">
                                    <p id="receipt-filename" class="text-xs font-bold text-slate-800 truncate max-w-[200px]">receipt.jpg</p>
                                    <p id="receipt-filesize" class="text-[10px] text-slate-400">1.2 MB</p>
                                    <button type="button" onclick="event.stopPropagation(); removeReceiptFile();" class="mt-1 text-[11px] font-bold text-rose-600 hover:underline">
                                        <i class="fas fa-trash-alt mr-1"></i> Remove
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 2-Hour Hold Notice -->
                    <div class="p-3.5 bg-amber-50/80 border border-amber-200 rounded-2xl flex items-start gap-3">
                        <i class="fas fa-stopwatch text-amber-600 mt-0.5 text-sm"></i>
                        <p class="text-xs text-amber-800 leading-relaxed">
                            <strong>2-Hour Inventory Lock:</strong> Once submitted, the room is held for <strong>2 hours</strong> for staff verification. If proof cannot be verified within 2 hours, the hold will automatically expire.
                        </p>
                    </div>
                </div>

            </div>

            <!-- RIGHT SUMMARY & SUBMISSION COLUMN (5 cols) -->
            <div class="lg:col-span-5 sticky top-24 space-y-6">
                
                <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-slate-100">
                    <h3 class="text-lg font-bold text-slate-900 mb-4 pb-3 border-b border-slate-100">
                        Stay Summary
                    </h3>

                    <!-- Selected Room Card -->
                    <div class="flex items-center gap-4 pb-5 border-b border-slate-100">
                        <div class="w-20 h-20 rounded-2xl overflow-hidden bg-slate-100 flex-shrink-0">
                            <img id="summary-room-img" src="assets/image/background.jpg" class="w-full h-full object-cover" alt="Room Preview">
                        </div>
                        <div>
                            <span id="summary-room-type" class="text-[10px] uppercase font-bold text-brand-blue tracking-wider">Hostel Room</span>
                            <h4 id="summary-room-name" class="text-base font-bold text-slate-900">Select A Room</h4>
                            <p class="text-xs text-slate-500 mt-0.5">₱<span id="summary-room-price">0.00</span> / night</p>
                        </div>
                    </div>

                    <!-- Stay Breakdown -->
                    <div class="py-4 space-y-2.5 text-xs border-b border-slate-100">
                        <div class="flex justify-between text-slate-500">
                            <span>Check-In</span>
                            <span id="summary-checkin" class="font-bold text-slate-800">-</span>
                        </div>
                        <div class="flex justify-between text-slate-500">
                            <span>Check-Out</span>
                            <span id="summary-checkout" class="font-bold text-slate-800">-</span>
                        </div>
                        <div class="flex justify-between text-slate-500">
                            <span>Duration</span>
                            <span id="summary-nights" class="font-bold text-slate-800">0 Nights</span>
                        </div>
                    </div>

                    <!-- Price Calculation -->
                    <div class="py-4 space-y-2.5 text-xs border-b border-slate-100">
                        <div class="flex justify-between text-slate-600">
                            <span>Room Total Bill</span>
                            <span id="summary-total-bill" class="font-bold text-slate-900">₱0.00</span>
                        </div>
                        <div class="flex justify-between text-brand-blue font-bold text-sm pt-1">
                            <span>Amount Due Today</span>
                            <span id="summary-amount-due">₱0.00</span>
                        </div>
                        <div class="flex justify-between text-slate-500 pt-0.5">
                            <span>Balance Due at Check-In</span>
                            <span id="summary-balance-due" class="font-semibold text-slate-700">₱0.00</span>
                        </div>
                    </div>

                    <!-- Policy Checkbox -->
                    <div class="pt-4">
                        <label class="flex items-start gap-2 cursor-pointer text-xs text-slate-500">
                            <input type="checkbox" id="book-terms-check" required class="mt-0.5 rounded text-brand-blue focus:ring-brand-blue">
                            <span>I agree to the <a href="#rules" class="text-brand-blue font-semibold hover:underline">Hostel Rules</a>, cancellation policy, and inventory hold terms.</span>
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-5">
                        <button type="button" id="submit-booking-btn" onclick="submitReservation()" class="w-full bg-gradient-to-r from-brand-blue to-teal-500 hover:from-sky-600 hover:to-teal-600 text-white py-3.5 rounded-2xl font-bold text-sm shadow-md hover:shadow-lg transition flex items-center justify-center gap-2">
                            <i class="fas fa-lock"></i> Confirm & Place Reservation
                        </button>
                    </div>

                </div>

                <!-- Hostel Support / Trust Card -->
                <div class="bg-slate-100/70 rounded-2xl p-4 border border-slate-200 text-xs text-slate-600 space-y-2">
                    <div class="flex items-center gap-2 font-bold text-slate-800">
                        <i class="fas fa-info-circle text-brand-blue"></i> Need Assistance?
                    </div>
                    <p class="leading-relaxed">
                        For immediate assistance or special arrival requests, message our reception directly at <strong class="text-slate-800">+63 917-888-9999</strong>.
                    </p>
                </div>

            </div>

        </div>

    </div>
</section>

<!-- ================= MODAL: FULL GCASH QR ZOOM ================= -->
<div id="qr-zoom-modal" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-[110] hidden items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-sm w-full p-6 text-center relative border border-slate-100">
        <button onclick="closeQrZoomModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 text-lg">
            <i class="fas fa-times"></i>
        </button>
        <h4 class="text-base font-bold text-slate-900 mb-1">Scan GCash QR Code</h4>
        <p class="text-xs text-slate-500 mb-4">W Island Hub Backpackers Official Account</p>
        
        <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100 mb-4">
            <img src="assets/image/background.jpg" class="w-64 h-64 mx-auto object-cover rounded-xl shadow-inner" alt="GCash Full QR">
        </div>

        <p class="text-xs font-mono font-bold text-slate-800">0917-888-9999</p>
        <p class="text-[11px] text-slate-400 mt-1">Please ensure to take a screenshot of your transfer confirmation receipt.</p>
    </div>
</div>

<!-- ================= MODAL: BOOKING SUCCESS / INVOICE VOUCHER ================= -->
<div id="booking-success-modal" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-[120] hidden items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full overflow-hidden shadow-2xl border border-slate-100 animate-in fade-in zoom-in duration-200">
        <div class="bg-gradient-to-r from-slate-900 to-brand-blue p-6 text-white text-center relative">
            <div class="w-12 h-12 rounded-full bg-emerald-400/20 text-emerald-300 flex items-center justify-center mx-auto mb-2 text-2xl">
                <i class="fas fa-check-circle"></i>
            </div>
            <h3 class="text-xl font-bold font-serif">Reservation Placed!</h3>
            <p class="text-xs text-teal-200 mt-0.5" id="success-modal-subtitle">Your room hold is now active</p>
        </div>

        <div class="p-6 space-y-4 text-xs">
            <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100 space-y-2">
                <div class="flex justify-between">
                    <span class="text-slate-500">Booking Reference:</span>
                    <strong id="success-ref-id" class="font-mono text-slate-800 text-sm">WIH-XXXXXX</strong>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Room:</span>
                    <strong id="success-room-name" class="text-slate-800">-</strong>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Dates:</span>
                    <strong id="success-dates" class="text-slate-800">-</strong>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Payment Pathway:</span>
                    <strong id="success-payment-method" class="text-slate-800">-</strong>
                </div>
                <div class="flex justify-between pt-2 border-t border-slate-200">
                    <span class="text-slate-600 font-bold">Total Bill:</span>
                    <strong id="success-total" class="text-brand-blue font-bold text-sm">₱0.00</strong>
                </div>
            </div>

            <!-- Hold Countdown Box -->
            <div id="success-hold-timer-box" class="p-4 bg-amber-50 rounded-2xl border border-amber-200 text-center">
                <p class="text-[11px] font-bold text-amber-800 uppercase tracking-wider">Active Inventory Hold Timer</p>
                <div id="hold-countdown-clock" class="text-2xl font-black text-amber-900 font-mono my-1">
                    01:59:59
                </div>
                <p class="text-[10px] text-amber-700" id="hold-countdown-hint">
                    Room is held while staff reviews your GCash payment proof.
                </p>
            </div>

            <div class="flex gap-3 pt-2">
                <a href="user/profile.php" class="flex-1 bg-brand-blue hover:bg-sky-600 text-white text-center font-bold py-3 rounded-xl transition">
                    View in My Bookings
                </a>
                <button onclick="closeSuccessModal()" class="px-5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition">
                    Done
                </button>
            </div>
        </div>
    </div>
</div>