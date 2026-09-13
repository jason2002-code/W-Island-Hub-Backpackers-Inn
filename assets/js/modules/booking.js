/**
 * BOOKING & PAYMENT MODULE
 * Handles reservation initiation, occupied date modals, payment method toggling,
 * GCash clipboard utility, receipt upload preview/removal, and multi-tier booking submission.
 */

let pendingBooking = null;

// Safe date string helper with fallback
function safeFormatDate(d) {
    if (typeof window.getLocalDateString === 'function') {
        return window.getLocalDateString(d);
    }
    const date = d || new Date();
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

/**
 * Displays modal when a chosen room is occupied on the requested dates,
 * showing booked intervals and suggesting the next vacant date.
 * 
 * @param {Object} room - The room data object.
 * @param {Array<{ref: string, from: string, to: string, guest: string}>} ranges - Booked date intervals.
 * @param {string|null} nextVacant - Suggested next available check-in date.
 */
function showOccupiedModal(room, ranges, nextVacant) {
    const modal = document.createElement('div');
    modal.id = 'occupied-modal';
    modal.className = 'fixed inset-0 bg-black/70 flex items-center justify-center z-50 p-4';
    modal.innerHTML = `
        <div class="bg-white rounded-2xl p-6 max-w-lg w-full mx-4 shadow-2xl">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-2xl font-bold text-gray-800">${room.name} Status</h3>
                <button onclick="document.getElementById('occupied-modal').remove()" class="text-3xl font-bold text-gray-500 hover:text-gray-700">&times;</button>
            </div>
            <div class="mb-6">
                <h4 class="font-semibold text-lg mb-3 text-gray-700">Upcoming Bookings:</h4>
                ${ranges.map(r => `
                    <div class="bg-orange-50 border border-orange-200 rounded-lg p-3 mb-2">
                        <div class="flex justify-between">
                            <span class="font-medium text-gray-800">${r.ref}</span>
                            <span class="text-sm text-gray-600">${r.from} → ${r.to}</span>
                        </div>
                        <div class="text-xs text-gray-500">Guest: ${r.guest}</div>
                    </div>
                `).join('')}
            </div>
            ${nextVacant ? `
                <div class="bg-green-50 border-2 border-green-200 rounded-xl p-4 mb-6 text-center">
                    <h4 class="font-bold text-xl text-green-800 mb-1">Next Available</h4>
                    <div class="text-2xl font-bold text-green-700">${nextVacant}</div>
                </div>
            ` : ''}
            <button onclick="setSuggestedDates('${nextVacant}'); document.getElementById('occupied-modal').remove();" 
                    class="w-full bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white py-3 px-6 rounded-xl font-semibold text-lg shadow-lg transition-all">
                Use Suggested Dates
            </button>
        </div>
    `;
    document.body.appendChild(modal);
}

/**
 * Applies suggested available dates into the check-in and check-out pickers.
 * 
 * @param {string} nextVacant - Date in YYYY-MM-DD format.
 */
function setSuggestedDates(nextVacant) {
    if (nextVacant) {
        const checkInEl = document.getElementById('check-in-date');
        if (checkInEl) {
            checkInEl.value = nextVacant;
            const tomorrow = new Date(nextVacant);
            tomorrow.setDate(tomorrow.getDate() + 1);
            const checkOutEl = document.getElementById('check-out-date');
            if (checkOutEl) {
                checkOutEl.value = tomorrow.toISOString().split('T')[0];
            }
            checkInEl.dispatchEvent(new Event('change'));
        }
    }
}

/**
 * Initializes a new booking reservation for a specific room.
 * Enforces guest authentication, performs real-time availability check,
 * computes duration and charges, evaluates 24-hour rule for Pay at Hotel,
 * and redirects user to the booking summary view.
 * 
 * @param {number|string} roomId - The ID of the room to book.
 */
async function initiateBooking(roomId, options = {}) {
    const currentUser = window.appState ? window.appState.currentUser : null;
    if (currentUser && currentUser.role === 'admin') {
        window.showToast('Admins are not allowed to book rooms.', 'warning');
        return;
    }

    const room = (window.appState && window.appState.rooms)
        ? (window.appState.rooms.find(r => r.id == roomId) || { id: roomId, name: 'Room ' + roomId, price: 0, image: 'assets/image/background.jpg', type: '' })
        : { id: roomId, name: 'Room ' + roomId, price: 0, image: 'assets/image/background.jpg', type: '' };

    // Determine adults, children, and total guests
    const adults = options.adults !== undefined ? options.adults : (parseInt(document.getElementById('adults-count')?.value, 10) || parseInt(document.getElementById('home-adults-count')?.value, 10) || 1);
    const children = options.children !== undefined ? options.children : (parseInt(document.getElementById('children-count')?.value, 10) || parseInt(document.getElementById('home-children-count')?.value, 10) || 0);
    const totalGuests = options.guests !== undefined ? options.guests : (adults + children);
    const roomsCount = Math.max(1, parseInt(options.roomsCount, 10) || 1);

    const roomCap = parseInt(room.max_capacity || room.capacity, 10) || 2;

    // Check if room capacity is 2 and party has 4 or more guests -> suggest recommendations!
    if (!options.bypassRecommendation && roomCap <= 2 && totalGuests >= 4) {
        if (typeof window.showRoomRecommendationModal === 'function') {
            window.showRoomRecommendationModal(room, totalGuests, adults, children);
            return;
        }
    }

    try {
        const todayStr = safeFormatDate(new Date());
        let checkIn = document.getElementById('check-in-date') ? document.getElementById('check-in-date').value : todayStr;
        let checkOut = document.getElementById('check-out-date') ? document.getElementById('check-out-date').value : '';

        if (!checkIn || checkIn < todayStr) {
            window.showToast('Check-in date cannot be in the past or outdated.', 'warning');
            checkIn = todayStr;
            if (document.getElementById('check-in-date')) {
                document.getElementById('check-in-date').value = todayStr;
            }
        }

        const cinDate = new Date(checkIn + 'T00:00:00');
        const minCheckOutDate = new Date(cinDate);
        minCheckOutDate.setDate(minCheckOutDate.getDate() + 1);
        const minCheckOutStr = safeFormatDate(minCheckOutDate);

        if (!checkOut || checkOut <= checkIn) {
            window.showToast('Check-out date must be strictly after check-in date.', 'warning');
            checkOut = minCheckOutStr;
            if (document.getElementById('check-out-date')) {
                document.getElementById('check-out-date').value = minCheckOutStr;
            }
            return;
        }

        // Verify room availability with server
        const availRes = await fetch('actions/book_action.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                action: 'get_room_availability', 
                room_id: roomId,
                check_in: checkIn,
                check_out: checkOut
            })
        });
        const availData = await availRes.json();

        if (availData.room && availData.room.status === 'Maintenance') {
            window.showToast('This room is currently under maintenance.', 'error');
            return;
        }

        if (availData.room && availData.room.price) {
            room.name = availData.room.name;
            room.price = parseFloat(availData.room.price);
            room.type = availData.room.type || room.type;
            if (availData.room.image) room.image = availData.room.image;
        }

        if (!availData.is_currently_available) {
            showOccupiedModal(room, availData.occupied_ranges || [], availData.next_vacant);
            return;
        }

        // Construct pending booking context
        const nights = Math.ceil((new Date(checkOut) - new Date(checkIn)) / (24*60*60*1000));
        const total = (room.price * nights * roomsCount) + 15; // (Price * nights * rooms) + Tax & service fees

        pendingBooking = {
            roomId: parseInt(roomId),
            roomName: room.name,
            roomsCount,
            guests: totalGuests,
            adults,
            children,
            checkIn,
            checkOut,
            nights: Math.max(1, nights),
            total,
            pricePerNight: room.price
        };
        window.pendingBooking = pendingBooking;

        // Populate booking summary card
        const roomImgEl = document.getElementById('summary-room-img');
        const roomTypeBadge = document.getElementById('summary-room-type-badge');
        if (roomImgEl) roomImgEl.src = room.image;
        if (roomTypeBadge) roomTypeBadge.textContent = room.type || 'Room';
        
        const summaryName = document.getElementById('summary-room-name');
        const summaryDesc = document.getElementById('summary-room-desc');
        const summaryPrice = document.getElementById('summary-price');
        const summaryRoomsRow = document.getElementById('summary-rooms-count-row');
        const summaryRoomsCount = document.getElementById('summary-rooms-count');
        const summaryNights = document.getElementById('summary-nights');
        const summaryTotal = document.getElementById('summary-total');

        if (summaryName) summaryName.textContent = (roomsCount > 1 ? `${roomsCount}x ` : '') + room.name;
        if (summaryDesc) summaryDesc.textContent = room.desc || room.type;
        if (summaryPrice) summaryPrice.textContent = '₱' + room.price.toFixed(2) + (roomsCount > 1 ? ` (x${roomsCount} rooms)` : '');
        if (summaryRoomsRow && summaryRoomsCount) {
            if (roomsCount > 1) {
                summaryRoomsRow.classList.remove('hidden');
                summaryRoomsCount.textContent = `${roomsCount} Rooms`;
            } else {
                summaryRoomsRow.classList.add('hidden');
            }
        }
        if (summaryNights) summaryNights.textContent = pendingBooking.nights + (pendingBooking.nights > 1 ? ' Nights' : ' Night');
        if (summaryTotal) summaryTotal.textContent = '₱' + total.toFixed(2);

        const detailsContent = document.getElementById('booking-details-content');
        if (detailsContent) {
            detailsContent.innerHTML = `
                <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl space-y-2 text-xs">
                    <div class="flex justify-between"><span>Check-in Date:</span> <strong class="text-gray-800">${checkIn} (2:00 PM)</strong></div>
                    <div class="flex justify-between"><span>Check-out Date:</span> <strong class="text-gray-800">${checkOut} (12:00 PM)</strong></div>
                    <div class="flex justify-between"><span>Guests:</span> <strong>${totalGuests} ${totalGuests === 1 ? 'Guest' : 'Guests'} (${adults} Adult${adults > 1 ? 's' : ''}${children > 0 ? `, ${children} Child${children > 1 ? 'ren' : ''}` : ''})</strong></div>
                    <div class="flex justify-between items-center"><span>Accommodation:</span> <strong>${roomsCount > 1 ? `${roomsCount}x ` : ''}${room.name} (${room.type})</strong></div>
                    ${roomsCount > 1 ? `<div class="flex justify-between text-blue-700 font-semibold pt-1 border-t border-emerald-200/60"><span>Multi-Room Booking:</span> <span>${roomsCount} Rooms &times; ₱${room.price.toFixed(2)}/night</span></div>` : ''}
                    <div class="flex justify-between text-emerald-700 font-bold mt-2 pt-2 border-t border-emerald-200">
                        <span>Real-Time Availability</span><span class="flex items-center gap-1"><i class="fas fa-check-circle text-emerald-500"></i> Locked & Available</span>
                    </div>
                </div>
            `;
        }

        // Fill guest details from session if logged in; otherwise keep inputs clear for guest entry
        const nameInput = document.getElementById('book-name');
        const emailInput = document.getElementById('book-email');
        const phoneInput = document.getElementById('book-phone');
        const guestBookingNotice = document.getElementById('guest-booking-notice');

        if (currentUser) {
            if (nameInput) nameInput.value = currentUser.name || '';
            if (emailInput) emailInput.value = currentUser.email || '';
            if (phoneInput) phoneInput.value = currentUser.phone || '';
            if (guestBookingNotice) {
                guestBookingNotice.innerHTML = `<span class="inline-flex items-center gap-1 text-xs text-emerald-700 font-semibold bg-emerald-50 px-3 py-1 rounded-full border border-emerald-200"><i class="fas fa-user-check text-emerald-500"></i> Logged in as ${currentUser.name || 'User'}</span>`;
            }
        } else {
            if (nameInput && !nameInput.value) nameInput.value = '';
            if (emailInput && !emailInput.value) emailInput.value = '';
            if (phoneInput && !phoneInput.value) phoneInput.value = '';
            if (guestBookingNotice) {
                guestBookingNotice.innerHTML = `<span class="inline-flex items-center gap-1 text-xs text-brand-blue font-semibold bg-blue-50 px-3 py-1 rounded-full border border-blue-200"><i class="fas fa-bolt text-amber-500"></i> Guest Checkout (No account needed) &bull; <a href="javascript:void(0)" onclick="router('login')" class="underline hover:text-brand-dark font-bold ml-1">Log in</a></span>`;
            }
        }

        // Enforce 24-hour rule for Pay at Hotel
        const checkInDateObj = new Date(checkIn + 'T14:00:00');
        const now = new Date();
        const diffHours = (checkInDateObj.getTime() - now.getTime()) / (1000 * 60 * 60);
        const isWithin24Hours = (diffHours <= 30);

        const hotelCard = document.getElementById('label-payment-hotel');
        const lockedNotice = document.getElementById('pay-at-hotel-locked-notice');
        const hotelRadio = document.getElementById('input-payment-hotel');

        if (isWithin24Hours) {
            if (hotelCard) hotelCard.classList.remove('hidden');
            if (lockedNotice) lockedNotice.classList.add('hidden');
            if (hotelRadio) hotelRadio.disabled = false;
        } else {
            if (hotelCard) hotelCard.classList.add('hidden');
            if (lockedNotice) lockedNotice.classList.remove('hidden');
            if (hotelRadio) hotelRadio.disabled = true;
        }

        // Default selection to Option 1: PayMongo Online
        const paymongoRadio = document.querySelector('input[name="payment"][value="paymongo"]');
        if (paymongoRadio) {
            paymongoRadio.checked = true;
            handlePaymentMethodChange('paymongo');
        }

        window.router('booking-summary');
    } catch (error) {
        console.error('Booking init error:', error);
        window.showToast('Unable to verify availability. Please try again.', 'error');
    }
}

/**
 * Handles switching between active payment pathways (PayMongo Online vs Pay at Hotel).
 * Updates price breakdowns, form inputs, and required verification sections.
 * 
 * @param {'paymongo'|'pay_at_hotel'} method - Selected payment method identifier.
 */
function handlePaymentMethodChange(method) {
    const paymongoCard = document.getElementById('label-payment-paymongo');
    const hotelCard = document.getElementById('label-payment-hotel');
    const paymongoSection = document.getElementById('paymongo-payment-section');
    const arrivalSection = document.getElementById('arrival-time-section');
    const upfrontEl = document.getElementById('summary-upfront-amount');
    const balanceEl = document.getElementById('summary-balance-amount');
    const paymongoAmountEl = document.getElementById('paymongo-amount-display');
    const timerHintEl = document.getElementById('summary-timer-hint');
    const btnText = document.getElementById('btn-confirm-text');

    const activeBooking = window.pendingBooking || pendingBooking;
    if (!activeBooking) return;

    const total = activeBooking.total;

    // Reset card highlight styles
    [paymongoCard, hotelCard].forEach(card => {
        if (card) {
            card.classList.remove('border-brand-blue', 'bg-blue-50/40', 'shadow-sm');
            card.classList.add('border-gray-200', 'bg-white');
        }
    });

    if (method === 'pay_at_hotel') {
        if (hotelCard) {
            hotelCard.classList.add('border-brand-blue', 'bg-blue-50/40', 'shadow-sm');
            hotelCard.classList.remove('border-gray-200', 'bg-white');
        }
        if (paymongoSection) paymongoSection.classList.add('hidden');
        if (arrivalSection) arrivalSection.classList.remove('hidden');

        const upfront = 0.00;
        const balance = total;
        if (upfrontEl) upfrontEl.textContent = '₱0.00';
        if (balanceEl) balanceEl.textContent = '₱' + balance.toFixed(2);
        if (timerHintEl) timerHintEl.innerHTML = '<i class="fas fa-clock text-purple-600"></i> <span>Held strictly until expected arrival time + 1 hour.</span>';
        if (btnText) btnText.innerHTML = '<i class="fas fa-store mr-1"></i> Lock Room Hold (Pay at Hotel)';
    } else {
        // Default to PayMongo instant checkout
        if (paymongoCard) {
            paymongoCard.classList.add('border-brand-blue', 'bg-blue-50/40', 'shadow-sm');
            paymongoCard.classList.remove('border-gray-200', 'bg-white');
        }
        if (paymongoSection) paymongoSection.classList.remove('hidden');
        if (arrivalSection) arrivalSection.classList.add('hidden');

        const upfront = total;
        const balance = 0.00;
        if (upfrontEl) upfrontEl.textContent = '₱' + upfront.toFixed(2);
        if (balanceEl) balanceEl.textContent = '₱' + balance.toFixed(2);
        if (paymongoAmountEl) paymongoAmountEl.textContent = '₱' + upfront.toFixed(2);
        if (timerHintEl) timerHintEl.innerHTML = '<i class="fas fa-bolt text-emerald-500"></i> <span>Automated online payment & instant confirmation.</span>';
        if (btnText) btnText.innerHTML = '<i class="fas fa-bolt mr-1"></i> Proceed to PayMongo Checkout &rarr;';
    }
}

/**
 * Copies the official inn GCash mobile number to the system clipboard.
 */
function copyGcashNumber() {
    navigator.clipboard.writeText('09753614030').then(() => {
        window.showToast('GCash number copied to clipboard: 0975-361-4030', 'success');
    }).catch(() => {
        window.showToast('GCash Number: 0975-361-4030', 'info');
    });
}

/**
 * Opens the high-resolution official GCash QR Code preview modal.
 */
function openGcashQrModal() {
    const modal = document.getElementById('gcash-qr-modal');
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
}

/**
 * Closes the official GCash QR Code preview modal.
 */
function closeGcashQrModal(event) {
    if (event && event.target && event.target.id !== 'gcash-qr-modal') {
        // Only backdrop clicks close when passed via event
        return;
    }
    const modal = document.getElementById('gcash-qr-modal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
}

/**
 * Renders a live preview thumbnail of an uploaded receipt screenshot proof.
 * 
 * @param {Event} event - File change input event.
 */
function handleReceiptPreview(event) {
    const input = event.target;
    const previewContainer = document.getElementById('receipt-preview-container');
    const previewImg = document.getElementById('receipt-preview-img');
    const placeholder = document.getElementById('receipt-upload-placeholder');

    if (input.files && input.files[0]) {
        const file = input.files[0];
        if (file.size > 5 * 1024 * 1024) {
            window.showToast('Receipt file size must be less than 5MB.', 'error');
            input.value = '';
            return;
        }
        const reader = new FileReader();
        reader.onload = function(e) {
            if (previewImg) previewImg.src = e.target.result;
            if (previewContainer) previewContainer.classList.remove('hidden');
            if (placeholder) placeholder.classList.add('hidden');
        };
        reader.readAsDataURL(file);
    }
}

/**
 * Clears the selected receipt image file from the input and hides the preview.
 * 
 * @param {Event} event - Button click event.
 */
function removeReceiptFile(event) {
    if (event && event.stopPropagation) event.stopPropagation();
    const input = document.getElementById('book-receipt-file');
    const previewContainer = document.getElementById('receipt-preview-container');
    const previewImg = document.getElementById('receipt-preview-img');
    const placeholder = document.getElementById('receipt-upload-placeholder');

    if (input) input.value = '';
    if (previewImg) previewImg.src = '';
    if (previewContainer) previewContainer.classList.add('hidden');
    if (placeholder) placeholder.classList.remove('hidden');
}

/**
 * Binds the main booking submission form handler.
 */
function initBookingForm() {
    const bookingForm = document.getElementById('booking-form');
    if (!bookingForm || bookingForm.dataset.initialized) return;
    bookingForm.dataset.initialized = 'true';

    bookingForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const currentUser = window.appState ? window.appState.currentUser : null;
        if (currentUser && currentUser.role === 'admin') {
            window.showToast("Admins are not allowed to book rooms.", 'error');
            return;
        }

        const activeBooking = window.pendingBooking || pendingBooking;
        if (!activeBooking) {
            window.showToast("Please select a room and dates first.", 'warning');
            window.router('rooms');
            return;
        }

        const todayStr = safeFormatDate(new Date());
        if (!activeBooking.checkIn || activeBooking.checkIn < todayStr) {
            window.showToast("Check-in date cannot be in the past or outdated. Please select new dates.", 'error');
            window.router('rooms');
            return;
        }
        if (!activeBooking.checkOut || activeBooking.checkOut <= activeBooking.checkIn) {
            window.showToast("Check-out date must be strictly after check-in date.", 'error');
            window.router('rooms');
            return;
        }

        const paymentMethodEl = document.querySelector('input[name="payment"]:checked');
        const paymentMethod = paymentMethodEl ? paymentMethodEl.value : 'paymongo';
        const guestName = document.getElementById('book-name') ? document.getElementById('book-name').value.trim() : '';
        const guestEmail = document.getElementById('book-email') ? document.getElementById('book-email').value.trim() : '';
        const guestPhone = document.getElementById('book-phone') ? document.getElementById('book-phone').value.trim() : '';
        const requests = document.getElementById('book-requests') ? document.getElementById('book-requests').value.trim() : '';

        // Validation for Guest Information (allows non-logged in customers)
        if (!guestName) {
            window.showToast("Please enter your full name.", 'warning');
            document.getElementById('book-name')?.focus();
            return;
        }
        if (!guestEmail) {
            window.showToast("Please enter your email address for your reservation confirmation.", 'warning');
            document.getElementById('book-email')?.focus();
            return;
        }
        if (!guestPhone) {
            window.showToast("Please enter your mobile/phone number.", 'warning');
            document.getElementById('book-phone')?.focus();
            return;
        }

        // Store guest details on pendingBooking for instant invoice rendering
        activeBooking.guestName = guestName;
        activeBooking.guestEmail = guestEmail;
        activeBooking.guestPhone = guestPhone;

        const formData = new FormData();
        formData.append('action', 'create');
        if (currentUser && currentUser.id) {
            formData.append('user_id', currentUser.id);
        }
        formData.append('room_id', activeBooking.roomId);
        formData.append('rooms_count', activeBooking.roomsCount || 1);
        formData.append('guests', activeBooking.guests || 1);
        formData.append('adults', activeBooking.adults || 1);
        formData.append('children', activeBooking.children || 0);
        formData.append('check_in', activeBooking.checkIn);
        formData.append('check_out', activeBooking.checkOut);
        formData.append('nights', activeBooking.nights);
        formData.append('total', activeBooking.total);
        formData.append('guest_name', guestName);
        formData.append('guest_email', guestEmail);
        formData.append('guest_phone', guestPhone);
        formData.append('special_requests', requests);
        formData.append('payment_method', paymentMethod);

        // Pathway-specific validations
        if (paymentMethod === 'pay_at_hotel') {
            const arrivalTime = document.getElementById('book-arrival-time') ? document.getElementById('book-arrival-time').value : '';
            if (!arrivalTime) {
                window.showToast("Please select your expected arrival time.", 'error');
                document.getElementById('book-arrival-time').focus();
                return;
            }
            formData.append('expected_arrival_time', arrivalTime);
        }

        const confirmBtn = document.getElementById('btn-confirm-booking');
        if (confirmBtn) {
            confirmBtn.disabled = true;
            confirmBtn.innerHTML = paymentMethod === 'paymongo' 
                ? '<i class="fas fa-spinner fa-spin mr-2"></i> Initializing PayMongo Checkout...' 
                : '<i class="fas fa-spinner fa-spin mr-2"></i> Processing Reservation...';
        }

        try {
            const response = await fetch('actions/bookings_action.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();

            if (data.success) {
                if (data.checkout_url) {
                    window.showToast(`Reservation Created! Ref: ${data.ref_id}. Redirecting to PayMongo Checkout...`, 'success');
                    setTimeout(() => {
                        window.location.href = data.checkout_url;
                    }, 700);
                    return;
                }

                window.showToast(`Reservation Created! Ref: ${data.ref_id}`, 'success');
                if (typeof window.showInvoiceModal === 'function') {
                    window.showInvoiceModal(data.ref_id, activeBooking, data);
                }
            } else {
                window.showToast("Booking Failed: " + (data.message || 'Unknown error'), 'error');
            }
        } catch (err) {
            console.error('Booking Error:', err);
            window.showToast("Network error occurred during booking. Please try again.", 'error');
        } finally {
            if (confirmBtn) {
                confirmBtn.disabled = false;
                confirmBtn.innerHTML = paymentMethod === 'paymongo'
                    ? '<i class="fas fa-bolt mr-2"></i> <span id="btn-confirm-text">Proceed to PayMongo Checkout &rarr;</span>'
                    : '<i class="fas fa-lock mr-2"></i> <span id="btn-confirm-text">Confirm Reservation</span>';
            }
        }
    });
}

// Auto-bind booking form on DOM ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initBookingForm);
} else {
    initBookingForm();
}

// Global window bindings for inline HTML and cross-module access
window.pendingBooking = pendingBooking;
window.showOccupiedModal = showOccupiedModal;
window.setSuggestedDates = setSuggestedDates;
window.initiateBooking = initiateBooking;
window.handlePaymentMethodChange = handlePaymentMethodChange;
window.copyGcashNumber = copyGcashNumber;
window.openGcashQrModal = openGcashQrModal;
window.closeGcashQrModal = closeGcashQrModal;
window.handleReceiptPreview = handleReceiptPreview;
window.removeReceiptFile = removeReceiptFile;
window.initBookingForm = initBookingForm;

