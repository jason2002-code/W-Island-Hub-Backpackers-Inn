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
                        <div>
                            <label for="guest-count" class="block text-sm font-bold text-gray-700 mb-1">Guests (Max 5)</label>
                            <input type="number" id="guest-count" name="guest_count" min="1" max="5" value="1" placeholder="1-5" 
                                   class="w-full border rounded-lg p-3 focus:ring-2 focus:ring-brand-blue outline-none text-gray-700">
                        </div>
                        <button type="submit" class="bg-brand-blue hover:bg-sky-600 text-white font-bold py-3 rounded-lg transition shadow-md">
                            Check Availability
                        </button>
                    </form>
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
                            <!-- Stay Details Summary -->
                            <div>
                                <h3 class="text-base font-bold text-gray-800 mb-3 flex items-center gap-2">
                                    <i class="fas fa-calendar-alt text-brand-blue"></i> Stay Summary
                                </h3>
                                <div id="booking-details-content" class="space-y-3 text-gray-600">
                                    <!-- Filled by JS -->
                                </div>
                            </div>
                            
                            <!-- Guest Information Form -->
                            <form id="booking-form" class="space-y-6">
                                <div>
                                    <h3 class="text-base font-bold text-gray-800 mb-3 flex items-center gap-2">
                                        <i class="fas fa-user-circle text-brand-blue"></i> Guest Information
                                    </h3>
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
                                            <input type="tel" id="book-phone" required class="w-full border border-gray-200 bg-slate-50 focus:bg-white rounded-xl p-3 text-sm focus:ring-2 focus:ring-brand-blue outline-none transition" placeholder="+63 912 345 6789">
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
                                        <!-- Option A: Full Payment (GCash) -->
                                        <label class="payment-option-card relative flex items-start gap-3 p-4 rounded-2xl border-2 border-brand-blue bg-blue-50/40 cursor-pointer hover:border-brand-blue transition shadow-sm" id="label-payment-full">
                                            <input type="radio" name="payment" value="full_gcash" checked onchange="handlePaymentMethodChange(this.value)" class="mt-1 text-brand-blue focus:ring-brand-blue">
                                            <div class="flex-1">
                                                <div class="flex items-center justify-between">
                                                    <span class="font-bold text-gray-800 text-sm flex items-center gap-2">
                                                        <i class="fas fa-qrcode text-blue-600 text-base"></i> Option A: Full Payment (GCash)
                                                    </span>
                                                    <span class="text-xs font-bold bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded">100% Upfront</span>
                                                </div>
                                                <p class="text-xs text-gray-500 mt-1">Pay full amount via GCash for instant booking priority. Room is verified and confirmed with zero front desk balance.</p>
                                            </div>
                                        </label>

                                        <!-- Option B: Strict "Pay at Hotel" (Conditional) -->
                                        <div id="option-c-wrapper">
                                            <label class="payment-option-card relative flex items-start gap-3 p-4 rounded-2xl border-2 border-gray-200 bg-white cursor-pointer hover:border-brand-blue transition" id="label-payment-hotel">
                                                <input type="radio" name="payment" value="pay_at_hotel" id="input-payment-hotel" onchange="handlePaymentMethodChange(this.value)" class="mt-1 text-brand-blue focus:ring-brand-blue">
                                                <div class="flex-1">
                                                    <div class="flex items-center justify-between">
                                                        <span class="font-bold text-gray-800 text-sm flex items-center gap-2">
                                                            <i class="fas fa-store text-purple-600 text-base"></i> Option B: Strict "Pay at Hotel"
                                                        </span>
                                                        <span class="text-xs font-bold bg-purple-100 text-purple-800 px-2 py-0.5 rounded">Same-Day / &le;24h Only</span>
                                                    </div>
                                                    <p class="text-xs text-gray-500 mt-1">Available strictly for bookings with check-in within 24 hours. Room is held strictly until your arrival time + 1 hour.</p>
                                                </div>
                                            </label>

                                            <!-- Notice shown when Pay at Hotel is unavailable due to >24hr rule -->
                                            <div id="pay-at-hotel-locked-notice" class="hidden p-3.5 bg-gray-50 rounded-2xl border border-gray-200 text-xs text-gray-500 flex items-center gap-2 mt-1">
                                                <i class="fas fa-lock text-gray-400"></i>
                                                <span><strong>Pay at Hotel</strong> is locked because your check-in date is more than 24 hours away. Please select Option A (Full Payment via GCash).</span>
                                            </div>
                                        </div>
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

                                <!-- GCash Proof of Payment Section (Required for Option A: Full Payment) -->
                                <div id="gcash-payment-section" class="bg-slate-50 p-6 rounded-2xl border border-blue-100 space-y-5">
                                    <div class="flex items-center justify-between border-b border-gray-200 pb-3">
                                        <div class="flex items-center gap-2">
                                            <div class="w-8 h-8 rounded-lg bg-blue-600 text-white flex items-center justify-center font-bold text-xs">G</div>
                                            <h4 class="font-bold text-gray-800 text-sm">GCash Payment & Proof Submission</h4>
                                        </div>
                                        <span class="text-xs font-bold bg-amber-100 text-amber-800 px-2.5 py-1 rounded-full flex items-center gap-1">
                                            <i class="fas fa-stopwatch"></i> 2-Hour Temporary Hold
                                        </span>
                                    </div>

                                    <!-- QR & Details Grid -->
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-center bg-white p-4 rounded-xl border border-gray-100 shadow-sm">
                                        <div class="text-center sm:text-left">
                                            <span class="text-[10px] uppercase font-bold text-gray-400 block">GCash Account Name</span>
                                            <strong class="text-gray-800 text-sm block mb-2">MA*K JA**N C. <span class="text-xs font-normal text-gray-500">(W Island Hub)</span></strong>
                                            
                                            <span class="text-[10px] uppercase font-bold text-gray-400 block">GCash Mobile Number</span>
                                            <div class="flex items-center justify-center sm:justify-start gap-2 mb-2">
                                                <strong class="font-mono text-base text-blue-600 font-bold" id="gcash-number-display">0975-361-4030</strong>
                                                <button type="button" onclick="copyGcashNumber()" class="text-xs text-gray-500 hover:text-blue-600 px-2 py-0.5 bg-gray-100 hover:bg-gray-200 rounded transition flex items-center gap-1" title="Copy Number">
                                                    <i class="far fa-copy"></i>
                                                    <span>Copy</span>
                                                </button>
                                            </div>

                                            <span class="text-[10px] uppercase font-bold text-gray-400 block">Amount to Send Now</span>
                                            <strong class="text-xl font-bold text-brand-blue" id="gcash-amount-to-send">₱0.00</strong>
                                        </div>

                                        <div class="flex flex-col items-center justify-center border-t sm:border-t-0 sm:border-l border-gray-100 pt-3 sm:pt-0 sm:pl-4">
                                            <div class="p-1.5 bg-white rounded-xl border border-gray-200 shadow-inner group cursor-pointer hover:border-blue-400 hover:shadow-md transition" onclick="openGcashQrModal()" title="Click to enlarge official GCash QR Code">
                                                <img id="gcash-qr-image" src="assets/image/gcash_qr_card.jpg" onerror="this.src='../assets/image/gcash_qr_card.jpg'" alt="Official GCash QR Code" class="w-36 h-auto max-h-48 object-contain rounded-lg group-hover:scale-105 transition-transform duration-200">
                                            </div>
                                            <button type="button" onclick="openGcashQrModal()" class="text-[11px] text-brand-blue font-semibold mt-1.5 flex items-center gap-1 hover:underline">
                                                <i class="fas fa-search-plus"></i> View Full GCash QR
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Inputs: Reference Number & Receipt Upload -->
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">
                                                GCash Reference Number <span class="text-rose-500">*</span>
                                            </label>
                                            <input type="text" id="book-gcash-ref" maxlength="40" placeholder="e.g., 901234567890" class="w-full border border-gray-300 bg-white rounded-xl p-3 text-sm font-mono uppercase focus:ring-2 focus:ring-brand-blue outline-none transition">
                                            <p class="text-[11px] text-gray-400 mt-1">Enter the exact 10-15 character reference number from your GCash SMS/receipt.</p>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">
                                                Upload Receipt Screenshot / Proof <span class="text-rose-500">*</span>
                                            </label>
                                            <div class="border-2 border-dashed border-gray-300 hover:border-brand-blue bg-white p-4 rounded-xl text-center cursor-pointer transition relative" id="receipt-dropzone" onclick="document.getElementById('book-receipt-file').click()">
                                                <input type="file" id="book-receipt-file" accept="image/jpeg,image/png,image/webp" class="hidden" onchange="handleReceiptPreview(event)">
                                                <div id="receipt-preview-container" class="hidden mb-2">
                                                    <img id="receipt-preview-img" src="" alt="Receipt Preview" class="max-h-40 mx-auto rounded-lg shadow-md object-contain">
                                                    <button type="button" onclick="removeReceiptFile(event)" class="mt-2 text-xs text-rose-600 hover:underline font-bold">
                                                        <i class="fas fa-trash-alt mr-1"></i> Remove / Choose another
                                                    </button>
                                                </div>
                                                <div id="receipt-upload-placeholder">
                                                    <i class="fas fa-cloud-upload-alt text-2xl text-brand-blue mb-1"></i>
                                                    <p class="text-xs font-bold text-gray-700">Click or drag receipt image here</p>
                                                    <p class="text-[10px] text-gray-400 mt-0.5">Supports JPG, PNG, WEBP (Max 5MB)</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Hold Expiry Notice -->
                                    <div class="p-3 bg-amber-50 rounded-xl text-xs text-amber-800 border border-amber-200 flex items-start gap-2">
                                        <i class="fas fa-hourglass-half text-amber-600 mt-0.5"></i>
                                        <span><strong>Inventory Hold Policy:</strong> Upon submission, your room is temporarily locked with a <strong>2-hour countdown timer</strong> while our front desk verifies your payment proof.</span>
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

        <!-- Official GCash QR Code Modal (Enlarged High-Res Preview) -->
        <div id="gcash-qr-modal" class="fixed inset-0 bg-black/75 backdrop-blur-xs z-[200] hidden items-center justify-center p-4" onclick="closeGcashQrModal(event)">
            <div class="bg-white rounded-3xl shadow-2xl max-w-sm w-full p-6 relative overflow-hidden text-center" onclick="event.stopPropagation()">
                <button type="button" onclick="closeGcashQrModal()" class="absolute top-4 right-4 w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-600 flex items-center justify-center transition" title="Close">
                    <i class="fas fa-times"></i>
                </button>
                <div class="mb-3">
                    <div class="inline-flex items-center gap-2 mb-1">
                        <span class="w-6 h-6 rounded-full bg-blue-600 text-white font-bold text-xs flex items-center justify-center">G</span>
                        <h4 class="font-bold text-gray-800 text-base">Official GCash QR Code</h4>
                    </div>
                    <p class="text-xs text-gray-500">Scan using GCash, Maya, or any InstaPay app</p>
                </div>
                
                <div class="p-2 bg-blue-50/50 rounded-2xl border border-blue-100 mb-3 inline-block">
                    <img src="assets/image/gcash_qr.jpg" onerror="this.src='../assets/image/gcash_qr.jpg'" alt="Official GCash QR Code" class="max-h-[58vh] w-auto mx-auto rounded-xl shadow-md object-contain">
                </div>
                
                <div class="bg-slate-50 rounded-2xl p-3 border border-gray-100 text-xs text-left space-y-1.5">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500">Account Name:</span>
                        <strong class="text-gray-800">MA*K JA**N C.</strong>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500">Mobile Number:</span>
                        <span class="flex items-center gap-1.5 font-mono font-bold text-blue-600">
                            0975-361-4030
                            <button type="button" onclick="copyGcashNumber()" class="text-gray-500 hover:text-blue-600 text-xs px-1.5 py-0.5 bg-white border border-gray-200 rounded transition" title="Copy Number">
                                <i class="far fa-copy"></i>
                            </button>
                        </span>
                    </div>
                </div>

                <button type="button" onclick="closeGcashQrModal()" class="w-full mt-4 bg-brand-blue hover:bg-sky-600 text-white font-bold py-2.5 rounded-xl transition text-sm shadow">
                    Done
                </button>
            </div>
        </div>