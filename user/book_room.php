<?php
if (!isset($pdo) || !$pdo) {
    require_once 'config/db.php';
    $pdo = getDB();
}
$system_max_capacity = 8;
if ($pdo) {
    try {
        $m = $pdo->query("SELECT MAX(GREATEST(COALESCE(capacity, 1), COALESCE(max_capacity, 1))) FROM rooms")->fetchColumn();
        if ($m && intval($m) > 0) $system_max_capacity = intval($m);
    } catch(Exception $e) {}
}
?>
  <!-- ================= ROOMS PAGE ================= -->
        <div id="rooms" class="page-section">
            <div class="bg-brand-dark text-white py-16 text-center">
                <h1 class="text-4xl font-serif font-bold">Our Accommodations</h1>
                <p class="mt-4 text-brand-sand">Choose the perfect space for your relaxation</p>
            </div>
            
            <div class="max-w-7xl mx-auto px-4 py-12">
                <!-- Date Filter for Availability -->
                <div class="bg-white p-6 rounded-xl shadow-lg -mt-24 relative z-10 mb-12 border border-gray-100">
                    <form id="check-availability-form" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Check-In</label>
                            <input type="date" id="check-in-date" min="<?php echo date('Y-m-d'); ?>" value="<?php echo date('Y-m-d'); ?>" class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-brand-blue outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Check-Out</label>
                            <input type="date" id="check-out-date" min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" value="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-brand-blue outline-none">
                        </div>
                        <!-- Guests Selector (Adults & Children in the same box) -->
                        <div class="relative" id="rooms-guest-box-container">
                            <label class="block text-sm font-bold text-gray-700 mb-1 flex items-center gap-1.5">
                                <i class="fas fa-users text-brand-blue"></i> Guests
                            </label>
                            <div id="rooms-guest-trigger" onclick="toggleGuestDropdown('rooms')" class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-brand-blue outline-none text-gray-700 bg-white hover:bg-slate-50 cursor-pointer flex items-center justify-between select-none transition">
                                <div class="flex items-center gap-2 truncate">
                                    <span id="rooms-guest-summary-text" class="truncate font-semibold text-gray-800 text-sm">1 Guest (1 Adult)</span>
                                </div>
                                <i id="rooms-guest-chevron" class="fas fa-chevron-down text-gray-400 text-xs transition-transform duration-200"></i>
                            </div>

                            <!-- Hidden Inputs for Form Submission / Logic -->
                            <input type="hidden" id="guest-count" name="guest_count" value="1">
                            <input type="hidden" id="adults-count" name="adults" value="1">
                            <input type="hidden" id="children-count" name="children" value="0">

                            <!-- Dropdown Popover (Same Box) -->
                            <div id="rooms-guest-dropdown" class="hidden absolute left-0 right-0 sm:right-auto sm:w-80 top-full mt-2 bg-white rounded-2xl shadow-2xl border border-gray-200 p-4 z-50 space-y-4">
                                <div class="flex justify-between items-center pb-2 border-b border-gray-100">
                                    <div>
                                        <h4 class="text-xs font-bold uppercase text-gray-700 tracking-wider">Select Guests</h4>
                                    </div>
                                    <button type="button" onclick="closeGuestDropdown('rooms')" class="text-gray-400 hover:text-gray-600 text-xs p-1 rounded-lg">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>

                                <!-- Adults: 18+ -->
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="font-bold text-gray-800 text-sm">Adults</div>
                                        <div class="text-[11px] text-gray-400 font-medium">Ages 18 and above</div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button type="button" onclick="updateGuestStepper('rooms', 'adults', -1)" class="w-8 h-8 rounded-full border border-gray-200 hover:border-gray-300 bg-gray-50 hover:bg-gray-100 flex items-center justify-center text-gray-600 font-bold transition disabled:opacity-30 disabled:cursor-not-allowed" id="rooms-adults-minus" disabled>
                                            <i class="fas fa-minus text-xs"></i>
                                        </button>
                                        <span id="rooms-adults-display" class="w-8 text-center font-bold text-gray-800 text-sm">1</span>
                                        <button type="button" onclick="updateGuestStepper('rooms', 'adults', 1)" class="w-8 h-8 rounded-full border border-gray-200 hover:border-brand-blue bg-gray-50 hover:bg-blue-50 flex items-center justify-center text-gray-700 hover:text-brand-blue font-bold transition">
                                            <i class="fas fa-plus text-xs"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Children: 17 & below -->
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="font-bold text-gray-800 text-sm">Children</div>
                                        <div class="text-[11px] text-gray-400 font-medium">Ages 17 &amp; below</div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button type="button" onclick="updateGuestStepper('rooms', 'children', -1)" class="w-8 h-8 rounded-full border border-gray-200 hover:border-gray-300 bg-gray-50 hover:bg-gray-100 flex items-center justify-center text-gray-600 font-bold transition disabled:opacity-30 disabled:cursor-not-allowed" id="rooms-children-minus" disabled>
                                            <i class="fas fa-minus text-xs"></i>
                                        </button>
                                        <span id="rooms-children-display" class="w-8 text-center font-bold text-gray-800 text-sm">0</span>
                                        <button type="button" onclick="updateGuestStepper('rooms', 'children', 1)" class="w-8 h-8 rounded-full border border-gray-200 hover:border-brand-blue bg-gray-50 hover:bg-blue-50 flex items-center justify-center text-gray-700 hover:text-brand-blue font-bold transition">
                                            <i class="fas fa-plus text-xs"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Age Guidance Badge -->
                                <div class="p-2.5 rounded-xl bg-amber-50/70 border border-amber-200/60 text-[11px] text-amber-900 flex items-start gap-2">
                                    <i class="fas fa-info-circle text-amber-500 mt-0.5 flex-shrink-0"></i>
                                    <span>Age 18+ counts as Adult. Age 17 and below counts as Child.</span>
                                </div>

                                <!-- Capacity Overlimit Warning in Dropdown -->
                                <div id="rooms-stepper-capacity-warning" class="hidden p-2.5 rounded-xl bg-rose-50 border border-rose-200 text-[11px] text-rose-800 flex items-start gap-2">
                                    <i class="fas fa-exclamation-triangle text-rose-500 mt-0.5 flex-shrink-0"></i>
                                    <span id="rooms-stepper-capacity-warning-text">The hotel does not accommodate this party size as room capacity is limited.</span>
                                </div>

                                <!-- Apply Button -->
                                <button type="button" onclick="closeGuestDropdown('rooms')" class="w-full bg-brand-blue hover:bg-sky-600 text-white font-bold py-2 rounded-xl text-xs transition shadow-sm">
                                    Done (<span id="rooms-total-guest-badge">1 Guest</span>)
                                </button>
                            </div>
                        </div>
                        <button type="submit" class="bg-brand-blue hover:bg-sky-600 text-white font-bold py-3 rounded-lg transition shadow-md">
                            Check Availability
                        </button>
                    </form>
                </div>
                
                <!-- Hotel Capacity Warning Banner -->
                <div id="capacity-warning-banner" class="hidden mb-8 p-5 bg-rose-50 border-2 border-rose-200 rounded-2xl text-rose-900 shadow-sm">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center flex-shrink-0 text-xl font-bold">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <h4 class="text-sm font-bold text-rose-900 uppercase tracking-wide">Hotel Capacity Limit Notice</h4>
                                <span class="text-[10px] bg-rose-200 text-rose-800 px-2 py-0.5 rounded-full font-bold">Capacity Exceeded</span>
                            </div>
                            <p id="capacity-warning-text" class="text-xs sm:text-sm text-rose-800 mt-1.5 font-semibold leading-relaxed">
                                The hotel does not accommodate the number of guests requested because available room capacity is limited.
                            </p>
                            <div class="mt-2.5 text-xs text-rose-700 flex flex-wrap items-center gap-4 font-normal">
                                <span><i class="fas fa-arrow-down mr-1 text-rose-500"></i> Please reduce your guest count or select different dates.</span>
                                <a href="javascript:void(0)" onclick="router('contact')" class="font-bold underline hover:text-rose-950 flex items-center gap-1">
                                    <i class="fas fa-headset text-rose-500"></i> Contact Front Desk
                                </a>
                            </div>
                        </div>
                        <button type="button" onclick="document.getElementById('capacity-warning-banner').classList.add('hidden')" class="text-rose-400 hover:text-rose-700 text-sm p-1 rounded-lg" title="Dismiss">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>

                <!-- Rooms Grid -->
                <?php include "user/rooms.php"; ?>
            </div>
        </div>
 
 
 
 <!-- ================= BOOKING SUMMARY ================= -->
        <div id="booking-summary" class="page-section">
            <div class="max-w-5xl mx-auto px-4 py-12">
                <div class="bg-white rounded-3xl shadow-2xl overflow-hidden border border-gray-100">
                    <div class="bg-gradient-to-r from-brand-dark via-slate-900 to-brand-blue p-6 sm:p-8 text-white flex justify-between items-center">
                        <div>
                            <span class="text-xs uppercase font-bold text-teal-300 tracking-wider">Step 2 of 2</span>
                            <h2 class="text-2xl sm:text-3xl font-serif font-bold mt-1">Review & Confirm Reservation</h2>
                        </div>
                        <button onclick="router('rooms')" class="text-sm font-semibold bg-white/10 hover:bg-white/20 px-4 py-2 rounded-xl transition flex items-center gap-2">
                            <i class="fas fa-arrow-left"></i> Change Room / Dates
                        </button>
                    </div>

                    <div class="p-6 sm:p-8 grid grid-cols-1 lg:grid-cols-12 gap-8">
                        <!-- Left Column: Form & Payment Selector -->
                        <div class="lg:col-span-7 space-y-6">
                            <!-- Stay Details Summary with Date Change Controls -->
                            <div>
                                <div class="flex items-center justify-between mb-3">
                                    <h3 class="text-base font-bold text-gray-800 flex items-center gap-2">
                                        <i class="fas fa-calendar-alt text-brand-blue"></i> Stay Dates &amp; Reservation Details
                                    </h3>
                                    <span id="summary-dates-badge" class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 flex items-center gap-1">
                                        <i class="fas fa-check-circle text-emerald-500"></i> <span id="summary-dates-badge-text">Available</span>
                                    </span>
                                </div>

                                <!-- Date Modification Card -->
                                <div class="p-4 sm:p-5 bg-slate-50 border border-slate-200/90 rounded-2xl mb-4 shadow-xs">
                                    <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                                        <span class="text-xs font-bold uppercase tracking-wider text-slate-700 flex items-center gap-1.5">
                                            <i class="fas fa-calendar-check text-amber-500"></i> Reservation Dates
                                        </span>
                                        <span class="text-[11px] text-slate-500 flex items-center gap-1">
                                            <i class="fas fa-edit text-brand-blue"></i> Modify dates anytime before confirmation
                                        </span>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                                        <div>
                                            <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">
                                                Check-in Date <span class="text-slate-400 font-normal">(2:00 PM)</span>
                                            </label>
                                            <div class="relative">
                                                <input type="date" id="summary-check-in-date" onchange="handleSummaryDateChange()" class="w-full bg-white border border-slate-300 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm font-semibold text-slate-800 focus:ring-2 focus:ring-brand-blue focus:border-brand-blue outline-none transition shadow-xs cursor-pointer">
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">
                                                Check-out Date <span class="text-slate-400 font-normal">(11:00 AM)</span>
                                            </label>
                                            <div class="relative">
                                                <input type="date" id="summary-check-out-date" onchange="handleSummaryDateChange()" class="w-full bg-white border border-slate-300 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm font-semibold text-slate-800 focus:ring-2 focus:ring-brand-blue focus:border-brand-blue outline-none transition shadow-xs cursor-pointer">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex flex-wrap items-center justify-between gap-2 text-xs pt-2.5 border-t border-slate-200/60 text-slate-600">
                                        <span id="summary-stay-duration" class="font-medium text-slate-700 flex items-center gap-1.5">
                                            <i class="fas fa-moon text-amber-500"></i> Duration: <strong id="summary-stay-nights-text">1 Night</strong>
                                        </span>
                                        <span id="summary-date-status-msg" class="text-[11px] text-emerald-600 font-medium flex items-center gap-1">
                                            <i class="fas fa-check text-emerald-500"></i> Pricing and availability auto-update
                                        </span>
                                    </div>
                                </div>

                                <div id="booking-details-content" class="space-y-3 text-gray-600">
                                    <!-- Filled by JS -->
                                </div>
                            </div>
                            
                            <!-- Guest Information Form -->
                            <form id="booking-form" class="space-y-6">
                                <div>
                                    <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                                        <h3 class="text-base font-bold text-gray-800 flex items-center gap-2">
                                            <i class="fas fa-user-circle text-brand-blue"></i> Guest Information
                                        </h3>
                                        <div id="guest-booking-notice">
                                            <span class="inline-flex items-center gap-1 text-xs text-brand-blue font-semibold bg-blue-50 px-3 py-1 rounded-full border border-blue-200">
                                                <i class="fas fa-bolt text-amber-500"></i> Guest Checkout (No login required) &bull; <a href="javascript:void(0)" onclick="router('login')" class="underline hover:text-brand-dark font-bold ml-1">Log in</a>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Full Name <span class="text-rose-500">*</span></label>
                                            <input type="text" id="book-name" required class="w-full border border-gray-200 bg-slate-50 focus:bg-white rounded-xl p-3 text-sm focus:ring-2 focus:ring-brand-blue outline-none transition" placeholder="Primary guest name">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Email Address <span class="text-rose-500">*</span></label>
                                            <input type="email" id="book-email" required class="w-full border border-gray-200 bg-slate-50 focus:bg-white rounded-xl p-3 text-sm focus:ring-2 focus:ring-brand-blue outline-none transition" placeholder="name@example.com">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Phone / Mobile <span class="text-rose-500">*</span></label>
                                            <input type="tel" id="book-phone" required class="w-full border border-gray-200 bg-slate-50 focus:bg-white rounded-xl p-3 text-sm focus:ring-2 focus:ring-brand-blue outline-none transition" placeholder="+63 900 000 0000">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Special Requests (Optional)</label>
                                            <input type="text" id="book-requests" class="w-full border border-gray-200 bg-slate-50 focus:bg-white rounded-xl p-3 text-sm focus:ring-2 focus:ring-brand-blue outline-none transition" placeholder="e.g., Lower bunk, early luggage drop">
                                        </div>
                                    </div>
                                </div>

                                <!-- Payment Pathway Selector -->
                                <div>
                                    <div class="flex items-center justify-between mb-3">
                                        <h3 class="text-base font-bold text-gray-800 flex items-center gap-2">
                                            <i class="fas fa-credit-card text-brand-blue"></i> Choose Payment Pathway
                                        </h3>
                                        <span id="payment-policy-badge" class="text-xs px-2.5 py-1 bg-blue-50 text-brand-blue font-bold rounded-full">
                                            Multi-Tier Engine
                                        </span>
                                    </div>

                                    <div class="space-y-3" id="payment-options-container">
                                        <!-- Option 1: Instant Online Payment (PayMongo - Recommended) -->
                                        <label class="payment-option-card relative flex items-start gap-3 p-4 rounded-2xl border-2 border-brand-blue bg-blue-50/40 cursor-pointer hover:border-brand-blue transition shadow-sm" id="label-payment-paymongo">
                                            <input type="radio" name="payment" value="paymongo" checked onchange="handlePaymentMethodChange(this.value)" class="mt-1 text-brand-blue focus:ring-brand-blue">
                                            <div class="flex-1">
                                                <div class="flex items-center justify-between flex-wrap gap-1">
                                                    <span class="font-bold text-gray-800 text-sm flex items-center gap-2">
                                                        <i class="fas fa-bolt text-amber-500 text-base"></i> Online Payment (PayMongo)
                                                    </span>
                                                    <span class="text-[11px] font-bold bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-full flex items-center gap-1">
                                                        <i class="fas fa-check-circle text-[10px]"></i> Instant Confirmation
                                                    </span>
                                                </div>
                                                <p class="text-xs text-gray-500 mt-1">Pay instantly via Credit/Debit Cards, GCash, Maya, or GrabPay. 100% automated &mdash; no receipt upload needed.</p>
                                                <div class="flex items-center gap-1.5 mt-2.5 pt-2 border-t border-blue-100/70 text-[11px] text-gray-600 flex-wrap">
                                                    <span class="inline-flex items-center gap-1 bg-white px-2 py-0.5 rounded border border-gray-200 font-semibold text-blue-700">
                                                        <i class="fab fa-cc-visa text-sm"></i> Visa
                                                    </span>
                                                    <span class="inline-flex items-center gap-1 bg-white px-2 py-0.5 rounded border border-gray-200 font-semibold text-rose-500">
                                                        <i class="fab fa-cc-mastercard text-sm"></i> Mastercard
                                                    </span>
                                                    <span class="inline-flex items-center gap-1 bg-white px-2 py-0.5 rounded border border-gray-200 font-semibold text-blue-600">
                                                        <span class="w-3.5 h-3.5 rounded-full bg-blue-600 text-white font-bold text-[9px] inline-flex items-center justify-center">G</span> GCash
                                                    </span>
                                                    <span class="inline-flex items-center gap-1 bg-white px-2 py-0.5 rounded border border-gray-200 font-semibold text-emerald-600">
                                                        <i class="fas fa-wallet text-emerald-500 text-xs"></i> Maya
                                                    </span>
                                                    <span class="inline-flex items-center gap-1 bg-white px-2 py-0.5 rounded border border-gray-200 font-semibold text-green-700">
                                                        GrabPay
                                                    </span>
                                                </div>
                                            </div>
                                        </label>

                                        <!-- Option 2: Strict "Pay at Hotel" (Conditional) -->
                                        <div id="option-c-wrapper">
                                            <label class="payment-option-card relative flex items-start gap-3 p-4 rounded-2xl border-2 border-gray-200 bg-white cursor-pointer hover:border-brand-blue transition" id="label-payment-hotel">
                                                <input type="radio" name="payment" value="pay_at_hotel" id="input-payment-hotel" onchange="handlePaymentMethodChange(this.value)" class="mt-1 text-brand-blue focus:ring-brand-blue">
                                                <div class="flex-1">
                                                    <div class="flex items-center justify-between">
                                                        <span class="font-bold text-gray-800 text-sm flex items-center gap-2">
                                                            <i class="fas fa-store text-purple-600 text-base"></i> Option 2: Strict "Pay at Hotel"
                                                        </span>
                                                        <span class="text-xs font-bold bg-purple-100 text-purple-800 px-2 py-0.5 rounded">Same-Day / &le;24h Only</span>
                                                    </div>
                                                    <p class="text-xs text-gray-500 mt-1">Available strictly for bookings with check-in within 24 hours. Room is held strictly until your arrival time + 1 hour.</p>
                                                </div>
                                            </label>

                                            <!-- Notice shown when Pay at Hotel is unavailable due to >24hr rule -->
                                            <div id="pay-at-hotel-locked-notice" class="hidden p-3.5 bg-gray-50 rounded-2xl border border-gray-200 text-xs text-gray-500 flex items-center gap-2 mt-1">
                                                <i class="fas fa-lock text-gray-400"></i>
                                                <span><strong>Pay at Hotel</strong> is locked because your check-in date is more than 24 hours away. Please proceed with PayMongo Online.</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- PayMongo Information Section (Active by default) -->
                                <div id="paymongo-payment-section" class="bg-gradient-to-br from-slate-50 to-blue-50/60 p-6 rounded-2xl border border-blue-200/80 space-y-4 shadow-sm">
                                    <div class="flex items-center justify-between border-b border-blue-100 pb-3">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-brand-blue to-teal-500 text-white flex items-center justify-center font-bold text-sm shadow-sm">
                                                <i class="fas fa-lock"></i>
                                            </div>
                                            <div>
                                                <h4 class="font-bold text-gray-800 text-sm">PayMongo Secure Checkout</h4>
                                                <p class="text-[11px] text-gray-500">PCI-DSS Encrypted &bull; 3D Secure / OTP Protected</p>
                                            </div>
                                        </div>
                                        <span class="text-xs font-bold bg-emerald-100 text-emerald-800 px-2.5 py-1 rounded-full flex items-center gap-1">
                                            <i class="fas fa-bolt"></i> Instant Hold
                                        </span>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                                        <div class="p-3 bg-white rounded-xl border border-gray-100 flex items-start gap-2 shadow-xs">
                                            <i class="fas fa-check-circle text-emerald-500 mt-0.5"></i>
                                            <div>
                                                <strong class="text-gray-800 block">Zero Receipt Uploads</strong>
                                                <span class="text-gray-500 text-[11px]">System automatically verifies your transaction within seconds.</span>
                                            </div>
                                        </div>
                                        <div class="p-3 bg-white rounded-xl border border-gray-100 flex items-start gap-2 shadow-xs">
                                            <i class="fas fa-shield-alt text-brand-blue mt-0.5"></i>
                                            <div>
                                                <strong class="text-gray-800 block">Bank-Grade Protection</strong>
                                                <span class="text-gray-500 text-[11px]">Direct encrypted payment powered by official PayMongo API.</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="p-3 bg-blue-50/70 rounded-xl text-xs text-brand-dark border border-blue-100 flex items-center justify-between">
                                        <span class="text-gray-600">Amount to pay via PayMongo:</span>
                                        <strong class="text-base font-bold text-brand-blue" id="paymongo-amount-display">₱0.00</strong>
                                    </div>
                                </div>

                                <!-- Expected Arrival Time Section (Required for Pay at Hotel) -->
                                <div id="arrival-time-section" class="hidden bg-purple-50/70 p-5 rounded-2xl border border-purple-200 space-y-3">
                                    <div class="flex items-center justify-between">
                                        <label class="block text-xs font-bold text-purple-900 uppercase tracking-wide">
                                            <i class="fas fa-clock mr-1 text-purple-600"></i> Mandatory Expected Arrival Time <span class="text-rose-500">*</span>
                                        </label>
                                        <span class="text-[11px] font-bold text-purple-700 bg-purple-100 px-2 py-0.5 rounded">Required for Hold</span>
                                    </div>
                                    <input type="time" id="book-arrival-time" class="w-full border border-purple-300 bg-white rounded-xl p-3 text-sm focus:ring-2 focus:ring-purple-500 outline-none font-semibold text-gray-800" value="14:00">
                                    <div class="p-3 bg-white/80 rounded-xl text-xs text-purple-900 border border-purple-100 flex items-start gap-2">
                                        <i class="fas fa-exclamation-triangle text-amber-500 mt-0.5"></i>
                                        <span><strong>Strict Hold Condition:</strong> Your room reservation will be held strictly until your expected arrival time <strong>+ 1 hour (cutoff)</strong>. If you do not arrive or contact us, the hold automatically expires and releases the room.</span>
                                    </div>
                                </div>



                                <button type="submit" id="btn-confirm-booking" class="w-full bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white font-bold py-4 rounded-2xl shadow-xl hover:shadow-2xl transition flex items-center justify-center gap-2 text-base">
                                    <i class="fas fa-lock"></i>
                                    <span id="btn-confirm-text">Confirm Reservation</span>
                                </button>
                            </form>
                        </div>

                        <!-- Right Column: Room Card & Price Breakdown -->
                        <div class="lg:col-span-5 space-y-6">
                            <div class="bg-slate-50 p-6 rounded-3xl border border-gray-100 shadow-sm sticky top-24">
                                <div class="relative rounded-2xl overflow-hidden mb-4 shadow-sm">
                                    <img id="summary-room-img" src="" class="w-full h-48 object-cover bg-gray-200" alt="Room Image">
                                    <span id="summary-room-type-badge" class="absolute top-3 left-3 bg-white/95 backdrop-blur px-3 py-1 rounded-full text-xs font-bold text-brand-blue uppercase shadow-sm">
                                        Room
                                    </span>
                                </div>

                                <h3 id="summary-room-name" class="text-xl font-bold text-brand-dark mb-1"></h3>
                                <p id="summary-room-desc" class="text-xs text-gray-500 mb-4 line-clamp-2"></p>

                                <div class="border-t border-gray-200 pt-4 space-y-2.5 text-sm">
                                    <div class="flex justify-between text-gray-600">
                                        <span>Price per night</span>
                                        <span id="summary-price" class="font-semibold text-gray-800">₱0.00</span>
                                    </div>
                                    <div class="flex justify-between text-gray-600 hidden" id="summary-rooms-count-row">
                                        <span>Rooms Reserved</span>
                                        <span id="summary-rooms-count" class="font-semibold text-brand-blue">1 Room</span>
                                    </div>
                                    <div class="flex justify-between text-gray-600">
                                        <span>Stay Duration</span>
                                        <span id="summary-nights" class="font-semibold text-gray-800">1 Night</span>
                                    </div>
                                    <div class="flex justify-between text-gray-600">
                                        <span>Taxes & Service Fees</span>
                                        <span id="summary-tax" class="font-semibold text-gray-800">₱15.00</span>
                                    </div>
                                    
                                    <div class="border-t border-gray-200 pt-3 flex justify-between items-center text-base font-bold text-gray-900">
                                        <span>Total Bill</span>
                                        <span id="summary-total" class="text-xl text-brand-dark">₱0.00</span>
                                    </div>
                                </div>

                                <!-- Dynamic Multi-Tier Breakdown Box -->
                                <div class="mt-5 p-4 rounded-2xl bg-white border border-gray-200 shadow-sm space-y-2 text-xs">
                                    <div class="flex justify-between items-center pb-2 border-b border-gray-100">
                                        <span class="text-gray-500 font-semibold uppercase tracking-wider">Required Upfront:</span>
                                        <span id="summary-upfront-amount" class="text-base font-bold text-emerald-600">₱0.00</span>
                                    </div>
                                    <div class="flex justify-between items-center pt-1">
                                        <span class="text-gray-500 font-semibold uppercase tracking-wider">Balance at Front Desk:</span>
                                        <span id="summary-balance-amount" class="text-sm font-bold text-brand-blue">₱0.00</span>
                                    </div>
                                    <div class="pt-2 text-[11px] text-gray-400 flex items-center gap-1.5" id="summary-timer-hint">
                                        <i class="fas fa-shield-alt text-brand-blue"></i>
                                        <span>Instant inventory lock & receipt voucher.</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>



        <!-- ================= ROOM RECOMMENDATION MODAL ================= -->
        <!-- Shown when user party size (e.g. 4+) exceeds a 2-capacity room -->
        <div id="room-recommendation-modal" class="fixed inset-0 bg-black/75 backdrop-blur-xs z-[200] hidden items-center justify-center p-4 transition-opacity duration-300" onclick="closeRoomRecommendationModal(event)">
            <div class="bg-white rounded-3xl shadow-2xl max-w-xl w-full p-6 sm:p-7 relative overflow-hidden border border-gray-100 max-h-[92vh] flex flex-col" onclick="event.stopPropagation()">
                <button type="button" onclick="closeRoomRecommendationModal()" class="absolute top-4 right-4 w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-500 flex items-center justify-center transition" title="Close">
                    <i class="fas fa-times text-sm"></i>
                </button>

                <div class="flex items-center gap-3 mb-4 flex-shrink-0">
                    <div class="w-11 h-11 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center text-xl flex-shrink-0 shadow-inner">
                        <i class="fas fa-lightbulb"></i>
                    </div>
                    <div>
                        <span class="text-[10px] uppercase font-bold tracking-wider text-amber-600">Smart Recommendation</span>
                        <h3 class="text-lg sm:text-xl font-serif font-bold text-gray-800 leading-tight">Need More Space for Your Group?</h3>
                    </div>
                </div>

                <!-- Explanation Banner -->
                <div class="p-3.5 bg-amber-50/90 rounded-2xl border border-amber-200 text-xs text-amber-900 mb-4 leading-relaxed flex-shrink-0" id="rec-explanation-banner">
                    <!-- Populated dynamically by JS -->
                </div>

                <!-- Recommendations Container (Scrollable) -->
                <div class="space-y-4 overflow-y-auto flex-1 pr-1" id="rec-options-container">
                    <!-- Populated dynamically by JS (Add Multiple Rooms & Switch to Larger Room) -->
                </div>

                <!-- Footer Alternative Actions -->
                <div class="pt-4 mt-4 border-t border-gray-100 flex items-center justify-between gap-3 flex-shrink-0 text-xs">
                    <button type="button" onclick="closeRoomRecommendationModal()" class="text-gray-500 hover:text-gray-800 font-semibold px-3 py-2 rounded-xl transition">
                        Change Guests
                    </button>
                    <button type="button" id="rec-proceed-single-btn" onclick="proceedSingleRoomAnyway()" class="text-slate-500 hover:text-slate-800 bg-slate-100 hover:bg-slate-200 px-3.5 py-2 rounded-xl font-medium transition">
                        Continue with 1 Room (2 Guests Max)
                    </button>
                </div>
            </div>
        </div>