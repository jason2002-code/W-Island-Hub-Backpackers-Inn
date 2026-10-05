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

    // Retrieve and validate child ages
    let childrenAges = [];
    if (options.childrenAges && Array.isArray(options.childrenAges)) {
        childrenAges = options.childrenAges;
    } else if (typeof window.getChildAges === 'function') {
        childrenAges = window.getChildAges().slice(0, children);
    } else {
        try {
            const rawAges = document.getElementById('children-ages')?.value || document.getElementById('home-children-ages')?.value || '[]';
            childrenAges = JSON.parse(rawAges).slice(0, children);
        } catch(e) {
            childrenAges = [];
        }
    }

    if (children > 0 && !options.bypassRecommendation) {
        const unselectedIdx = childrenAges.findIndex(a => a === '' || a === undefined || a === null);
        if (unselectedIdx !== -1) {
            window.showToast(`Please select the age for Child ${unselectedIdx + 1} at check-in.`, 'warning');
            if (typeof window.toggleGuestDropdown === 'function') {
                const dropdown = document.getElementById('rooms-guest-dropdown');
                if (dropdown && dropdown.classList.contains('hidden')) {
                    window.toggleGuestDropdown('rooms');
                }
                const sel = document.getElementById(`rooms-child-age-${unselectedIdx}`);
                if (sel) sel.focus();
            }
            return;
        }
    }

    const roomCap = parseInt(room.max_capacity || room.capacity, 10) || 2;
    const totalAllowedCap = roomCap * roomsCount;

    // Check if group size exceeds accommodated capacity for this reservation
    if (totalGuests > totalAllowedCap) {
        if (!options.bypassRecommendation && typeof window.showRoomRecommendationModal === 'function') {
            window.showRoomRecommendationModal(room, totalGuests, adults, children);
            return;
        } else {
            window.showToast(`The hotel does not accommodate ${totalGuests} guests for this room. The room capacity is limited to ${totalAllowedCap} guests.`, 'warning');
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

        const isDorm = (parseInt(roomId) === 5 || (room.type && room.type.toLowerCase().includes('dorm')));
        const dormRemainingBeds = isDorm ? (availData.remaining_beds !== undefined ? parseInt(availData.remaining_beds, 10) : 8) : 0;

        if (isDorm && dormRemainingBeds <= 0) {
            showOccupiedModal(room, availData.occupied_ranges || [], availData.next_vacant);
            return;
        }

        if (!isDorm && !availData.is_currently_available) {
            showOccupiedModal(room, availData.occupied_ranges || [], availData.next_vacant);
            return;
        }

        // Construct pending booking context
        const nights = Math.max(1, Math.ceil((new Date(checkOut) - new Date(checkIn)) / (24*60*60*1000)));
        const bedPrice = 460.00;
        let initialBeds = isDorm ? Math.min(dormRemainingBeds, Math.max(1, totalGuests || 1)) : 1;
        const pricePerNight = isDorm ? (initialBeds * bedPrice) : room.price;
        const total = isDorm ? ((pricePerNight * nights) + 15) : ((room.price * nights * roomsCount) + 15);

        pendingBooking = {
            roomId: parseInt(roomId),
            roomName: room.name,
            roomsCount: isDorm ? 1 : roomsCount,
            isDorm: isDorm,
            bedsCount: initialBeds,
            remainingBeds: dormRemainingBeds,
            totalBeds: 8,
            bedPrice: bedPrice,
            guests: isDorm ? initialBeds : totalGuests,
            adults: isDorm ? initialBeds : adults,
            children: isDorm ? 0 : children,
            childrenAges: isDorm ? [] : childrenAges,
            checkIn,
            checkOut,
            nights: nights,
            total,
            pricePerNight
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
        const summaryDormRow = document.getElementById('summary-dorm-beds-row');
        const summaryDormBeds = document.getElementById('summary-dorm-beds');
        const summaryNights = document.getElementById('summary-nights');
        const summaryTotal = document.getElementById('summary-total');

        if (summaryName) summaryName.textContent = (!isDorm && roomsCount > 1 ? `${roomsCount}x ` : '') + room.name;
        if (summaryDesc) summaryDesc.textContent = room.desc || room.type;

        // Dorm Bed Controls in Form
        const dormSelectorContainer = document.getElementById('dorm-bed-selector-container');
        const dormRemainingBadge = document.getElementById('dorm-remaining-beds-badge');
        const dormBedCountDisplay = document.getElementById('dorm-bed-count-display');
        const dormBedsInput = document.getElementById('dorm-beds-count-input');
        const dormBedSubtotalHint = document.getElementById('dorm-bed-subtotal-hint');
        const btnDormMinus = document.getElementById('btn-dorm-bed-minus');
        const btnDormPlus = document.getElementById('btn-dorm-bed-plus');

        if (isDorm) {
            if (dormSelectorContainer) dormSelectorContainer.classList.remove('hidden');
            if (dormRemainingBadge) {
                dormRemainingBadge.textContent = `${dormRemainingBeds} Bed${dormRemainingBeds > 1 ? 's' : ''} Available (${availData.occupied_beds || (8 - dormRemainingBeds)} Occupied / 8 Total)`;
            }
            if (dormBedCountDisplay) dormBedCountDisplay.textContent = `${initialBeds} Bed${initialBeds > 1 ? 's' : ''}`;
            if (dormBedsInput) dormBedsInput.value = initialBeds;
            if (dormBedSubtotalHint) dormBedSubtotalHint.textContent = `${initialBeds} bed${initialBeds > 1 ? 's' : ''} × ₱460.00 = ₱${(initialBeds * bedPrice).toFixed(2)} / night`;
            if (btnDormMinus) btnDormMinus.disabled = (initialBeds <= 1);
            if (btnDormPlus) btnDormPlus.disabled = (initialBeds >= dormRemainingBeds);

            if (summaryRoomsRow) summaryRoomsRow.classList.add('hidden');
            if (summaryDormRow) summaryDormRow.classList.remove('hidden');
            if (summaryDormBeds) summaryDormBeds.textContent = `${initialBeds} Bed${initialBeds > 1 ? 's' : ''} (₱460.00/bed)`;
            if (summaryPrice) summaryPrice.textContent = `₱${(initialBeds * bedPrice).toFixed(2)} (${initialBeds} Bed${initialBeds > 1 ? 's' : ''} @ ₱460.00)`;
        } else {
            if (dormSelectorContainer) dormSelectorContainer.classList.add('hidden');
            if (summaryDormRow) summaryDormRow.classList.add('hidden');
            if (summaryPrice) summaryPrice.textContent = '₱' + room.price.toFixed(2) + (roomsCount > 1 ? ` (x${roomsCount} rooms)` : '');
            if (summaryRoomsRow && summaryRoomsCount) {
                if (roomsCount > 1) {
                    summaryRoomsRow.classList.remove('hidden');
                    summaryRoomsCount.textContent = `${roomsCount} Rooms`;
                } else {
                    summaryRoomsRow.classList.add('hidden');
                }
            }
        }

        if (summaryNights) summaryNights.textContent = pendingBooking.nights + (pendingBooking.nights > 1 ? ' Nights' : ' Night');
        if (summaryTotal) summaryTotal.textContent = '₱' + total.toFixed(2);

        // Populate stay date inputs in Review & Confirm section
        const summaryCheckIn = document.getElementById('summary-check-in-date');
        const summaryCheckOut = document.getElementById('summary-check-out-date');
        const summaryStayNightsText = document.getElementById('summary-stay-nights-text');
        const summaryDatesBadge = document.getElementById('summary-dates-badge');
        const summaryDatesBadgeText = document.getElementById('summary-dates-badge-text');
        const summaryDateStatusMsg = document.getElementById('summary-date-status-msg');
        const confirmBtn = document.getElementById('btn-confirm-booking');

        if (summaryCheckIn) {
            summaryCheckIn.min = todayStr;
            summaryCheckIn.value = checkIn;
        }
        if (summaryCheckOut) {
            summaryCheckOut.min = minCheckOutStr;
            summaryCheckOut.value = checkOut;
        }
        if (summaryStayNightsText) {
            summaryStayNightsText.textContent = pendingBooking.nights + (pendingBooking.nights > 1 ? ' Nights' : ' Night');
        }
        if (summaryDatesBadge) {
            summaryDatesBadge.className = 'text-xs font-semibold px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 flex items-center gap-1';
        }
        if (summaryDatesBadgeText) {
            summaryDatesBadgeText.textContent = 'Locked & Available';
        }
        if (summaryDateStatusMsg) {
            summaryDateStatusMsg.innerHTML = '<i class="fas fa-check text-emerald-500"></i> Pricing and availability auto-update';
            summaryDateStatusMsg.className = 'text-[11px] text-emerald-600 font-medium flex items-center gap-1';
        }
        if (confirmBtn) {
            confirmBtn.disabled = false;
            confirmBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        }

        const detailsContent = document.getElementById('booking-details-content');
        if (detailsContent) {
            let childAgesLine = '';
            if (children > 0 && pendingBooking && pendingBooking.childrenAges && pendingBooking.childrenAges.length > 0) {
                const ageTags = pendingBooking.childrenAges.map((a, idx) => {
                    const ageNum = parseInt(a, 10);
                    return `Child ${idx + 1}: ${ageNum === 0 ? 'Infant (under 1)' : `${ageNum} yr${ageNum > 1 ? 's' : ''} old`}`;
                }).join(', ');
                childAgesLine = `
                    <div class="flex justify-between items-center text-[11px] pt-1.5 border-t border-gray-100 text-slate-600">
                        <span class="text-gray-500 font-medium"><i class="fas fa-child text-brand-blue mr-1"></i>Child Ages:</span>
                        <strong class="text-gray-800 font-semibold">${ageTags}</strong>
                    </div>
                `;
            }

            detailsContent.innerHTML = `
                <div class="p-4 bg-white border border-gray-200 rounded-2xl space-y-2.5 text-xs shadow-xs">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500">Accommodation:</span>
                        <strong class="text-gray-900 font-semibold">${roomsCount > 1 ? `${roomsCount}x ` : ''}${room.name} (${room.type})</strong>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500">Guests:</span>
                        <strong class="text-gray-900 font-semibold">${totalGuests} ${totalGuests === 1 ? 'Guest' : 'Guests'} (${adults} Adult${adults > 1 ? 's' : ''}${children > 0 ? `, ${children} Child${children > 1 ? 'ren' : ''}` : ''})</strong>
                    </div>
                    ${childAgesLine}
                    ${roomsCount > 1 ? `<div class="flex justify-between text-blue-700 font-semibold pt-1 border-t border-gray-100"><span>Multi-Room Booking:</span> <span>${roomsCount} Rooms &times; ₱${room.price.toFixed(2)}/night</span></div>` : ''}
                    <div class="flex justify-between text-emerald-700 font-bold mt-2 pt-2 border-t border-gray-100">
                        <span>Inventory Status:</span>
                        <span class="flex items-center gap-1"><i class="fas fa-shield-alt text-emerald-500"></i> Real-Time Available</span>
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

        // Pay at Hotel with 20% online security deposit is available for all dates
        const hotelCard = document.getElementById('label-payment-hotel');
        const lockedNotice = document.getElementById('pay-at-hotel-locked-notice');
        const hotelRadio = document.getElementById('input-payment-hotel');

        if (hotelCard) hotelCard.classList.remove('hidden');
        if (lockedNotice) lockedNotice.classList.add('hidden');
        if (hotelRadio) hotelRadio.disabled = false;

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
 * Adjusts the number of reserved dormitory beds for Room 5 (Shared Dormitory).
 * Clamps between 1 and the remaining beds available on the selected dates.
 * Recalculates nightly bed subtotal, total bill with taxes, and payment amounts.
 * 
 * @param {number} delta - (+1 or -1)
 */
function updateDormBedCount(delta) {
    const activeBooking = window.pendingBooking || pendingBooking;
    if (!activeBooking || !activeBooking.isDorm) return;

    const remaining = (activeBooking.remainingBeds !== undefined) ? parseInt(activeBooking.remainingBeds, 10) : 8;
    let currentBeds = activeBooking.bedsCount || 1;
    let newBeds = currentBeds + delta;

    if (newBeds < 1) newBeds = 1;
    if (newBeds > remaining) {
        newBeds = remaining;
        if (typeof window.showToast === 'function') {
            window.showToast(`Only ${remaining} bed(s) available in Room 5 for your chosen dates.`, 'warning');
        }
    }

    activeBooking.bedsCount = newBeds;
    activeBooking.guests = newBeds;
    activeBooking.adults = newBeds;
    activeBooking.children = 0;

    const bedPrice = activeBooking.bedPrice || 460.00;
    activeBooking.pricePerNight = newBeds * bedPrice;
    activeBooking.total = (activeBooking.pricePerNight * activeBooking.nights) + 15.00;
    pendingBooking = activeBooking;
    window.pendingBooking = activeBooking;

    // Update Form Inputs and Displays
    const countDisplay = document.getElementById('dorm-bed-count-display');
    const countInput = document.getElementById('dorm-beds-count-input');
    const subtotalHint = document.getElementById('dorm-bed-subtotal-hint');
    const minusBtn = document.getElementById('btn-dorm-bed-minus');
    const plusBtn = document.getElementById('btn-dorm-bed-plus');
    const summaryDormBeds = document.getElementById('summary-dorm-beds');
    const summaryPrice = document.getElementById('summary-price');
    const summaryTotal = document.getElementById('summary-total');

    if (countDisplay) countDisplay.textContent = `${newBeds} Bed${newBeds > 1 ? 's' : ''}`;
    if (countInput) countInput.value = newBeds;
    if (subtotalHint) subtotalHint.textContent = `${newBeds} bed${newBeds > 1 ? 's' : ''} × ₱460.00 = ₱${(newBeds * bedPrice).toFixed(2)} / night`;
    if (minusBtn) minusBtn.disabled = (newBeds <= 1);
    if (plusBtn) plusBtn.disabled = (newBeds >= remaining);
    if (summaryDormBeds) summaryDormBeds.textContent = `${newBeds} Bed${newBeds > 1 ? 's' : ''} (₱460.00/bed)`;
    if (summaryPrice) summaryPrice.textContent = `₱${(newBeds * bedPrice).toFixed(2)} (${newBeds} Bed${newBeds > 1 ? 's' : ''} @ ₱460.00)`;
    if (summaryTotal) summaryTotal.textContent = '₱' + activeBooking.total.toFixed(2);

    // Re-evaluate payment amounts (upfront vs balance)
    const selectedPayment = document.querySelector('input[name="payment"]:checked')?.value || 'paymongo';
    handlePaymentMethodChange(selectedPayment);
}

/**
 * Handles user date modifications directly from the Review & Confirm Reservation screen.
 * Validates check-in/out bounds, queries real-time room availability from the server,
 * updates nights and pricing breakdown, re-evaluates payment pathways, and gives instant feedback.
 */
async function handleSummaryDateChange() {
    const checkInInput = document.getElementById('summary-check-in-date');
    const checkOutInput = document.getElementById('summary-check-out-date');
    if (!checkInInput || !checkOutInput) return;

    const todayStr = safeFormatDate(new Date());
    let newCheckIn = checkInInput.value;
    let newCheckOut = checkOutInput.value;

    // Validate Check-in not in past
    if (!newCheckIn || newCheckIn < todayStr) {
        if (typeof window.showToast === 'function') {
            window.showToast('Check-in date cannot be in the past or outdated.', 'warning');
        }
        newCheckIn = todayStr;
        checkInInput.value = todayStr;
    }

    // Compute minimum check-out date (Check-in + 1 day)
    const cinDate = new Date(newCheckIn + 'T00:00:00');
    const minCheckOutDate = new Date(cinDate);
    minCheckOutDate.setDate(minCheckOutDate.getDate() + 1);
    const minCheckOutStr = safeFormatDate(minCheckOutDate);

    checkOutInput.min = minCheckOutStr;

    // If Check-out is missing or on/before Check-in, auto-adjust to check-in + 1
    if (!newCheckOut || newCheckOut <= newCheckIn) {
        newCheckOut = minCheckOutStr;
        checkOutInput.value = minCheckOutStr;
    }

    const activeBooking = window.pendingBooking || pendingBooking;
    if (!activeBooking || !activeBooking.roomId) return;

    const statusBadge = document.getElementById('summary-dates-badge');
    const statusBadgeText = document.getElementById('summary-dates-badge-text');
    const statusMsg = document.getElementById('summary-date-status-msg');
    const confirmBtn = document.getElementById('btn-confirm-booking');

    if (statusMsg) {
        statusMsg.innerHTML = '<i class="fas fa-spinner fa-spin text-brand-blue"></i> Checking room availability for new dates...';
        statusMsg.className = 'text-[11px] text-brand-blue font-medium flex items-center gap-1';
    }

    try {
        const availRes = await fetch('actions/book_action.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                action: 'get_room_availability', 
                room_id: activeBooking.roomId,
                check_in: newCheckIn,
                check_out: newCheckOut
            })
        });
        const availData = await availRes.json();

        if (availData.room && availData.room.status === 'Maintenance') {
            if (statusBadge) {
                statusBadge.className = 'text-xs font-semibold px-2.5 py-0.5 rounded-full bg-rose-100 text-rose-800 flex items-center gap-1';
            }
            if (statusBadgeText) {
                statusBadgeText.textContent = 'Under Maintenance';
            }
            if (statusMsg) {
                statusMsg.innerHTML = '<i class="fas fa-exclamation-triangle text-rose-500"></i> Room is under maintenance on these dates.';
                statusMsg.className = 'text-[11px] text-rose-600 font-medium flex items-center gap-1';
            }
            if (confirmBtn) {
                confirmBtn.disabled = true;
                confirmBtn.classList.add('opacity-50', 'cursor-not-allowed');
            }
            if (typeof window.showToast === 'function') {
                window.showToast('This room is under maintenance on the selected dates.', 'error');
            }
            return;
        }

        const isDorm = (parseInt(activeBooking.roomId) === 5 || activeBooking.isDorm || (availData.room && availData.room.type === 'Dorm'));

        if (isDorm) {
            const dormRemaining = (availData.remaining_beds !== undefined) ? parseInt(availData.remaining_beds, 10) : 8;
            activeBooking.remainingBeds = dormRemaining;
            activeBooking.isDorm = true;

            if (dormRemaining <= 0) {
                if (statusBadge) statusBadge.className = 'text-xs font-semibold px-2.5 py-0.5 rounded-full bg-rose-100 text-rose-800 flex items-center gap-1';
                if (statusBadgeText) statusBadgeText.textContent = 'All Beds Occupied';
                if (statusMsg) {
                    statusMsg.innerHTML = '<i class="fas fa-times-circle text-rose-500"></i> All 8 beds in Room 5 are occupied on selected dates.';
                    statusMsg.className = 'text-[11px] text-rose-600 font-medium flex items-center gap-1';
                }
                if (confirmBtn) {
                    confirmBtn.disabled = true;
                    confirmBtn.classList.add('opacity-50', 'cursor-not-allowed');
                }
                if (typeof window.showToast === 'function') {
                    window.showToast('All beds in Room 5 are occupied for these dates. Please choose different dates.', 'error');
                }
                return;
            }

            // Clamp bedsCount to remaining beds
            if (!activeBooking.bedsCount || activeBooking.bedsCount < 1) activeBooking.bedsCount = 1;
            if (activeBooking.bedsCount > dormRemaining) {
                activeBooking.bedsCount = dormRemaining;
                if (typeof window.showToast === 'function') {
                    window.showToast(`Selected beds adjusted to ${dormRemaining} to match remaining availability.`, 'info');
                }
            }

            const bedPrice = 460.00;
            activeBooking.bedPrice = bedPrice;
            activeBooking.pricePerNight = activeBooking.bedsCount * bedPrice;
        } else {
            if (!availData.is_currently_available) {
                if (statusBadge) {
                    statusBadge.className = 'text-xs font-semibold px-2.5 py-0.5 rounded-full bg-rose-100 text-rose-800 flex items-center gap-1';
                }
                if (statusBadgeText) {
                    statusBadgeText.textContent = 'Unavailable';
                }
                if (statusMsg) {
                    statusMsg.innerHTML = '<i class="fas fa-times-circle text-rose-500"></i> Occupied on selected dates. Please choose another date.';
                    statusMsg.className = 'text-[11px] text-rose-600 font-medium flex items-center gap-1';
                }
                if (confirmBtn) {
                    confirmBtn.disabled = true;
                    confirmBtn.classList.add('opacity-50', 'cursor-not-allowed');
                }
                if (typeof window.showToast === 'function') {
                    window.showToast('Room is occupied on these dates. Please adjust your stay dates.', 'error');
                }
                return;
            }

            // Update room price if returned from server
            if (availData.room && availData.room.price) {
                activeBooking.pricePerNight = parseFloat(availData.room.price);
            }
        }

        // Room is available! Re-enable confirm button
        if (confirmBtn) {
            confirmBtn.disabled = false;
            confirmBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        }

        // Recalculate duration & new total
        const nights = Math.max(1, Math.ceil((new Date(newCheckOut) - new Date(newCheckIn)) / (24*60*60*1000)));
        const total = activeBooking.isDorm 
            ? ((activeBooking.pricePerNight * nights) + 15.00)
            : ((activeBooking.pricePerNight * nights * (activeBooking.roomsCount || 1)) + 15.00);

        // Update active booking context
        activeBooking.checkIn = newCheckIn;
        activeBooking.checkOut = newCheckOut;
        activeBooking.nights = nights;
        activeBooking.total = total;
        pendingBooking = activeBooking;
        window.pendingBooking = activeBooking;

        // Update summary cards
        const nightsLabel = nights + (nights > 1 ? ' Nights' : ' Night');
        const summaryNights = document.getElementById('summary-nights');
        const summaryStayNightsText = document.getElementById('summary-stay-nights-text');
        const summaryTotal = document.getElementById('summary-total');

        if (summaryNights) summaryNights.textContent = nightsLabel;
        if (summaryStayNightsText) summaryStayNightsText.textContent = nightsLabel;
        if (summaryTotal) summaryTotal.textContent = '₱' + total.toFixed(2);

        // Update Dorm Bed Controls if Room 5
        if (activeBooking.isDorm) {
            const dormRemainingBadge = document.getElementById('dorm-remaining-beds-badge');
            const dormBedCountDisplay = document.getElementById('dorm-bed-count-display');
            const dormBedsInput = document.getElementById('dorm-beds-count-input');
            const dormBedSubtotalHint = document.getElementById('dorm-bed-subtotal-hint');
            const btnDormMinus = document.getElementById('btn-dorm-bed-minus');
            const btnDormPlus = document.getElementById('btn-dorm-bed-plus');
            const summaryDormBeds = document.getElementById('summary-dorm-beds');
            const summaryPrice = document.getElementById('summary-price');

            if (dormRemainingBadge) {
                dormRemainingBadge.textContent = `${activeBooking.remainingBeds} Bed${activeBooking.remainingBeds > 1 ? 's' : ''} Available (${availData.occupied_beds || (8 - activeBooking.remainingBeds)} Occupied / 8 Total)`;
            }
            if (dormBedCountDisplay) dormBedCountDisplay.textContent = `${activeBooking.bedsCount} Bed${activeBooking.bedsCount > 1 ? 's' : ''}`;
            if (dormBedsInput) dormBedsInput.value = activeBooking.bedsCount;
            if (dormBedSubtotalHint) dormBedSubtotalHint.textContent = `${activeBooking.bedsCount} bed${activeBooking.bedsCount > 1 ? 's' : ''} × ₱460.00 = ₱${(activeBooking.bedsCount * 460.00).toFixed(2)} / night`;
            if (btnDormMinus) btnDormMinus.disabled = (activeBooking.bedsCount <= 1);
            if (btnDormPlus) btnDormPlus.disabled = (activeBooking.bedsCount >= activeBooking.remainingBeds);
            if (summaryDormBeds) summaryDormBeds.textContent = `${activeBooking.bedsCount} Bed${activeBooking.bedsCount > 1 ? 's' : ''} (₱460.00/bed)`;
            if (summaryPrice) summaryPrice.textContent = `₱${(activeBooking.bedsCount * 460.00).toFixed(2)} (${activeBooking.bedsCount} Bed${activeBooking.bedsCount > 1 ? 's' : ''} @ ₱460.00)`;
        }

        // Update badges
        if (statusBadge) {
            statusBadge.className = 'text-xs font-semibold px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 flex items-center gap-1';
        }
        if (statusBadgeText) {
            statusBadgeText.textContent = 'Locked & Available';
        }
        if (statusMsg) {
            statusMsg.innerHTML = '<i class="fas fa-check-circle text-emerald-500"></i> Dates updated successfully (' + nightsLabel + ')';
            statusMsg.className = 'text-[11px] text-emerald-600 font-medium flex items-center gap-1';
        }

        // Pay at Hotel with 20% online security deposit is always available
        const hotelCard = document.getElementById('label-payment-hotel');
        const lockedNotice = document.getElementById('pay-at-hotel-locked-notice');
        const hotelRadio = document.getElementById('input-payment-hotel');

        if (hotelCard) hotelCard.classList.remove('hidden');
        if (lockedNotice) lockedNotice.classList.add('hidden');
        if (hotelRadio) hotelRadio.disabled = false;

        // Re-apply payment amounts (updates upfront and balance)
        const selectedPayment = document.querySelector('input[name="payment"]:checked')?.value || 'paymongo';
        handlePaymentMethodChange(selectedPayment);

        // Synchronize main search date pickers
        const mainCheckIn = document.getElementById('check-in-date');
        const mainCheckOut = document.getElementById('check-out-date');
        if (mainCheckIn) mainCheckIn.value = newCheckIn;
        if (mainCheckOut) mainCheckOut.value = newCheckOut;

        const homeCheckIn = document.getElementById('home-check-in-date');
        const homeCheckOut = document.getElementById('home-check-out-date');
        if (homeCheckIn) homeCheckIn.value = newCheckIn;
        if (homeCheckOut) homeCheckOut.value = newCheckOut;

        if (typeof window.showToast === 'function') {
            window.showToast(`Reservation dates updated: ${newCheckIn} to ${newCheckOut} (${nightsLabel})`, 'success');
        }
    } catch (error) {
        console.error('Error updating dates:', error);
        if (statusMsg) {
            statusMsg.innerHTML = '<i class="fas fa-exclamation-circle text-rose-500"></i> Error checking date availability.';
            statusMsg.className = 'text-[11px] text-rose-600 font-medium flex items-center gap-1';
        }
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
    const paymongoLabelEl = document.getElementById('paymongo-amount-label');
    const paymongoSubtextEl = document.getElementById('paymongo-amount-subtext');
    const paymongoBadgeEl = document.getElementById('paymongo-section-badge');
    const paymongoTitleEl = document.getElementById('paymongo-section-title');
    const timerHintEl = document.getElementById('summary-timer-hint');
    const btnText = document.getElementById('btn-confirm-text');

    const activeBooking = window.pendingBooking || pendingBooking;
    if (!activeBooking) return;

    const total = activeBooking.total;
    const deposit = Math.round(total * 0.20 * 100) / 100;
    const balance = Math.round((total - deposit) * 100) / 100;

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
        if (paymongoSection) paymongoSection.classList.remove('hidden');
        if (arrivalSection) arrivalSection.classList.remove('hidden');

        if (paymongoTitleEl) paymongoTitleEl.textContent = 'PayMongo 20% Deposit Checkout';
        if (paymongoBadgeEl) paymongoBadgeEl.innerHTML = '<i class="fas fa-shield-alt text-xs"></i> 20% Security Deposit';
        if (paymongoLabelEl) paymongoLabelEl.textContent = '20% Security Deposit (Paid Online):';
        if (paymongoAmountEl) paymongoAmountEl.textContent = '₱' + deposit.toFixed(2);
        if (paymongoSubtextEl) paymongoSubtextEl.textContent = `Pay 20% deposit online now to secure against no-show. Remaining 80% balance (₱${balance.toFixed(2)}) is payable at the hotel front desk upon check-in.`;

        if (upfrontEl) upfrontEl.textContent = '₱' + deposit.toFixed(2) + ' (20% Deposit)';
        if (balanceEl) balanceEl.textContent = '₱' + balance.toFixed(2) + ' (80% at Hotel)';
        if (timerHintEl) timerHintEl.innerHTML = '<i class="fas fa-shield-alt text-purple-600"></i> <span>20% online deposit secures your room hold against no-shows.</span>';
        if (btnText) btnText.innerHTML = '<i class="fas fa-lock mr-1"></i> Pay 20% Deposit Online (₱' + deposit.toFixed(2) + ') &rarr;';
    } else {
        // Default to PayMongo instant checkout
        if (paymongoCard) {
            paymongoCard.classList.add('border-brand-blue', 'bg-blue-50/40', 'shadow-sm');
            paymongoCard.classList.remove('border-gray-200', 'bg-white');
        }
        if (paymongoSection) paymongoSection.classList.remove('hidden');
        if (arrivalSection) arrivalSection.classList.add('hidden');

        if (paymongoTitleEl) paymongoTitleEl.textContent = 'PayMongo Secure Checkout';
        if (paymongoBadgeEl) paymongoBadgeEl.innerHTML = '<i class="fas fa-bolt text-xs"></i> Instant Confirmation';
        if (paymongoLabelEl) paymongoLabelEl.textContent = 'Amount to pay via PayMongo (100%):';
        if (paymongoAmountEl) paymongoAmountEl.textContent = '₱' + total.toFixed(2);
        if (paymongoSubtextEl) paymongoSubtextEl.textContent = 'Full online settlement with ₱0.00 balance upon check-in.';

        if (upfrontEl) upfrontEl.textContent = '₱' + total.toFixed(2);
        if (balanceEl) balanceEl.textContent = '₱0.00';
        if (timerHintEl) timerHintEl.innerHTML = '<i class="fas fa-bolt text-emerald-500"></i> <span>Automated online payment & instant confirmation.</span>';
        if (btnText) btnText.innerHTML = '<i class="fas fa-bolt mr-1"></i> Proceed to PayMongo Checkout (₱' + total.toFixed(2) + ') &rarr;';
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

        // Verify total guests against room capacity limits
        const roomObj = (window.appState && Array.isArray(window.appState.rooms))
            ? window.appState.rooms.find(r => r.id == activeBooking.roomId)
            : null;
        const roomMaxCap = roomObj ? (parseInt(roomObj.max_capacity || roomObj.capacity, 10) || 2) : 2;
        const totalCapAllowed = roomMaxCap * (activeBooking.roomsCount || 1);

        if ((activeBooking.guests || 1) > totalCapAllowed) {
            window.showToast(`The hotel does not accommodate ${activeBooking.guests} guests for this reservation as room capacity is limited to ${totalCapAllowed} guests.`, 'error');
            return;
        }

        const formData = new FormData();
        formData.append('action', 'create');
        if (currentUser && currentUser.id) {
            formData.append('user_id', currentUser.id);
        }
        formData.append('room_id', activeBooking.roomId);
        formData.append('rooms_count', activeBooking.roomsCount || 1);
        if (activeBooking.isDorm || activeBooking.roomId == 5) {
            const dormBeds = parseInt(document.getElementById('dorm-beds-count-input')?.value || activeBooking.bedsCount || 1, 10);
            activeBooking.bedsCount = dormBeds;
            formData.append('beds_count', dormBeds);
            formData.append('guests', dormBeds);
            formData.append('adults', dormBeds);
            formData.append('children', 0);
            formData.append('children_ages', '[]');
        } else {
            formData.append('beds_count', 1);
            formData.append('guests', activeBooking.guests || 1);
            formData.append('adults', activeBooking.adults || 1);
            formData.append('children', activeBooking.children || 0);
            formData.append('children_ages', JSON.stringify(activeBooking.childrenAges || []));
        }
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
        const deposit = Math.round((activeBooking.total || 0) * 0.20 * 100) / 100;
        if (confirmBtn) {
            confirmBtn.disabled = true;
            confirmBtn.innerHTML = paymentMethod === 'pay_at_hotel' 
                ? '<i class="fas fa-spinner fa-spin mr-2"></i> Initializing 20% Deposit Checkout...' 
                : '<i class="fas fa-spinner fa-spin mr-2"></i> Initializing PayMongo Checkout...';
        }

        try {
            const response = await fetch('actions/bookings_action.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();

            if (data.success) {
                if (data.checkout_url) {
                    const noticeText = (paymentMethod === 'pay_at_hotel' || data.is_deposit)
                        ? `Reservation Created! Ref: ${data.ref_id}. Redirecting to PayMongo to pay 20% deposit (₱${parseFloat(data.deposit_amount || deposit).toFixed(2)})...`
                        : `Reservation Created! Ref: ${data.ref_id}. Redirecting to PayMongo Checkout...`;
                    window.showToast(noticeText, 'success');
                    setTimeout(() => {
                        window.location.href = data.checkout_url;
                    }, 700);
                    return;
                }

                window.showToast(`Reservation Created! Ref: ${data.ref_id}`, 'success');
                if (typeof window.showVoucherModal === 'function') {
                    window.showVoucherModal(data.ref_id, activeBooking, data);
                } else if (typeof window.showInvoiceModal === 'function') {
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
                confirmBtn.innerHTML = paymentMethod === 'pay_at_hotel'
                    ? '<i class="fas fa-lock mr-2"></i> <span id="btn-confirm-text">Pay 20% Deposit Online (₱' + deposit.toFixed(2) + ') &rarr;</span>'
                    : '<i class="fas fa-bolt mr-2"></i> <span id="btn-confirm-text">Proceed to PayMongo Checkout &rarr;</span>';
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
window.handleSummaryDateChange = handleSummaryDateChange;
window.updateDormBedCount = updateDormBedCount;

